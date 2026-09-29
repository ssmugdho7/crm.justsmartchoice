<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_200 extends App_module_migration
{
    public function up()
    {
        if (!option_exists('customtables_enabled')) { add_option('customtables_enabled', '1'); }
        if (!option_exists('table_custom_style')) { add_option('table_custom_style', '[]'); }
        if (!option_exists('custom_css_for_table')) { add_option('custom_css_for_table', ''); }
        if (!option_exists('customtables_module_description')) {
            add_option('customtables_module_description', 'Custom Data Tables helps staff personalize CRM tables, control columns, style table views, and improve project, sales, and office workflow visibility.');
        }
        return true;
    }
    public function down() {}
}
