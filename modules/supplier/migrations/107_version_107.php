<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_107 extends App_module_migration
{
    public function up()
    {
        // Supplier v1.0.7 UI/email delivery compatibility update. No database changes required.
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
