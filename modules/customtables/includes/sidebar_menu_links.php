<?php

defined('BASEPATH') || exit('No direct script access allowed');

hooks()->add_action('admin_init', function () {
    if (has_permission('customtables', '', 'view')) {
        get_instance()->app_menu->add_setup_menu_item('customtables', [
            'slug'     => 'customtables',
            'name'     => _l('customtables'),
            'icon'     => 'fa-solid fa-table-cells-large',
            'position' => 35,
        ]);
        get_instance()->app_menu->add_setup_children_item('customtables', [
            'slug'     => 'tablecustomize',
            'name'     => _l('tablecustomize'),
            'href'     => admin_url('customtables/index'),
            'position' => 28,
        ]);
        get_instance()->app_menu->add_setup_children_item('customtables', [
            'slug'     => 'table_design',
            'name'     => _l('table_design'),
            'href'     => admin_url('customtables/tableDesign'),
            'position' => 29,
        ]);
    }
});
