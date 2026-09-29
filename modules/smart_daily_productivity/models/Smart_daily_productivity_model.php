<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_daily_productivity_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_entry(array $data): int
    {
        $insert = [
            'staff_id'             => (int) ($data['staff_id'] ?? get_staff_user_id()),
            'entry_date'           => $this->clean_date((string) ($data['entry_date'] ?? date('Y-m-d'))),
            'source_type'          => $this->safe_text((string) ($data['source_type'] ?? 'manual'), 50),
            'source_id'            => !empty($data['source_id']) ? (int) $data['source_id'] : null,
            'related_type'         => $this->safe_nullable_text($data['related_type'] ?? null, 100),
            'related_id'           => !empty($data['related_id']) ? (int) $data['related_id'] : null,
            'title'                => $this->safe_text((string) ($data['title'] ?? ''), 255),
            'description'          => $this->safe_nullable_text($data['description'] ?? null, 5000),
            'minutes_spent'        => max(0, (int) ($data['minutes_spent'] ?? 0)),
            'productivity_points'  => (float) ($data['productivity_points'] ?? 0),
            'status'               => $this->safe_text((string) ($data['status'] ?? 'completed'), 50),
            'category'             => $this->safe_text((string) ($data['category'] ?? 'Other Process'), 100),
            'created_by'           => (int) get_staff_user_id(),
            'created_at'           => date('Y-m-d H:i:s'),
        ];

        if ($insert['title'] === '') {
            return 0;
        }

        $this->db->insert(db_prefix() . 'smart_daily_productivity_entries', $insert);
        $id = (int) $this->db->insert_id();
        $this->recalculate_score($insert['staff_id'], $insert['entry_date']);
        $this->write_log('entry_created', 'Daily productivity entry created.', $insert['staff_id']);
        return $id;
    }

    public function update_entry(int $id, array $data): bool
    {
        $existing = $this->get_entry($id);
        if (!$existing) {
            return false;
        }

        $update = [
            'entry_date'          => $this->clean_date((string) ($data['entry_date'] ?? $existing->entry_date)),
            'title'               => $this->safe_text((string) ($data['title'] ?? $existing->title), 255),
            'description'         => $this->safe_nullable_text($data['description'] ?? $existing->description, 5000),
            'minutes_spent'       => max(0, (int) ($data['minutes_spent'] ?? $existing->minutes_spent)),
            'productivity_points' => (float) ($data['productivity_points'] ?? $existing->productivity_points),
            'category'            => $this->safe_text((string) ($data['category'] ?? $existing->category), 100),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        $this->db->where('id', $id)->update(db_prefix() . 'smart_daily_productivity_entries', $update);
        $this->recalculate_score((int) $existing->staff_id, (string) $existing->entry_date);
        $this->recalculate_score((int) $existing->staff_id, $update['entry_date']);
        $this->write_log('entry_updated', 'Daily productivity entry updated.', (int) $existing->staff_id);
        return true;
    }

    public function delete_entry(int $id): bool
    {
        $existing = $this->get_entry($id);
        if (!$existing) {
            return false;
        }

        $this->db->where('id', $id)->delete(db_prefix() . 'smart_daily_productivity_entries');
        $this->recalculate_score((int) $existing->staff_id, (string) $existing->entry_date);
        $this->write_log('entry_deleted', 'Daily productivity entry deleted.', (int) $existing->staff_id);
        return true;
    }

    public function get_entry(int $id)
    {
        return $this->db->where('id', $id)->get(db_prefix() . 'smart_daily_productivity_entries')->row();
    }

    public function get_entries(array $filters = []): array
    {
        $this->db->select('e.*, s.firstname, s.lastname')
            ->from(db_prefix() . 'smart_daily_productivity_entries e')
            ->join(db_prefix() . 'staff s', 's.staffid = e.staff_id', 'left');

        if (!empty($filters['staff_id'])) {
            $this->db->where('e.staff_id', (int) $filters['staff_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('e.entry_date >=', $this->clean_date((string) $filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('e.entry_date <=', $this->clean_date((string) $filters['date_to']));
        }
        if (!empty($filters['category'])) {
            $this->db->where('e.category', (string) $filters['category']);
        }
        if (!empty($filters['search'])) {
            $search = $this->db->escape_like_str((string) $filters['search']);
            $this->db->group_start()->like('e.title', $search)->or_like('e.description', $search)->group_end();
        }

        return $this->db->order_by('e.entry_date', 'DESC')->order_by('e.id', 'DESC')->limit(500)->get()->result_array();
    }

    public function get_scores(array $filters = []): array
    {
        $this->db->select('sc.*, s.firstname, s.lastname, s.email')
            ->from(db_prefix() . 'smart_daily_productivity_scores sc')
            ->join(db_prefix() . 'staff s', 's.staffid = sc.staff_id', 'left');

        if (!empty($filters['staff_id'])) {
            $this->db->where('sc.staff_id', (int) $filters['staff_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('sc.score_date >=', $this->clean_date((string) $filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('sc.score_date <=', $this->clean_date((string) $filters['date_to']));
        }

        return $this->db->order_by('sc.score_date', 'DESC')->order_by('sc.score_percent', 'DESC')->get()->result_array();
    }

    public function dashboard_summary(string $dateFrom, string $dateTo, ?int $staffId = null): array
    {
        $filters = ['date_from' => $dateFrom, 'date_to' => $dateTo];
        if ($staffId !== null && $staffId > 0) {
            $filters['staff_id'] = $staffId;
        }

        $entries = $this->get_entries($filters);
        $scores = $this->get_scores($filters);
        $summary = [
            'entries_total' => count($entries),
            'minutes_total' => 0,
            'tasks_total' => 0,
            'tickets_total' => 0,
            'manual_total' => 0,
            'average_score' => 0,
            'categories' => [],
        ];

        foreach ($entries as $entry) {
            $summary['minutes_total'] += (int) $entry['minutes_spent'];
            if ($entry['source_type'] === 'task') {
                $summary['tasks_total']++;
            } elseif ($entry['source_type'] === 'ticket') {
                $summary['tickets_total']++;
            } else {
                $summary['manual_total']++;
            }
            $category = (string) $entry['category'];
            $summary['categories'][$category] = ($summary['categories'][$category] ?? 0) + 1;
        }

        if (count($scores) > 0) {
            $total = 0;
            foreach ($scores as $score) {
                $total += (float) $score['score_percent'];
            }
            $summary['average_score'] = round($total / count($scores), 2);
        }

        return $summary;
    }

    public function recalculate_score(int $staffId, string $date): void
    {
        $date = $this->clean_date($date);
        $entries = $this->get_entries(['staff_id' => $staffId, 'date_from' => $date, 'date_to' => $date]);

        $score = [
            'completed_tasks' => 0,
            'manual_entries' => 0,
            'ticket_replies' => 0,
            'ticket_closed' => 0,
            'ticket_open' => 0,
            'project_tasks' => 0,
            'contract_tasks' => 0,
            'client_tasks' => 0,
            'research_tasks' => 0,
            'other_tasks' => 0,
            'minutes_total' => 0,
        ];

        foreach ($entries as $entry) {
            $score['minutes_total'] += (int) $entry['minutes_spent'];
            if ($entry['source_type'] === 'task') {
                $score['completed_tasks']++;
            } elseif ($entry['source_type'] === 'ticket') {
                if ($entry['status'] === 'closed' || $entry['status'] === 'completed') {
                    $score['ticket_closed']++;
                } else {
                    $score['ticket_replies']++;
                }
            } else {
                $score['manual_entries']++;
            }

            $category = strtolower((string) $entry['category']);
            if (strpos($category, 'project') !== false) {
                $score['project_tasks']++;
            } elseif (strpos($category, 'contract') !== false) {
                $score['contract_tasks']++;
            } elseif (strpos($category, 'client') !== false) {
                $score['client_tasks']++;
            } elseif (strpos($category, 'research') !== false) {
                $score['research_tasks']++;
            } else {
                $score['other_tasks']++;
            }
        }

        $targetMinutes = max(1, (int) get_option('smart_daily_productivity_daily_target_minutes'));
        $targetTasks = max(1, (int) get_option('smart_daily_productivity_daily_target_tasks'));
        $taskUnits = $score['completed_tasks'] + $score['manual_entries'] + $score['ticket_replies'] + $score['ticket_closed'];
        $timeScore = min(100, ($score['minutes_total'] / $targetMinutes) * 100);
        $taskScore = min(100, ($taskUnits / $targetTasks) * 100);
        $scorePercent = round(($timeScore * 0.45) + ($taskScore * 0.55), 2);
        $rating = $this->rating_label($scorePercent);

        $row = array_merge($score, [
            'staff_id' => $staffId,
            'score_date' => $date,
            'score_percent' => $scorePercent,
            'rating_label' => $rating,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $existing = $this->db->where('staff_id', $staffId)->where('score_date', $date)->get(db_prefix() . 'smart_daily_productivity_scores')->row();
        if ($existing) {
            $this->db->where('id', (int) $existing->id)->update(db_prefix() . 'smart_daily_productivity_scores', $row);
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'smart_daily_productivity_scores', $row);
        }
    }

    public function capture_completed_task($data): void
    {
        if (get_option('smart_daily_productivity_enabled') !== '1') {
            return;
        }

        $taskId = (int) ($data['task_id'] ?? $data['id'] ?? 0);
        if ($taskId <= 0 || !$this->db->table_exists(db_prefix() . 'tasks')) {
            return;
        }

        $task = $this->db->where('id', $taskId)->get(db_prefix() . 'tasks')->row();
        if (!$task || (int) $task->status !== 5) {
            return;
        }

        $exists = $this->db->where('source_type', 'task')->where('source_id', $taskId)->get(db_prefix() . 'smart_daily_productivity_entries')->row();
        if ($exists) {
            return;
        }

        $staffId = (int) ($task->finished_by ?? get_staff_user_id());
        if ($staffId <= 0) {
            $staffId = (int) get_staff_user_id();
        }

        $this->add_entry([
            'staff_id' => $staffId,
            'entry_date' => date('Y-m-d'),
            'source_type' => 'task',
            'source_id' => $taskId,
            'related_type' => (string) ($task->rel_type ?? ''),
            'related_id' => (int) ($task->rel_id ?? 0),
            'title' => (string) ($task->name ?? 'Completed Task'),
            'description' => 'Automatically captured from completed Perfex CRM task.',
            'minutes_spent' => 0,
            'productivity_points' => 1,
            'status' => 'completed',
            'category' => $this->category_from_relation((string) ($task->rel_type ?? '')),
        ]);
    }

    public function capture_ticket_status_change($data): void
    {
        if (get_option('smart_daily_productivity_enabled') !== '1' || get_option('smart_daily_productivity_include_tickets') !== '1') {
            return;
        }

        $ticketId = (int) ($data['ticket_id'] ?? $data['id'] ?? 0);
        if ($ticketId <= 0 || !$this->db->table_exists(db_prefix() . 'tickets')) {
            return;
        }

        $ticket = $this->db->where('ticketid', $ticketId)->get(db_prefix() . 'tickets')->row();
        if (!$ticket) {
            return;
        }

        $status = (string) ($ticket->status ?? 'updated');
        $staffId = (int) get_staff_user_id();
        if ($staffId <= 0) {
            return;
        }

        $this->add_entry([
            'staff_id' => $staffId,
            'entry_date' => date('Y-m-d'),
            'source_type' => 'ticket',
            'source_id' => $ticketId,
            'related_type' => 'ticket',
            'related_id' => $ticketId,
            'title' => 'Ticket Activity: ' . (string) ($ticket->subject ?? ('Ticket #' . $ticketId)),
            'description' => 'Automatically captured from ticket status activity.',
            'minutes_spent' => 0,
            'productivity_points' => 1,
            'status' => $status,
            'category' => 'Client Ticket',
        ]);
    }

    public function repair_database(): array
    {
        require module_dir_path('smart_daily_productivity') . 'install.php';
        $this->write_log('database_repair', 'Safe database checker repair was executed.', (int) get_staff_user_id());
        return $this->health_status();
    }

    public function cleanup_cache(): void
    {
        $paths = [APPPATH . 'cache'];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }
            $files = glob(rtrim($path, '/') . '/*');
            if (!is_array($files)) {
                continue;
            }
            foreach ($files as $file) {
                if (!is_file($file)) {
                    continue;
                }
                if (basename($file) === 'index.html') {
                    continue;
                }
                @unlink($file);
            }
        }
        $this->write_log('cache_cleanup', 'Safe cache cleanup completed without deleting index.html.', (int) get_staff_user_id());
    }

    public function health_status(): array
    {
        $modulePath = module_dir_path('smart_daily_productivity');
        $tables = [
            db_prefix() . 'smart_daily_productivity_entries',
            db_prefix() . 'smart_daily_productivity_scores',
            db_prefix() . 'smart_daily_productivity_logs',
        ];
        $files = [
            'smart_daily_productivity.php',
            'install.php',
            'uninstall.php',
            'config/routes.php',
            'controllers/Smart_daily_productivity.php',
            'models/Smart_daily_productivity_model.php',
            'views/index.php',
            'views/my_day.php',
            'views/reports.php',
            'views/settings.php',
            'views/help.php',
            'views/health.php',
            'language/english/smart_daily_productivity/smart_daily_productivity_lang.php',
            'language/english/smart_daily_productivity_lang.php',
            'migrations/101_version_101.php',
            'assets/css/smart_daily_productivity.css',
            'assets/js/smart_daily_productivity.js',
        ];
        $options = [
            'smart_daily_productivity_enabled',
            'smart_daily_productivity_version',
            'smart_daily_productivity_daily_target_minutes',
            'smart_daily_productivity_daily_target_tasks',
            'smart_daily_productivity_include_tickets',
            'smart_daily_productivity_allow_employee_manual_entries',
            'smart_daily_productivity_allow_employee_reports',
            'smart_daily_productivity_allow_department_reports',
            'smart_daily_productivity_score_high',
            'smart_daily_productivity_score_low',
        ];

        $result = [
            smart_daily_productivity_label('smart_daily_productivity_database_checker') => [],
            smart_daily_productivity_label('smart_daily_productivity_module_files') => [],
            smart_daily_productivity_label('smart_daily_productivity_module_options') => [],
        ];

        foreach ($tables as $table) {
            $result[smart_daily_productivity_label('smart_daily_productivity_database_checker')][$table] = $this->db->table_exists($table);
        }
        foreach ($files as $file) {
            $result[smart_daily_productivity_label('smart_daily_productivity_module_files')][$file] = is_file($modulePath . $file);
        }
        foreach ($options as $option) {
            $result[smart_daily_productivity_label('smart_daily_productivity_module_options')][ucwords(str_replace('_', ' ', $option))] = get_option($option) !== false;
        }

        return $result;
    }

    public function get_logs(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'smart_daily_productivity_logs')) {
            return [];
        }
        return $this->db->select('l.*, s.firstname, s.lastname')
            ->from(db_prefix() . 'smart_daily_productivity_logs l')
            ->join(db_prefix() . 'staff s', 's.staffid = l.staff_id', 'left')
            ->order_by('l.id', 'DESC')
            ->limit(100)
            ->get()->result_array();
    }

    private function rating_label(float $score): string
    {
        $high = (float) get_option('smart_daily_productivity_score_high');
        $low = (float) get_option('smart_daily_productivity_score_low');
        if ($score >= $high) {
            return 'High Performance';
        }
        if ($score < $low) {
            return 'Low Performance';
        }
        return 'Good Performance';
    }

    private function category_from_relation(string $relType): string
    {
        $relType = strtolower($relType);
        if ($relType === 'project') {
            return 'Project Task';
        }
        if ($relType === 'contract') {
            return 'Contract Task';
        }
        if ($relType === 'customer' || $relType === 'client') {
            return 'Client Task';
        }
        if ($relType === 'lead') {
            return 'Research Task';
        }
        return 'Other Process';
    }

    private function clean_date(string $date): string
    {
        $time = strtotime($date);
        return $time ? date('Y-m-d', $time) : date('Y-m-d');
    }

    private function safe_text(string $value, int $length): string
    {
        $value = trim($value);
        return mb_substr($value, 0, $length);
    }

    private function safe_nullable_text($value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }
        return mb_substr($text, 0, $length);
    }

    private function write_log(string $action, string $message, int $staffId): void
    {
        if (function_exists('smart_daily_productivity_write_log')) {
            smart_daily_productivity_write_log($action, $message, $staffId);
        }
    }
}
