<?php defined('BASEPATH') or exit('No direct script access allowed');

function scps_ensure_meta_table()
{
    $CI = &get_instance();
    $table = db_prefix() . 'sc_sales_meta';

    if (!$CI->db->table_exists($table)) {
        $CI->db->query("CREATE TABLE `{$table}` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `rel_type` VARCHAR(30) NOT NULL,
          `rel_id` INT UNSIGNED NOT NULL,
          `deposit_percent` DECIMAL(7,2) NOT NULL DEFAULT 100.00,
          `payment_stage` VARCHAR(30) NOT NULL DEFAULT 'full',
          `discount_reason` VARCHAR(100) NULL,
          `discount_title` VARCHAR(191) NULL,
          `discount_description` TEXT NULL,
          `discount_mode` VARCHAR(20) NOT NULL DEFAULT 'percent',
          `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
          `contract_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
          `due_now` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
          `remaining_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
          `auto_create_balance` TINYINT(1) NOT NULL DEFAULT 1,
          `created_at` DATETIME NULL,
          `updated_at` DATETIME NULL,
          PRIMARY KEY (`id`), UNIQUE KEY `rel_unique` (`rel_type`,`rel_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    // Repair tables created by earlier builds that used different column names.
    $fields = $CI->db->field_data($table);
    $existing = [];
    foreach ($fields as $field) {
        $existing[$field->name] = true;
    }
    $columns = [
        'deposit_percent' => "DECIMAL(7,2) NOT NULL DEFAULT 100.00",
        'payment_stage' => "VARCHAR(30) NOT NULL DEFAULT 'full'",
        'discount_reason' => "VARCHAR(100) NULL",
        'discount_title' => "VARCHAR(191) NULL",
        'discount_description' => "TEXT NULL",
        'discount_mode' => "VARCHAR(20) NOT NULL DEFAULT 'percent'",
        'discount_amount' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
        'contract_total' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
        'due_now' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
        'remaining_balance' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
        'auto_create_balance' => "TINYINT(1) NOT NULL DEFAULT 1",
        'created_at' => "DATETIME NULL",
        'updated_at' => "DATETIME NULL",
    ];
    foreach ($columns as $name => $definition) {
        if (!isset($existing[$name])) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `{$name}` {$definition}");
        }
    }

    // Preserve values from legacy columns when they exist.
    if (isset($existing['down_payment_percent'])) {
        $CI->db->query("UPDATE `{$table}` SET `deposit_percent`=`down_payment_percent` WHERE (`deposit_percent`=100 OR `deposit_percent`=0) AND `down_payment_percent` IS NOT NULL");
    }
    if (isset($existing['discount_category'])) {
        $CI->db->query("UPDATE `{$table}` SET `discount_reason`=`discount_category` WHERE (`discount_reason` IS NULL OR `discount_reason`='') AND `discount_category` IS NOT NULL");
    }
}

function scps_extract_meta_from_data(&$data)
{
    $keys = [
        'scps_deposit_percent', 'scps_payment_stage', 'scps_discount_reason',
        'scps_discount_title', 'scps_discount_description', 'scps_discount_mode',
        'scps_discount_amount', 'scps_contract_total', 'scps_due_now',
        'scps_remaining_balance', 'scps_auto_create_balance'
    ];
    $meta = [];
    foreach ($keys as $key) {
        if (array_key_exists($key, $data)) {
            $meta[$key] = $data[$key];
            unset($data[$key]);
        }
    }
    return $meta;
}

function scps_save_meta($relType, $relId, $meta)
{
    if (!$relId || empty($meta)) {
        return;
    }
    scps_ensure_meta_table();
    $CI = &get_instance();
    $table = db_prefix() . 'sc_sales_meta';
    $row = [
        'rel_type' => $relType,
        'rel_id' => (int) $relId,
        'deposit_percent' => max(0, min(100, (float) ($meta['scps_deposit_percent'] ?? 100))),
        'payment_stage' => (string) ($meta['scps_payment_stage'] ?? 'full'),
        'discount_reason' => (string) ($meta['scps_discount_reason'] ?? ''),
        'discount_title' => (string) ($meta['scps_discount_title'] ?? ''),
        'discount_description' => (string) ($meta['scps_discount_description'] ?? ''),
        'discount_mode' => (string) ($meta['scps_discount_mode'] ?? 'percent'),
        'discount_amount' => (float) ($meta['scps_discount_amount'] ?? 0),
        'contract_total' => (float) ($meta['scps_contract_total'] ?? 0),
        'due_now' => (float) ($meta['scps_due_now'] ?? 0),
        'remaining_balance' => (float) ($meta['scps_remaining_balance'] ?? 0),
        'auto_create_balance' => isset($meta['scps_auto_create_balance']) ? 1 : 0,
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    $existing = $CI->db->where(['rel_type' => $relType, 'rel_id' => (int) $relId])->get($table)->row();
    if ($existing) {
        $CI->db->where('id', $existing->id)->update($table, $row);
    } else {
        $row['created_at'] = date('Y-m-d H:i:s');
        $CI->db->insert($table, $row);
    }
}

function scps_get_meta($relType, $relId)
{
    if (!$relId) {
        return null;
    }
    scps_ensure_meta_table();
    $CI = &get_instance();
    return $CI->db->where(['rel_type' => $relType, 'rel_id' => (int) $relId])->get(db_prefix() . 'sc_sales_meta')->row();
}


if (!function_exists('scps_normalize_document_fields')) {
    function scps_normalize_document_fields(array &$data)
    {
        $number = static function ($value) {
            $value = preg_replace('/[^0-9,\.\-]/', '', (string) $value);
            if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
                $value = strrpos($value, '.') > strrpos($value, ',')
                    ? str_replace(',', '', $value)
                    : str_replace(',', '.', str_replace('.', '', $value));
            } else {
                $value = str_replace(',', '', $value);
            }
            return is_numeric($value) ? (float) $value : 0.0;
        };

        $mode = isset($data['sc_down_payment_mode']) && $data['sc_down_payment_mode'] === 'fixed'
            ? 'fixed'
            : 'percent';

        $data['sc_down_payment_mode'] = $mode;
        $data['sc_down_payment_percent'] = max(0, min(100, $number($data['sc_down_payment_percent'] ?? 100)));
        $data['sc_down_payment_amount'] = max(0, $number($data['sc_down_payment_amount'] ?? 0));
        $data['sc_contract_total'] = max(0, $number($data['sc_contract_total'] ?? 0));
        $data['sc_remaining_balance'] = max(0, $number($data['sc_remaining_balance'] ?? 0));
        $data['sc_discount_reason'] = trim((string) ($data['sc_discount_reason'] ?? ''));

        foreach (['subtotal', 'total', 'discount_total', 'discount_percent', 'adjustment'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $number($data[$field]);
            }
        }
    }
}

if (!function_exists('scps_sync_document_meta')) {
    function scps_sync_document_meta($relType, $relId, $document)
    {
        if (!$relId || !$document) {
            return;
        }
        $percent = max(0, min(100, (float) ($document->sc_down_payment_percent ?? 100)));
        $dueNow = max(0, (float) ($document->total ?? 0));
        $contractTotal = max(0, (float) ($document->sc_contract_total ?? 0));
        if ($contractTotal <= 0) {
            $contractTotal = $percent > 0 && $percent < 100 ? ($dueNow / ($percent / 100)) : $dueNow;
        }
        $remaining = max(0, $contractTotal - $dueNow);
        scps_save_meta($relType, $relId, [
            'scps_deposit_percent' => $percent,
            'scps_payment_stage' => $percent < 100 ? 'partial' : 'full',
            'scps_discount_reason' => (string) ($document->sc_discount_reason ?? ''),
            'scps_discount_mode' => ((float) ($document->discount_percent ?? 0)) > 0 ? 'percent' : 'fixed',
            'scps_discount_amount' => (float) ($document->discount_total ?? 0),
            'scps_contract_total' => $contractTotal,
            'scps_due_now' => $dueNow,
            'scps_remaining_balance' => $remaining,
            'scps_auto_create_balance' => 1,
        ]);
    }
}
