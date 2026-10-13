<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance(); $CI->load->dbforge();
$schemas = [
    'scsl_identities' => [
        'id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
        'audience'=>['type'=>'VARCHAR','constraint'=>10],
        'user_id'=>['type'=>'INT','unsigned'=>true],
        'provider'=>['type'=>'VARCHAR','constraint'=>10],
        'subject_hash'=>['type'=>'VARCHAR','constraint'=>64],
        'enabled'=>['type'=>'TINYINT','default'=>1],
        'created_at'=>['type'=>'BIGINT'],
    ],
    'scsl_transactions' => [
        'state_hash'=>['type'=>'VARCHAR','constraint'=>64],
        'browser_hash'=>['type'=>'VARCHAR','constraint'=>64],
        'ip_hash'=>['type'=>'VARCHAR','constraint'=>64],
        'provider'=>['type'=>'VARCHAR','constraint'=>10],
        'audience'=>['type'=>'VARCHAR','constraint'=>10],
        'intent'=>['type'=>'VARCHAR','constraint'=>10],
        'actor_id'=>['type'=>'INT','unsigned'=>true,'default'=>0],
        'nonce'=>['type'=>'VARCHAR','constraint'=>64],
        'verifier'=>['type'=>'VARCHAR','constraint'=>128],
        'created_at'=>['type'=>'BIGINT'],
    ],
];
foreach ($schemas as $name=>$fields) {
    $table = db_prefix() . $name;
    if (!$CI->db->table_exists($table)) {
        $CI->dbforge->add_field($fields);
        $CI->dbforge->add_key($name === 'scsl_identities' ? 'id' : 'state_hash', true);
        $CI->dbforge->add_key($name === 'scsl_identities' ? ['audience','user_id'] : 'created_at');
        $forgeName = $CI->db->dbprefix && strpos($table, $CI->db->dbprefix) === 0 ? substr($table, strlen($CI->db->dbprefix)) : $table;
        if (!$CI->dbforge->create_table($forgeName, true, ['ENGINE'=>'InnoDB'])) { show_error(_l('scsl_install_failed')); exit; }
    }
    $indexes = array_column($CI->db->query('SHOW INDEX FROM `' . $table . '`')->result_array(), 'Key_name');
    $requiredIndexes = $name === 'scsl_identities' ? [
        'scsl_subject'=>'UNIQUE INDEX `scsl_subject` (`audience`,`provider`,`subject_hash`)',
        'scsl_owner_provider'=>'UNIQUE INDEX `scsl_owner_provider` (`audience`,`user_id`,`provider`)',
    ] : ['scsl_ip_created'=>'INDEX `scsl_ip_created` (`ip_hash`,`created_at`)'];
    foreach ($requiredIndexes as $key=>$definition) {
        if (!in_array($key,$indexes,true) && !$CI->db->query('ALTER TABLE `' . $table . '` ADD ' . $definition)) {
            show_error(_l('scsl_install_failed')); exit;
        }
    }
}
add_option('scsl_client_registration', '1');
foreach (['google','facebook','apple'] as $provider) {
    foreach (['client_id'=>'','client_secret'=>'','staff'=>'0','client'=>'0'] as $key=>$default) {
        add_option('scsl_' . $provider . '_' . $key, $default);
    }
}
add_option('scsl_facebook_graph_version', 'v26.0');
