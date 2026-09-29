<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Engineering Hub
Description: Engineering Hub is a Smart Choice Contractors construction and engineering management module for Perfex CRM. It connects engineering projects, CRM projects, customers, contractors, drawings, permit documents, plan sets, uploaded files, shared project media, and status tracking in one operational workspace. The module is designed so plans and engineering documents can be uploaded once, stored inside organized project folders, linked to existing CRM projects, reviewed by staff, and tracked through engineering stages such as survey, drafting, permit, installation schedule, and final inspection.
Version: 2.5.5
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
*/

define('ENGINEERING_PROJECTS_MODULE_NAME', 'engineering_projects');
define('PROJECT_FILES_FOLDER', FCPATH . 'uploads/Projects' . '/');
define('DRAWING_FILES_FOLDER', FCPATH . 'uploads/Projects' . '/');
define('DOCUMENT_FILES_FOLDER', FCPATH . 'uploads/Projects' . '/');
$CI = &get_instance();
hooks()->add_action('admin_init', 'engineering_projects_module_init_menu_items');
hooks()->add_action('staff_member_deleted', 'engineering_projects_staff_member_deleted');
hooks()->add_action('admin_init', 'engineering_projects_permissions');

// Add Engineering tab inside Perfex CRM project profile.
hooks()->add_action('admin_init', 'engineering_projects_register_project_tab');
function engineering_projects_register_project_tab()
{
    $CI = &get_instance();
    if (isset($CI->app_tabs)) {
        $CI->app_tabs->add_project_tab('engineering_hub', [
            'name'     => _l('engineering_hub'),
            'icon'     => 'fa fa-cubes',
            'view'     => 'engineering_projects/project_tab',
            'position' => 45,
        ]);
    }
}


// Run lightweight safety migrations on every admin load. This avoids Perfex version-jump migration failures.
hooks()->add_action('admin_init', 'engineering_projects_run_upgrade_database_fixes');
function engineering_projects_run_upgrade_database_fixes()
{
    $CI = &get_instance();
    $install = __DIR__ . '/install.php';
    if (file_exists($install)) {
        require_once($install);
    }
}



function engineering_projects_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'eng_engineering_projects', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function engineering_projects_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('engineering_projects', $capabilities, _l('engineering_projects'));

    // Register documents/drawings/project_files capabilities
    $capset = [
        'capabilities' => [
            'view' => _l('permission_view') . ' (' . _l('permission_global') . ')',
            'view_own' => _l('permission_view_own'),
            'create' => _l('permission_create'),
            'edit' => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];
    register_staff_capabilities('documents', $capset, _l('documents'));
    register_staff_capabilities('drawings', $capset, _l('drawings'));
    register_staff_capabilities('project_files', $capset, _l('project_files'));

}

/**
* Register activation module hook
*/
register_activation_hook(ENGINEERING_PROJECTS_MODULE_NAME, 'engineering_projects_module_activation_hook');

function engineering_projects_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}
/**
* Load the module helper
*/
$CI->load->helper(ENGINEERING_PROJECTS_MODULE_NAME . '/project_files');

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(ENGINEERING_PROJECTS_MODULE_NAME, [ENGINEERING_PROJECTS_MODULE_NAME]);

/**
 * Init engineering_projects module menu items in setup in admin_init hook
 * @return null
 */
function engineering_projects_module_init_menu_items() {
  $CI = &get_instance();

  $CI->app->add_quick_actions_link([
      'name' => _l('engineering_project'),
      'url' => 'engineering_projects/engineering_project',
      'permission' => 'engineering_projects',
      'position' => 59
  ]);

  // Settings link
  $CI->app_menu->add_sidebar_menu_item('engineering_projects_settings', [
      'name'     => _l('settings'),
      'href'     => admin_url('engineering_projects/settings'),
      'icon'     => 'fa fa-cog',
      'position' => 60,
      'permission' => 'engineering_projects',
      'slug' => 'engineering-projects-settings'
  ]);


  if (has_permission('engineering_projects', '', 'view')) {

    $CI->app_menu->add_sidebar_menu_item('engineering_projects', [
        'name' => _l('engineering_hub'),
        'position' => 11,
        'icon' => 'fa fa-cubes',
    ]);
    $CI->app_menu->add_sidebar_children_item('engineering_projects', [
        'slug' => 'engineering_projects',
        'name' => _l('engineering_hub'),
        'href' => admin_url('engineering_projects'),
        'position' => 27,
    ]);
    $CI->app_menu->add_sidebar_children_item('engineering_projects', [
        'slug' => 'contractors',
        'name' => _l('contractor'),
        'href' => admin_url('engineering_projects/contractors'),
        'position' => 28,
    ]);
    $CI->app_menu->add_sidebar_children_item('engineering_projects', [
        'slug' => 'drawings',
        'name' => _l('drawing'),
        'href' => admin_url('engineering_projects/drawings'),
        'position' => 29,
    ]);
    $CI->app_menu->add_sidebar_children_item('engineering_projects', [
        'slug' => 'documents',
        'name' => _l('document'),
        'href' => admin_url('engineering_projects/documents'),
        'position' => 29,
    ]);
  }
}

/**
 * Render <select> field optimized for admin area and bootstrap-select plugin
 * @param  string  $name             select name
 * @param  string  $id             select id
 * @param  array  $options          option to include
 * @param  array   $option_attrs     additional options attributes to include, attributes accepted based on the bootstrap-selectp lugin
 * @param  string  $label            select label
 * @param  string  $selected         default selected value
 * @param  array   $select_attrs     <select> additional attributes
 * @param  array   $form_group_attr  <div class="form-group"> div wrapper html attributes
 * @param  string  $form_group_class <div class="form-group"> additional class
 * @param  string  $select_class     additional <select> class
 * @param  boolean $include_blank    do you want to include the first <option> to be empty
 * @return string
 */
if (!function_exists('render_multi_select')) {
function render_multi_select($name,$id, $options, $option_attrs = [], $label = '', $selected = '', $select_attrs = [], $form_group_attr = [], $form_group_class = '', $select_class = '', $include_blank = true)
{
    $callback_translate = '';
    if (isset($options['callback_translate'])) {
        $callback_translate = $options['callback_translate'];
        unset($options['callback_translate']);
    }
    $select           = '';
    $_form_group_attr = '';
    $_select_attrs    = '';
    if (!isset($select_attrs['data-width'])) {
        $select_attrs['data-width'] = '100%';
    }
    if (!isset($select_attrs['data-none-selected-text'])) {
        $select_attrs['data-none-selected-text'] = _l('dropdown_non_selected_tex');
    }
    foreach ($select_attrs as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_select_attrs .= $key . '=' . '"' . $val . '" ';
    }

    $_select_attrs = rtrim($_select_attrs);

    $form_group_attr['app-field-wrapper'] = $name;
    foreach ($form_group_attr as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_form_group_attr .= $key . '=' . '"' . $val . '" ';
    }
    $_form_group_attr = rtrim($_form_group_attr);
    if (!empty($select_class)) {
        $select_class = ' ' . $select_class;
    }
    if (!empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }
    $select .= '<div class="select-placeholder form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
    if ($label != '') {
        $select .= '<label for="' . $id . '" class="control-label">' . _l($label, '', false) . '</label>';
    }
    $select .= '<select id="' . $id . '" name="' . $name . '" class="selectpicker' . $select_class . '" ' . $_select_attrs . ' data-live-search="true">';
    if ($include_blank == true) {
        $select .= '<option value=""></option>';
    }
    foreach ($options as $option) {
        $val       = '';
        $_selected = '';
        $key       = '';
        if (isset($option[$option_attrs[0]]) && !empty($option[$option_attrs[0]])) {
            $key = $option[$option_attrs[0]];
        }
        if (!is_array($option_attrs[1])) {
            $val = $option[$option_attrs[1]];
        } else {
            foreach ($option_attrs[1] as $_val) {
                $val .= $option[$_val] . ' ';
            }
        }
        $val = trim($val);

        if ($callback_translate != '') {
            if (function_exists($callback_translate) && is_callable($callback_translate)) {
                $val = call_user_func($callback_translate, $key);
            }
        }

        $data_sub_text = '';
        if (!is_array($selected)) {
            if ($selected != '') {
                if ($selected == $key) {
                    $_selected = ' selected';
                }
            }
        } else {
            foreach ($selected as $id) {
                if ($key == $id) {
                    $_selected = ' selected';
                }
            }
        }
        if (isset($option_attrs[2])) {
            if (strpos($option_attrs[2], ',') !== false) {
                $sub_text = '';
                $_temp    = explode(',', $option_attrs[2]);
                foreach ($_temp as $t) {
                    if (isset($option[$t])) {
                        $sub_text .= $option[$t] . ' ';
                    }
                }
            } else {
                if (isset($option[$option_attrs[2]])) {
                    $sub_text = $option[$option_attrs[2]];
                } else {
                    $sub_text = $option_attrs[2];
                }
            }
            $data_sub_text = ' data-subtext=' . '"' . $sub_text . '"';
        }
        $data_content = '';
        if (isset($option['option_attributes'])) {
            foreach ($option['option_attributes'] as $_opt_attr_key => $_opt_attr_val) {
                $data_content .= $_opt_attr_key . '=' . '"' . $_opt_attr_val . '"';
            }
            if ($data_content != '') {
                $data_content = ' ' . $data_content;
            }
        }
        $select .= '<option value="' . $key . '"' . $_selected . $data_content . $data_sub_text . '>' . $val . '</option>';
    }
    $select .= '</select>';
    $select .= '</div>';

    return $select;
}
// Smart Choice v2.5.3 marker: project/drawing/document create route fix.
}
