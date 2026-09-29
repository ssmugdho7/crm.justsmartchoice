<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_430 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        $columns = [
            'clients' => 'google_map_link', 'leads' => 'google_map_link', 'invoices' => 'google_map_link',
            'estimates' => 'google_map_link', 'proposals' => 'google_map_link', 'contracts' => 'google_map_link',
            'projects' => 'google_map_link', 'creditnotes' => 'google_map_link',
        ];
        foreach ($columns as $table => $column) {
            $full = db_prefix() . $table;
            if ($CI->db->table_exists($full) && !$CI->db->field_exists($column, $full)) {
                $CI->db->query('ALTER TABLE `'.$full.'` ADD `'.$column.'` TEXT NULL');
            }
        }
        if (get_option('solar_pro_google_api_key') === false) add_option('solar_pro_google_api_key', '');
        if (get_option('solar_pro_google_geocoding_api_key') === false) add_option('solar_pro_google_geocoding_api_key', '');
        if (get_option('solar_pro_google_required_quality') === false) add_option('solar_pro_google_required_quality', 'HIGH');
        update_option('sc_crm_build_version', '4.3.0');
        update_option('smart_choice_crm_build', '4.3.0');
        update_option('smart_choice_crm_current_version', '4.3.0');
    }
    public function down() {}
}
