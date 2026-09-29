<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_329 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        require_once module_dir_path('subcontractors', 'install.php');
        if ($CI->db->table_exists(db_prefix() . 'smartsource_subcontractors') && !$CI->db->field_exists('staff_id', db_prefix() . 'smartsource_subcontractors')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'smartsource_subcontractors` ADD `staff_id` INT(11) NULL AFTER `portal_token`');
        }
        if (function_exists('smartsource_insert_email_templates')) {
            smartsource_insert_email_templates();
        }
    }
}
