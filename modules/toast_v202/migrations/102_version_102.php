<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        update_option('toast_master_enable', '1');
        update_option('toaster_style', get_option('toaster_style') ?: '1');
        update_option('toaster_position', get_option('toaster_position') ?: 'top-right');
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
