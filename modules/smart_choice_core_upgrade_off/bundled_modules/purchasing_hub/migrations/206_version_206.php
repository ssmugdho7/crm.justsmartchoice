<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_206 extends App_module_migration
{
    public function up()
    {
        update_option('purchasing_hub_module_version', '2.0.6');
    }
}
