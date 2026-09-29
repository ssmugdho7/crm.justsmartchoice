<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_359 extends CI_Migration
{
    public function up()
    {
        if (function_exists('update_option')) {
            update_option('smart_choice_core_version', '3.5.9 SC');
            update_option('smart_choice_pdf_signature_scale_percent', get_option('smart_choice_pdf_signature_scale_percent') ?: '100');
            update_option('smart_choice_quick_create_compact', '1');
        }
    }
    public function down() { }
}
