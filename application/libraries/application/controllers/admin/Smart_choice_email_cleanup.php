<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_email_cleanup extends AdminController
{
    private $spanishTokens = [
        'Estimado', 'Estimada', 'Presupuesto', 'Información', 'Gracias', 'Proyecto',
        'Factura', 'Contrato', 'Puede revisar', 'Nuestro equipo', 'No dude',
        'Será un placer', 'Esperamos', 'Número de', 'Estado:'
    ];

    private $englishTokens = [
        'Dear', 'Estimate', 'Information', 'Thank you', 'Project', 'Invoice',
        'Contract', 'You may review', 'Our team', 'Please do not hesitate',
        'We look forward', 'Number:', 'Status:'
    ];

    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            access_denied('Smart Choice Email Cleanup');
        }
    }

    public function index()
    {
        $this->preview();
    }

    public function preview()
    {
        $rows = $this->find_bilingual_english_rows();
        $this->output_report($rows, false);
    }

    public function apply()
    {
        $rows = $this->find_bilingual_english_rows();
        $fixed = [];

        foreach ($rows as $row) {
            $split = $this->split_message($row->message);
            if (!$split) {
                continue;
            }

            $backupFile = APPPATH . 'cache/smart_choice_email_template_backup_' . date('Ymd_His') . '_' . (int) $row->emailtemplateid . '.html';
            @file_put_contents($backupFile, $row->message);

            $this->db->where('emailtemplateid', $row->emailtemplateid);
            $this->db->update(db_prefix() . 'emailtemplates', [
                'message' => $split['english'],
            ]);

            $spanishRow = $this->find_spanish_row($row);

            if ($spanishRow) {
                $this->db->where('emailtemplateid', $spanishRow->emailtemplateid);
                $this->db->update(db_prefix() . 'emailtemplates', [
                    'message' => $split['spanish'],
                ]);
            } else {
                $new = (array) $row;
                unset($new['emailtemplateid']);
                $new['language'] = 'spanish';
                $new['message'] = $split['spanish'];
                $new['subject'] = isset($new['subject']) ? $this->spanish_subject($new['subject']) : '';
                $this->db->insert(db_prefix() . 'emailtemplates', $new);
            }

            $fixed[] = [
                'id' => $row->emailtemplateid,
                'slug' => $row->slug,
                'name' => $row->name,
                'backup' => $backupFile,
            ];
        }

        $this->output_report($fixed, true);
    }

    private function find_bilingual_english_rows()
    {
        if (!$this->db->table_exists(db_prefix() . 'emailtemplates')) {
            return [];
        }

        $this->db->from(db_prefix() . 'emailtemplates');
        $this->db->where('language', 'english');
        $this->db->like('message', '<hr', 'both');
        $query = $this->db->get();
        $rows = $query ? $query->result() : [];

        $matched = [];
        foreach ($rows as $row) {
            if ($this->split_message($row->message)) {
                $matched[] = $row;
            }
        }

        return $matched;
    }

    private function split_message($message)
    {
        if (stripos($message, '<hr') === false) {
            return false;
        }

        $parts = preg_split('/<hr\b[^>]*>/i', $message);
        if (!$parts || count($parts) < 2) {
            return false;
        }

        $english = trim($parts[0]);
        $spanish = trim(implode('<hr>', array_slice($parts, 1)));

        if ($english === '' || $spanish === '') {
            return false;
        }

        $spanishScore = $this->score($spanish, $this->spanishTokens);
        $englishScore = $this->score($english, $this->englishTokens);

        if ($spanishScore < 2 || $englishScore < 1) {
            return false;
        }

        return [
            'english' => $english,
            'spanish' => $spanish,
        ];
    }

    private function score($text, $tokens)
    {
        $score = 0;
        foreach ($tokens as $token) {
            if (stripos($text, $token) !== false) {
                $score++;
            }
        }
        return $score;
    }

    private function find_spanish_row($row)
    {
        $this->db->from(db_prefix() . 'emailtemplates');
        $this->db->where('language', 'spanish');
        $this->db->group_start();
        if (isset($row->slug)) {
            $this->db->or_where('slug', $row->slug);
        }
        if (isset($row->type)) {
            $this->db->or_where('type', $row->type);
        }
        $this->db->group_end();
        $q = $this->db->get();
        return $q ? $q->row() : null;
    }

    private function spanish_subject($subject)
    {
        $replacements = [
            'Estimate' => 'Presupuesto',
            'Invoice' => 'Factura',
            'Proposal' => 'Propuesta',
            'Contract' => 'Contrato',
            'Accepted' => 'Aceptado',
            'Declined' => 'Rechazado',
            'Reminder' => 'Recordatorio',
            'Payment' => 'Pago',
            'Project' => 'Proyecto',
        ];

        return strtr($subject, $replacements);
    }

    private function output_report($rows, $applied)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Smart Choice Email Cleanup</title>';
        echo '<style>body{font-family:Arial,Helvetica,sans-serif;padding:24px;color:#263238}.box{max-width:980px;margin:auto;border:1px solid #dfe7f3;border-radius:6px;padding:22px}h1{color:#1f3c88}table{width:100%;border-collapse:collapse}td,th{border-bottom:1px solid #e5e7eb;padding:8px;text-align:left}.btn{display:inline-block;background:#008000;color:white;padding:10px 14px;border-radius:6px;text-decoration:none;font-weight:bold}.warn{background:#fff7ed;border-left:4px solid #d96b00;padding:12px;margin:12px 0}</style>';
        echo '</head><body><div class="box">';
        echo '<h1>Smart Choice Email Cleanup</h1>';

        if ($applied) {
            echo '<p><strong>Cleanup applied.</strong> English and Spanish email bodies were split into separate Perfex template language records.</p>';
        } else {
            echo '<p>This preview finds English email template records that contain both English and Spanish separated by an HR line.</p>';
            echo '<div class="warn">Review this list first. Click Apply only after confirming these are bilingual templates that need separation.</div>';
            echo '<p><a class="btn" href="' . admin_url('smart_choice_email_cleanup/apply') . '">Apply Cleanup</a></p>';
        }

        echo '<p>Total found: <strong>' . count($rows) . '</strong></p>';
        echo '<table><thead><tr><th>ID</th><th>Slug</th><th>Name</th><th>Backup</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            if (is_array($row)) {
                echo '<tr><td>' . html_escape($row['id']) . '</td><td>' . html_escape($row['slug']) . '</td><td>' . html_escape($row['name']) . '</td><td>' . html_escape($row['backup']) . '</td></tr>';
            } else {
                echo '<tr><td>' . html_escape($row->emailtemplateid) . '</td><td>' . html_escape($row->slug) . '</td><td>' . html_escape($row->name) . '</td><td>Not applied yet</td></tr>';
            }
        }
        echo '</tbody></table>';
        echo '<p style="margin-top:20px"><a href="' . admin_url('settings?group=email_templates') . '">Return To Email Templates</a></p>';
        echo '</div></body></html>';
        exit;
    }
}
