<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_151 extends App_module_migration
{
    public function up()
    {
        update_option('perfex_office_theme_version','1.5.1');
        update_option('perfex_office_theme_client_header_native','1');
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
