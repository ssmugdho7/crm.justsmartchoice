<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        // Perform database upgrade here
        if (!$CI->db->table_exists(db_prefix() . 'flexiblewa_generic_rules')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexiblewa_generic_rules` (
            `id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `rule_name` varchar(100) NOT NULL,
            `when_event` varchar(100) NOT NULL,
            `rule_value` MEDIUMTEXT NOT NULL,
            `rule_type` varchar(100) NOT NULL,
            `rule_action` varchar(100) NOT NULL,
            `order` int(10) NOT NULL DEFAULT 0,
            `date_created` datetime NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
          
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexiblewa_generic_rules`
            ADD PRIMARY KEY (`id`);');
          
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexiblewa_generic_rules`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
          }
          
    }

    public function down()
    {
        // Safe no-op rollback for Smart Choice structural compatibility.
        return true;
    }
}