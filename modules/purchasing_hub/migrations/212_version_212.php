<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_212 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_purchase_use_crm_items', '1');
    }
    public function down()
    {
        // Safe no-op rollback for Smart Choice patched migration.
    }
}
