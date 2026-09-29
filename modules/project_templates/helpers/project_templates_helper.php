<?php
defined('BASEPATH') or exit('No direct script access allowed');

function add_project_templates_own_scripts($group){
    if($group == "admin") {
        $CI = &get_instance();
        $CI->app_scripts->add('project-templates-js', module_dir_url(PROJECT_TEMPLATES_MODULE_NAME, 'assets/project-templates.js?v='.PROJECT_TEMPLATES_MODULE_VERSION));

        if(module_is_active('task_templates')){
            add_task_templates_own_scripts($group);
        }
    }
}

function add_project_templates_global_scripts($group){
    if($group == "admin") {
        $CI = &get_instance();
        $CI->app_scripts->add('project-templates-global-js', module_dir_url(PROJECT_TEMPLATES_MODULE_NAME, 'assets/project-templates-global.js?v='.PROJECT_TEMPLATES_MODULE_VERSION));
    }
}

function add_project_templates_css($group){
    if($group == "admin") {
        $task_template_module_is_active = module_is_active('task_templates');
        if($task_template_module_is_active !== false && $task_template_module_is_active != "1.0.0"){
            add_task_templates_css($group);
        }
    }
}

/**
 * Init language editor module menu items in setup in admin_init hook
 * @return null
 */
function add_setup_menu_project_templates_link(){
    if (staff_can('view', 'project_templates') || staff_can('view_own', 'project_templates')) {
        $CI = &get_instance();
        /**
         * If the logged in user is administrator, add custom menu in Setup
         */
        $CI->app_menu->add_setup_menu_item('project_templates', [
            'href'     => admin_url('project_templates'),
            'name'     => _l('pt_module_title'),
            'position' => 300,
        ]);
    }
}

/**
 * Staff permissions for translation module
 * @param $corePermissions array
 * @param $data array
 * @return array
 */
function project_templates_staff_permissions($corePermissions, $data){
    $corePermissions['project_templates'] = [
        'name'         => _l('pt_module_title'),
        'capabilities' => [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'view_own'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit' => _l('permission_edit'),
            'delete' => _l('permission_delete'),
            'create_milestones' => _l('permission_create_milestones'),
            'edit_milestones' => _l('permission_edit_milestones'),
            'delete_milestones' => _l('permission_delete_milestones'),
        ],
    ];
    return $corePermissions;
}


/**
 * Check if a module is active or not. If active return version of module
 * @param $module String
 * @return boolean|string
 */
if(!function_exists('module_is_active')){

    function module_is_active($module){
        $CI =& get_instance();

        $active = $CI->app_object_cache->get($module.'_is_active');

        if (!$active || empty($active)) {
            $active = $CI->db->where("module_name", $module)->get("tblmodules")->row_array();
            $CI->app_object_cache->add($module.'_is_active', $active);
        }

        if(!empty($active) && $active['active'] == "1"){
            return $active['installed_version'];
        }
        return false;
    }
}



/**
 * Check for custom fields for , update on $_POST
 * @param  mixed $rel_id        the main ID from the table
 * @param  array $custom_fields all custom fields with id and values
 * @return boolean
 */
function handle_custom_fields_post_for_project_templates($rel_id, $custom_fields, $is_cf_items = false)
{
    $affectedRows = 0;
    $CI           = & get_instance();

    foreach ($custom_fields as $key => $fields) {
        foreach ($fields as $field_id => $field_value) {
            $CI->db->where('relid', $rel_id);
            $CI->db->where('fieldid', $field_id);
            $CI->db->where('fieldto', ($is_cf_items ? 'items_pr' : $key));
            $row = $CI->db->get(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES)->row();
            if (!is_array($field_value)) {
                $field_value = trim($field_value);
            }
            // Make necessary checkings for fields
            if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                $CI->db->where('id', $field_id);
                $field_checker = $CI->db->get(db_prefix() . 'customfields')->row();
                if ($field_checker->type == 'date_picker') {
                    $field_value = to_sql_date($field_value);
                } elseif ($field_checker->type == 'date_picker_time') {
                    $field_value = to_sql_date($field_value, true);
                } elseif ($field_checker->type == 'textarea') {
                    $field_value = nl2br($field_value);
                } elseif ($field_checker->type == 'checkbox' || $field_checker->type == 'multiselect') {
                    if ($field_checker->disalow_client_to_edit == 1 && is_client_logged_in()) {
                        continue;
                    }
                    if (is_array($field_value)) {
                        $v = 0;
                        foreach ($field_value as $chk) {
                            if ($chk == 'cfk_hidden') {
                                unset($field_value[$v]);
                            }
                            $v++;
                        }
                        $field_value = implode(', ', $field_value);
                    }
                }
            }
            if ($row) {
                $CI->db->where('id', $row->id);
                $CI->db->update(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES, [
                    'value' => $field_value,
                ]);
                if ($CI->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            } else {
                if ($field_value != '') {
                    $CI->db->insert(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES, [
                        'relid'   => $rel_id,
                        'fieldid' => $field_id,
                        'fieldto' => $is_cf_items ? 'items_pr' : $key,
                        'value'   => $field_value,
                    ]);
                    $insert_id = $CI->db->insert_id();
                    if ($insert_id) {
                        $affectedRows++;
                    }
                }
            }
        }
    }
    if ($affectedRows > 0) {
        return true;
    }

    return false;
}

function get_project_template_custom_field_value($rel_id, $field_id, $format = true)
{
    $CI = & get_instance();

    $CI->db->select(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES . '.value,' . db_prefix() . 'customfields.type');
    $CI->db->join(db_prefix() . 'customfields', db_prefix() . 'customfields.id=' . PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES . '.fieldid');
    $CI->db->where(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES . '.relid', $rel_id);
    if (is_numeric($field_id)) {
        $CI->db->where(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES . '.fieldid', $field_id);
    }

    $row = $CI->db->get(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES)->row();

    $result = '';
    if ($row) {
        $result = $row->value;
        if ($format == true) {
            if ($row->type == 'date_picker') {
                $result = _d($result);
            } elseif ($row->type == 'date_picker_time') {
                $result = _dt($result);
            }
        }
    }

    return $result;
}

/**
 * Check if project has recurring tasks
 * @param  mixed $id project id
 * @return boolean
 */
function project_template_has_recurring_tasks($id)
{
    if(module_is_active("task_templates")){
        return total_rows(TASK_TEMPLATES_TABLE_NAME, 'repeat_every!="" AND rel_id="' . get_instance()->db->escape_str($id) . '" AND rel_type="project"') > 0;
    }
    return false;
}

function pt_put_custom_field_value_with_js($rel_id)
{
    $fields = get_custom_fields('projects');

    foreach ($fields as $field){
        $result = get_project_template_custom_field_value($rel_id, $field['id']);
        $name = 'custom_fields[projects]['.$field['id'].']';
        $js = '$(\'[name="'.$name.'"]\').val(\''.$result.'\');';
        echo ($js);
    }
}


/**
 * Default project tabs
 * @return array
 */

function get_project_templates_tabs_admin()
{
    $tabs = get_project_tabs_admin();
    $unset = [
        'project_overview',
        'project_tasks',
        'project_milestones',
        'project_files',
        'project_notes',
    ];
    if(!module_is_active('task_templates')){
        unset($unset[1]);
    }
    foreach ($tabs as $index=>$tab){
        if(!in_array($index, $unset)){
            unset($tabs[$index]);
            continue;
        }
        if(!isset($tab['children']) || empty($tab['children'])){
            $tabs[$index]['view'] = str_replace('admin/projects/', '', $tab['view']);
        }
    }
    return $tabs;
}


/**
 * Handles upload for project files
 * @param  mixed $project_id project id
 * @return boolean
 */
function handle_project_template_file_uploads($project_id)
{
    $filesIDS = [];
    $errors   = [];

    if (isset($_FILES['file']['name'])
        && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)) {
//        hooks()->do_action('before_upload_project_attachment', $project_id);

        if (!is_array($_FILES['file']['name'])) {
            $_FILES['file']['name']     = [$_FILES['file']['name']];
            $_FILES['file']['type']     = [$_FILES['file']['type']];
            $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
            $_FILES['file']['error']    = [$_FILES['file']['error']];
            $_FILES['file']['size']     = [$_FILES['file']['size']];
        }

        $path = PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $project_id . '/';

        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            if (_perfex_upload_error($_FILES['file']['error'][$i])) {
                $errors[$_FILES['file']['name'][$i]] = _perfex_upload_error($_FILES['file']['error'][$i]);

                continue;
            }

            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'][$i];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $originalFilename = unique_filename($path, $_FILES['file']['name'][$i]);
                $filename = app_generate_hash() . '.' . get_file_extension($originalFilename);

                // In case client side validation is bypassed
                if (!_upload_extension_allowed($filename)) {
                    continue;
                }

                $newFilePath = $path . $filename;
                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $CI = & get_instance();
                    $data = [
                        'project_template_id' => $project_id,
                        'file_name'  => $filename,
                        'original_file_name'  => $originalFilename,
                        'filetype'   => $_FILES['file']['type'][$i],
                        'dateadded'  => date('Y-m-d H:i:s'),
                        'subject'    => $originalFilename,
                    ];
                    $data['visible_to_customer'] = ($CI->input->post('visible_to_customer') == 'true' ? 1 : 0);

                    $CI->db->insert(PROJECT_TEMPLATES_FILES_TABLE_NAME, $data);

                    $insert_id = $CI->db->insert_id();
                    if ($insert_id) {
                        if (is_image($newFilePath)) {
                            create_img_thumb($path, $filename);
                        }
                        array_push($filesIDS, $insert_id);
                    } else {
                        unlink($newFilePath);

                        return false;
                    }
                }
            }
        }
    }

    if (count($errors) > 0) {
        $message = '';
        foreach ($errors as $filename => $error_message) {
            $message .= $filename . ' - ' . $error_message . '<br />';
        }
        header('HTTP/1.0 400 Bad error');
        echo $message;
        die;
    }

    if (count($filesIDS) > 0) {
        return true;
    }

    return false;
}

function prepare_project_template_file_url($project_id, $view){
    return base_url(PROJECT_TEMPLATES_ATTACHMENTS_FOLDER_REL . $project_id . '/' . $view);
}

function get_project_template_name_by_id($id)
{
    $CI      = & get_instance();
    $project = $CI->app_object_cache->get('project-template-name-data-' . $id);

    if (!$project) {
        $CI->db->select('name');
        $CI->db->where('id', $id);
        $project = $CI->db->get(PROJECT_TEMPLATES_TABLE_NAME)->row();
        $CI->app_object_cache->add('project-template-name-data-' . $id, $project);
    }

    if ($project) {
        return $project->name;
    }

    return '';
}

function copy_project_template_file_uploads($original_project_id, $new_project_id)
{
    $CI = & get_instance();
    $project_files = $CI->db->where("project_template_id", $original_project_id)->get(PROJECT_TEMPLATES_FILES_TABLE_NAME)->result_array();
    $original_path     = PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $original_project_id . '/';
    $new_path     = PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $new_project_id . '/';
    _maybe_create_upload_path($new_path);
    foreach($project_files as $file){
        $fullOriginalPath = $original_path . $file['file_name'];
        $fullNewPath = $new_path . $file['file_name'];
        if (file_exists($fullOriginalPath)) {
            if(copy($fullOriginalPath, $fullNewPath)){
                $CI->db->insert(PROJECT_TEMPLATES_FILES_TABLE_NAME, [
                    'project_template_id' => $new_project_id,
                    'file_name' => $file['file_name'],
                    'original_file_name' => $file['original_file_name'],
                    'subject' => $file['subject'],
                    'description' => $file['description'],
                    'filetype' => $file['filetype'],
                    'dateadded' => date('Y-m-d H:i:s'),
                    'visible_to_customer' => $file['visible_to_customer'],
                    'external' => $file['external'],
                    'external_link' => $file['external_link'],
                    'thumbnail_link' => $file['thumbnail_link'],
                ]);

                $insert_id = $CI->db->insert_id();
                if ($insert_id) {
                    if (is_image($fullNewPath)) {
                        create_img_thumb($new_path, $file['file_name']);
                    }
                }
            }
        }
    }
}