<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_daily_productivity extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smart_daily_productivity/Smart_daily_productivity_model');
        $this->load->model('staff_model');
    }

    public function index(): void
    {
        $this->require_any_permission(['view', 'view_own']);
        $dateFrom = $this->input->get('date_from', true) ?: date('Y-m-01');
        $dateTo = $this->input->get('date_to', true) ?: date('Y-m-d');
        $staffId = $this->can_view_global() ? (int) ($this->input->get('staff_id', true) ?: 0) : (int) get_staff_user_id();

        $data = [
            'title' => smart_daily_productivity_label('smart_daily_productivity_dashboard'),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'staff_id' => $staffId,
            'staff' => $this->staff_model->get('', ['active' => 1]),
            'summary' => $this->Smart_daily_productivity_model->dashboard_summary($dateFrom, $dateTo, $staffId > 0 ? $staffId : null),
            'scores' => $this->Smart_daily_productivity_model->get_scores(['date_from' => $dateFrom, 'date_to' => $dateTo, 'staff_id' => $staffId]),
        ];
        $this->load->view('index', $data);
    }

    public function my_day(): void
    {
        $this->require_any_permission(['view', 'view_own']);
        $staffId = $this->can_view_global() && $this->input->get('staff_id', true) ? (int) $this->input->get('staff_id', true) : (int) get_staff_user_id();
        $date = $this->input->get('date', true) ?: date('Y-m-d');

        if ($this->input->post()) {
            if (!has_permission('smart_daily_productivity', '', 'create')) {
                access_denied('Smart Daily Productivity');
            }
            $this->Smart_daily_productivity_model->add_entry([
                'staff_id' => $staffId,
                'entry_date' => $this->input->post('entry_date', true),
                'title' => $this->input->post('title', true),
                'description' => $this->input->post('description', true),
                'minutes_spent' => (int) $this->input->post('minutes_spent', true),
                'category' => $this->input->post('category', true),
                'source_type' => 'manual',
                'status' => 'completed',
            ]);
            set_alert('success', smart_daily_productivity_label('smart_daily_productivity_entry_saved'));
            redirect(admin_url('smart_daily_productivity/my_day?date=' . urlencode((string) $this->input->post('entry_date', true))));
        }

        $data = [
            'title' => smart_daily_productivity_label('smart_daily_productivity_my_day'),
            'date' => $date,
            'staff_id' => $staffId,
            'staff' => $this->staff_model->get('', ['active' => 1]),
            'entries' => $this->Smart_daily_productivity_model->get_entries(['staff_id' => $staffId, 'date_from' => $date, 'date_to' => $date]),
        ];
        $this->load->view('my_day', $data);
    }

    public function reports(): void
    {
        if (!has_permission('smart_daily_productivity', '', 'reports')) {
            access_denied('Smart Daily Productivity Reports');
        }
        $dateFrom = $this->input->get('date_from', true) ?: date('Y-m-01');
        $dateTo = $this->input->get('date_to', true) ?: date('Y-m-d');
        $staffId = (int) ($this->input->get('staff_id', true) ?: 0);
        $data = [
            'title' => smart_daily_productivity_label('smart_daily_productivity_reports'),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'staff_id' => $staffId,
            'staff' => $this->staff_model->get('', ['active' => 1]),
            'scores' => $this->Smart_daily_productivity_model->get_scores(['date_from' => $dateFrom, 'date_to' => $dateTo, 'staff_id' => $staffId]),
            'entries' => $this->Smart_daily_productivity_model->get_entries(['date_from' => $dateFrom, 'date_to' => $dateTo, 'staff_id' => $staffId]),
        ];
        $this->load->view('reports', $data);
    }

    public function settings(): void
    {
        if (!has_permission('smart_daily_productivity', '', 'settings')) {
            access_denied('Smart Daily Productivity Settings');
        }
        if ($this->input->post()) {
            $allowed = [
                'smart_daily_productivity_enabled',
                'smart_daily_productivity_daily_target_minutes',
                'smart_daily_productivity_daily_target_tasks',
                'smart_daily_productivity_include_tickets',
                'smart_daily_productivity_allow_employee_manual_entries',
                'smart_daily_productivity_allow_employee_reports',
                'smart_daily_productivity_allow_department_reports',
                'smart_daily_productivity_score_high',
                'smart_daily_productivity_score_low',
            ];
            foreach ($allowed as $option) {
                $value = $this->input->post($option, true);
                if ($value === null) {
                    $value = '0';
                }
                update_option($option, (string) $value);
            }
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('settings?group=smart_daily_productivity'));
        }
        $data = ['title' => smart_daily_productivity_label('smart_daily_productivity_settings')];
        $this->load->view('settings', $data);
    }

    public function help(): void
    {
        $this->require_any_permission(['view', 'view_own']);
        $this->load->view('help', ['title' => smart_daily_productivity_label('smart_daily_productivity_help_guide')]);
    }

    public function health(): void
    {
        if (!has_permission('smart_daily_productivity', '', 'health')) {
            access_denied('Smart Daily Productivity Health');
        }
        if ($this->input->post('cleanup_cache')) {
            $this->Smart_daily_productivity_model->cleanup_cache();
            set_alert('success', smart_daily_productivity_label('smart_daily_productivity_cache_cleaned'));
            redirect(admin_url('smart_daily_productivity/health'));
        }
        if ($this->input->post('repair_database')) {
            $this->Smart_daily_productivity_model->repair_database();
            set_alert('success', smart_daily_productivity_label('smart_daily_productivity_database_repaired'));
            redirect(admin_url('smart_daily_productivity/health'));
        }
        $data = [
            'title' => smart_daily_productivity_label('smart_daily_productivity_health_checker'),
            'health' => $this->Smart_daily_productivity_model->health_status(),
            'logs' => $this->Smart_daily_productivity_model->get_logs(),
        ];
        $this->load->view('health', $data);
    }

    public function database_checker(): void
    {
        $this->health();
    }

    public function delete_entry($id): void
    {
        if (!has_permission('smart_daily_productivity', '', 'delete')) {
            access_denied('Smart Daily Productivity Delete');
        }
        if (!$this->input->post()) {
            access_denied('Smart Daily Productivity Delete');
        }
        $this->Smart_daily_productivity_model->delete_entry((int) $id);
        set_alert('success', smart_daily_productivity_label('smart_daily_productivity_entry_deleted'));
        redirect(admin_url('smart_daily_productivity/my_day'));
    }

    private function can_view_global(): bool
    {
        return has_permission('smart_daily_productivity', '', 'view');
    }

    private function require_any_permission(array $permissions): void
    {
        foreach ($permissions as $permission) {
            if (has_permission('smart_daily_productivity', '', $permission)) {
                return;
            }
        }
        access_denied('Smart Daily Productivity');
    }
}
