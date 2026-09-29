<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_208 extends App_module_migration
{
    public function up()
    {
        $options = [
            'prchat_template_ui_enabled' => '1',
            'prchat_template_navbar_height' => '58',
            'prchat_template_logo_max_height' => '52',
            'prchat_template_hamburger_size' => '22',
            'prchat_template_dropdown_max_height' => '300',
            'prchat_template_table_font_size' => '12',
            'prchat_template_full_name_width' => '170',
            'prchat_template_email_width' => '145',
            'prchat_template_client_login_font_size' => '12',
            'prchat_template_client_login_padding' => '7',
            'prchat_template_gradient_start' => '#0f766e',
            'prchat_template_gradient_end' => '#1d4ed8',
            'prchat_template_gradient_text' => '#ffffff',
            'prchat_template_hover_text' => '#111827',
            'prchat_template_button_bg' => '#169179',
            'prchat_template_button_text' => '#ffffff',
            'prchat_template_saved_profile' => '',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === false) {
                add_option($name, $value);
            }
        }
    }
}
