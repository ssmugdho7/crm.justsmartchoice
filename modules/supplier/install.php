<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'suppliers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "suppliers` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `supplier_name` VARCHAR(191) NOT NULL,
        `website` VARCHAR(255) NULL,
        `phone` VARCHAR(80) NULL,
        `email` VARCHAR(191) NULL,
        `trade` VARCHAR(120) NULL,
        `supplier_type` VARCHAR(120) NULL,
        `registration_url` VARCHAR(255) NULL,
        `short_description` VARCHAR(255) NULL,
        `notes` TEXT NULL,
        `location_id` INT(11) DEFAULT 0,
        `only_me` TINYINT(1) DEFAULT 0,
        `staff_id` INT(11) DEFAULT 0,
        `created_date` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `location_id` (`location_id`),
        KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'supplier_locations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "supplier_locations` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `location_name` VARCHAR(191) NOT NULL,
        `description` TEXT NULL,
        `ordering` INT(11) DEFAULT 0,
        `staff_id` INT(11) DEFAULT 0,
        `created_date` DATETIME NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'supplier_documents')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "supplier_documents` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `supplier_id` INT(11) NOT NULL,
        `file_name` VARCHAR(191) NOT NULL,
        `original_name` VARCHAR(191) NOT NULL,
        `file_path` VARCHAR(255) NOT NULL,
        `file_type` VARCHAR(120) NULL,
        `file_size` INT(11) DEFAULT 0,
        `description` VARCHAR(255) NULL,
        `uploaded_by` INT(11) DEFAULT 0,
        `dateadded` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `supplier_id` (`supplier_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Import legacy bookmarks once if the old module existed.
if ($CI->db->table_exists(db_prefix() . 'bookmarks') && $CI->db->count_all(db_prefix() . 'suppliers') === 0) {
    $legacy = $CI->db->get(db_prefix() . 'bookmarks')->result_array();
    foreach ($legacy as $row) {
        $CI->db->insert(db_prefix() . 'suppliers', [
            'supplier_name'      => $row['title'] ?? 'Imported Supplier',
            'website'            => $row['url'] ?? '',
            'phone'              => $row['vendor_phone'] ?? '',
            'email'              => $row['vendor_email'] ?? '',
            'trade'              => $row['trade'] ?? '',
            'supplier_type'      => $row['vendor_type'] ?? '',
            'registration_url'   => $row['registration_url'] ?? '',
            'short_description'  => isset($row['description']) ? mb_substr(strip_tags((string)$row['description']), 0, 180) : '',
            'notes'              => $row['notes'] ?? '',
            'location_id'        => (int)($row['location_id'] ?? 0),
            'only_me'            => (int)($row['only_me'] ?? 0),
            'staff_id'           => (int)($row['staff_id'] ?? get_staff_user_id()),
            'created_date'       => $row['created_date'] ?? date('Y-m-d H:i:s'),
        ]);
    }
}
