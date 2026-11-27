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


    /** Personal daily checklist: task definitions persist; checkmarks reset by date. */
    public function checklist(): void
    {
        $this->daily_tasks_page(false);
    }

    public function manage_tasks(): void
    {
        $this->daily_tasks_page(true);
    }

    private function daily_tasks_page(bool $manage): void
    {
        $this->require_any_permission(['view', 'view_own']);
        $staffId = (int) get_staff_user_id();
        $tasks = db_prefix() . 'sdp_checklist_items';
        $checks = db_prefix() . 'sdp_checklist_checks';
        if (!$this->db->table_exists($tasks) || !$this->db->table_exists($checks)) {
            show_error('Daily Task Checklist needs migration 106. Upgrade the module database.', 503);
            return;
        }
        $date = (string) ($this->input->get('date', true) ?: date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || date('Y-m-d', strtotime($date)) !== $date) {
            $date = date('Y-m-d');
        }
        if ($this->input->method(true) === 'POST') {
            $action = (string) $this->input->post('action', true);
            $id = (int) $this->input->post('id', true);
            $title = trim((string) $this->input->post('title', true));
            if ($action === 'add' || $action === 'edit') {
                if (!is_admin() && !has_permission('smart_daily_productivity', '', $action === 'add' ? 'create' : 'edit')) {
                    access_denied('Daily Task Checklist');
                }
                if ($title === '' || mb_strlen($title) > 190) {
                    set_alert('danger', 'Enter a task title (up to 190 characters).');
                } elseif ($action === 'add') {
                    $this->db->insert($tasks, ['staff_id' => $staffId, 'title' => $title,
                        'notes' => (string) $this->input->post('notes', true), 'is_active' => 1,
                        'sort_order' => 0, 'created_at' => date('Y-m-d H:i:s')]);
                    set_alert('success', 'Daily task added.');
                } else {
                    $this->db->where('id', $id)->where('staff_id', $staffId)->update($tasks,
                        ['title' => $title, 'notes' => (string) $this->input->post('notes', true)]);
                    set_alert('success', 'Daily task updated.');
                }
            } elseif ($action === 'import_starter') {
                if (!is_admin() && !has_permission('smart_daily_productivity', '', 'create')) {
                    access_denied('Daily Task Checklist');
                }
                // Examples based on common daily reminders; no existing items are deleted or overwritten.
                $starter = [
                    ['Check daily customer emails', 'Review incoming customer correspondence and respond to urgent requests.'],
                    ['Review appointments and daily schedule', 'Check appointments, travel arrangements, and priorities.'],
                    ['Follow up on customers and estimates', 'Review pending estimates and contact customers when necessary.'],
                    ['Contact vendors', 'Follow up on materials, deliveries, and vendor questions.'],
                    ['Create or update estimates', 'Prepare estimates that need attention today.'],
                    ['Review employee tasks', 'Check outstanding assignments and progress.'],
                    ['Back up important business data', 'Verify scheduled server, website, and database backups.'],
                    ['Submit daily work report', 'Record completed work and any unresolved issues.'],
                ];
                $added = 0;
                foreach ($starter as $row) {
                    $exists = $this->db->where('staff_id', $staffId)->where('title', $row[0])->count_all_results($tasks);
                    if (!$exists) {
                        if ($this->db->insert($tasks, ['staff_id' => $staffId, 'title' => $row[0], 'notes' => $row[1],
                            'is_active' => 1, 'sort_order' => 0, 'created_at' => date('Y-m-d H:i:s')])) {
                            $added++;
                        }
                    }
                }
                set_alert('success', $added . ' starter tasks added. Existing tasks were preserved.');
            } elseif ($action === 'check' && $id > 0) {
                $exists = $this->db->where('id', $id)->where('staff_id', $staffId)->where('is_active', 1)->count_all_results($tasks);
                if ($exists) {
                    $checked = $this->input->post('checked', true) === '1';
                    if ($checked) {
                        $this->db->query('INSERT INTO `' . $checks . '` (`item_id`,`staff_id`,`check_date`,`completed_at`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE `completed_at`=VALUES(`completed_at`)',
                            [$id, $staffId, $date, date('Y-m-d H:i:s')]);
                    } else {
                        $this->db->where('item_id', $id)->where('staff_id', $staffId)->where('check_date', $date)->delete($checks);
                    }
                }
            } elseif ($action === 'toggle' && $id > 0) {
                if (!is_admin() && !has_permission('smart_daily_productivity', '', 'edit')) { access_denied('Daily Task Checklist'); }
                $this->db->where('id', $id)->where('staff_id', $staffId)->update($tasks,
                    ['is_active' => $this->input->post('active', true) === '1' ? 1 : 0]);
            }
            redirect(admin_url('smart_daily_productivity/' . ($manage ? 'manage_tasks' : 'checklist') . '?date=' . urlencode($date)));
            return;
        }
        $items = $this->db->where('staff_id', $staffId)->order_by('is_active', 'DESC')->order_by('id', 'ASC')->get($tasks)->result_array();
        $done = $this->db->select('item_id')->where('staff_id', $staffId)->where('check_date', $date)->get($checks)->result_array();
        $checked = array_fill_keys(array_map('intval', array_column($done, 'item_id')), true);
        $this->load->view($manage ? 'manage_tasks' : 'daily_tasks', ['title' => $manage ? 'Manage Daily Tasks' : 'Daily Reminders', 'items' => $items,
            'checked' => $checked, 'date' => $date]);
    }

    public function reminders(): void
    {
        $this->require_any_permission(['view', 'view_own']);
        $staffId = (int) get_staff_user_id();
        $table = db_prefix() . 'sdp_reminders';
        if (!$this->db->table_exists($table)) {
            show_error('Reminder database is not installed. Run the module database upgrade (migration 105).', 503);
            return;
        }
        if ($this->input->post()) {
            if (!has_permission('smart_daily_productivity', '', 'create') && !is_admin()) { access_denied('Daily Reminders'); }
            $title = trim((string) $this->input->post('title', true));
            $date = (string) $this->input->post('remind_at', true);
            $alarm = (string) $this->input->post('alarm', true);
            $timestamp = strtotime($date);
            if ($title === '' || strlen($title) > 190 || !$timestamp || !in_array($alarm, ['alarm 1.mp3', 'alarm 2.mp3', 'none'], true)) {
                set_alert('danger', 'Enter a title, valid reminder date and alarm.');
            } else {
                $this->db->insert($table, [
                    'staff_id' => $staffId, 'title' => $title,
                    'notes' => (string) $this->input->post('notes', true),
                    'remind_at' => date('Y-m-d H:i:s', $timestamp),
                    'alarm_file' => $alarm, 'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                set_alert('success', 'Daily reminder saved.');
            }
            redirect(admin_url('smart_daily_productivity/reminders'));
            return;
        }
        if ($this->input->get('complete')) {
            $id = (int) $this->input->get('complete');
            if ($id > 0) {
                $this->db->where('id', $id)->where('staff_id', $staffId)->update($table, ['status' => 'completed']);
            }
            redirect(admin_url('smart_daily_productivity/reminders'));
            return;
        }
        $data = ['title' => 'Daily Reminders',
            'reminders' => $this->db->where('staff_id', $staffId)->order_by('remind_at', 'ASC')->get($table)->result_array(),
            'audio_files' => [
                'alarm 1.mp3' => is_file(FCPATH . 'media/Alarms/alarm 1.mp3'),
                'alarm 2.mp3' => is_file(FCPATH . 'media/Alarms/alarm 2.mp3'),
            ],
        ];
        $this->load->view('reminders', $data);
    }

    public function reminder_due(): void
    {
        $this->require_any_permission(['view', 'view_own']);
        $table = db_prefix() . 'sdp_reminders';
        $this->output->set_content_type('application/json');
        if (!$this->db->table_exists($table)) {
            $this->output->set_output(json_encode(['reminders' => []]));
            return;
        }
        $staffId = (int) get_staff_user_id();
        $rows = $this->db->select('id,title,notes,alarm_file,remind_at')
            ->where('staff_id', $staffId)->where('status', 'pending')
            ->where('remind_at <=', date('Y-m-d H:i:s'))
            ->order_by('remind_at', 'ASC')->limit(10)->get($table)->result_array();
        $this->output->set_output(json_encode(['reminders' => $rows]));
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
        $selectedDate = $this->input->get('date', true);
        $date = $selectedDate ? to_sql_date($selectedDate) : date('Y-m-d');

        if ($this->input->post()) {
            $staffId = (int)get_staff_user_id();
            if (!has_permission('smart_daily_productivity', '', 'create')) {
                access_denied('Smart Daily Productivity');
            }
            $entryId = $this->Smart_daily_productivity_model->add_entry([
                'staff_id' => $staffId,
                'entry_date' => $this->input->post('entry_date', true),
                'title' => $this->input->post('title', true),
                'description' => $this->input->post('description', true),
                'minutes_spent' => (int) $this->input->post('minutes_spent', true),
                'category' => $this->input->post('category', true),
                'source_type' => 'manual',
                'status' => 'completed',
            ]);
            set_alert($entryId ? 'success' : 'danger', smart_daily_productivity_label($entryId ? 'smart_daily_productivity_entry_saved' : 'smart_daily_productivity_entry_save_failed'));
            redirect(admin_url('smart_daily_productivity/my_day?date=' . urlencode((string) $this->input->post('entry_date', true))));
        }

        $data = [
            'title' => smart_daily_productivity_label('smart_daily_productivity_my_day'),
            'date' => $date,
            'selected_date' => $selectedDate ? $date : '',
            'staff_id' => $staffId,
            'staff' => $this->staff_model->get('', ['active' => 1]),
            'entries' => $this->Smart_daily_productivity_model->get_entries(['staff_id' => $staffId, 'date_from' => $selectedDate ? $date : null, 'date_to' => $selectedDate ? $date : null]),
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
        $staffIds = array_values(array_unique(array_filter(array_map('intval', (array)$this->input->get('staff_ids')))));
        $data = [
            'title' => smart_daily_productivity_label('smart_daily_productivity_reports'),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'staff_id' => $staffId,
            'staff_ids' => $staffIds,
            'staff' => $this->staff_model->get('', ['active' => 1]),
            'scores' => $this->Smart_daily_productivity_model->get_scores(['date_from' => $dateFrom, 'date_to' => $dateTo, 'staff_id' => $staffId]),
            'entries' => $this->Smart_daily_productivity_model->get_entries(['date_from' => $dateFrom, 'date_to' => $dateTo, 'staff_id' => $staffId]),
        ];
        if ($staffIds) {
            $data['scores'] = array_values(array_filter($data['scores'], static function ($row) use ($staffIds) { return in_array((int)$row['staff_id'], $staffIds, true); }));
            $data['entries'] = array_values(array_filter($data['entries'], static function ($row) use ($staffIds) { return in_array((int)$row['staff_id'], $staffIds, true); }));
        }
        $this->load->view('reports', $data);
    }

    public function staff_day($staffId = 0): void
    {
        $staffId = (int)$staffId;
        $own = $staffId === (int)get_staff_user_id();
        if (!$own && !has_permission('smart_daily_productivity', '', 'reports')) {
            access_denied('Smart Daily Productivity Reports');
        }
        if ($own) { $this->require_any_permission(['view', 'view_own', 'reports']); }
        $date = (string)($this->input->get('date', true) ?: date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate((int)substr($date,5,2), (int)substr($date,8,2), (int)substr($date,0,4))) {
            show_404(); return;
        }
        $member = $this->staff_model->get($staffId);
        if (!$member) { show_404(); return; }
        $entries = $this->Smart_daily_productivity_model->get_entries(['staff_id'=>$staffId, 'date_from'=>$date, 'date_to'=>$date]);
        $this->load->view('staff_day', ['title'=>'Employee Daily Activity', 'member'=>$member, 'date'=>$date, 'entries'=>$entries]);
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

    public function update_entry($id): void
    {
        if (!has_permission('smart_daily_productivity', '', 'edit')) {
            access_denied('Smart Daily Productivity Edit');
        }
        if (!$this->input->post()) {
            access_denied('Smart Daily Productivity Edit');
        }

        $entry = $this->Smart_daily_productivity_model->get_entry((int) $id);
        if (!$entry || $entry->source_type !== 'manual') {
            set_alert('danger', smart_daily_productivity_label('smart_daily_productivity_entry_update_failed'));
            redirect(admin_url('smart_daily_productivity/my_day'));
        }
        if (!$this->can_view_global() && (int) $entry->staff_id !== (int) get_staff_user_id()) {
            access_denied('Smart Daily Productivity Edit');
        }

        $category = (string) $this->input->post('category', true);
        $allowedCategories = ['Project Task', 'Contract Task', 'Client Task', 'Research Task', 'Client Ticket', 'Other Process'];
        $updated = in_array($category, $allowedCategories, true) && $this->Smart_daily_productivity_model->update_entry((int) $id, [
            'entry_date' => $this->input->post('entry_date', true),
            'title' => $this->input->post('edit_title', true),
            'description' => $this->input->post('edit_description', true),
            'minutes_spent' => (int) $this->input->post('edit_minutes_spent', true),
            'category' => $category,
        ]);
        set_alert($updated ? 'success' : 'danger', smart_daily_productivity_label($updated ? 'smart_daily_productivity_entry_updated' : 'smart_daily_productivity_entry_update_failed'));
        redirect(admin_url('smart_daily_productivity/my_day?' . http_build_query([
            'staff_id' => (int) $entry->staff_id,
            'date' => (string) $this->input->post('entry_date', true),
        ])));
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
