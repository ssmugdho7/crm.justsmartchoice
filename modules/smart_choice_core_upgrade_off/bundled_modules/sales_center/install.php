<?php

defined('BASEPATH') or exit('No direct script access allowed');


// Safe fallback when migrations load install.php directly before the main module file is fully bootstrapped.
if (!defined('SALES_CENTER_MODULE_NAME')) {
    define('SALES_CENTER_MODULE_NAME', 'sales_center');
}
if (!defined('SALES_CENTER_UPLOAD_FOLDER')) {
    define('SALES_CENTER_UPLOAD_FOLDER', FCPATH . 'uploads/sales_center/');
}

$CI = &get_instance();

if (!function_exists('sales_center_add_column')) {
    function sales_center_add_column($table, $column, $definition)
    {
        $CI = &get_instance();
        $fullTable = db_prefix() . $table;
        if (!$CI->db->field_exists($column, $fullTable)) {
            $CI->db->query('ALTER TABLE `' . $fullTable . '` ADD `' . $column . '` ' . $definition);
        }
    }
}

if (!function_exists('sales_center_seed_defaults')) {
    function sales_center_seed_defaults()
    {
        $CI = &get_instance();
        $now = date('Y-m-d H:i:s');

        $categories = ['General Salesperson', 'Electrical', 'Plumbing', 'Roofing', 'HVAC', 'Framing', 'Drywall', 'Painting', 'Concrete', 'Flooring'];
        foreach ($categories as $name) {
            if (!$CI->db->where('name', $name)->get(db_prefix() . 'sales_center_categories')->row()) {
                $CI->db->insert(db_prefix() . 'sales_center_categories', ['name' => $name, 'color' => '#169179', 'datecreated' => $now]);
            }
        }

        $subStatuses = [
            ['Active','active','#22c55e'], ['Inactive','inactive','#6b7280'], ['Pending','pending','#f59e0b'], ['Blocked','blocked','#ef4444']
        ];
        foreach ($subStatuses as $row) {
            if (!$CI->db->where('slug', $row[1])->get(db_prefix() . 'sales_center_statuses')->row()) {
                $CI->db->insert(db_prefix() . 'sales_center_statuses', ['name'=>$row[0], 'slug'=>$row[1], 'color'=>$row[2], 'datecreated'=>$now]);
            }
        }

        $contractStatuses = [
            ['Draft','draft','#6b7280'], ['Sent','sent','#3b82f6'], ['Signed','signed','#22c55e'], ['Completed','completed','#169179'], ['Cancelled','cancelled','#ef4444'], ['Expired','expired','#991b1b']
        ];
        foreach ($contractStatuses as $row) {
            if (!$CI->db->where('slug', $row[1])->get(db_prefix() . 'sales_center_contract_statuses')->row()) {
                $CI->db->insert(db_prefix() . 'sales_center_contract_statuses', ['name'=>$row[0], 'slug'=>$row[1], 'color'=>$row[2], 'datecreated'=>$now]);
            }
        }

        if (!$CI->db->where('name', 'Standard Sales Agreement')->get(db_prefix() . 'sales_center_templates')->row()) {
            $CI->db->insert(db_prefix() . 'sales_center_templates', [
                'name' => 'Standard Sales Agreement',
                'contract_type' => 'Standard Agreement',
                'content' => '<h2>Sales Agreement</h2><p>This agreement defines the scope of work, project requirements, insurance requirements, payment terms, schedule, safety standards, documentation requirements, and salesperson responsibilities.</p><h3>Scope of Work</h3><p>Enter detailed scope here.</p><h3>Payment Terms</h3><p>Enter payment terms here.</p><h3>Insurance and License Requirements</h3><p>Salesperson must maintain active license and required insurance coverage.</p>',
                'created_by' => get_staff_user_id() ?: 0,
                'datecreated' => $now,
            ]);
        }
    }
}


if (!function_exists('sales_center_insert_email_templates')) {
    function sales_center_insert_email_templates()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'emailtemplates';

        if (!$CI->db->table_exists($table)) {
            return;
        }

        $templates = [
            [
                'slug' => 'sales-center-agreement-sent-staff',
                'name' => 'Sales Agreement Sent To Staff',
                'subject' => 'Sales Agreement Sent - {contract_subject}',
                'message' => '<p>Hello,</p><p>A sales agreement has been created or sent.</p><p><strong>Salesperson:</strong> {salesperson_name}</p><p><strong>Contract:</strong> {contract_subject}</p><p><strong>Project:</strong> {project_name}</p><p>Please review the sales contract record inside Smart Choice CRM.</p>',
            ],
            [
                'slug' => 'sales-center-agreement-sent-salesperson',
                'name' => 'Sales Agreement Sent To Salesperson',
                'subject' => 'Sales Agreement For Review - {contract_subject}',
                'message' => '<p>Hello {salesperson_name},</p><p>Smart Choice Contractors USA has prepared a sales agreement for your review.</p><p><strong>Contract:</strong> {contract_subject}</p><p><strong>Project:</strong> {project_name}</p><p>Please contact our office if you have any questions.</p>',
            ],
            [
                'slug' => 'sales-center-agreement-sent-client',
                'name' => 'Sales Agreement Sent To Client',
                'subject' => 'Project Sales Agreement Update - {project_name}',
                'message' => '<p>Hello,</p><p>This is a project update from Smart Choice Contractors USA.</p><p>A sales agreement has been recorded for the project.</p><p><strong>Project:</strong> {project_name}</p><p><strong>Salesperson:</strong> {salesperson_name}</p>',
            ],
            [
                'slug' => 'sales-center-welcome-salesperson',
                'name' => 'Welcome Email To Salesperson',
                'subject' => 'Welcome To Smart Choice Contractors USA Sales Team',
                'message' => '<p>Hello {salesperson_name},</p><p>Welcome to Smart Choice Contractors USA Sales Hub.</p><p>Please keep your contact information, tax documents, identification, agreements, and sales records updated in the CRM portal.</p><p>Thank you for working with Smart Choice Contractors USA.</p>',
            ],
            [
                'slug' => 'sales-center-documents-missing',
                'name' => 'Salesperson Documents Missing',
                'subject' => 'Missing Salesperson Documents',
                'message' => '<p>Hello {salesperson_name},</p><p>Your Sales Hub profile is missing required documents. Please upload your W-9 or W-4, identification, direct deposit form, agreement, and any other required onboarding documents.</p>',
            ],
            [
                'slug' => 'sales-center-commission-ready',
                'name' => 'Sales Commission Ready For Review',
                'subject' => 'Commission Ready For Review - {salesperson_name}',
                'message' => '<p>Hello,</p><p>A sales commission record is ready for review.</p><p><strong>Salesperson:</strong> {salesperson_name}</p><p><strong>Invoice:</strong> {invoice_number}</p><p><strong>Commission Owed:</strong> {commission_owed}</p>',
            ],
        ];

        foreach ($templates as $tpl) {
            $slug = isset($tpl['slug']) ? trim((string) $tpl['slug']) : '';
            $name = isset($tpl['name']) ? trim((string) $tpl['name']) : '';
            $subject = isset($tpl['subject']) ? trim((string) $tpl['subject']) : '';
            $message = isset($tpl['message']) ? trim((string) $tpl['message']) : '';

            if ($slug === '' || $name === '' || $subject === '' || $message === '') {
                continue;
            }

            if ($CI->db->where('slug', $slug)->get($table)->row()) {
                continue;
            }

            $data = [
                'type' => 'sales_center',
                'slug' => $slug,
                'language' => 'english',
                'name' => $name,
                'subject' => $subject,
                'message' => $message,
                'fromname' => '{companyname}',
                'active' => 1,
            ];

            foreach (array_keys($data) as $field) {
                if (!$CI->db->field_exists($field, $table)) {
                    unset($data[$field]);
                }
            }

            if (isset($data['slug']) && $data['slug'] !== '') {
                $CI->db->insert($table, $data);
            }
        }
    }
}


if (!is_dir(SALES_CENTER_UPLOAD_FOLDER)) {
    @mkdir(SALES_CENTER_UPLOAD_FOLDER, 0755, true);
}

if (!get_option('sales_center_enabled')) {
    add_option('sales_center_enabled', '1');
}
if (!get_option('sales_center_delete_data_on_uninstall')) {
    add_option('sales_center_delete_data_on_uninstall', '0');
}

$salesCenterDefaultOptions = [
    'sales_center_hide_core_sales_menu' => '0',
    'sales_center_hide_core_proposals' => '0',
    'sales_center_hide_core_estimates' => '0',
    'sales_center_hide_core_invoices' => '0',
    'sales_center_hide_core_payments' => '0',
    'sales_center_hide_core_credit_notes' => '0',
    'sales_center_hide_core_items' => '0',
    'sales_center_view_mode' => 'combined',
];
foreach ($salesCenterDefaultOptions as $optionName => $optionValue) {
    if (get_option($optionName) === false || get_option($optionName) === '') {
        add_option($optionName, $optionValue);
    }
}

if (!get_option('sales_center_help_sections_json')) {
    add_option('sales_center_help_sections_json', json_encode([
        ['title' => '1. Salesperson Records', 'content' => '<p>Create salesperson profiles with company, contact, trade, licensing, insurance, documents, staff link, and portal status.</p>'],
        ['title' => '2. Portal Workflow', 'content' => '<p>Send the public portal link to new salespersons so they can create their profile, upload documents, and choose English or Spanish.</p>'],
        ['title' => '3. Contract Workflow', 'content' => '<p>Create salesperson contracts from templates, add merge fields, signatures, initials, files, PDF views, and notifications.</p>'],
    ]));
}

if (!$CI->db->table_exists(db_prefix() . 'sales_center')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "sales_center` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `company` varchar(191) NOT NULL,
        `contact_name` varchar(191) NULL,
        `email` varchar(191) NULL,
        `phone` varchar(50) NULL,
        `trade` varchar(191) NULL,
        `position_type` varchar(100) NULL,
        `employment_type` varchar(100) NULL,
        `department_id` int(11) NULL,
        `commission_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
        `sales_goal` decimal(15,2) NOT NULL DEFAULT 0.00,
        `license_number` varchar(191) NULL,
        `dbpr_link` text NULL,
        `county_license_link` text NULL,
        `profile_image` varchar(255) NULL,
        `portal_enabled` tinyint(1) NOT NULL DEFAULT 1,
        `portal_token` varchar(64) NULL,
        `staff_id` int(11) NULL,
        `insurance_expiration` date NULL,
        `address` text NULL,
        `city` varchar(100) NULL,
        `state` varchar(50) NULL,
        `zip` varchar(30) NULL,
        `status` varchar(50) NOT NULL DEFAULT 'active',
        `category` varchar(191) NULL,
        `notes` mediumtext NULL,
        `assigned` int(11) NULL,
        `created_by` int(11) NULL,
        `datecreated` datetime NOT NULL,
        `dateupdated` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `company` (`company`),
        KEY `trade` (`trade`),
        KEY `department_id` (`department_id`),
        KEY `status` (`status`),
        KEY `category` (`category`),
        KEY `assigned` (`assigned`),
        KEY `portal_token` (`portal_token`),
        KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    sales_center_add_column('sales_center', 'dbpr_link', 'TEXT NULL');
    sales_center_add_column('sales_center', 'county_license_link', 'TEXT NULL');
    sales_center_add_column('sales_center', 'profile_image', 'VARCHAR(255) NULL');
    sales_center_add_column('sales_center', 'category', 'VARCHAR(191) NULL');
    sales_center_add_column('sales_center', 'position_type', 'VARCHAR(100) NULL');
    sales_center_add_column('sales_center', 'employment_type', 'VARCHAR(100) NULL');
    sales_center_add_column('sales_center', 'department_id', 'INT(11) NULL');
    sales_center_add_column('sales_center', 'commission_rate', 'DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    sales_center_add_column('sales_center', 'sales_goal', 'DECIMAL(15,2) NOT NULL DEFAULT 0.00');
    sales_center_add_column('sales_center', 'portal_enabled', 'TINYINT(1) NOT NULL DEFAULT 1');
    sales_center_add_column('sales_center', 'portal_token', 'VARCHAR(64) NULL');
    sales_center_add_column('sales_center', 'staff_id', 'INT(11) NULL');
}

if (!$CI->db->table_exists(db_prefix() . 'sales_center_contracts')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "sales_center_contracts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `subject` varchar(191) NOT NULL,
        `salesperson_id` int(11) NOT NULL,
        `project_id` int(11) NULL,
        `contract_type` varchar(191) NULL,
        `contract_value` decimal(15,2) NOT NULL DEFAULT 0.00,
        `start_date` date NULL,
        `end_date` date NULL,
        `signed_date` date NULL,
        `status` varchar(50) NOT NULL DEFAULT 'draft',
        `description` longtext NULL,
        `content` longtext NULL,
        `hidden_from_customer` tinyint(1) NOT NULL DEFAULT 0,
        `is_trash` tinyint(1) NOT NULL DEFAULT 0,
        `company_initials` varchar(50) NULL,
        `salesperson_initials` varchar(50) NULL,
        `company_signature` longtext NULL,
        `salesperson_signature` longtext NULL,
        `company_signed_at` datetime NULL,
        `salesperson_signed_at` datetime NULL,
        `company_signed_ip` varchar(100) NULL,
        `salesperson_signed_ip` varchar(100) NULL,
        `assigned` int(11) NULL,
        `created_by` int(11) NULL,
        `datecreated` datetime NOT NULL,
        `dateupdated` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `salesperson_id` (`salesperson_id`),
        KEY `project_id` (`project_id`),
        KEY `contract_type` (`contract_type`),
        KEY `status` (`status`),
        KEY `is_trash` (`is_trash`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    sales_center_add_column('sales_center_contracts', 'contract_type', 'VARCHAR(191) NULL');
    sales_center_add_column('sales_center_contracts', 'hidden_from_customer', 'TINYINT(1) NOT NULL DEFAULT 0');
    sales_center_add_column('sales_center_contracts', 'is_trash', 'TINYINT(1) NOT NULL DEFAULT 0');
    sales_center_add_column('sales_center_contracts', 'company_initials', 'VARCHAR(50) NULL');
    sales_center_add_column('sales_center_contracts', 'salesperson_initials', 'VARCHAR(50) NULL');
    sales_center_add_column('sales_center_contracts', 'company_signature', 'LONGTEXT NULL');
    sales_center_add_column('sales_center_contracts', 'salesperson_signature', 'LONGTEXT NULL');
    sales_center_add_column('sales_center_contracts', 'company_signed_at', 'DATETIME NULL');
    sales_center_add_column('sales_center_contracts', 'salesperson_signed_at', 'DATETIME NULL');
    sales_center_add_column('sales_center_contracts', 'company_signed_ip', 'VARCHAR(100) NULL');
    sales_center_add_column('sales_center_contracts', 'salesperson_signed_ip', 'VARCHAR(100) NULL');
}

if (!$CI->db->table_exists(db_prefix() . 'sales_center_files')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "sales_center_files` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `rel_id` int(11) NOT NULL,
        `rel_type` varchar(50) NOT NULL,
        `file_name` varchar(255) NOT NULL,
        `original_file_name` varchar(255) NULL,
        `filetype` varchar(191) NULL,
        `visible_to_customer` tinyint(1) NOT NULL DEFAULT 0,
        `staffid` int(11) NULL,
        `dateadded` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `rel_id` (`rel_id`),
        KEY `rel_type` (`rel_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    sales_center_add_column('sales_center_files', 'original_file_name', 'VARCHAR(255) NULL');
}

if (!$CI->db->table_exists(db_prefix() . 'sales_center_project_links')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "sales_center_project_links` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `salesperson_id` int(11) NOT NULL,
        `project_id` int(11) NOT NULL,
        `trade` varchar(191) NULL,
        `scope` mediumtext NULL,
        `created_by` int(11) NULL,
        `datecreated` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `salesperson_project` (`salesperson_id`,`project_id`),
        KEY `project_id` (`project_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'sales_center_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "sales_center_categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(191) NOT NULL,
        `color` varchar(20) NULL,
        `datecreated` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'sales_center_statuses')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "sales_center_statuses` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(191) NOT NULL,
        `slug` varchar(100) NOT NULL,
        `color` varchar(20) NULL,
        `datecreated` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'sales_center_contract_statuses')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "sales_center_contract_statuses` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(191) NOT NULL,
        `slug` varchar(100) NOT NULL,
        `color` varchar(20) NULL,
        `datecreated` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'sales_center_templates')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "sales_center_templates` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(191) NOT NULL,
        `contract_type` varchar(191) NULL,
        `content` longtext NULL,
        `created_by` int(11) NULL,
        `datecreated` datetime NOT NULL,
        `dateupdated` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `contract_type` (`contract_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


// Smart Choice CRM staff integration support. This does not bypass core permissions.
if ($CI->db->table_exists(db_prefix() . 'staff')) {
    sales_center_add_column('staff', 'is_salesperson', 'TINYINT(1) NOT NULL DEFAULT 0');
    sales_center_add_column('staff', 'sales_center_salesperson_id', 'INT(11) NULL');
}

// Make existing salespersons portal-ready.
if ($CI->db->table_exists(db_prefix() . 'sales_center')) {
    $rows = $CI->db->select('id, portal_token')->get(db_prefix() . 'sales_center')->result_array();
    foreach ($rows as $row) {
        if (empty($row['portal_token'])) {
            $CI->db->where('id', (int) $row['id'])->update(db_prefix() . 'sales_center', [
                'portal_token' => bin2hex(random_bytes(24)),
                'portal_enabled' => 1,
            ]);
        }
    }
}


if (!function_exists('sales_center_seed_professional_templates_v200')) {
    function sales_center_seed_professional_templates_v200()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'sales_center_templates';
        if (!$CI->db->table_exists($table)) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $templates = [];

        $commonSignature = '<div style="margin-top:35px;border-top:2px solid #111;padding-top:15px;"><table style="width:100%;border-collapse:collapse;"><tr><td style="width:50%;vertical-align:top;padding:10px;"><strong>Smart Choice Contractors USA</strong><br>Company Initials: {company_initials}<br>{company_signature}<br>{contract_signed_stamp}</td><td style="width:50%;vertical-align:top;padding:10px;"><strong>Salesperson</strong><br>Name: {salesperson_name}<br>Initials: {salesperson_initials}<br>{salesperson_signature}<br>{contract_signed_stamp}</td></tr></table></div>';

        $templates[] = [
            'name' => 'Master Sales Agreement',
            'contract_type' => 'Master Agreement',
            'content' => '<h1 style="text-align:center;">SALES REPRESENTATIVE AGREEMENT</h1><h3 style="text-align:center;">Smart Choice Contractors USA</h3><p>This Master Sales Agreement is entered into between Smart Choice Contractors USA and {salesperson_name}. This agreement governs salesperson services, project assignments, scope of work, trade responsibilities, insurance, license requirements, payment procedures, safety rules, document control, project communication, confidentiality, and compliance with Florida construction requirements.</p><h2>1. Salesperson Information</h2><p><strong>Salesperson:</strong> {salesperson_name}<br><strong>Email:</strong> {salesperson_email}<br><strong>Phone:</strong> {salesperson_phone}<br><strong>Project:</strong> {project_name}<br><strong>Contract Subject:</strong> {contract_subject}<br><strong>Contract Value:</strong> ${contract_value}</p><h2>2. Scope Of Work</h2><p>The salesperson shall provide labor, supervision, tools, equipment, materials when assigned, cleanup, coordination, and documentation necessary to complete the assigned work in a professional manner. The exact scope may be described in the project estimate, work order, drawings, project notes, change orders, attachments, or written instructions issued by Smart Choice Contractors USA.</p><h2>3. License, Insurance, And Compliance</h2><p>The salesperson must maintain active license status when licensing is required, general liability insurance, workers compensation coverage or exemption documentation when applicable, W-9 records, and any trade certifications required for the assigned work. The salesperson must comply with Florida Building Code, local permitting requirements, OSHA safety practices, project inspections, and Smart Choice Contractors USA jobsite standards.</p><h2>4. Payment Terms</h2><p>Payment is subject to completed work, inspection approval when applicable, delivery of required documents, approved invoices, lien releases, photos, and confirmation that the work is complete and acceptable. Smart Choice Contractors USA may withhold payment for incomplete work, failed inspections, missing documentation, damages, cleanup issues, unauthorized changes, or unresolved punch list items.</p><h2>5. Project Documentation</h2><p>The salesperson agrees to upload or provide photos, videos, license documents, insurance documents, W-9 forms, receipts, material delivery confirmations, daily reports, inspection results, and other requested project records through the CRM or salesperson portal.</p><h2>6. Change Orders</h2><p>No change order is payable unless approved in writing by Smart Choice Contractors USA before the additional work is performed. Text message, email, signed change order, or CRM approval may be used as written approval.</p><h2>7. Safety And Conduct</h2><p>The salesperson must maintain a clean and safe jobsite, protect customer property, follow instructions, avoid unauthorized work, communicate delays, and act professionally with clients, staff, inspectors, suppliers, and other trades.</p><h2>8. Independent Salesperson Status</h2><p>The sales representative is an independent representative and is responsible for its own employees, taxes, insurance, tools, licenses, vehicles, payroll, supervision, and legal obligations.</p><h2>9. Florida Lien And Release Requirements</h2><p>The salesperson may be required to provide partial or final lien waivers and releases. Florida law provides that lien rights may only be waived to the extent of labor, services, or materials furnished and may not be waived in advance.</p><h2>10. Signatures And Initials</h2><p>By signing or initialing electronically, both parties acknowledge that the electronic signature, initials, date, time, and IP address may be stored as part of the CRM record.</p>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Electrical Sales Agreement',
            'contract_type' => 'Electrical',
            'content' => '<h1 style="text-align:center;">ELECTRICAL SALES AGREEMENT</h1><p>This agreement applies to electrical subcontract work assigned by Smart Choice Contractors USA to {salesperson_name} for {project_name}. Electrical work may include service equipment, panels, feeders, branch circuits, lighting, devices, rough-in, trim-out, EV charger circuits, troubleshooting, inspection corrections, and related electrical services.</p><h2>Scope Requirements</h2><p>The salesperson must complete all electrical work according to applicable Florida Building Code, National Electrical Code requirements adopted by the jurisdiction, utility requirements, project drawings, permit conditions, and inspection instructions.</p><h2>Required Documents</h2><ul><li>Electrical license verification when required.</li><li>General liability insurance.</li><li>Workers compensation or exemption.</li><li>W-9.</li><li>Photos before concealment.</li><li>Inspection correction photos and reports.</li></ul><h2>Payment Conditions</h2><p>Payment is subject to completed scope, passed inspections when applicable, labeled panels, safe installations, cleanup, and delivery of all required documentation.</p>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Plumbing Sales Agreement',
            'contract_type' => 'Plumbing',
            'content' => '<h1 style="text-align:center;">PLUMBING SALES AGREEMENT</h1><p>This agreement applies to plumbing subcontract work assigned by Smart Choice Contractors USA to {salesperson_name} for {project_name}. Plumbing work may include water lines, drain lines, fixture installation, water heaters, kitchen plumbing, bathroom plumbing, repiping, rough plumbing, trim plumbing, pressure testing, and inspection corrections.</p><h2>Scope Requirements</h2><p>The salesperson must perform plumbing work according to permit documents, Florida Building Code Plumbing requirements, local inspection requirements, manufacturer instructions, and written project directions.</p><h2>Documentation</h2><p>Salesperson must provide required license information, insurance, W-9, photos of rough-in before covering, test documentation, and final completion photos.</p><h2>Payment Conditions</h2><p>Payments may be held until pressure tests, rough inspections, final inspections, punch list completion, and documentation requirements are complete.</p>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Notice Of Commencement Form Layout',
            'contract_type' => 'Notice Of Commencement',
            'content' => '<h1 style="text-align:center;">NOTICE OF COMMENCEMENT</h1><table style="width:100%;border-collapse:collapse;border:1px solid #111;"><tr><td style="border:1px solid #111;padding:8px;">Permit No.</td><td style="border:1px solid #111;padding:8px;">________________________</td><td style="border:1px solid #111;padding:8px;">Tax Folio No.</td><td style="border:1px solid #111;padding:8px;">________________________</td></tr><tr><td style="border:1px solid #111;padding:8px;">State</td><td style="border:1px solid #111;padding:8px;">Florida</td><td style="border:1px solid #111;padding:8px;">County</td><td style="border:1px solid #111;padding:8px;">________________________</td></tr></table><p>The undersigned hereby gives notice that improvement will be made to certain real property, and in accordance with Chapter 713, Florida Statutes, the following information is provided in this Notice of Commencement.</p><h3>1. Description Of Property</h3><p>Legal description and street address: __________________________________________________________________________</p><h3>2. General Description Of Improvement</h3><p>__________________________________________________________________________</p><h3>3. Owner Information</h3><p>Name: {client_company}<br>Address: {client_address}, {client_city}, {client_state} {client_zip}<br>Phone: {client_phonenumber}</p><h3>4. Salesperson</h3><p>Smart Choice Contractors USA<br>1663 US Highway 41, Spring Hill, Florida 34610<br>Phone: (727) 755-3786</p><h3>5. Surety</h3><p>Name: __________________ Address: __________________ Amount: $_______________</p><h3>6. Lender</h3><p>Name: __________________ Address: __________________ Phone: __________________</p><h3>7. Expiration Date</h3><p>Expiration date of Notice of Commencement: ____ / ____ / ______</p><h3>Warning To Owner</h3><p>Florida Construction Lien Law requires proper recording and posting of a Notice of Commencement when required. Consult the county clerk, lender, or attorney before commencing work or recording this notice.</p><p>Printed Name: __________________________ Signature: __________________________ Date: _______________</p>',
        ];

        $templates[] = [
            'name' => 'Waiver And Release Of Lien Upon Progress Payment',
            'contract_type' => 'Lien Waiver Progress Payment',
            'content' => '<h1 style="text-align:center;">WAIVER AND RELEASE OF LIEN UPON PROGRESS PAYMENT</h1><p>The undersigned lienor, in consideration of the sum of $__________, hereby waives and releases its lien and right to claim a lien for labor, services, or materials furnished through ____ / ____ / ______ to the following property:</p><p>Property: {client_address}, {client_city}, {client_state} {client_zip}</p><p>This waiver and release does not cover any retention or labor, services, or materials furnished after the date specified above.</p><p>Salesperson: {salesperson_name}<br>Project: {project_name}<br>Contract: {contract_subject}</p>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Waiver And Release Of Lien Upon Final Payment',
            'contract_type' => 'Lien Waiver Final Payment',
            'content' => '<h1 style="text-align:center;">WAIVER AND RELEASE OF LIEN UPON FINAL PAYMENT</h1><p>The undersigned lienor, in consideration of final payment in the amount of $__________, hereby waives and releases its lien and right to claim a lien for labor, services, or materials furnished to the following property:</p><p>Property: {client_address}, {client_city}, {client_state} {client_zip}</p><p>This final waiver and release is provided for the specific scope, payment, project, and contract identified herein.</p><p>Salesperson: {salesperson_name}<br>Project: {project_name}<br>Contract: {contract_subject}</p>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Salesperson Final Payment Affidavit',
            'contract_type' => 'Final Affidavit',
            'content' => '<h1 style="text-align:center;">SALES REPRESENTATIVE FINAL PAYMENT AFFIDAVIT</h1><p>{salesperson_name} certifies that all laborers, employees, suppliers, sub-representatives, material providers, and other parties working under its scope have been paid or will be paid from final payment. Salesperson further certifies that no unpaid claims exist for the assigned project scope except those listed below.</p><h2>Project Information</h2><p>Project: {project_name}<br>Contract: {contract_subject}<br>Contract Value: ${contract_value}</p><h2>Exceptions</h2><p>__________________________________________________________________________</p><h2>Certification</h2><p>Salesperson acknowledges that false statements may result in withholding of payment, contract default, legal action, or other remedies available under Florida law and the subcontract agreement.</p>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Salesperson Document Request Form',
            'contract_type' => 'Document Request',
            'content' => '<h1 style="text-align:center;">SUBCONTRACTOR DOCUMENT REQUEST</h1><p>Smart Choice Contractors USA requests that {salesperson_name} provide the following documentation before work assignment, payment release, or contract approval.</p><table style="width:100%;border-collapse:collapse;"><tr><th style="border:1px solid #111;padding:8px;">Document</th><th style="border:1px solid #111;padding:8px;">Required</th><th style="border:1px solid #111;padding:8px;">Received</th></tr><tr><td style="border:1px solid #111;padding:8px;">W-9</td><td style="border:1px solid #111;padding:8px;">Yes</td><td style="border:1px solid #111;padding:8px;">____</td></tr><tr><td style="border:1px solid #111;padding:8px;">General Liability Insurance</td><td style="border:1px solid #111;padding:8px;">Yes</td><td style="border:1px solid #111;padding:8px;">____</td></tr><tr><td style="border:1px solid #111;padding:8px;">Workers Compensation Or Exemption</td><td style="border:1px solid #111;padding:8px;">Yes</td><td style="border:1px solid #111;padding:8px;">____</td></tr><tr><td style="border:1px solid #111;padding:8px;">License Verification</td><td style="border:1px solid #111;padding:8px;">If Applicable</td><td style="border:1px solid #111;padding:8px;">____</td></tr></table>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Permit Cancellation Request',
            'contract_type' => 'Permit Cancellation',
            'content' => '<h1 style="text-align:center;">PERMIT CANCELLATION REQUEST</h1><p>This document is used to request cancellation, closure, or correction of a permit record related to a project when work has been cancelled, reassigned, completed under another permit, or requires administrative correction.</p><p>Project: {project_name}<br>Property: {client_address}, {client_city}, {client_state} {client_zip}<br>Permit Number: ______________________</p><p>Reason For Request: __________________________________________________________________________</p><p>Authorized Representative: __________________________ Date: _______________</p>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Credit Card Authorization Form',
            'contract_type' => 'Credit Card Authorization',
            'content' => '<h1 style="text-align:center;">CREDIT CARD AUTHORIZATION FORM</h1><p>I authorize Smart Choice Contractors USA to charge the credit card listed below for approved project charges, deposits, permit payments, material purchases, change orders, or other authorized construction-related payments.</p><p>Cardholder Name: __________________________<br>Billing Address: __________________________<br>Last Four Digits: __________ Expiration: ____ / ____<br>Authorized Amount: $________________</p><p>This authorization may be stored in the project CRM record. Credit card processing fees may apply where permitted.</p><p>Cardholder Signature: __________________________ Date: _______________</p>',
        ];


        $templates[] = [
            'name' => 'Commission Sales Representative Agreement',
            'contract_type' => 'Commission Agreement',
            'content' => '<h1 style="text-align:center;">COMMISSION SALES REPRESENTATIVE AGREEMENT</h1><h3 style="text-align:center;">Smart Choice Contractors USA</h3><p>This agreement defines the commission relationship between Smart Choice Contractors USA and {salesperson_name}. The representative may be assigned leads, estimates, proposals, invoices, follow-up responsibilities, CRM updates, sales documentation, project communication, and customer coordination duties.</p><h2>Commission Terms</h2><p>Commission is calculated only on approved invoices, collected payments, and company policy. If an invoice is partially paid, the commission may be calculated based on the collected amount rather than the full invoice value. Any refunds, chargebacks, cancellations, collection disputes, customer non-payment, or project cancellation may reduce or delay commission payment.</p><h2>Sales Documentation</h2><p>The representative must keep accurate CRM notes, customer contact history, proposal details, estimate notes, project scope, follow-up activity, financing notes, payment status, and issue notes.</p><h2>Payment Timing</h2><p>Commission payments are not due until the company receives payment from the customer and verifies the invoice, project status, and applicable company policies.</p><h2>Confidentiality</h2><p>The representative shall protect all company pricing, customer information, project information, CRM access, financing information, subcontractor information, and internal business methods.</p>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Sales Manager Override Agreement',
            'contract_type' => 'Sales Manager Agreement',
            'content' => '<h1 style="text-align:center;">SALES MANAGER OVERRIDE AGREEMENT</h1><p>This agreement applies to a Sales Manager or Sales Director assigned to supervise sales representatives, review opportunities, support closing activity, verify CRM documentation, and monitor sales performance.</p><h2>Manager Responsibilities</h2><ul><li>Monitor assigned sales representatives.</li><li>Review lead follow-up and CRM notes.</li><li>Support estimate and proposal strategy.</li><li>Review open invoices and unpaid balances.</li><li>Coordinate with office staff regarding commissions and collection status.</li></ul><h2>Override Compensation</h2><p>Manager override compensation is based on written company policy, assigned sales team, collected payments, and verified invoice status.</p>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Sales Onboarding Document Checklist',
            'contract_type' => 'Onboarding Checklist',
            'content' => '<h1 style="text-align:center;">SALES ONBOARDING DOCUMENT CHECKLIST</h1><p>The following documents may be required before a salesperson is approved for full access or commission payments.</p><table style="width:100%;border-collapse:collapse;"><tr><th style="border:1px solid #111;padding:8px;">Document</th><th style="border:1px solid #111;padding:8px;">Required</th><th style="border:1px solid #111;padding:8px;">Received</th></tr><tr><td style="border:1px solid #111;padding:8px;">Driver License Or Government ID</td><td style="border:1px solid #111;padding:8px;">Yes</td><td style="border:1px solid #111;padding:8px;">____</td></tr><tr><td style="border:1px solid #111;padding:8px;">W-9 Or W-4</td><td style="border:1px solid #111;padding:8px;">Yes</td><td style="border:1px solid #111;padding:8px;">____</td></tr><tr><td style="border:1px solid #111;padding:8px;">I-9 Supporting Documents If Employee</td><td style="border:1px solid #111;padding:8px;">If Applicable</td><td style="border:1px solid #111;padding:8px;">____</td></tr><tr><td style="border:1px solid #111;padding:8px;">Direct Deposit Form</td><td style="border:1px solid #111;padding:8px;">Optional</td><td style="border:1px solid #111;padding:8px;">____</td></tr><tr><td style="border:1px solid #111;padding:8px;">Signed Sales Agreement</td><td style="border:1px solid #111;padding:8px;">Yes</td><td style="border:1px solid #111;padding:8px;">____</td></tr></table>' . $commonSignature,
        ];

        $templates[] = [
            'name' => 'Sales Representative Policy Acknowledgment',
            'contract_type' => 'Policy Acknowledgment',
            'content' => '<h1 style="text-align:center;">SALES REPRESENTATIVE POLICY ACKNOWLEDGMENT</h1><p>{salesperson_name} acknowledges receipt and understanding of Smart Choice Contractors USA sales policies, including customer communication standards, CRM documentation rules, estimate accuracy requirements, lead handling rules, commission conditions, confidentiality requirements, and professional conduct expectations.</p><h2>Initial Acknowledgments</h2><table style="width:100%;border-collapse:collapse;"><tr><td style="border:1px solid #111;padding:8px;">I understand that all leads and customer information belong to Smart Choice Contractors USA.</td><td style="border:1px solid #111;padding:8px;">{salesperson_initials}</td></tr><tr><td style="border:1px solid #111;padding:8px;">I understand commissions are based on collected revenue and company policy.</td><td style="border:1px solid #111;padding:8px;">{salesperson_initials}</td></tr><tr><td style="border:1px solid #111;padding:8px;">I agree to update the CRM with accurate notes, customer communication, and project details.</td><td style="border:1px solid #111;padding:8px;">{salesperson_initials}</td></tr></table>' . $commonSignature,
        ];

        foreach ($templates as $tpl) {
            $existing = $CI->db->where('name', $tpl['name'])->get($table)->row();
            $data = [
                'name' => $tpl['name'],
                'contract_type' => $tpl['contract_type'],
                'content' => $tpl['content'],
                'created_by' => get_staff_user_id() ?: 0,
                'datecreated' => $now,
                'dateupdated' => $now,
            ];
            if ($existing) {
                $CI->db->where('id', (int) $existing->id)->update($table, [
                    'contract_type' => $tpl['contract_type'],
                    'content' => $tpl['content'],
                    'dateupdated' => $now,
                ]);
            } else {
                $CI->db->insert($table, $data);
            }
        }
    }
}

sales_center_seed_defaults();
sales_center_seed_professional_templates_v200();
sales_center_insert_email_templates();


if (!$CI->db->table_exists(db_prefix() . 'sales_center_commissions')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "sales_center_commissions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `salesperson_id` int(11) NOT NULL,
        `invoice_id` int(11) NULL,
        `payment_id` int(11) NULL,
        `invoice_total` decimal(15,2) NOT NULL DEFAULT 0.00,
        `amount_collected` decimal(15,2) NOT NULL DEFAULT 0.00,
        `commission_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
        `commission_earned` decimal(15,2) NOT NULL DEFAULT 0.00,
        `commission_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
        `commission_owed` decimal(15,2) NOT NULL DEFAULT 0.00,
        `status` varchar(50) NOT NULL DEFAULT 'pending',
        `issue_notes` text NULL,
        `datecreated` datetime NOT NULL,
        `dateupdated` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `salesperson_id` (`salesperson_id`),
        KEY `invoice_id` (`invoice_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


sales_center_add_column('sales_center_commissions', 'issue_notes', 'text NULL');
sales_center_add_column('sales_center_commissions', 'manager_id', 'int(11) NULL');
sales_center_add_column('sales_center_commissions', 'department_id', 'int(11) NULL');


// Version 1.3.3 safe sales document integration. This is additive only and preserves CRM business data.
if (!$CI->db->table_exists(db_prefix() . 'sales_center_document_links')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . "sales_center_document_links` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `salesperson_id` int(11) NOT NULL DEFAULT 0,
        `manager_id` int(11) NULL,
        `department_id` int(11) NULL,
        `rel_type` varchar(50) NOT NULL DEFAULT 'invoice',
        `rel_id` int(11) NOT NULL DEFAULT 0,
        `document_total` decimal(15,2) NOT NULL DEFAULT 0.00,
        `amount_collected` decimal(15,2) NOT NULL DEFAULT 0.00,
        `commission_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
        `commission_earned` decimal(15,2) NOT NULL DEFAULT 0.00,
        `commission_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
        `commission_owed` decimal(15,2) NOT NULL DEFAULT 0.00,
        `status` varchar(50) NOT NULL DEFAULT 'pending',
        `issue_notes` text NULL,
        `created_by` int(11) NOT NULL DEFAULT 0,
        `datecreated` datetime NOT NULL,
        `dateupdated` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `salesperson_id` (`salesperson_id`),
        KEY `rel_type` (`rel_type`),
        KEY `rel_id` (`rel_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

sales_center_add_column('sales_center_commissions', 'proposal_id', 'int(11) NULL');
sales_center_add_column('sales_center_commissions', 'estimate_id', 'int(11) NULL');
sales_center_add_column('sales_center_commissions', 'credit_note_id', 'int(11) NULL');
sales_center_add_column('sales_center_commissions', 'rel_type', "varchar(50) NOT NULL DEFAULT 'invoice'");
sales_center_add_column('sales_center_commissions', 'rel_id', 'int(11) NULL');
sales_center_add_column('sales_center_commissions', 'document_total', 'decimal(15,2) NOT NULL DEFAULT 0.00');

update_option('sales_center_version', '1.3.6');
update_option('sales_center_native_pdf_email_fix', '1');
update_option('sales_center_integrated_core_sales', '1');

update_option('sales_center_upgrade_rescue_136', date('Y-m-d H:i:s'));
