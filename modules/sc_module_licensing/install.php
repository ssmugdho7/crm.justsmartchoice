<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$CI->load->dbforge();
$schemas = [
    'scl_licenses' => [
        'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
        'key_hash' => ['type'=>'VARCHAR','constraint'=>64],
        'company' => ['type'=>'VARCHAR','constraint'=>191],
        'module' => ['type'=>'VARCHAR','constraint'=>80],
        'site' => ['type'=>'VARCHAR','constraint'=>255],
        'expires' => ['type'=>'BIGINT'],
        'active' => ['type'=>'TINYINT','default'=>1],
        'created_by' => ['type'=>'INT','unsigned'=>true],
        'created_at' => ['type'=>'BIGINT'],
    ],
    'scl_activations' => [
        'module' => ['type'=>'VARCHAR','constraint'=>80],
        'key_encrypted' => ['type'=>'TEXT'],
        'certificate' => ['type'=>'TEXT'],
        'checked_at' => ['type'=>'BIGINT','default'=>0],
    ],
    'scl_audit' => [
        'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
        'license_id' => ['type'=>'INT','unsigned'=>true],
        'actor' => ['type'=>'INT','unsigned'=>true],
        'action' => ['type'=>'VARCHAR','constraint'=>40],
        'occurred_at' => ['type'=>'BIGINT'],
    ],
];
foreach ($schemas as $name => $fields) {
    if (!$CI->db->table_exists(db_prefix() . $name)) {
        $CI->dbforge->add_field($fields);
        $CI->dbforge->add_key($name === 'scl_activations' ? 'module' : 'id', true);
        if ($name === 'scl_licenses') {
            $CI->dbforge->add_key('created_by');
        }
        $table = db_prefix() . $name;
        // DB Forge prepends the driver prefix; Perfex installations may use an empty driver prefix.
        $forgeName = $CI->db->dbprefix && strpos($table, $CI->db->dbprefix) === 0 ? substr($table, strlen($CI->db->dbprefix)) : $table;
        if (!$CI->dbforge->create_table($forgeName, true, ['ENGINE'=>'InnoDB'])) { show_error(_l('scl_install_failed')); exit; }
    }
}
$table = db_prefix() . 'scl_licenses';
$indexes = array_column($CI->db->query('SHOW INDEX FROM `' . $table . '`')->result_array(), 'Key_name');
if (!in_array('scl_key_hash', $indexes, true)
    && !$CI->db->query('CREATE UNIQUE INDEX `scl_key_hash` ON `' . $table . '` (`key_hash`)')) {
    show_error(_l('scl_install_failed')); exit;
}
add_option('scl_installation', bin2hex(random_bytes(32)));
