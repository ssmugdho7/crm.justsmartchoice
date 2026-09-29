<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 204 - Smart Choice Purchasing Hub route/view repair.
 *
 * This migration is intentionally safe and non-destructive.
 * It does not drop tables or remove purchase records.
 */
class Migration_Version_204 extends App_module_migration
{
    public function up()
    {
        update_option('purchasing_hub_module_version', '2.0.4');
        add_option('purchasing_hub_safe_uninstall_mode', '1');

        return true;
    }

    public function down()
    {
        return true;
    }
}
