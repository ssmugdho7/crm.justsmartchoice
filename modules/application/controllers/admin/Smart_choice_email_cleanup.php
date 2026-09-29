<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_email_cleanup extends AdminController
{
    private $table;
    private $backup_table;
    private $id_col;
    private $lang_col;
    private $message_col;
    private $subject_col;
    private $slug_col;
    private $type_col;
    private $name_col;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->table = db_prefix() . 'emailtemplates';
        $this->backup_table = db_prefix() . 'smart_choice_email_template_backups';
        $this->detect_columns();
    }

    public function index()
    {
        $this->preview();
    }

    public function preview()
    {
        $rows = $this->scan_templates();
        $this->render_page('Preview Only', $rows, false, '');
    }

    public function apply()
    {
        $rows = $this->scan_templates();
        $result = $this->apply_cleanup($rows);
        $rows_after = $this->scan_templates();
        $this->render_page('Cleanup Applied', $rows_after, true, $result);
    }

    private function detect_columns()
    {
        if (!$this->db->table_exists($this->table)) {
            show_error('Email template table not found: ' . html_escape($this->table));
        }

        $fields = $this->db->list_fields($this->table);
        $pick = function ($choices) use ($fields) {
            foreach ($choices as $choice) {
                if (in_array($choice, $fields, true)) {
                    return $choice;
                }
            }
            return null;
        };

        $this->id_col      = $pick(['emailtemplateid', 'id']);
        $this->lang_col    = $pick(['language', 'lang']);
        $this->message_col = $pick(['message', 'emailmessage', 'content', 'body']);
        $this->subject_col = $pick(['subject', 'emailsubject']);
        $this->slug_col    = $pick(['slug']);
        $this->type_col    = $pick(['type']);
        $this->name_col    = $pick(['name']);

        if (!$this->id_col || !$this->lang_col || !$this->message_col) {
            show_error('Required email template columns were not detected. Required: ID, language, and message/body column.');
        }
    }

    private function scan_templates()
    {
        $templates = $this->db->get($this->table)->result_array();
        $out = [];

        foreach ($templates as $row) {
            $message = (string)($row[$this->message_col] ?? '');
            $language = strtolower(trim((string)($row[$this->lang_col] ?? '')));
            $is_mixed = $this->contains_english_and_spanish($message);
            $needs_logo = stripos($message, '{logo_image_with_url}') === false;
            $needs_signature = stripos($message, '{email_signature}') === false;
            $needs_brand = stripos($message, 'smart-choice-email-card') === false;

            if ($is_mixed || $needs_logo || $needs_signature || $needs_brand) {
                $out[] = [
                    'id' => $row[$this->id_col],
                    'language' => $language,
                    'name' => $this->get_label($row),
                    'slug' => $this->slug_col ? (string)($row[$this->slug_col] ?? '') : '',
                    'type' => $this->type_col ? (string)($row[$this->type_col] ?? '') : '',
                    'is_mixed' => $is_mixed,
                    'needs_logo' => $needs_logo,
                    'needs_signature' => $needs_signature,
                    'needs_brand' => $needs_brand,
                    'row' => $row,
                ];
            }
        }

        return $out;
    }

    private function apply_cleanup($items)
    {
        $this->ensure_backup_table();
        $changed = 0;
        $created_spanish = 0;
        $backup_run = date('YmdHis');

        foreach ($items as $item) {
            $row = $item['row'];
            $message = (string)($row[$this->message_col] ?? '');
            $lang = strtolower(trim((string)($row[$this->lang_col] ?? '')));

            $english_body = $message;
            $spanish_body = '';

            if ($item['is_mixed']) {
                list($english_body, $spanish_body) = $this->split_mixed_body($message);
            }

            if ($this->is_english_language($lang)) {
                $this->backup_row($backup_run, $row);
                $new_english = $this->build_branded_body($english_body, 'english');
                $this->db->where($this->id_col, $row[$this->id_col]);
                $this->db->update($this->table, [$this->message_col => $new_english]);
                $changed++;

                if ($spanish_body !== '') {
                    $spanish_row = $this->find_matching_language_row($row, 'spanish');
                    $new_spanish = $this->build_branded_body($spanish_body, 'spanish');
                    if ($spanish_row) {
                        $this->backup_row($backup_run, $spanish_row);
                        $this->db->where($this->id_col, $spanish_row[$this->id_col]);
                        $this->db->update($this->table, [$this->message_col => $new_spanish]);
                        $changed++;
                    } else {
                        $insert = $row;
                        unset($insert[$this->id_col]);
                        $insert[$this->lang_col] = 'spanish';
                        $insert[$this->message_col] = $new_spanish;
                        if ($this->subject_col && isset($insert[$this->subject_col])) {
                            $insert[$this->subject_col] = $this->translate_subject_fallback((string)$insert[$this->subject_col]);
                        }
                        $this->db->insert($this->table, $insert);
                        $created_spanish++;
                    }
                }
            } elseif ($this->is_spanish_language($lang)) {
                $this->backup_row($backup_run, $row);
                $source = $spanish_body !== '' ? $spanish_body : $message;
                $new_spanish = $this->build_branded_body($source, 'spanish');
                $this->db->where($this->id_col, $row[$this->id_col]);
                $this->db->update($this->table, [$this->message_col => $new_spanish]);
                $changed++;
            } else {
                $this->backup_row($backup_run, $row);
                $new_body = $this->build_branded_body($message, 'english');
                $this->db->where($this->id_col, $row[$this->id_col]);
                $this->db->update($this->table, [$this->message_col => $new_body]);
                $changed++;
            }
        }

        return 'Backup run: ' . $backup_run . '. Updated records: ' . $changed . '. Created Spanish records: ' . $created_spanish . '.';
    }

    private function contains_english_and_spanish($html)
    {
        $plain = strtolower(strip_tags($html));
        $has_english = (strpos($plain, 'dear ') !== false || strpos($plain, 'thank you') !== false || strpos($plain, 'estimate information') !== false || strpos($plain, 'view estimate') !== false);
        $has_spanish = (strpos($plain, 'estimado ') !== false || strpos($plain, 'gracias por') !== false || strpos($plain, 'información del') !== false || strpos($plain, 'ver presupuesto') !== false);
        return $has_english && $has_spanish;
    }

    private function split_mixed_body($html)
    {
        $markers = [
            '<p style="margin: 0 0 20px 0; font-size: 22px; font-weight: bold; color: #1f3c88;">Estimado',
            '<p style="margin:0 0 20px 0;font-size:22px;font-weight:bold;color:#1f3c88;">Estimado',
            '>Estimado ',
            'Estimado {contact_firstname}',
            'Estimado&nbsp;{contact_firstname}',
        ];

        $pos = false;
        foreach ($markers as $marker) {
            $p = stripos($html, $marker);
            if ($p !== false) {
                $pos = $p;
                break;
            }
        }

        if ($pos === false) {
            return [$html, ''];
        }

        $english = substr($html, 0, $pos);
        $spanish = substr($html, $pos);
        $english = preg_replace('/<hr\b[^>]*>/i', '', $english);
        $spanish = preg_replace('/^.*?(<p[^>]*>\s*Estimado|Estimado)/is', '$1', $spanish, 1);

        return [trim($english), trim($spanish)];
    }

    private function build_branded_body($body, $language)
    {
        $body = $this->strip_outer_wrapper($body);
        $body = $this->remove_mixed_language_section($body, $language);
        $body = $this->remove_footer_blocks($body);
        $body = $this->remove_existing_logo($body);
        $body = $this->remove_existing_signature($body);
        $body = trim($body);

        $logo = '<div style="text-align:center; margin:0 0 22px 0;"><div style="display:inline-block; max-width:260px; max-height:100px; overflow:hidden; line-height:0;">{logo_image_with_url}</div></div>';
        $signature = '<div style="margin-top:28px; padding-top:18px; border-top:1px solid #e5e7eb; font-size:15px; line-height:1.7; color:#374151;">{email_signature}</div>';

        return '<div class="smart-choice-email-card" style="font-family:Arial, Helvetica, sans-serif; color:#1f2937; max-width:640px; margin:0 auto; padding:0;">'
            . '<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; padding:28px 30px; box-shadow:0 4px 18px rgba(31,60,136,0.08);">'
            . $logo
            . '<div style="font-size:16px; line-height:1.7;">'
            . $this->normalize_buttons_and_boxes($body)
            . '</div>'
            . $signature
            . '</div>'
            . '</div>';
    }

    private function normalize_buttons_and_boxes($html)
    {
        $html = preg_replace('/<a\s+([^>]*style=")([^"]*)"/i', '<a $1$2; border-radius:6px; text-align:center; line-height:1.2;"', $html);
        $html = preg_replace('/(<a\s+(?![^>]*style=)[^>]*>)/i', '$1', $html);
        $html = preg_replace('/border-left:\s*4px\s+solid\s+#d96b00;\s*background:\s*#f8f8f8;/i', 'border-left:4px solid #d96b00; background:#f8f8f8; border-radius:6px;', $html);
        $html = str_replace('VIEW ESTIMATE', 'VIEW ESTIMATE', $html);
        $html = str_replace('VER PRESUPUESTO', 'VER PRESUPUESTO', $html);
        return $html;
    }

    private function strip_outer_wrapper($html)
    {
        $html = preg_replace('/<div class="smart-choice-email-card"[\s\S]*?<div[^>]*>\s*/i', '', $html, 1);
        $html = preg_replace('/<\/div>\s*<\/div>\s*$/i', '', $html, 1);
        return $html;
    }

    private function remove_mixed_language_section($html, $language)
    {
        if (!$this->contains_english_and_spanish($html)) {
            return $html;
        }
        list($english, $spanish) = $this->split_mixed_body($html);
        return $language === 'spanish' ? $spanish : $english;
    }

    private function remove_footer_blocks($html)
    {
        $html = preg_replace('/<hr[^>]*>\s*<p[^>]*>\s*Visit\s+us[\s\S]*$/i', '', $html);
        $html = preg_replace('/<hr[^>]*>\s*<div[^>]*>\s*Visit\s+us[\s\S]*$/i', '', $html);
        $html = preg_replace('/<p[^>]*>\s*Visit\s+us[\s\S]*$/i', '', $html);
        $html = preg_replace('/<div[^>]*>\s*Visit\s+us[\s\S]*$/i', '', $html);
        $html = preg_replace('/<hr[^>]*>\s*<p[^>]*>\s*Visítenos[\s\S]*$/i', '', $html);
        return $html;
    }

    private function remove_existing_logo($html)
    {
        $html = preg_replace('/<div[^>]*>\s*\{logo_image_with_url\}\s*<\/div>/i', '', $html);
        $html = str_ireplace('{dark_logo_image_with_url}', '', $html);
        return $html;
    }

    private function remove_existing_signature($html)
    {
        $html = preg_replace('/<p[^>]*>\s*\{email_signature\}\s*<\/p>/i', '', $html);
        $html = preg_replace('/<div[^>]*>\s*\{email_signature\}\s*<\/div>/i', '', $html);
        $html = str_ireplace('{email_signature}', '', $html);
        return $html;
    }

    private function find_matching_language_row($source_row, $language)
    {
        $this->db->from($this->table);
        $this->db->where($this->lang_col, $language);

        if ($this->slug_col && !empty($source_row[$this->slug_col])) {
            $this->db->where($this->slug_col, $source_row[$this->slug_col]);
        } elseif ($this->type_col && !empty($source_row[$this->type_col]) && $this->name_col && !empty($source_row[$this->name_col])) {
            $this->db->where($this->type_col, $source_row[$this->type_col]);
            $this->db->where($this->name_col, $source_row[$this->name_col]);
        } elseif ($this->name_col && !empty($source_row[$this->name_col])) {
            $this->db->where($this->name_col, $source_row[$this->name_col]);
        } else {
            return null;
        }

        return $this->db->get()->row_array();
    }

    private function translate_subject_fallback($subject)
    {
        $map = [
            'Estimate Accepted' => 'Presupuesto Aceptado',
            'Estimate' => 'Presupuesto',
            'Invoice' => 'Factura',
            'Proposal' => 'Propuesta',
            'Payment' => 'Pago',
            'Contract' => 'Contrato',
            'Project' => 'Proyecto',
        ];
        return strtr($subject, $map);
    }

    private function get_label($row)
    {
        if ($this->name_col && !empty($row[$this->name_col])) {
            return (string)$row[$this->name_col];
        }
        if ($this->slug_col && !empty($row[$this->slug_col])) {
            return (string)$row[$this->slug_col];
        }
        if ($this->type_col && !empty($row[$this->type_col])) {
            return (string)$row[$this->type_col];
        }
        return 'Email Template #' . $row[$this->id_col];
    }

    private function is_english_language($language)
    {
        return in_array($language, ['english', 'en', 'en_us', 'en-us'], true);
    }

    private function is_spanish_language($language)
    {
        return in_array($language, ['spanish', 'es', 'es_es', 'es-es'], true);
    }

    private function ensure_backup_table()
    {
        if ($this->db->table_exists($this->backup_table)) {
            return;
        }
        $this->db->query('CREATE TABLE `' . $this->backup_table . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `backup_run` VARCHAR(32) NOT NULL,
            `template_id` INT(11) NULL,
            `template_data` LONGTEXT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `backup_run` (`backup_run`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    private function backup_row($backup_run, $row)
    {
        $this->db->insert($this->backup_table, [
            'backup_run' => $backup_run,
            'template_id' => isset($row[$this->id_col]) ? (int)$row[$this->id_col] : null,
            'template_data' => json_encode($row),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function render_page($title, $rows, $applied, $result)
    {
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Smart Choice Email Cleanup</title>';
        echo '<style>body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;color:#111827;padding:24px}.card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;max-width:1200px;margin:auto}.btn{display:inline-block;background:#008000;color:#fff;text-decoration:none;padding:9px 14px;border-radius:6px;font-weight:700;margin-right:8px}.btn.alt{background:#1f3c88}table{width:100%;border-collapse:collapse;margin-top:18px}th,td{border-bottom:1px solid #e5e7eb;padding:9px;text-align:left;font-size:13px}th{background:#f8fafc}.yes{color:#008000;font-weight:700}.no{color:#777}.warn{background:#fff7ed;border-left:4px solid #d96b00;padding:12px;border-radius:6px;margin:12px 0}</style>';
        echo '</head><body><div class="card">';
        echo '<h1 style="margin-top:0;color:#1f3c88;">Smart Choice Email Cleanup</h1>';
        echo '<p>This tool separates mixed English and Spanish email templates, applies the Smart Choice branded body style, adds the logo merge field at the top, and adds the email signature merge field at the bottom.</p>';
        echo '<div class="warn"><strong>Important:</strong> The mixed language content is stored in the database table <code>' . html_escape($this->table) . '</code>, not in a language folder. This tool updates those database records and creates backups before writing.</div>';
        if ($result !== '') { echo '<p class="warn">' . html_escape($result) . '</p>'; }
        echo '<p><a class="btn alt" href="' . admin_url('smart_choice_email_cleanup/preview') . '">Preview</a><a class="btn" href="' . admin_url('smart_choice_email_cleanup/apply') . '">Apply Cleanup</a></p>';
        echo '<h2>' . html_escape($title) . '</h2>';
        echo '<p>Templates needing cleanup: <strong>' . count($rows) . '</strong></p>';
        echo '<table><thead><tr><th>ID</th><th>Language</th><th>Template</th><th>Mixed Languages</th><th>Needs Logo</th><th>Needs Signature</th><th>Needs Brand Wrapper</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            echo '<tr>';
            echo '<td>' . html_escape($r['id']) . '</td>';
            echo '<td>' . html_escape($r['language']) . '</td>';
            echo '<td>' . html_escape($r['name']) . '</td>';
            echo '<td class="' . ($r['is_mixed'] ? 'yes' : 'no') . '">' . ($r['is_mixed'] ? 'Yes' : 'No') . '</td>';
            echo '<td class="' . ($r['needs_logo'] ? 'yes' : 'no') . '">' . ($r['needs_logo'] ? 'Yes' : 'No') . '</td>';
            echo '<td class="' . ($r['needs_signature'] ? 'yes' : 'no') . '">' . ($r['needs_signature'] ? 'Yes' : 'No') . '</td>';
            echo '<td class="' . ($r['needs_brand'] ? 'yes' : 'no') . '">' . ($r['needs_brand'] ? 'Yes' : 'No') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div></body></html>';
    }
}
