<?php

defined('BASEPATH') or exit('No direct script access allowed');


/**
 * Return a translated CRM label, but never expose a raw language key in a
 * customer document. Customized CRM language packs can omit legacy sales-table
 * keys; StyleFlow falls back to clean localized/plain labels in that case.
 */
function styleflow_safe_lang($keys, $fallback)
{
    foreach ((array) $keys as $key) {
        $translated = _l($key);
        if (is_string($translated) && $translated !== '' && $translated !== $key && strpos($translated, '_') === false) {
            return $translated;
        }
    }
    return $fallback;
}

function styleflow_item_table_heading($type, $column)
{
    $type = strtolower((string) $type);
    $map = [
        'number'   => [['the_number_sign'], '#'],
        'item'     => [[ $type . '_table_item_heading', 'item' ], styleflow_safe_lang(['item'], 'Item')],
        'quantity' => [[ $type . '_table_quantity_heading', 'quantity' ], styleflow_safe_lang(['quantity'], 'Quantity')],
        'rate'     => [[ $type . '_table_rate_heading', 'rate' ], styleflow_safe_lang(['rate'], 'Rate')],
        'tax'      => [[ $type . '_table_tax_heading', 'tax' ], styleflow_safe_lang(['tax'], 'Tax')],
        'amount'   => [[ $type . '_table_amount_heading', 'amount' ], styleflow_safe_lang(['amount'], 'Amount')],
    ];
    if (!isset($map[$column])) {
        return ucfirst(str_replace('_', ' ', (string) $column));
    }
    return styleflow_safe_lang($map[$column][0], $map[$column][1]);
}

function styleflow_templates_table()
{
    return db_prefix() . 'styleflow_templates';
}

function styleflow_install_or_repair_schema()
{
    $CI = &get_instance();
    $table = styleflow_templates_table();

    if (!$CI->db->table_exists($table)) {
        $CI->db->query("CREATE TABLE `{$table}` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `slug` VARCHAR(100) NOT NULL,
            `name` VARCHAR(150) NOT NULL,
            `primary_color` VARCHAR(7) NOT NULL DEFAULT '#3598DB',
            `secondary_color` VARCHAR(7) NOT NULL DEFAULT '#F28C28',
            `accent_color` VARCHAR(7) NOT NULL DEFAULT '#169179',
            `text_color` VARCHAR(7) NOT NULL DEFAULT '#333333',
            `font_family` VARCHAR(50) NOT NULL DEFAULT 'helvetica',
            `table_style` VARCHAR(30) NOT NULL DEFAULT 'rounded',
            `header_style` VARCHAR(30) NOT NULL DEFAULT 'band',
            `staff_photo_mode` VARCHAR(20) NOT NULL DEFAULT 'none',
            `selected_staff_id` INT UNSIGNED NOT NULL DEFAULT 0,
            `available_invoice` TINYINT(1) NOT NULL DEFAULT 1,
            `available_estimate` TINYINT(1) NOT NULL DEFAULT 1,
            `available_proposal` TINYINT(1) NOT NULL DEFAULT 1,
            `is_system` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    styleflow_repair_optional_columns();
}

function styleflow_repair_optional_columns()
{
    $CI = &get_instance();
    $table = styleflow_templates_table();

    if (!$CI->db->table_exists($table)) {
        return;
    }

    if (!$CI->db->field_exists('staff_photo_mode', $table)) {
        $CI->db->query("ALTER TABLE `{$table}` ADD `staff_photo_mode` VARCHAR(20) NOT NULL DEFAULT 'none' AFTER `header_style`");
    }
    if (!$CI->db->field_exists('selected_staff_id', $table)) {
        $CI->db->query("ALTER TABLE `{$table}` ADD `selected_staff_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `staff_photo_mode`");
    }
}

function styleflow_ensure_ready()
{
    $CI = &get_instance();
    $table = styleflow_templates_table();

    if (!$CI->db->table_exists($table)) {
        styleflow_install_or_repair_schema();
    } else {
        styleflow_repair_optional_columns();
    }

    // Repair only: restore bundled sample rows that are missing without ever
    // overwriting existing template edits stored in the database.
    styleflow_seed_templates();

    add_option('styleflow_selected_invoice_template', 'default');
    add_option('styleflow_selected_estimate_template', 'default');
    add_option('styleflow_selected_proposal_template', 'default');
    add_option('styleflow_version', STYLEFLOW_VERSION);
    update_option('styleflow_version', STYLEFLOW_VERSION);
}

function styleflow_seed_templates()
{
    $CI = &get_instance();
    $table = styleflow_templates_table();

    if (!$CI->db->table_exists($table)) {
        return;
    }

    $rows = [
        ['default','Default Perfex Template','#3598DB','#F28C28','#169179','#333333','helvetica','classic','clean','none'],
        ['vblue','VBlue Template','#1E88E5','#90CAF9','#0D47A1','#263238','helvetica','stripe','band','none'],
        ['vred','VRed Template','#D32F2F','#FFCDD2','#8E0000','#2D2D2D','helvetica','stripe','band','none'],
        ['vyellow','VYellow Template','#F9A825','#FFF59D','#6D4C41','#333333','helvetica','classic','band','none'],
        ['vgreen','VGreen Template','#169179','#A7E8D8','#0E6F5B','#263238','helvetica','stripe','band','none'],
        ['vorange','VOrange Template','#F28C28','#FFD3A8','#C45D00','#333333','helvetica','stripe','band','none'],
        ['sysblue','SYSBlue Template','#3598DB','#D9EDF9','#1B5E8A','#333333','helvetica','boxed','line','none'],
        ['sysred','SYSRed Template','#C0392B','#FADBD8','#7B241C','#333333','helvetica','boxed','line','none'],
        ['sysyellow','SYSYellow Template','#D4AC0D','#FCF3CF','#7D6608','#333333','helvetica','boxed','line','none'],
        ['sysgreen','SYSGreen Template','#169179','#D5F5E3','#0E6F5B','#333333','helvetica','boxed','line','none'],
        ['sysorange','SYSOrange Template','#F28C28','#FDEBD0','#BA4A00','#333333','helvetica','boxed','line','none'],
        ['xblue','XBlue Template','#1565C0','#E3F2FD','#0D47A1','#263238','helvetica','minimal','split','none'],
        ['xgreen','XGreen Template','#00897B','#E0F2F1','#004D40','#263238','helvetica','minimal','split','none'],
        ['xorange','XOrange Template','#EF6C00','#FFF3E0','#E65100','#263238','helvetica','minimal','split','none'],
        ['xred','XRed Template','#B71C1C','#FFEBEE','#7F0000','#263238','helvetica','minimal','split','none'],
        ['xyellow','XYellow Template','#F9A825','#FFFDE7','#827717','#263238','helvetica','minimal','split','none'],
        ['sc_modern_edge','SC Modern Edge','#0E6F5B','#F28C28','#3598DB','#243238','helvetica','rounded','split','none'],
        ['sc_blueprint','SC Blueprint','#17365D','#3598DB','#F28C28','#23303D','dejavusans','line','band','none'],
        ['sc_citrus','SC Citrus','#F28C28','#FFF0D9','#169179','#333333','helvetica','rounded','band','none'],
        ['sc_executive','SC Executive','#1F2937','#D1D5DB','#F28C28','#111827','times','double','clean','none'],
        ['sc_coastal','SC Coastal','#3598DB','#EAF6FC','#169179','#263238','helvetica','soft','split','none'],
        ['sc_emerald','SC Emerald','#169179','#DDF5EE','#0E6F5B','#243238','dejavusans','rounded','line','none'],
        ['sc_contrast','SC Contrast','#111827','#F28C28','#3598DB','#111827','helvetica','double','band','none'],
        ['sc_signature','SC Signature','#0E6F5B','#F6F7F8','#C79A3B','#2D3436','times','line','clean','creator'],
    ];

    $existing = $CI->db->select('slug')->get($table)->result_array();
    $existingSlugs = [];
    foreach ($existing as $existingRow) {
        $existingSlugs[(string) $existingRow['slug']] = true;
    }

    $now = date('Y-m-d H:i:s');
    $insert = [];
    foreach ($rows as $r) {
        if (isset($existingSlugs[$r[0]])) {
            continue;
        }
        $insert[] = [
            'slug' => $r[0],
            'name' => $r[1],
            'primary_color' => $r[2],
            'secondary_color' => $r[3],
            'accent_color' => $r[4],
            'text_color' => $r[5],
            'font_family' => $r[6],
            'table_style' => $r[7],
            'header_style' => $r[8],
            'staff_photo_mode' => $r[9],
            'selected_staff_id' => 0,
            'available_invoice' => 1,
            'available_estimate' => 1,
            'available_proposal' => 1,
            'is_system' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    if ($insert) {
        $CI->db->insert_batch($table, $insert);
    }
}

function styleflow_template_display_name($template)
{
    $slug = is_array($template) ? (string) ($template['slug'] ?? '') : (string) $template;
    $key = 'styleflow_template_' . $slug;
    $translated = _l($key);
    if ($translated !== $key) {
        return $translated;
    }
    if (is_array($template) && !empty($template['name'])) {
        return (string) $template['name'];
    }
    return $slug;
}

function styleflow_table_style_options()
{
    return [
        ['id'=>'classic','name'=>_l('styleflow_table_classic')],
        ['id'=>'rounded','name'=>_l('styleflow_table_rounded')],
        ['id'=>'stripe','name'=>_l('styleflow_table_stripe')],
        ['id'=>'boxed','name'=>_l('styleflow_table_boxed')],
        ['id'=>'minimal','name'=>_l('styleflow_table_minimal')],
        ['id'=>'line','name'=>_l('styleflow_table_line')],
        ['id'=>'double','name'=>_l('styleflow_table_double')],
        ['id'=>'soft','name'=>_l('styleflow_table_soft')],
    ];
}

function styleflow_header_style_options()
{
    return [
        ['id'=>'clean','name'=>_l('styleflow_header_clean')],
        ['id'=>'band','name'=>_l('styleflow_header_band')],
        ['id'=>'line','name'=>_l('styleflow_header_line')],
        ['id'=>'split','name'=>_l('styleflow_header_split')],
    ];
}

function styleflow_staff_photo_mode_options()
{
    return [
        ['id'=>'none','name'=>_l('styleflow_staff_photo_none')],
        ['id'=>'creator','name'=>_l('styleflow_staff_photo_creator')],
        ['id'=>'selected','name'=>_l('styleflow_staff_photo_selected')],
    ];
}

function styleflow_staff_options()
{
    $CI = &get_instance();
    $rows = $CI->db->select('staffid, firstname, lastname, active')
        ->from(db_prefix() . 'staff')
        ->order_by('active', 'DESC')
        ->order_by('firstname', 'ASC')
        ->get()->result_array();

    $out = [];
    foreach ($rows as $row) {
        $name = trim((string) $row['firstname'] . ' ' . (string) $row['lastname']);
        if (empty($row['active'])) {
            $name .= ' (' . _l('inactive') . ')';
        }
        $out[] = ['id'=>(int)$row['staffid'], 'name'=>$name];
    }
    return $out;
}

function styleflow_get_templates($type = null)
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(styleflow_templates_table())) return [];
    if ($type && in_array($type, ['invoice','estimate','proposal'], true)) {
        $CI->db->where('available_' . $type, 1);
    }
    return $CI->db->order_by('is_system','DESC')->order_by('name','ASC')->get(styleflow_templates_table())->result_array();
}

function styleflow_get_template($slug)
{
    $CI = &get_instance();
    if ($slug === 'default') {
        return [
            'slug'=>'default','name'=>'Default Perfex Template','primary_color'=>'#3598DB',
            'secondary_color'=>'#F28C28','accent_color'=>'#169179','text_color'=>'#333333',
            'font_family'=>'helvetica','table_style'=>'classic','header_style'=>'clean',
            'staff_photo_mode'=>'none','selected_staff_id'=>0,
            'available_invoice'=>1,'available_estimate'=>1,'available_proposal'=>1,
        ];
    }
    if (!$CI->db->table_exists(styleflow_templates_table())) return null;
    return $CI->db->where('slug', $slug)->get(styleflow_templates_table())->row_array();
}

function styleflow_active_template($type)
{
    $slug = get_option('styleflow_selected_' . $type . '_template');
    return $slug ?: 'default';
}

function styleflow_template_available($slug, $type)
{
    if ($slug === 'default') return true;
    $tpl = styleflow_get_template($slug);
    return $tpl && !empty($tpl['available_' . $type]);
}

function styleflow_sanitize_color($value, $fallback)
{
    return preg_match('/^#[0-9A-Fa-f]{6}$/', (string)$value) ? strtoupper($value) : $fallback;
}

function styleflow_supported_fonts()
{
    return ['helvetica'=>'Helvetica','times'=>'Times','courier'=>'Courier','dejavusans'=>'DejaVu Sans'];
}

/**
 * Gives every bundled design a stable visual family. This changes structure,
 * spacing, typography and header treatment in addition to colors.
 */
function styleflow_design_variant($template)
{
    $slug = is_array($template) ? (string)($template['slug'] ?? '') : (string)$template;
    $map = [
        'sc_modern_edge'=>'modern-edge', 'sc_blueprint'=>'blueprint', 'sc_citrus'=>'citrus',
        'sc_executive'=>'executive', 'sc_coastal'=>'coastal', 'sc_emerald'=>'emerald',
        'sc_contrast'=>'contrast', 'sc_signature'=>'signature',
        'xblue'=>'split', 'xgreen'=>'coastal', 'xorange'=>'citrus', 'xred'=>'contrast', 'xyellow'=>'blueprint',
        'sysblue'=>'boxed', 'sysred'=>'executive', 'sysyellow'=>'blueprint', 'sysgreen'=>'emerald', 'sysorange'=>'modern-edge',
        'vblue'=>'band', 'vred'=>'contrast', 'vyellow'=>'citrus', 'vgreen'=>'emerald', 'vorange'=>'modern-edge',
    ];
    return $map[$slug] ?? 'classic';
}

function styleflow_template_preview_image_url($template)
{
    $slug = is_array($template) ? (string)($template['slug'] ?? '') : (string)$template;
    $file = module_dir_path(STYLEFLOW_MODULE_NAME, 'uploads/examples/' . $slug . '-example-v1.0.0.png');
    if (!is_file($file)) {
        return null;
    }
    return module_dir_url(STYLEFLOW_MODULE_NAME, 'uploads/examples/' . $slug . '-example-v1.0.0.png');
}

function styleflow_document_creator_staff_id($document)
{
    foreach (['addedfrom', 'added_from', 'staffid', 'staff_id'] as $field) {
        if (isset($document->{$field}) && (int)$document->{$field} > 0) {
            return (int)$document->{$field};
        }
        if (is_array($document) && !empty($document[$field])) {
            return (int)$document[$field];
        }
    }
    return 0;
}

function styleflow_template_staff_id($template, $document)
{
    $mode = (string)($template['staff_photo_mode'] ?? 'none');
    if ($mode === 'creator') {
        return styleflow_document_creator_staff_id($document);
    }
    if ($mode === 'selected') {
        return (int)($template['selected_staff_id'] ?? 0);
    }
    return 0;
}

function styleflow_staff_photo_source($staffId, $forPdf = false)
{
    $staffId = (int)$staffId;
    if ($staffId <= 0) return null;

    $CI = &get_instance();
    $row = $CI->db->select('profile_image')->where('staffid', $staffId)->get(db_prefix() . 'staff')->row_array();
    if (!$row || empty($row['profile_image'])) return null;

    $filename = basename((string)$row['profile_image']);
    $dir = FCPATH . 'uploads/staff_profile_images/' . $staffId . '/';
    foreach (['small_' . $filename, 'thumb_' . $filename, $filename] as $candidate) {
        $path = $dir . $candidate;
        if (is_file($path)) {
            return $forPdf ? str_replace('\\', '/', $path) : base_url('uploads/staff_profile_images/' . $staffId . '/' . rawurlencode($candidate));
        }
    }

    if (function_exists('staff_profile_image_url')) {
        return staff_profile_image_url($staffId, 'small');
    }
    return null;
}

function styleflow_staff_display_name($staffId)
{
    $staffId = (int)$staffId;
    if ($staffId <= 0) return '';
    $CI = &get_instance();
    $row = $CI->db->select('firstname, lastname')->where('staffid', $staffId)->get(db_prefix() . 'staff')->row_array();
    return $row ? trim((string)$row['firstname'] . ' ' . (string)$row['lastname']) : '';
}

function styleflow_pdf_class_path($type)
{
    $slug = styleflow_active_template($type);
    if ($slug === 'default' || !styleflow_template_available($slug, $type)) return null;
    $path = APP_MODULES_PATH . 'styleflow/libraries/pdf/' . ucfirst($type) . '_pdf.php';
    return is_file($path) ? $path : null;
}

function styleflow_invoice_pdf_path($path) { return styleflow_pdf_class_path('invoice') ?: $path; }
function styleflow_estimate_pdf_path($path) { return styleflow_pdf_class_path('estimate') ?: $path; }
function styleflow_proposal_pdf_path($path) { return styleflow_pdf_class_path('proposal') ?: $path; }

hooks()->add_filter('invoice_pdf_class_path', 'styleflow_invoice_pdf_path', 100);
hooks()->add_filter('estimate_pdf_class_path', 'styleflow_estimate_pdf_path', 100);
hooks()->add_filter('proposal_pdf_class_path', 'styleflow_proposal_pdf_path', 100);

function styleflow_get_items_table_data($transaction, $type, $for = 'pdf', $admin_preview = false)
{
    require_once(APP_MODULES_PATH . 'styleflow/libraries/Styleflow_dynamic_items_table.php');
    return new Styleflow_dynamic_items_table($transaction, $type, $for, $admin_preview);
}

/**
 * Detect a customer-facing sales document page without styling unrelated
 * customer-portal screens.
 */
function styleflow_public_document_type()
{
    $CI = &get_instance();
    $uri = strtolower(trim((string)$CI->uri->uri_string(), '/'));
    foreach (['proposal', 'estimate', 'invoice'] as $type) {
        if (preg_match('~(^|/)' . $type . 's?(/|$)~', $uri)) {
            return $type;
        }
    }
    return null;
}

function styleflow_public_document_styles()
{
    $type = styleflow_public_document_type();
    if (!$type) return;

    $slug = styleflow_active_template($type);
    if ($slug === 'default' || !styleflow_template_available($slug, $type)) return;

    $tpl = styleflow_get_template($slug);
    if (!$tpl) return;

    $p = styleflow_sanitize_color($tpl['primary_color'] ?? '', '#3598DB');
    $s = styleflow_sanitize_color($tpl['secondary_color'] ?? '', '#F6F7F8');
    $a = styleflow_sanitize_color($tpl['accent_color'] ?? '', '#169179');
    $t = styleflow_sanitize_color($tpl['text_color'] ?? '', '#333333');
    $variant = styleflow_design_variant($tpl);
    $radius = in_array($variant, ['modern-edge','citrus','coastal','emerald'], true) ? '12px' : '4px';
    $variantCss = '';
    if ($variant === 'modern-edge') {
        $variantCss = '.proposal-html .panel-body,.estimate-html .panel-body,.invoice-html .panel-body{border-left:8px solid var(--sf-primary)!important;}';
    } elseif ($variant === 'blueprint') {
        $variantCss = '.proposal-html .panel-body,.estimate-html .panel-body,.invoice-html .panel-body{border:2px solid var(--sf-primary)!important;background-image:linear-gradient(rgba(23,54,93,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(23,54,93,.035) 1px,transparent 1px)!important;background-size:18px 18px!important;}';
    } elseif ($variant === 'executive') {
        $variantCss = '.proposal-html .panel-body,.estimate-html .panel-body,.invoice-html .panel-body{border-top:8px solid #111827!important;font-family:Georgia,serif!important;}.proposal-html table thead th,.estimate-html table thead th,.invoice-html table thead th{background:#111827!important;}';
    } elseif ($variant === 'contrast') {
        $variantCss = '.proposal-html .panel-body,.estimate-html .panel-body,.invoice-html .panel-body{border-top:10px solid #111827!important;border-bottom:6px solid var(--sf-accent)!important;}.proposal-html table thead th,.estimate-html table thead th,.invoice-html table thead th{background:#111827!important;}';
    } elseif ($variant === 'signature') {
        $variantCss = '.proposal-html .panel-body,.estimate-html .panel-body,.invoice-html .panel-body{font-family:Georgia,serif!important;}.proposal-html hr,.estimate-html hr,.invoice-html hr{border-top:1px solid var(--sf-accent)!important;}';
    } elseif ($variant === 'split') {
        $variantCss = '.proposal-html h1,.proposal-html h2,.estimate-html h1,.estimate-html h2,.invoice-html h1,.invoice-html h2{border-left:5px solid var(--sf-primary)!important;padding-left:12px!important;}';
    } elseif ($variant === 'boxed') {
        $variantCss = '.proposal-html table td,.estimate-html table td,.invoice-html table td{border:1px solid #d9dee5!important;}';
    } elseif ($variant === 'band' || $variant === 'citrus') {
        $variantCss = '.proposal-html .panel-heading,.estimate-html .panel-heading,.invoice-html .panel-heading{background:var(--sf-primary)!important;color:#fff!important;}';
    }

    echo '<style id="styleflow-public-' . html_escape($type) . '">'
        . 'body{--sf-primary:' . $p . ';--sf-secondary:' . $s . ';--sf-accent:' . $a . ';--sf-text:' . $t . ';}'
        . '.proposal-html,.estimate-html,.invoice-html,.proposal-view,.estimate-view,.invoice-view,.document-html{color:var(--sf-text)!important;}'
        . '.proposal-html .panel_s,.estimate-html .panel_s,.invoice-html .panel_s,.proposal-view .panel_s,.estimate-view .panel_s,.invoice-view .panel_s{border-radius:' . $radius . '!important;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.08)!important;}'
        . '.proposal-html h1,.proposal-html h2,.proposal-html h3,.estimate-html h1,.estimate-html h2,.estimate-html h3,.invoice-html h1,.invoice-html h2,.invoice-html h3{color:var(--sf-primary)!important;}'
        . '.proposal-html table thead th,.estimate-html table thead th,.invoice-html table thead th,.proposal-view table thead th,.estimate-view table thead th,.invoice-view table thead th,.table.items thead th,.panel_s table.items thead th{background:var(--sf-primary)!important;color:#fff!important;border-color:var(--sf-primary)!important;}'
        . '.proposal-html table tbody tr:nth-child(odd),.estimate-html table tbody tr:nth-child(odd),.invoice-html table tbody tr:nth-child(odd),.table.items tbody tr:nth-child(odd){background:' . $s . '!important;}'
        . '.proposal-html .btn-success,.estimate-html .btn-success,.invoice-html .btn-success,.proposal-html .btn-primary,.estimate-html .btn-primary,.invoice-html .btn-primary{background:var(--sf-primary)!important;border-color:var(--sf-primary)!important;}'
        . '.proposal-html hr,.estimate-html hr,.invoice-html hr{border-color:var(--sf-accent)!important;}'
        . $variantCss
        . '</style>';
}

function styleflow_public_document_record($type)
{
    $CI = &get_instance();
    $uri = trim((string)$CI->uri->uri_string(), '/');
    $segments = array_values(array_filter(explode('/', $uri), 'strlen'));
    $id = 0;
    foreach ($segments as $segment) {
        if (ctype_digit((string)$segment) && (int)$segment > 0) {
            $id = (int)$segment;
            break;
        }
    }
    if ($id <= 0) return null;

    $tableMap = [
        'invoice' => db_prefix() . 'invoices',
        'estimate' => db_prefix() . 'estimates',
        'proposal' => db_prefix() . 'proposals',
    ];
    if (!isset($tableMap[$type]) || !$CI->db->table_exists($tableMap[$type])) return null;
    return $CI->db->where('id', $id)->get($tableMap[$type])->row();
}

function styleflow_public_document_staff_badge()
{
    $type = styleflow_public_document_type();
    if (!$type) return;

    $slug = styleflow_active_template($type);
    if ($slug === 'default' || !styleflow_template_available($slug, $type)) return;
    $tpl = styleflow_get_template($slug);
    if (!$tpl || ($tpl['staff_photo_mode'] ?? 'none') === 'none') return;

    $record = styleflow_public_document_record($type);
    $staffId = styleflow_template_staff_id($tpl, $record ?: []);
    if ($staffId <= 0) return;
    $photo = styleflow_staff_photo_source($staffId, false);
    if (!$photo) return;
    $name = styleflow_staff_display_name($staffId);
    $accent = styleflow_sanitize_color($tpl['accent_color'] ?? '', '#169179');

    echo '<div id="styleflow-staff-badge" style="display:none;margin-top:18px;padding-top:12px;border-top:1px solid ' . html_escape($accent) . ';align-items:center;gap:10px;">'
        . '<img src="' . html_escape($photo) . '" alt="" style="width:56px;height:56px;border-radius:50%;object-fit:cover;">'
        . '<div><strong>' . html_escape($name) . '</strong><br><small>' . html_escape(_l('styleflow_employee_representative')) . '</small></div></div>'
        . '<script>(function(){var b=document.getElementById("styleflow-staff-badge");if(!b)return;var t=document.querySelector(".proposal-html,.estimate-html,.invoice-html,.document-html,.panel_s .panel-body");if(t){b.style.display="flex";t.appendChild(b);}})();</script>';
}
