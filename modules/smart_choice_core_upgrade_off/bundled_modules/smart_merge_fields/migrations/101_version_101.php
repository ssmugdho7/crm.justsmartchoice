<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        add_option('smart_merge_fields_help_guide_version', '1.0.1');
        add_option('smart_merge_fields_last_modified', '2026-06-30');
    }
}
