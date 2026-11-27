<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_108 extends App_module_migration {
    public function up() {
        // UI/navigation-only upgrade. Preserve existing tables, tasks, checkmarks and reports.
        update_option('smart_daily_productivity_version', '1.0.8');
    }
}
