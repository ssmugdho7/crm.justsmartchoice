<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_130 extends App_module_migration
{
    public function up()
    {
        update_option('cabinet_maker_version', '1.3.0');
        update_option('cabinet_maker_editor_mode', 'exact_layout');
    }
}
