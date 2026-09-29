<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        add_option('qs_aws_key', '');
        add_option('qs_aws_secret', '');
        add_option('qs_aws_region', '');
        add_option('qs_aws_bucket', '');
        add_option('qs_storage_engine', 'server');

        $CI = &get_instance();

        $CI->db->query('ALTER TABLE `' . db_prefix() . 'quick_share_uploads` ADD `destination` INT DEFAULT 0 AFTER `ip`;');

    }
}