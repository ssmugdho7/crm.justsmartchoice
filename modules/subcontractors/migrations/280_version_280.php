<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_280 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'smartsource_subcontractor_contracts')) {
            $fields = $CI->db->list_fields($prefix . 'smartsource_subcontractor_contracts');
            if (!in_array('template_id', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $prefix . "smartsource_subcontractor_contracts` ADD `template_id` INT(11) NULL AFTER `contract_type`");
            }
            if (!in_array('clientid', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $prefix . "smartsource_subcontractor_contracts` ADD `clientid` INT(11) NULL AFTER `project_id`");
            }
            if (!in_array('public_token', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $prefix . "smartsource_subcontractor_contracts` ADD `public_token` VARCHAR(64) NULL AFTER `status`");
            }
            if (!in_array('email_last_sent_at', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $prefix . "smartsource_subcontractor_contracts` ADD `email_last_sent_at` DATETIME NULL AFTER `dateupdated`");
            }
            if (!in_array('xml_last_generated_at', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $prefix . "smartsource_subcontractor_contracts` ADD `xml_last_generated_at` DATETIME NULL AFTER `email_last_sent_at`");
            }
        }
        if ($CI->db->table_exists($prefix . 'smartsource_subcontractor_templates')) {
            $fields = $CI->db->list_fields($prefix . 'smartsource_subcontractor_templates');
            if (!in_array('cover_html', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $prefix . "smartsource_subcontractor_templates` ADD `cover_html` LONGTEXT NULL AFTER `content`");
            }
        }
        if (!$CI->db->table_exists($prefix . 'smartsource_subcontractor_contract_comments')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "smartsource_subcontractor_contract_comments` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `contract_id` INT(11) NOT NULL,
                `staffid` INT(11) NULL,
                `comment` MEDIUMTEXT NULL,
                `dateadded` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `contract_id` (`contract_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }
        return true;
    }

    public function down()
    {
        return true;
    }
}
