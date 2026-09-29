<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_145 extends App_module_migration {
    public function up() {
        if (get_option('acc_training_video_url') === false) { add_option('acc_training_video_url', 'https://www.youtube.com/embed/'); }
        if (get_option('acc_quickbooks_desktop_export_version') === false) { add_option('acc_quickbooks_desktop_export_version', 'Enterprise Desktop'); }
        if (get_option('acc_help_guide_enabled') === false) { add_option('acc_help_guide_enabled', '1'); }
    }
    public function down() { }
}
