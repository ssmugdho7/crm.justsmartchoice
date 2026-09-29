<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

/**
 * Create procurement tables if they do not exist
 */

$CI->load->dbforge();

// Main items table
if (!$CI->db->table_exists(db_prefix() . 'procurement_items')) {
    $fields = [
        'id' => [
            'type'           => 'INT',
            'constraint'     => 11,
            'unsigned'       => true,
            'auto_increment' => true,
        ],
        'project_id' => [
            'type'       => 'INT',
            'constraint' => 11,
            'null'       => true,
        ],
        'category' => [
            'type'       => 'VARCHAR',
            'constraint' => 191,
            'null'       => true,
        ],
        'item_name' => [
            'type'       => 'VARCHAR',
            'constraint' => 191,
            'null'       => false,
        ],
        'unit' => [
            'type'       => 'VARCHAR',
            'constraint' => 50,
            'null'       => true,
        ],
        'qty' => [
            'type'       => 'DECIMAL',
            'constraint' => '15,2',
            'null'       => true,
        ],
        'quoted_price' => [
            'type'       => 'DECIMAL',
            'constraint' => '15,2',
            'null'       => true,
        ],
        'alt_price' => [
            'type'       => 'DECIMAL',
            'constraint' => '15,2',
            'null'       => true,
        ],
        'marketplace_price' => [
            'type'       => 'DECIMAL',
            'constraint' => '15,2',
            'null'       => true,
        ],
        'supplier_name' => [
            'type'       => 'VARCHAR',
            'constraint' => 191,
            'null'       => true,
        ],
        'supplier_email' => [
            'type'       => 'VARCHAR',
            'constraint' => 191,
            'null'       => true,
        ],
        'source' => [
            'type'       => 'VARCHAR',
            'constraint' => 50,
            'null'       => true,
        ],
        'status' => [
            'type'       => 'VARCHAR',
            'constraint' => 50,
            'null'       => true,
        ],
        'notes' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'attachment' => [
            'type'       => 'VARCHAR',
            'constraint' => 191,
            'null'       => true,
        ],
        'date_created' => [
            'type' => 'DATETIME',
            'null' => true,
        ],
        'date_updated' => [
            'type' => 'DATETIME',
            'null' => true,
        ],
    ];

    $CI->dbforge->add_field($fields);
    $CI->dbforge->add_key('id', true);
    $CI->dbforge->create_table(db_prefix() . 'procurement_items');
}

// Simple suppliers table (optional standalone suppliers)
if (!$CI->db->table_exists(db_prefix() . 'procurement_suppliers')) {
    $fields = [
        'id' => [
            'type'           => 'INT',
            'constraint'     => 11,
            'unsigned'       => true,
            'auto_increment' => true,
        ],
        'name' => [
            'type'       => 'VARCHAR',
            'constraint' => 191,
            'null'       => false,
        ],
        'contact_name' => [
            'type'       => 'VARCHAR',
            'constraint' => 191,
            'null'       => true,
        ],
        'email' => [
            'type'       => 'VARCHAR',
            'constraint' => 191,
            'null'       => true,
        ],
        'phone' => [
            'type'       => 'VARCHAR',
            'constraint' => 50,
            'null'       => true,
        ],
        'website' => [
            'type'       => 'VARCHAR',
            'constraint' => 191,
            'null'       => true,
        ],
        'address' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'is_active' => [
            'type'       => 'TINYINT',
            'constraint' => 1,
            'default'    => 1,
        ],
        'date_created' => [
            'type' => 'DATETIME',
            'null' => true,
        ],
        'date_updated' => [
            'type' => 'DATETIME',
            'null' => true,
        ],
    ];

    $CI->dbforge->add_field($fields);
    $CI->dbforge->add_key('id', true);
    $CI->dbforge->create_table(db_prefix() . 'procurement_suppliers');
}
