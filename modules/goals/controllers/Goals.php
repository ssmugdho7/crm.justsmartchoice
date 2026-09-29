<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Goals extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('goals/goals_model');
    }

    private function can_view()
    {
        return staff_can('view', 'goals') || staff_can('view_own', 'goals');
    }

    private function add_navigation_data(array &$data, $active = 'dashboard')
    {
        $data['goals_nav_active'] = $active;
    }


    public function index()
    {
        if (!$this->can_view()) { access_denied('goals'); }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('goals', 'table'));
        }
        $this->app_scripts->add('circle-progress-js', 'assets/plugins/jquery-circle-progress/circle-progress.min.js');
        $data['title'] = _l('goals_tracking');
        $this->add_navigation_data($data, 'dashboard');
        $data['summary'] = $this->goals_model->get_summary();
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        $this->load->view('manage', $data);
    }

    public function goal($id = '')
    {
        if (!$this->can_view()) { access_denied('goals'); }
        if ($this->input->post()) {
            if ($id === '') {
                if (staff_cant('create', 'goals')) { access_denied('goals'); }
                $id = $this->goals_model->add($this->input->post());
                if ($id) { set_alert('success', _l('added_successfully', _l('goal'))); redirect(admin_url('goals/goal/' . $id)); }
            } else {
                if (staff_cant('edit', 'goals')) { access_denied('goals'); }
                if ($this->goals_model->update($this->input->post(), $id)) { set_alert('success', _l('updated_successfully', _l('goal'))); }
                redirect(admin_url('goals/goal/' . $id));
            }
        }
        if ($id === '') {
            $data['title'] = _l('add_new', _l('goal_lowercase'));
            $this->add_navigation_data($data, 'new');
        } else {
            $data['goal'] = $this->goals_model->get($id);
            if (!$data['goal']) { show_404(); }
            if (staff_cant('view', 'goals') && (int)$data['goal']->staff_id !== get_staff_user_id()) { access_denied('goals'); }
            $data['achievement'] = $this->goals_model->calculate_goal_achievement($id);
            $data['title'] = _l('edit', _l('goal_lowercase'));
            $this->add_navigation_data($data, 'goals');
        }
        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        $this->load->model('contracts_model');
        $data['contract_types'] = $this->contracts_model->get_contract_types();
        $this->app_scripts->add('circle-progress-js', 'assets/plugins/jquery-circle-progress/circle-progress.min.js');
        $this->load->view('goal', $data);
    }

    public function delete($id)
    {
        if (staff_cant('delete', 'goals')) { access_denied('goals'); }
        if ($id && $this->goals_model->delete($id)) { set_alert('success', _l('deleted', _l('goal'))); }
        redirect(admin_url('goals'));
    }

    public function notify($id, $notify_type)
    {
        if (staff_cant('edit', 'goals') && staff_cant('create', 'goals')) { access_denied('goals'); }
        $success = $this->goals_model->notify_staff_members($id, $notify_type);
        set_alert($success ? 'success' : 'warning', _l($success ? 'goal_notify_staff_notified_manually_success' : 'goal_notify_staff_notified_manually_fail'));
        redirect(admin_url('goals/goal/' . $id));
    }


    public function reports()
    {
        if (!$this->can_view()) { access_denied('goals'); }
        $data['title'] = _l('goals_reports');
        $data['summary'] = $this->goals_model->get_dashboard_metrics();
        $data['by_department'] = $this->goals_model->get_goals_by_department();
        $data['monthly'] = $this->goals_model->get_monthly_completion();
        $data['staff'] = $this->goals_model->get_staff_performance();
        $this->add_navigation_data($data, 'reports');
        $this->load->view('reports', $data);
    }

    public function health()
    {
        if (!$this->can_view()) { access_denied('goals'); }
        $table = db_prefix() . 'goals';
        $required = ['id','subject','description','start_date','end_date','goal_type','achievement','staff_id','priority','department_id','goal_status','weight'];
        $data['checks'] = [
            'module_folder' => module_dir_path('goals', ''),
            'main_file' => module_dir_path('goals', 'goals.php'),
            'table_exists' => $this->db->table_exists($table),
            'record_count' => $this->db->table_exists($table) ? $this->db->count_all($table) : 0,
            'missing_fields' => [],
            'english_language' => is_file(module_dir_path('goals', 'language/english/goals_lang.php')),
            'spanish_language' => is_file(module_dir_path('goals', 'language/spanish/goals_lang.php')),
            'migration_250' => is_file(module_dir_path('goals', 'migrations/250_version_250.php')),
        ];
        if ($data['checks']['table_exists']) {
            foreach ($required as $field) { if (!$this->db->field_exists($field, $table)) { $data['checks']['missing_fields'][] = $field; } }
        }
        $data['title'] = _l('goals_health_check');
        $this->add_navigation_data($data, 'health');
        $this->load->view('health', $data);
    }

    public function repair_database()
    {
        if (!is_admin()) { access_denied('goals'); }
        require module_dir_path('goals', 'install.php');
        set_alert('success', _l('goals_database_repaired'));
        redirect(admin_url('goals/health'));
    }

    public function help()
    {
        if (!$this->can_view()) { access_denied('goals'); }
        $data['title'] = _l('goals_help_guide');
        $this->add_navigation_data($data, 'help');
        $this->load->view('help', $data);
    }
}
