<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_171 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Smart Choice Contractors safe migration shim for broken version jumps.
        $tables = [
            db_prefix() . 'rec_campaign_form_web',
            db_prefix() . 'rec_candidate',
            db_prefix() . 'rec_applied_jobs',
        ];

        if ($CI->db->table_exists(db_prefix() . 'rec_campaign_form_web')) {
            if (!$CI->db->field_exists('form_key', db_prefix() . 'rec_campaign_form_web')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . "rec_campaign_form_web` ADD `form_key` VARCHAR(64) NULL");
            } else {
                $CI->db->query('ALTER TABLE `' . db_prefix() . "rec_campaign_form_web` MODIFY `form_key` VARCHAR(64) NULL");
            }
            if (!$CI->db->field_exists('form_data', db_prefix() . 'rec_campaign_form_web')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . "rec_campaign_form_web` ADD `form_data` LONGTEXT NULL");
            } else {
                $CI->db->query('ALTER TABLE `' . db_prefix() . "rec_campaign_form_web` MODIFY `form_data` LONGTEXT NULL");
            }
            if (!$CI->db->field_exists('success_submit_msg', db_prefix() . 'rec_campaign_form_web')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . "rec_campaign_form_web` ADD `success_submit_msg` TEXT NULL");
            }
        }

        if ($CI->db->table_exists(db_prefix() . 'rec_candidate')) {
            $columns = [
                'candidate_name' => 'VARCHAR(200) NULL',
                'last_name' => 'VARCHAR(200) NULL',
                'email' => 'VARCHAR(200) NULL',
                'phonenumber' => 'VARCHAR(50) NULL',
                'experience' => 'TEXT NULL',
                'introduction' => 'TEXT NULL',
                'rec_campaign' => 'INT(11) NULL',
            ];
            foreach ($columns as $column => $definition) {
                if (!$CI->db->field_exists($column, db_prefix() . 'rec_candidate')) {
                    $CI->db->query('ALTER TABLE `' . db_prefix() . "rec_candidate` ADD `$column` $definition");
                }
            }
        }

        if ($CI->db->table_exists(db_prefix() . 'rec_applied_jobs')) {
            if (!$CI->db->field_exists('activate', db_prefix() . 'rec_applied_jobs')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . "rec_applied_jobs` ADD `activate` VARCHAR(10) NULL DEFAULT '1'");
            } else {
                $CI->db->query('ALTER TABLE `' . db_prefix() . "rec_applied_jobs` MODIFY `activate` VARCHAR(10) NULL DEFAULT '1'");
            }
        }
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
