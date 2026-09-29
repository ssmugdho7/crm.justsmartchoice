<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_109 extends App_module_migration
{
    public function up()
    {
        if (function_exists('smart_choice_links_ensure_database')) {
            smart_choice_links_ensure_database();
        }
        update_option('smart_choice_links_panel_width', get_option('smart_choice_links_panel_width') ?: '285');
        update_option('smart_choice_links_row_height', get_option('smart_choice_links_row_height') ?: '34');
        update_option('smart_choice_links_icon_size', get_option('smart_choice_links_icon_size') ?: '16');
        update_option('smart_choice_links_font_size', get_option('smart_choice_links_font_size') ?: '13');
        update_option('smart_choice_links_version', '1.0.9');
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
