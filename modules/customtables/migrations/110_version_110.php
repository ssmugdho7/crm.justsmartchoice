<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration {
    public function up() {
        $data = get_instance()->db->select('staffid')->where('admin', 1)->get(db_prefix() . 'staff')->result();
        $admin = array_column($data, "staffid");
        get_instance()->config->load('customtables/config');
        $tabs = array_keys(config_item('datatables_all_tabs'));
        foreach ($admin as $id) {
            foreach ($tabs as $key) {
                if (!empty(get_option($key . '_show_columns'))) {
                    update_staff_meta($id, $key . '_show_columns', get_option($key . '_show_columns'));
                }
            }
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
