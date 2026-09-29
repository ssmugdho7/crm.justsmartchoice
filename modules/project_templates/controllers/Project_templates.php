<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Project_templates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // IF MODULE DISABLED THEN SHOW 404
        if (!defined('PROJECT_TEMPLATES_MODULE_NAME'))
            show_404();
        $this->load->model("project_templates_model");
        $this->load->model("projects_model");
        $this->load->helper("security");
        hooks()->add_action('before_compile_css_assets', 'add_project_templates_css');
        hooks()->add_action('before_compile_scripts_assets', 'add_project_templates_own_scripts');
    }

    public function index(){
        if (! (staff_can('view','project_templates') || staff_can('view_own', 'project_templates'))) {
            access_denied('project_templates');
        }

        $data['title'] = _l('pt_module_title');
        $this->load->view('manage', $data);
    }

    public function table()
    {
        $this->app->get_table_data(module_views_path('project_templates', 'tables/project_templates'));
    }


    public function template($id = '')
    {
        if (!staff_can('edit', 'project_templates') && !staff_can('create', 'project_templates')) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            $data                = $this->input->post();
            $data['description'] = html_purify($this->input->post('description', false));
            if ($id == '') {
                if (!staff_can('create', 'project_templates')) {
                    access_denied('Project_templates');
                }
                $id      = $this->project_templates_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('pt_project_template_lower')));
                    redirect(admin_url('project_templates/view/' . $id));
                }
            } else {
                if (!staff_can('edit', 'project_templates')) {
                    access_denied('Project_templates');
                }
                $success = $this->project_templates_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('pt_project_template_lower')));
                }
                redirect(admin_url('project_templates/view/' . $id));
            }
            die;
        }

        if ($id == '') {
            $title = _l('add_new', _l('pt_project_template_lower'));
            $data['auto_select_billing_type'] = $this->projects_model->get_most_used_billing_type();
        } else {
            $data['project'] = $this->project_templates_model->get($id);
            $data['project']->settings->available_features = unserialize($data['project']->settings->available_features);
            $data['project_members'] = $this->project_templates_model->get_project_template_members($id);

            $title = _l('edit', _l('pt_project_template_lower')) . ' ' . $data['project']->name;
        }

        $data['last_project_settings'] = $this->projects_model->get_last_project_settings();
        if (count($data['last_project_settings'])) {
            $key                                          = array_search('available_features', array_column($data['last_project_settings'], 'name'));
            $data['last_project_settings'][$key]['value'] = unserialize($data['last_project_settings'][$key]['value']);
        }
        $data['settings'] = $this->projects_model->get_settings();
        $data['statuses'] = $this->projects_model->get_project_statuses();
        $data['staff']    = $this->staff_model->get('', ['active' => 1]);

        $data['id']    = $id;
        $data['title'] = $title;
        $this->load->view('new_template', $data);
    }

    public function delete_project_template($id)
    {
        if (!staff_can('delete', 'project_templates')) {
            access_denied('project_templates');
        }
        $success = $this->project_templates_model->delete_project_template($id);
        $message = _l('problem_deleting', _l('pt_project_template_lower'));
        if ($success) {
            $message = _l('deleted', _l('pt_project_template_lower'));
            set_alert('success', $message);
        } else {
            set_alert('warning', $message);
        }

        redirect(admin_url('project_templates'));
    }

    public function copy($project_id)
    {
        if (staff_can('create', 'project_templates')) {
            $new_project_id = $this->project_templates_model->copy($project_id);
            $message = _l('failed_to_copy_project_template');
            if ($new_project_id) {
                $message = _l('pt_project_template_copied_successfully');
                set_alert('success', $message);
                redirect(admin_url('project_templates/view/'.$new_project_id));
                exit;
            }
            else{
                set_alert('warning', $message);
            }
            redirect(admin_url('project_templates'));
        }
    }

    public function view($id)
    {
        if (staff_can('view', 'project_templates') || staff_can('view_own', 'project_templates')) {
            $project = $this->project_templates_model->get($id);

            if (!$project) {
                blank_page(_l('pt_project_template_not_found'));
            }

            $project->settings->available_features = unserialize($project->settings->available_features);
            $data['statuses']                      = $this->projects_model->get_project_statuses();

            $group = !$this->input->get('group') ? 'project_overview' : $this->input->get('group');

            if (strpos($group, '#') !== false) {
                $group = str_replace('#', '', $group);
            }

            $data['tabs'] = get_project_templates_tabs_admin();
            $data['tab']  = $this->app_tabs->filter_tab($data['tabs'], $group);

            if (!$data['tab']) {
                show_404();
            }

            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get('', [], true);

            $data['project']  = $project;

            $data['staff']   = $this->staff_model->get('', ['active' => 1]);
            $data['members'] = $this->project_templates_model->get_project_template_members($id);
            foreach ($data['members'] as $key => $member) {
                $data['members'][$key]['total_logged_time'] = 0;
                $member_timesheets                          = $this->tasks_model->get_unique_member_logged_task_ids($member['staff_id'], ' AND task_id IN (SELECT id FROM ' . db_prefix() . 'tasks WHERE rel_type="project" AND rel_id="' . $this->db->escape_str($id) . '")');

                foreach ($member_timesheets as $member_task) {
                    $data['members'][$key]['total_logged_time'] += $this->tasks_model->calc_task_total_time($member_task->task_id, ' AND staff_id=' . $member['staff_id']);
                }
            }
            $data['bodyclass'] = '';

            $this->app_scripts->add(
                'projects-js',
                base_url($this->app_scripts->core_file('assets/js', 'projects.js')) . '?v=' . $this->app_scripts->core_version(),
                'admin',
                ['app-js', 'jquery-comments-js', 'frappe-gantt-js', 'circle-progress-js']
            );

            if ($group == 'project_overview') {

            } elseif ($group == 'project_milestones') {
                $data['bodyclass'] .= 'project-milestones ';
                $data['milestones_exclude_completed_tasks'] = $this->input->get('exclude_completed') && $this->input->get('exclude_completed') == 'yes' || !$this->input->get('exclude_completed');

                $data['total_milestones'] = total_rows(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME, ['project_template_id' => $id]);
                $data['milestones_found'] = $data['total_milestones'] > 0 || (!$data['total_milestones'] && total_rows(db_prefix() . 'tasks', ['rel_id' => $id, 'rel_type' => 'project', 'milestone' => 0]) > 0);
            } elseif ($group == 'project_files') {
                $data['files'] = $this->project_templates_model->get_files($id);
            } elseif ($group == 'project_notes') {
                $data['staff_notes'] = $this->project_templates_model->get_staff_notes($id);
            }

            if($project->progress_from_tasks == 1)
                $percent = 0;
            else
                $percent = $project->progress;
            $data['percent'] = $percent;

            $this->app_scripts->add('circle-progress-js', 'assets/plugins/jquery-circle-progress/circle-progress.min.js');

            $data['title']          = $data['project']->name;
            $data['bodyclass'] .= 'project invoices-total-manual estimates-total-manual';
            $data['project_status'] = get_project_status_by_id($project->status);

            $this->load->view('view', $data);
        } else {
            access_denied('Project View');
        }
    }

    public function save_note($project_id)
    {
        if (! (staff_can('view', 'project_templates') || staff_can('view_own', 'project_templates')) ) {
            access_denied('Project View');
        }
        if ($this->input->post()) {
            $success = $this->project_templates_model->save_note($this->input->post(null, false), $project_id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('project_note')));
            }
            redirect(admin_url('project_templates/view/' . $project_id . '?group=project_notes'));
        }
    }

    public function upload_file($project_id)
    {
        if (! (staff_can('view', 'project_templates') || staff_can('view_own', 'project_templates')) ) {
            ajax_access_denied();
        }
        handle_project_template_file_uploads($project_id);
    }

    public function change_file_visibility($id, $visible)
    {
        if (! (staff_can('view', 'project_templates') || staff_can('view_own', 'project_templates')) ) {
            ajax_access_denied();
        }
        if ($this->input->is_ajax_request()) {
            $this->project_templates_model->change_file_visibility($id, $visible);
        }
    }

    public function remove_file($project_id, $id)
    {
        if (! (staff_can('view', 'project_templates') || staff_can('view_own', 'project_templates')) ) {
            access_denied('Project View');
        }
        $this->project_templates_model->remove_file($id);
        redirect(admin_url('project_templates/view/' . $project_id . '?group=project_files'));
    }

    public function bulk_action_files()
    {
        $total_deleted       = 0;
        $hasPermissionDelete = staff_can('delete', 'project_templates');
        // bulk action for projects currently only have delete button
        if ($this->input->post()) {
            $fVisibility = $this->input->post('visible_to_customer') == 'true' ? 1 : 0;
            $ids         = $this->input->post('ids');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($hasPermissionDelete && $this->input->post('mass_delete') && $this->project_templates_model->remove_file($id)) {
                        $total_deleted++;
                    } else {
                        $this->project_templates_model->change_file_visibility($id, $fVisibility);
                    }
                }
            }
        }
        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_files_deleted', $total_deleted));
        }
    }

    public function download_all_files($id)
    {
        if (staff_can('view', 'project_templates') || staff_can('view_own', 'project_templates')) {
            $files = $this->project_templates_model->get_files($id);
            if (count($files) == 0) {
                set_alert('warning', _l('no_files_found'));
                redirect(admin_url('project_templates/view/' . $id . '?group=project_files'));
            }
            $path = PROJECT_TEMPLATES_ATTACHMENTS_FOLDER . $id;
            $this->load->library('zip');
            foreach ($files as $file) {
                if ($file['original_file_name'] != '') {
                    $this->zip->read_file($path . '/' . $file['file_name'], $file['original_file_name']);
                } else {
                    $this->zip->read_file($path . '/' . $file['file_name']);
                }
            }
            $this->zip->download(slug_it(get_project_template_name_by_id($id)) . '-files.zip');
            $this->zip->clear_data();
        }
    }

    public function milestone(){
        if ($this->input->post()) {
            $message = '';
            $success = false;
            if (!$this->input->post('id')) {
                if (!staff_can('create_milestones', 'project_templates')) {
                    access_denied();
                }

                $id = $this->project_templates_model->add_milestone($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('project_milestone')));
                }
            } else {
                if (!staff_can('edit_milestones', 'project_templates')) {
                    access_denied();
                }

                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->project_templates_model->update_milestone($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('project_milestone')));
                }
            }
        }

        redirect(admin_url('project_templates/view/' . $this->input->post('project_template_id') . '?group=project_milestones'));
    }

    public function milestones($project_id)
    {
        if (staff_can('view', 'project_templates') || staff_can('view_own', 'project_templates')) {
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data(module_views_path('project_templates', 'tables/milestones'), [
                    'project_template_id' => $project_id,
                ]);
            }
        }
    }

    public function milestones_kanban()
    {
        $data['milestones_exclude_completed_tasks'] = $this->input->get('exclude_completed_tasks') && $this->input->get('exclude_completed_tasks') == 'yes';

        $data['project_template_id'] = $this->input->get('project_template_id');
        $data['milestones'] = [];

        $data['milestones'][] = [
            'name'              => _l('milestones_uncategorized'),
            'id'                => 0,
            'total_logged_time' => 0,
            'color'             => null,
        ];

        $_milestones = $this->project_templates_model->get_milestones($data['project_template_id']);

        foreach ($_milestones as $m) {
            $data['milestones'][] = $m;
        }

        echo $this->load->view('milestones_kan_ban', $data, true);
    }

    public function delete_milestone($project_id, $id)
    {
        if (staff_can('delete_milestones', 'project_templates')) {
            if ($this->project_templates_model->delete_milestone($id)) {
                set_alert('deleted', 'project_milestone');
            }
        }
        redirect(admin_url('project_templates/view/' . $project_id . '?group=project_milestones'));
    }

    public function milestones_kanban_load_more()
    {
        $status     = $this->input->get('status');
        $page       = $this->input->get('page');
        $project_id = $this->input->get('project_id');
        $where      = [];
        $tasks = $this->project_templates_model->do_milestones_kanban_query($status, $project_id, $page, $where);
        foreach ($tasks as $task) {
            $this->load->view('admin/projects/_milestone_kanban_card', ['task' => $task, 'milestone' => $status]);
        }
    }

    public function update_milestones_order(){
        if ($post_data = $this->input->post()) {
            $this->project_templates_model->update_milestones_order($post_data);
        }
    }

    public function change_milestone_color()
    {
        if ($this->input->post()) {
            $this->project_templates_model->update_milestone_color($this->input->post());
        }
    }

    public function update_task_milestone()
    {
        if ($this->input->post()) {
            $this->project_templates_model->update_task_milestone($this->input->post());
        }
    }

    public function file($id, $project_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();

        $data['file'] = $this->project_templates_model->get_file($id, $project_id);

        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $this->load->view('_file', $data);
    }

    public function update_file_data()
    {
        if ($this->input->post()) {
            $this->project_templates_model->update_file_data($this->input->post());
        }
    }

    public function create_project(){
        if (!staff_can('create', 'projects')) {
            access_denied('Projects');
        }

        if ($this->input->post()) {
            $data                = $this->input->post();

            $id      = $this->project_templates_model->create_project($data);
            if ($id) {
                set_alert('success', _l('added_successfully', _l('project')));
                redirect(admin_url('projects/view/' . $id));
                exit;
            }
            redirect(admin_url('project_templates'));
        }

        $data['project_templates'] = $this->project_templates_model->get_all();
        $data['id'] = $this->input->get('id');
        $title = _l('pt_new_project_from_template');
        $data['title'] = $title;
        $this->load->view('new_project', $data);
    }
}
