<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Project_templates_model extends App_Model
{

    public function __construct(){
        parent::__construct();

    }

    /**
     * Add new staff project
     * @param array $data project $_POST data
     * @return mixed
     */
    public function add($data)
    {
        if (isset($data['settings'])) {
            $project_settings = $data['settings'];
            unset($data['settings']);
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        if (isset($data['progress_from_tasks'])) {
            $data['progress_from_tasks'] = 1;
        } else {
            $data['progress_from_tasks'] = 0;
        }

        if (isset($data['contact_notification'])) {
            if ($data['contact_notification'] == 2) {
                $data['notify_contacts'] = serialize($data['notify_contacts']);
            } else {
                $data['notify_contacts'] = serialize([]);
            }
        }

        $data['project_cost']    = !empty($data['project_cost']) ? $data['project_cost'] : null;
        $data['estimated_hours'] = !empty($data['estimated_hours']) ? $data['estimated_hours'] : null;

        if (isset($data['project_members'])) {
            $project_members = $data['project_members'];
            unset($data['project_members']);
        }
        if ($data['billing_type'] == 1) {
            $data['project_rate_per_hour'] = 0;
        } elseif ($data['billing_type'] == 2) {
            $data['project_cost'] = 0;
        } else {
            $data['project_rate_per_hour'] = 0;
            $data['project_cost']          = 0;
        }

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }


        $data['dateadded']             = date('Y-m-d H:i:s');
        $data['added_by']             = get_staff_user_id();

        $this->db->insert(PROJECT_TEMPLATES_TABLE_NAME, $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            handle_tags_save($tags, $insert_id, 'project_templates');

            if (isset($custom_fields)) {
                handle_custom_fields_post_for_project_templates($insert_id, $custom_fields);
            }

            if (isset($project_members)) {
                $_pm['project_members'] = $project_members;
                $this->add_edit_members($_pm, $insert_id);
            }

            $original_settings = $this->projects_model->get_settings();
            if (isset($project_settings)) {
                $_settings = [];
                $_values   = [];
                foreach ($project_settings as $name => $val) {
                    array_push($_settings, $name);
                    $_values[$name] = $val;
                }
                foreach ($original_settings as $setting) {
                    if ($setting != 'available_features') {
                        if (in_array($setting, $_settings)) {
                            $value_setting = 1;
                        } else {
                            $value_setting = 0;
                        }
                    } else {
                        $tabs         = get_project_tabs_admin();
                        $tab_settings = [];
                        foreach ($_values[$setting] as $tab) {
                            $tab_settings[$tab] = 1;
                        }
                        foreach ($tabs as $tab) {
                            if (!isset($tab['collapse'])) {
                                if (!in_array($tab['slug'], $_values[$setting])) {
                                    $tab_settings[$tab['slug']] = 0;
                                }
                            } else {
                                foreach ($tab['children'] as $tab_dropdown) {
                                    if (!in_array($tab_dropdown['slug'], $_values[$setting])) {
                                        $tab_settings[$tab_dropdown['slug']] = 0;
                                    }
                                }
                            }
                        }
                        $value_setting = serialize($tab_settings);
                    }
                    $this->db->insert(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME, [
                        'project_template_id' => $insert_id,
                        'name'       => $setting,
                        'value'      => $value_setting,
                    ]);
                }
            } else {
                foreach ($original_settings as $setting) {
                    $value_setting = 0;
                    $this->db->insert(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME, [
                        'project_template_id' => $insert_id,
                        'name'       => $setting,
                        'value'      => $value_setting,
                    ]);
                }
            }

            log_activity('New Project Template Added [ID:' . $insert_id . ', Name: ' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update project data
     * @param  array $data project data $_POST
     * @param  mixed $id   project id
     * @return boolean
     */
    public function update($data, $id)
    {
        $original_project = $this->get($id);

        $affectedRows = 0;
        if (!isset($data['settings'])) {
            $this->db->where('project_template_id', $id);
            $this->db->update(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME, [
                'value' => 0,
            ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } else {
            $_settings = [];
            $_values   = [];

            foreach ($data['settings'] as $name => $val) {
                array_push($_settings, $name);
                $_values[$name] = $val;
            }

            unset($data['settings']);
            $original_settings = $this->get_project_settings($id);

            foreach ($original_settings as $setting) {
                if ($setting['name'] != 'available_features') {
                    if (in_array($setting['name'], $_settings)) {
                        $value_setting = 1;
                    } else {
                        $value_setting = 0;
                    }
                } else {
                    $tabs         = get_project_tabs_admin();
                    $tab_settings = [];
                    foreach ($_values[$setting['name']] as $tab) {
                        $tab_settings[$tab] = 1;
                    }
                    foreach ($tabs as $tab) {
                        if (!isset($tab['collapse'])) {
                            if (!in_array($tab['slug'], $_values[$setting['name']])) {
                                $tab_settings[$tab['slug']] = 0;
                            }
                        } else {
                            foreach ($tab['children'] as $tab_dropdown) {
                                if (!in_array($tab_dropdown['slug'], $_values[$setting['name']])) {
                                    $tab_settings[$tab_dropdown['slug']] = 0;
                                }
                            }
                        }
                    }
                    $value_setting = serialize($tab_settings);
                }

                $this->db->where('project_template_id', $id);
                $this->db->where('name', $setting['name']);
                $this->db->update(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME, [
                    'value' => $value_setting,
                ]);

                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
        }

        $data['project_cost']    = !empty($data['project_cost']) ? $data['project_cost'] : null;
        $data['estimated_hours'] = !empty($data['estimated_hours']) ? $data['estimated_hours'] : null;

        if (isset($data['progress_from_tasks'])) {
            $data['progress_from_tasks'] = 1;
        } else {
            $data['progress_from_tasks'] = 0;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post_for_project_templates($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if ($data['billing_type'] == 1) {
            $data['project_rate_per_hour'] = 0;
        } elseif ($data['billing_type'] == 2) {
            $data['project_cost'] = 0;
        } else {
            $data['project_rate_per_hour'] = 0;
            $data['project_cost']          = 0;
        }
        if (isset($data['project_members'])) {
            $project_members = $data['project_members'];
            unset($data['project_members']);
        }
        $_pm = [];
        if (isset($project_members)) {
            $_pm['project_members'] = $project_members;
        }
        if ($this->add_edit_members($_pm, $id)) {
            $affectedRows++;
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'project_templates')) {
                $affectedRows++;
            }
            unset($data['tags']);
        }

        if (isset($data['contact_notification'])) {
            if ($data['contact_notification'] == 2) {
                $data['notify_contacts'] = serialize($data['notify_contacts']);
            } else {
                $data['notify_contacts'] = serialize([]);
            }
        }

        $this->db->where('id', $id);
        $this->db->update(PROJECT_TEMPLATES_TABLE_NAME, $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            log_activity('Project Template Updated [ID:' . $id . ', Name: ' . $data['name'] . ']');
            return true;
        }

        return false;
    }

    public function add_edit_members($data, $id)
    {
        $affectedRows = 0;
        if (isset($data['project_members'])) {
            $project_members = $data['project_members'];
        }

        $project_members_in = $this->get_project_template_members($id);
        if (sizeof($project_members_in) > 0) {
            foreach ($project_members_in as $project_member) {
                if (isset($project_members)) {
                    if (!in_array($project_member['staff_id'], $project_members)) {
                        $this->db->where('project_template_id', $id);
                        $this->db->where('staff_id', $project_member['staff_id']);
                        $this->db->delete(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                } else {
                    $this->db->where('project_template_id', $id);
                    $this->db->delete(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if (isset($project_members)) {
                foreach ($project_members as $staff_id) {
                    $this->db->where('project_template_id', $id);
                    $this->db->where('staff_id', $staff_id);
                    $_exists = $this->db->get(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME)->row();
                    if (!$_exists) {
                        if (empty($staff_id)) {
                            continue;
                        }
                        $this->db->insert(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME, [
                            'project_template_id' => $id,
                            'staff_id'   => $staff_id,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            }
        } else {
            if (isset($project_members)) {
                foreach ($project_members as $staff_id) {
                    if (empty($staff_id)) {
                        continue;
                    }
                    $this->db->insert(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME, [
                        'project_template_id' => $id,
                        'staff_id'   => $staff_id,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get project by id
     * @param  mixed $id project id
     * @return object
     */
    public function get($id, $where = [])
    {
        if(!staff_can('view', 'project_templates')){
            $where['added_by'] = get_staff_user_id();
        }
        $this->db->where('id', $id);
        $this->db->where($where);
        $project = $this->db->get(PROJECT_TEMPLATES_TABLE_NAME)->row();
        if ($project) {
            $settings                      = $this->get_project_settings($id);

            // SYNC NEW TABS
            $tabs                        = get_project_tabs_admin();
            $tabs_flatten                = [];
            $settings_available_features = [];

            $available_features_index = false;
            foreach ($settings as $key => $setting) {
                if ($setting['name'] == 'available_features') {
                    $available_features_index = $key;
                    $available_features       = unserialize($setting['value']);
                    if (is_array($available_features)) {
                        foreach ($available_features as $name => $avf) {
                            $settings_available_features[] = $name;
                        }
                    }
                }
            }
            foreach ($tabs as $tab) {
                if (isset($tab['collapse'])) {
                    foreach ($tab['children'] as $d) {
                        $tabs_flatten[] = $d['slug'];
                    }
                } else {
                    $tabs_flatten[] = $tab['slug'];
                }
            }
            if (count($settings_available_features) != $tabs_flatten) {
                foreach ($tabs_flatten as $tab) {
                    if (!in_array($tab, $settings_available_features)) {
                        if ($available_features_index) {
                            $current_available_features_settings = $settings[$available_features_index];
                            $tmp                                 = unserialize($current_available_features_settings['value']);
                            $tmp[$tab]                           = 1;
                            $this->db->where('id', $current_available_features_settings['id']);
                            $this->db->update(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME, ['value' => serialize($tmp)]);
                        }
                    }
                }
            }

            $project->settings = new StdClass();

            foreach ($settings as $setting) {
                $project->settings->{$setting['name']} = $setting['value'];
            }
        }

        return hooks()->apply_filters('get_project_template', $project);
    }

    public function get_project_settings($project_id)
    {
        $this->db->where('project_template_id', $project_id);

        return $this->db->get(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME)->result_array();
    }

    public function get_all($where = [])
    {
        if(!staff_can('view', 'project_templates')){
            $where['added_by'] = get_staff_user_id();
        }
        $this->db->where($where);
        return $this->db->get(PROJECT_TEMPLATES_TABLE_NAME)->result_array();
    }

    /**
     * Delete project and all connections
     * @param  mixed $id projectid
     * @return boolean
     */
    public function delete_project_template($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(PROJECT_TEMPLATES_TABLE_NAME);
        if ($this->db->affected_rows() > 0) {

            $this->db->where('project_template_id', $id);
            $this->db->delete(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME);

            $this->db->where('project_template_id', $id);
            $this->db->delete(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME);

            $this->db->where('relid', $id);
            $this->db->delete(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES);

            $this->db->where('project_template_id', $id);
            $this->db->delete(PROJECT_TEMPLATES_NOTES_TABLE_NAME);

            $project_files = $this->db->where("project_template_id", $id)->get(PROJECT_TEMPLATES_FILES_TABLE_NAME)->result_array();
            foreach($project_files as $project_file){
                $this->remove_file($project_file->id);
            }

            $this->db->where('project_template_id', $id);
            $this->db->delete(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME);

            if(module_is_active('task_templates')){
                $this->load->model("task_templates/task_templates_model");
                $this->task_templates_model->delete_tasks_of_project_template($id);
            }

            hooks()->do_action('project_template_deleted', $id);

            return true;
        }

        return false;
    }

    public function copy($project_id)
    {
        $project           = $this->get($project_id);
        $fields_projects   = $this->db->list_fields(PROJECT_TEMPLATES_TABLE_NAME);
        $_new_project_data = [];

        foreach ($fields_projects as $field) {
            if (isset($project->$field)) {
                $_new_project_data[$field] = $project->$field;
            }
        }

        unset($_new_project_data['id']);

        $_new_project_data['name']             = $project->name.' '._l('copy');
        $_new_project_data['dateadded']             = date('Y-m-d H:i:s');
        $_new_project_data['added_by']             = get_staff_user_id();

        $_new_project_data = hooks()->apply_filters('before_add_project_template', $_new_project_data);

        $this->db->insert(PROJECT_TEMPLATES_TABLE_NAME, $_new_project_data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if(module_is_active('task_templates')){
                $this->load->model("task_templates/task_templates_model");
                $this->task_templates_model->copy_tasks_of_project($project_id, $insert_id);
            }

            $tags = get_tags_in($project_id, 'project_templates');
            $_new_tags = !empty($tags) ? implode(",", $tags) : '';
            handle_tags_save($_new_tags, $insert_id, 'project_templates');

            $project_settings = $this->db->where("project_template_id", $project_id)->get(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME)->result_array();
            foreach($project_settings as $project_setting){
                $this->db->insert(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME, [
                    'project_template_id' => $insert_id,
                    'name' => $project_setting['name'],
                    'value' => $project_setting['value'],
                ]);
            }

            $project_members = $this->db->where("project_template_id", $project_id)->get(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME)->result_array();
            foreach($project_members as $project_member){
                $this->db->insert(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME, [
                    'project_template_id' => $insert_id,
                    'staff_id' => $project_member['staff_id'],
                ]);
            }

            $project_custom_fields = $this->db->where("relid", $project_id)->get(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES)->result_array();
            foreach($project_custom_fields as $project_custom_field){
                $this->db->insert(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES, [
                    'relid' => $insert_id,
                    'fieldid' => $project_custom_field['fieldid'],
                    'fieldto' => $project_custom_field['fieldto'],
                    'value' => $project_custom_field['value'],
                ]);
            }

            $project_notes = $this->db->where("project_template_id", $project_id)->get(PROJECT_TEMPLATES_NOTES_TABLE_NAME)->result_array();
            foreach($project_notes as $project_note){
                $this->db->insert(PROJECT_TEMPLATES_NOTES_TABLE_NAME, [
                    'project_template_id' => $insert_id,
                    'content' => $project_note['content'],
                    'staff_id' => $project_note['staff_id'],
                ]);
            }

            copy_project_template_file_uploads($project_id, $insert_id);

            $project_milestones = $this->db->where("project_template_id", $project_id)->get(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME)->result_array();
            foreach($project_milestones as $project_milestone){
                $original_milestone_id = $project_milestone['id'];
                $this->db->insert(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME, [
                    'project_template_id' => $insert_id,
                    'name' => $project_milestone['name'],
                    'description' => $project_milestone['description'],
                    'description_visible_to_customer' => $project_milestone['description_visible_to_customer'],
                    'start_date' => $project_milestone['start_date'],
                    'due_date' => $project_milestone['due_date'],
                    'color' => $project_milestone['color'],
                    'milestone_order' => $project_milestone['milestone_order'],
                    'datecreated' => date('Y-m-d H:i:s'),
                    'hide_from_customer' => $project_milestone['hide_from_customer'],
                ]);
                $new_milestone_id = $this->db->insert_id();

                if(module_is_active('task_templates')){
                    $this->load->model("task_templates/task_templates_model");
                    $this->task_templates_model->copy_tasks_of_project($project_id, $insert_id, $original_milestone_id, $new_milestone_id, '');
                }

            }
            hooks()->do_action('after_add_project_template', $insert_id);

            return $insert_id;
        }

        return false;
    }

    public function get_project_template_members($id, $with_name = false)
    {
        if ($with_name) {
            $this->db->select('firstname,lastname,email,project_template_id,staff_id,staffid');
        } else {
            $this->db->select('email,project_template_id,staff_id,staffid');
        }
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . PROJECT_TEMPLATES_MEMBERS_TABLE_NAME . '.staff_id');
        $this->db->where('project_template_id', $id);

        return $this->db->get(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME)->result_array();
    }

    public function get_staff_notes($project_id)
    {
        $this->db->where('project_template_id', $project_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get(PROJECT_TEMPLATES_NOTES_TABLE_NAME)->row();
        if ($notes) {
            return $notes->content;
        }

        return '';
    }

    public function save_note($data, $project_id)
    {
        // Check if the note exists for this project;
        $this->db->where('project_template_id', $project_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get(PROJECT_TEMPLATES_NOTES_TABLE_NAME)->row();
        if ($notes) {
            $this->db->where('id', $notes->id);
            $this->db->update(PROJECT_TEMPLATES_NOTES_TABLE_NAME, [
                'content' => $data['content'],
            ]);
            if ($this->db->affected_rows() > 0) {
                return true;
            }

            return false;
        }
        $this->db->insert(PROJECT_TEMPLATES_NOTES_TABLE_NAME, [
            'staff_id'   => get_staff_user_id(),
            'content'    => $data['content'],
            'project_template_id' => $project_id,
        ]);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return true;
        }

        return false;
    }

    public function get_files($project_id)
    {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('project_template_id', $project_id);

        return $this->db->get(PROJECT_TEMPLATES_FILES_TABLE_NAME)->result_array();
    }

    public function get_file($id, $project_id = false)
    {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('id', $id);
        $file = $this->db->get(PROJECT_TEMPLATES_FILES_TABLE_NAME)->row();

        if ($file && $project_id) {
            if ($file->project_template_id != $project_id) {
                return false;
            }
        }

        return $file;
    }

    public function change_file_visibility($id, $visible)
    {
        $this->db->where('id', $id);
        $this->db->update(PROJECT_TEMPLATES_FILES_TABLE_NAME, [
            'visible_to_customer' => $visible,
        ]);
    }

    public function update_file_data($data)
    {
        $this->db->where('id', $data['id']);
        unset($data['id']);
        $this->db->update(PROJECT_TEMPLATES_FILES_TABLE_NAME, $data);
    }

    public function remove_file($id)
    {
        $this->db->where('id', $id);
        $file = $this->db->get(PROJECT_TEMPLATES_FILES_TABLE_NAME)->row();
        if ($file) {
            if (empty($file->external)) {
                $path     = PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $file->project_template_id . '/';
                $fullPath = $path . $file->file_name;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                    $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $thumbPath = $path . $fname . '_thumb.' . $fext;

                    if (file_exists($thumbPath)) {
                        unlink($thumbPath);
                    }
                }
            }

            $this->db->where('id', $id);
            $this->db->delete(PROJECT_TEMPLATES_FILES_TABLE_NAME);

            if (is_dir(PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $file->project_template_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $file->project_template_id);
                if (count($other_attachments) == 0) {
                    delete_dir(PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $file->project_template_id);
                }
            }

            return true;
        }

        return false;
    }

    public function add_milestone($data)
    {
        $data['datecreated']                     = date('Y-m-d');
        $data['description']                     = nl2br($data['description']);
        $data['description_visible_to_customer'] = isset($data['description_visible_to_customer']) ? 1 : 0;
        $data['hide_from_customer']              = isset($data['hide_from_customer']) ? 1 : 0;

        $this->db->insert(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME, $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('Project Template Milestone Created [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function update_milestone($data, $id)
    {
        $this->db->where('id', $id);
        $data['description']                     = nl2br($data['description']);
        $data['description_visible_to_customer'] = isset($data['description_visible_to_customer']) ? 1 : 0;
        $data['hide_from_customer']              = isset($data['hide_from_customer']) ? 1 : 0;

        $this->db->where('id', $id);
        $this->db->update(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME, $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Project Template Milestone Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function get_milestones($project_id, $where = [])
    {
        $this->db->select('*, (SELECT COUNT(id) FROM ' . db_prefix() . 'tasks WHERE rel_type="project" AND rel_id=' . $this->db->escape_str($project_id) . ' and milestone=' . PROJECT_TEMPLATES_MILESTONE_TABLE_NAME . '.id) as total_tasks, (SELECT COUNT(id) FROM ' . db_prefix() . 'tasks WHERE rel_type="project" AND rel_id=' . $this->db->escape_str($project_id) . ' and milestone=' . PROJECT_TEMPLATES_MILESTONE_TABLE_NAME . '.id AND status=5) as total_finished_tasks');
        $this->db->where('project_template_id', $project_id);
        $this->db->order_by('milestone_order', 'ASC');
        $this->db->where($where);
        $milestones = $this->db->get(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME)->result_array();

        return $milestones;
    }

    public function do_milestones_kanban_query($milestone_id, $project_id, $page = 1, $where = [], $count = false)
    {
        $where['milestone'] = $milestone_id;
        $limit              = get_option('tasks_kanban_limit');
        $tasks              = $this->get_tasks($project_id, $where, true, $count, function () use ($count, $page, $limit) {
            if ($count == false) {
                if ($page > 1) {
                    $position = (($page - 1) * $limit);
                    $this->db->limit($limit, $position);
                } else {
                    $this->db->limit($limit);
                }
            }
        });

        return $tasks;
    }

    public function delete_milestone($id)
    {
        $this->db->where('id', $id);
        $milestone = $this->db->get(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME)->row();
        $this->db->where('id', $id);
        $this->db->delete(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME);
        if ($this->db->affected_rows() > 0) {
            /*$this->db->where('milestone', $id);
            $this->db->update(db_prefix() . 'tasks', [
                'milestone' => 0,
            ]);*/
            log_activity('Project Template Milestone Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function update_milestones_order($data)
    {
        foreach ($data['order'] as $status) {
            $this->db->where('id', $status[0]);
            $this->db->update(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME, [
                'milestone_order' => $status[1],
            ]);
        }
    }

    public function update_milestone_color($data)
    {
        $this->db->where('id', $data['milestone_id']);
        $this->db->update(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME, [
            'color' => $data['color'],
        ]);
    }

    public function get_tasks($id, $where = [], $apply_restrictions = false, $count = false, $callback = null)
    {
        if(!module_is_active('task_templates')){
            if ($count == false) {
                return [];
            } else {
                return 0;
            }
        }
        $has_permission                    = staff_can('view', 'task_templates');
        $show_all_tasks_for_project_member = get_option('show_all_tasks_for_project_member');

        $select = implode(', ', prefixed_table_fields_array(TASK_TEMPLATES_TABLE_NAME)) . ',' . PROJECT_TEMPLATES_MILESTONE_TABLE_NAME . '.name as milestone_name,
           (SELECT GROUP_CONCAT(staffid ORDER BY ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . '.id ASC SEPARATOR ",") FROM ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . ' WHERE taskid=' . TASK_TEMPLATES_TABLE_NAME . '.id) as assignees_ids
        ';

        if (!is_client_logged_in() && is_staff_logged_in()) {
            $select .= ',(SELECT staffid FROM ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . ' WHERE taskid=' . TASK_TEMPLATES_TABLE_NAME . '.id AND staffid=' . get_staff_user_id() . ') as current_user_is_assigned';
        }

        if (is_client_logged_in()) {
            $this->db->where('visible_to_client', 1);
        }

        $this->db->select($select);

        $this->db->join(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME, PROJECT_TEMPLATES_MILESTONE_TABLE_NAME . '.id = ' . TASK_TEMPLATES_TABLE_NAME . '.milestone', 'left');
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'project');
        if ($apply_restrictions == true) {
            if (!is_client_logged_in() && !$has_permission && $show_all_tasks_for_project_member == 0) {
                $this->db->where('(
                    ' . TASK_TEMPLATES_TABLE_NAME . '.id IN (SELECT taskid FROM ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . ' WHERE staffid=' . get_staff_user_id() . ')
                    OR ' . TASK_TEMPLATES_TABLE_NAME . '.id IN(SELECT taskid FROM ' . TASK_TEMPLATES_FOLLOWERS_TABLE_NAME . ' WHERE staffid=' . get_staff_user_id() . ')
                    OR is_public = 1
                    OR (addedfrom =' . get_staff_user_id() . ' AND is_added_from_contact = 0)
                    )');
            }
        }

        if (isset($where[PROJECT_TEMPLATES_MILESTONE_TABLE_NAME . '.hide_from_customer'])) {
            $this->db->group_start();
            $this->db->where(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME . '.hide_from_customer', $where[PROJECT_TEMPLATES_MILESTONE_TABLE_NAME . '.hide_from_customer']);
            $this->db->or_where(TASK_TEMPLATES_TABLE_NAME . '.milestone', 0);
            $this->db->group_end();
            unset($where[PROJECT_TEMPLATES_MILESTONE_TABLE_NAME . '.hide_from_customer']);
        }

        $this->db->where($where);

        // Milestones kanban order
        // Request is admin/projects/milestones_kanban
        if ($this->uri->segment(3) == 'milestones_kanban' | $this->uri->segment(3) == 'milestones_kanban_load_more') {
            $this->db->order_by('milestone_order', 'asc');
        } else {
            $orderByString = hooks()->apply_filters('project_tasks_array_default_order', 'FIELD(status, 5), duedate IS NULL ASC, duedate');
            $this->db->order_by($orderByString, '', false);
        }

        if ($callback) {
            $callback();
        }

        if ($count == false) {
            $tasks = $this->db->get(TASK_TEMPLATES_TABLE_NAME)->result_array();
        } else {
            $tasks = $this->db->count_all_results(TASK_TEMPLATES_TABLE_NAME);
        }

        $tasks = hooks()->apply_filters('get_project_templates_tasks', $tasks, [
            'project_id' => $id,
            'where'      => $where,
            'count'      => $count,
        ]);

        return $tasks;
    }

    public function update_task_milestone($data)
    {
        $this->db->where('id', $data['task_id']);
        $this->db->update(TASK_TEMPLATES_TABLE_NAME, [
            'milestone' => $data['milestone_id'],
        ]);

        foreach ($data['order'] as $order) {
            $this->db->where('id', $order[0]);
            $this->db->update(TASK_TEMPLATES_TABLE_NAME, [
                'milestone_order' => $order[1],
            ]);
        }
    }

    public function create_project($data){
        $project_template = $this->get($data['project_template']);

        // fields from post data
        $_new_project_data['start_date'] = $data['start_date'];
        $_new_project_data['clientid'] = $data['clientid'];

        // fields from template
        $_new_project_data['name'] = $project_template->name;
        if($project_template->progress_from_tasks == "1"){
            $_new_project_data['progress_from_tasks'] = "on";
        }
        $_new_project_data['progress'] = $project_template->progress;
        $_new_project_data['billing_type'] = $project_template->billing_type;
        $_new_project_data['status'] = $project_template->status;
        $_new_project_data['project_cost'] = $project_template->project_cost;
        $_new_project_data['project_rate_per_hour'] = $project_template->project_rate_per_hour;
        $_new_project_data['estimated_hours'] = $project_template->estimated_hours;
        $_new_project_data['deadline'] = '';
        if(!empty($project_template->duration)){
            $duration_value = intval($project_template->duration);
            $_new_project_data['deadline'] = _d(date("Y-m-d", strtotime("+".$duration_value." days", strtotime(to_sql_date($_new_project_data['start_date'])))));
        }
        $_template_members = $this->get_project_template_members($project_template->id);
        $project_members = [];
        foreach($_template_members as $member){
            $project_members[] = $member['staff_id'];
        }
        $_new_project_data['project_members'] = $project_members;
        $tags = get_tags_in($project_template->id, 'project_templates');
        $_new_project_data['tags'] = !empty($tags) ? implode(",", $tags) : '';

        $_custom_fields = get_custom_fields('projects');
        $custom_fields = [];
        foreach($_custom_fields as $custom_field){
            $custom_field_value = get_project_template_custom_field_value($project_template->id, $custom_field['id']);
            $custom_fields[$custom_field['fieldto']][$custom_field['id']] = $custom_field_value;
        }
        $_new_project_data['custom_fields'] = $custom_fields;
        $_new_project_data['description'] = $project_template->description;
        if($project_template->send_created_email == "1"){
            $_new_project_data['send_created_email'] = "on";
        }
        $_new_project_data['contact_notification'] = $project_template->contact_notification;
        $_new_project_data['notify_contacts'] = unserialize($project_template->notify_contacts);
        $settings = [];
        foreach($project_template->settings as $index => $setting){
            if($index == "available_features"){
                $available_features = unserialize($setting);
                foreach ($available_features as $feature_name => $feature_value){
                    if($feature_value == "1"){
                        $settings[$index][] = $feature_name;
                    }
                }
            }
            else if($setting == 1){
                $settings[$index] = "on";
            }
        }
        $_new_project_data['settings'] = $settings;
//        var_dump($_new_project_data);
//        exit;


        $this->load->model("projects_model");
        $insert_id = $this->projects_model->add($_new_project_data);

        if($insert_id > 0){
            // COPY PROJECT NOTES
            $project_notes = $this->db->where("project_template_id", $project_template->id)->get(PROJECT_TEMPLATES_NOTES_TABLE_NAME)->result_array();
            foreach($project_notes as $project_note){
                $this->db->insert(db_prefix().'project_notes', [
                    'project_id' => $insert_id,
                    'content' => $project_note['content'],
                    'staff_id' => $project_note['staff_id'],
                ]);
            }

            // COPY PROJECT FILES
            $project_files = $this->project_templates_model->get_files($project_template->id);
            $original_path     = PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $project_template->id . '/';
            $new_path     = get_upload_path_by_type('project') . $insert_id . '/';
            _maybe_create_upload_path($new_path);
            foreach($project_files as $file){
                $fullOriginalPath = $original_path . $file['file_name'];
                $fullNewPath = $new_path . $file['file_name'];
                if (file_exists($fullOriginalPath)) {
                    if(copy($fullOriginalPath, $fullNewPath)){
                        $this->db->insert(db_prefix() . 'project_files', [
                            'project_id' => $insert_id,
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

                        $file_insert_id = $this->db->insert_id();
                        if ($file_insert_id) {
                            if (is_image($fullNewPath)) {
                                create_img_thumb($new_path, $file['file_name']);
                            }
                        }
                    }
                }
            }

            // COPY MILESTONE AND TASKS OF MILESTONE
            $project_milestones = $this->db->where("project_template_id", $project_template->id)->get(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME)->result_array();
            foreach($project_milestones as $project_milestone){
                $original_milestone_id = $project_milestone['id'];
                $new_milestone_start_date = _d(date("Y-m-d", strtotime("+".$project_milestone['start_date']." days", strtotime(to_sql_date($_new_project_data['start_date'])))));
                $new_milestone_due_date = _d(date("Y-m-d", strtotime("+".$project_milestone['due_date']." days", strtotime($new_milestone_start_date))));
                $new_milestone_data = [
                    'project_id' => $insert_id,
                    'name' => $project_milestone['name'],
                    'description' => $project_milestone['description'],
                    'start_date' => $new_milestone_start_date,
                    'due_date' => $new_milestone_due_date,
                    'color' => $project_milestone['color'],
                    'milestone_order' => $project_milestone['milestone_order'],
                ];
                if($project_milestone['description_visible_to_customer'] == "1"){
                    $new_milestone_data['description_visible_to_customer'] = 1;
                }
                if($project_milestone['hide_from_customer'] == "1"){
                    $new_milestone_data['hide_from_customer'] = 1;
                }
                $new_milestone_id = $this->projects_model->add_milestone($new_milestone_data);

                if(module_is_active('task_templates')){
                    $this->load->model("task_templates/task_templates_model");
                    $tasks = $this->db
                        ->where("after_task_id", "0")
                        ->where("rel_type", "project")
                        ->where("rel_id", $project_template->id)
                        ->where("milestone", $original_milestone_id)
                        ->get(TASK_TEMPLATES_TABLE_NAME)
                        ->result_array();
                    foreach($tasks as $task){
                        $start_from_date = ($task['start_type'] == "milestone" ? $new_milestone_data['start_date'] : $_new_project_data['start_date']);
                        $start_from_date_type = (in_array($task['startdate_type'], ['day', 'month', 'week']) ? $task['startdate_type'] : 'day');
                        $task_start_date = date("Y-m-d", strtotime("+" . $task['startdate'] . " ".$start_from_date_type, strtotime(to_sql_date($start_from_date))));
                        $new_task_data['task_template'] = $task['id'];
                        $new_task_data['startdate'] = $task_start_date;
                        $new_task_data['rel_type'] = 'project';
                        $new_task_data['rel_id'] = $insert_id;
                        $new_task_data['milestone'] = $new_milestone_id;
                        $this->task_templates_model->create_task($new_task_data);
                    }
                }
            }

            // COPY STANDALONE TASKS
            if(module_is_active('task_templates')){
                $this->load->model("task_templates/task_templates_model");
                $tasks = $this->db
                    ->where("after_task_id" ,"0")
                    ->where("rel_type", "project")
                    ->where("rel_id", $project_template->id)
                    ->where("(milestone IS NULL OR milestone=0)")
                    ->get(TASK_TEMPLATES_TABLE_NAME)
                    ->result_array();
                foreach($tasks as $task){
                    $start_from_date = ($task['start_type'] == "milestone" ? $new_milestone_data['start_date'] : $_new_project_data['start_date']);
                    $start_from_date_type = (in_array($task['startdate_type'], ['day', 'month', 'week']) ? $task['startdate_type'] : 'day');
                    $task_start_date = date("Y-m-d", strtotime("+" . $task['startdate'] . " ".$start_from_date_type, strtotime(to_sql_date($start_from_date))));
                    $new_task_data['task_template'] = $task['id'];
                    $new_task_data['startdate'] = $task_start_date;
                    $new_task_data['rel_type'] = 'project';
                    $new_task_data['rel_id'] = $insert_id;
                    $new_task_data['milestone'] = null;
                    $this->task_templates_model->create_task($new_task_data);
                }
            }
        }

        return $insert_id;
    }

    public function create_task($project_template_id, $milestone_id = null){

    }
}
