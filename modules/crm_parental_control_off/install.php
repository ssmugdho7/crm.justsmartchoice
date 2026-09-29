<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'crm_pc_runs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "crm_pc_runs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `run_type` varchar(50) NOT NULL DEFAULT 'preview',
        `status` varchar(50) NOT NULL DEFAULT 'created',
        `summary` longtext NULL,
        `snapshot_json` longtext NULL,
        `created_by` int(11) NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'crm_pc_departments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "crm_pc_departments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `code` varchar(25) NOT NULL,
        `name` varchar(191) NOT NULL,
        `description` text NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'crm_pc_role_templates')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "crm_pc_role_templates` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `role_name` varchar(191) NOT NULL,
        `department_code` varchar(25) NOT NULL,
        `security_level` int(11) NOT NULL DEFAULT 2,
        `rules_json` longtext NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `role_name` (`role_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'crm_pc_backups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "crm_pc_backups` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `backup_type` varchar(50) NOT NULL,
        `file_path` text NULL,
        `file_size` bigint(20) NULL,
        `status` varchar(50) NOT NULL DEFAULT 'created',
        `message` text NULL,
        `created_by` int(11) NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'crm_pc_audit_log')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "crm_pc_audit_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `event_type` varchar(100) NOT NULL,
        `details` longtext NULL,
        `created_by` int(11) NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

$departments = [
    ['EXEC','Executive','Company ownership and executive decisions'],
    ['ADMIN','Administration','Office operations and customer administration'],
    ['ACCT','Accounting','Accounting and financial management'],
    ['SALES','Sales','Leads, estimates, proposals, customers, and commissions'],
    ['OPS','Operations','Project scheduling and project management'],
    ['ENG','Engineering','Plans, permits, drawings, inspections, and estimating intelligence'],
    ['FIELD','Field Operations','Installers, foremen, superintendents, and subcontractor execution'],
    ['PUR','Purchasing','Vendors, suppliers, purchasing, and procurement'],
    ['HR','Human Resources','Hiring, records, payroll support, and employee management'],
    ['MKT','Marketing','Website, SEO, campaigns, advertising, and social media'],
    ['IT','Information Technology','CRM, servers, automation, security, backup, API, and logs'],
];
foreach ($departments as $dept) {
    if (!$CI->db->where('code', $dept[0])->get(db_prefix() . 'crm_pc_departments')->row()) {
        $CI->db->insert(db_prefix() . 'crm_pc_departments', [
            'code' => $dept[0], 'name' => $dept[1], 'description' => $dept[2], 'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

$roles = [
    ['Owner','EXEC',5], ['Management','EXEC',4], ['General Manager','EXEC',4],
    ['Office Manager','ADMIN',3], ['Office Assistant','ADMIN',2],
    ['Accounting Manager','ACCT',3], ['Accounting Staff','ACCT',3],
    ['Sales Manager','SALES',3], ['Sales Representative','SALES',2], ['Canvasser','SALES',2],
    ['Project Manager','OPS',3], ['Estimator','OPS',3], ['Dispatcher','OPS',2],
    ['Engineer','ENG',3], ['Superintendent','FIELD',3], ['Field Technician','FIELD',2], ['Installer','FIELD',2], ['Subcontractor','FIELD',1],
    ['Purchasing Manager','PUR',3], ['Marketing Specialist','MKT',2],
    ['IT Manager','IT',4], ['CRM Administrator','IT',4], ['Automation Bot','IT',1], ['Employee','ADMIN',2]
];
foreach ($roles as $role) {
    if (!$CI->db->where('role_name', $role[0])->get(db_prefix() . 'crm_pc_role_templates')->row()) {
        $CI->db->insert(db_prefix() . 'crm_pc_role_templates', [
            'role_name' => $role[0], 'department_code' => $role[1], 'security_level' => $role[2], 'rules_json' => json_encode([]), 'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

if (!get_option('crm_parental_control_version')) {
    add_option('crm_parental_control_version', '1.0.3');
} else {
    update_option('crm_parental_control_version', '1.0.3');
}
