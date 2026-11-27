<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_107 extends App_module_migration
{
    public function up(): void
    {
        // Existing tables and user records are untouched; sample tasks are imported only on request.
        update_option('smart_daily_productivity_version', '1.0.7');
        update_option('smart_daily_productivity_migration_107_completed', date('Y-m-d H:i:s'));
    }
    public function down() { return true; }
}
