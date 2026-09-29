<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_meet_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = null)
    {
        $table = db_prefix() . 'google_meet_meetings';
        if (!$this->db->table_exists($table)) {
            return $id !== null ? null : [];
        }

        if ($id !== null) {
            $this->db->where('id', (int)$id);
            return $this->db->get($table)->row();
        }

        $this->db->order_by('start_time', 'DESC');
        return $this->db->get($table)->result_array();
    }

    public function create($data)
    {
        $now = date('Y-m-d H:i:s');
        $subject = trim((string)($data['subject'] ?? $data['title'] ?? ''));
        if ($subject === '') {
            $subject = 'Google Meet Meeting - ' . date('m/d/Y g:i A');
        }

        $duration = max(15, (int)($data['duration_minutes'] ?? get_option('google_meet_default_duration') ?: 30));
        $startTime = !empty($data['start_time']) ? date('Y-m-d H:i:s', strtotime($data['start_time'])) : $now;
        $endTime = !empty($data['end_time']) ? date('Y-m-d H:i:s', strtotime($data['end_time'])) : date('Y-m-d H:i:s', strtotime($startTime . ' +' . $duration . ' minutes'));

        $meeting = [
            'title' => $subject,
            'subject' => $subject,
            'description' => (string)($data['description'] ?? ''),
            'meeting_type' => (string)($data['meeting_type'] ?? 'scheduled'),
            'status' => (($data['meeting_type'] ?? 'scheduled') === 'instant') ? 'live' : 'scheduled',
            'meet_link' => $this->normalize_meet_link($data['meet_link'] ?? ''),
            'project_id' => !empty($data['project_id']) ? (int)$data['project_id'] : null,
            'appointment_id' => !empty($data['appointment_id']) ? (int)$data['appointment_id'] : null,
            'created_by' => is_staff_logged_in() ? get_staff_user_id() : null,
            'assigned_staff_id' => !empty($data['assigned_staff_id']) ? (int)$data['assigned_staff_id'] : (is_staff_logged_in() ? get_staff_user_id() : null),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $duration,
            'notify_staff' => (!empty($data['notify_staff']) || !empty($data['Notify Staff'])) ? 1 : 0,
            'notify_customer' => !empty($data['notify_customer']) ? 1 : 0,
            'notify_customers' => (!empty($data['notify_customers']) || !empty($data['notify_customer'])) ? 1 : 0,
            'send_invitations_now' => (!empty($data['send_invitations_now']) || !empty($data['send_notifications'])) ? 1 : 0,
            'notes' => (string)($data['notes'] ?? ''),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $apiResult = ['success' => false, 'meet_link' => '', 'event_id' => '', 'error' => 'manual'];
        if (empty($meeting['meet_link']) && (string)get_option('google_meet_auto_create_link') === '1') {
            $apiResult = $this->create_google_calendar_event($meeting, $data);
            if (!empty($apiResult['success'])) {
                $meeting['meet_link'] = $apiResult['meet_link'];
                $meeting['google_event_id'] = $apiResult['event_id'];
                $meeting['google_api_status'] = 'created';
            } else {
                $meeting['meet_link'] = $this->generate_placeholder_meet_link();
                $meeting['google_api_status'] = 'fallback_' . ($apiResult['error'] ?? 'unknown');
            }
        }

        if (empty($meeting['meet_link'])) {
            $meeting['meet_link'] = $this->generate_placeholder_meet_link();
            $meeting['google_api_status'] = 'manual_fallback';
        }

        $this->db->insert(db_prefix() . 'google_meet_meetings', $meeting);
        $id = (int)$this->db->insert_id();

        if ($id > 0) {
            $this->sync_attendees($id, $data);
            $this->add_log($id, 'created', 'Meeting created. API status: ' . ($meeting['google_api_status'] ?? 'manual'));
            if (!empty($meeting['send_invitations_now'])) {
                $this->notify_attendees($id);
            }
        }

        return $id;
    }

    public function update($id, $data)
    {
        $existing = $this->get((int)$id);
        if (!$existing) {
            return false;
        }

        $subject = trim((string)($data['subject'] ?? $data['title'] ?? $existing->subject ?? $existing->title ?? 'Google Meet Meeting'));
        $duration = max(15, (int)($data['duration_minutes'] ?? $existing->duration_minutes ?? get_option('google_meet_default_duration') ?: 30));
        $startTime = !empty($data['start_time']) ? date('Y-m-d H:i:s', strtotime($data['start_time'])) : ($existing->start_time ?? date('Y-m-d H:i:s'));
        $endTime = !empty($data['end_time']) ? date('Y-m-d H:i:s', strtotime($data['end_time'])) : date('Y-m-d H:i:s', strtotime($startTime . ' +' . $duration . ' minutes'));

        $meeting = [
            'title' => $subject,
            'subject' => $subject,
            'description' => (string)($data['description'] ?? ''),
            'meeting_type' => (string)($data['meeting_type'] ?? 'scheduled'),
            'meet_link' => $this->normalize_meet_link($data['meet_link'] ?? $existing->meet_link ?? ''),
            'project_id' => !empty($data['project_id']) ? (int)$data['project_id'] : null,
            'appointment_id' => !empty($data['appointment_id']) ? (int)$data['appointment_id'] : null,
            'assigned_staff_id' => !empty($data['assigned_staff_id']) ? (int)$data['assigned_staff_id'] : ($existing->assigned_staff_id ?? null),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $duration,
            'notify_staff' => (!empty($data['notify_staff']) || !empty($data['Notify Staff'])) ? 1 : 0,
            'notify_customer' => !empty($data['notify_customer']) ? 1 : 0,
            'notify_customers' => (!empty($data['notify_customers']) || !empty($data['notify_customer'])) ? 1 : 0,
            'send_invitations_now' => (!empty($data['send_invitations_now']) || !empty($data['send_notifications'])) ? 1 : 0,
            'notes' => (string)($data['notes'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (empty($meeting['meet_link']) && (string)get_option('google_meet_auto_create_link') === '1') {
            $apiResult = $this->create_google_calendar_event($meeting, $data);
            if (!empty($apiResult['success'])) {
                $meeting['meet_link'] = $apiResult['meet_link'];
                $meeting['google_event_id'] = $apiResult['event_id'];
                $meeting['google_api_status'] = 'created';
            } else {
                $meeting['meet_link'] = $this->generate_placeholder_meet_link();
                $meeting['google_api_status'] = 'fallback_' . ($apiResult['error'] ?? 'unknown');
            }
        }

        if (empty($meeting['meet_link'])) {
            $meeting['meet_link'] = $this->generate_placeholder_meet_link();
            $meeting['google_api_status'] = 'manual_fallback';
        }

        $this->db->where('id', (int)$id)->update(db_prefix() . 'google_meet_meetings', $meeting);
        $this->sync_attendees((int)$id, $data);
        $this->add_log((int)$id, 'updated', 'Meeting updated');
        return true;
    }

    public function report_meetings($filters = [])
    {
        $table = db_prefix() . 'google_meet_meetings gm';
        $this->db->select('gm.*, CONCAT(st.firstname, " ", st.lastname) as created_by_name, CONCAT(ast.firstname, " ", ast.lastname) as assigned_staff_name');
        $this->db->from($table);
        $this->db->join(db_prefix() . 'staff st', 'st.staffid = gm.created_by', 'left');
        $this->db->join(db_prefix() . 'staff ast', 'ast.staffid = gm.assigned_staff_id', 'left');

        if (!empty($filters['staff_id'])) {
            $staffId = (int)$filters['staff_id'];
            $this->db->group_start();
            $this->db->where('gm.created_by', $staffId);
            $this->db->or_where('gm.assigned_staff_id', $staffId);
            $this->db->or_where('gm.id IN (SELECT meeting_id FROM ' . db_prefix() . 'google_meet_attendees WHERE staff_id=' . $staffId . ')', null, false);
            $this->db->group_end();
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('gm.start_time >=', date('Y-m-d 00:00:00', strtotime($filters['date_from'])));
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('gm.start_time <=', date('Y-m-d 23:59:59', strtotime($filters['date_to'])));
        }
        if (!empty($filters['q'])) {
            $q = trim((string)$filters['q']);
            $this->db->group_start();
            $this->db->like('gm.subject', $q);
            $this->db->or_like('gm.title', $q);
            $this->db->or_like('gm.description', $q);
            $this->db->or_like('gm.notes', $q);
            $this->db->group_end();
        }
        if (!empty($filters['status'])) {
            $this->db->where('gm.status', $filters['status']);
        }

        $this->db->order_by('gm.start_time', 'DESC');
        return $this->db->get()->result_array();
    }

    public function active_staff()
    {
        if (!$this->db->table_exists(db_prefix() . 'staff')) { return []; }
        if ($this->db->field_exists('active', db_prefix() . 'staff')) { $this->db->where('active', 1); }
        return $this->db->order_by('firstname', 'ASC')->get(db_prefix() . 'staff')->result_array();
    }

    public function active_contacts()
    {
        if (!$this->db->table_exists(db_prefix() . 'contacts')) { return []; }
        if ($this->db->field_exists('active', db_prefix() . 'contacts')) { $this->db->where('active', 1); }
        return $this->db->order_by('firstname', 'ASC')->get(db_prefix() . 'contacts')->result_array();
    }

    public function projects_for_select()
    {
        if (!$this->db->table_exists(db_prefix() . 'projects')) { return []; }
        $orderColumn = $this->db->field_exists('name', db_prefix() . 'projects') ? 'name' : 'id';
        return $this->db->order_by($orderColumn, 'ASC')->get(db_prefix() . 'projects')->result_array();
    }

    public function sync_attendees($meeting_id, $data)
    {
        if (!$this->db->table_exists(db_prefix() . 'google_meet_attendees')) { return; }
        $this->db->where('meeting_id', (int)$meeting_id)->delete(db_prefix() . 'google_meet_attendees');
        $now = date('Y-m-d H:i:s');
        $staff_ids = $data['staff_ids'] ?? [];
        if (!is_array($staff_ids)) { $staff_ids = []; }
        foreach ($staff_ids as $staff_id) {
            $staff = $this->db->where('staffid', (int)$staff_id)->get(db_prefix() . 'staff')->row();
            $this->db->insert(db_prefix() . 'google_meet_attendees', [
                'meeting_id' => (int)$meeting_id,
                'attendee_type' => 'staff',
                'staff_id' => (int)$staff_id,
                'email' => $staff->email ?? '',
                'name' => trim(($staff->firstname ?? '') . ' ' . ($staff->lastname ?? '')),
                'created_at' => $now,
            ]);
        }
        $contact_ids = $data['contact_ids'] ?? [];
        if (!is_array($contact_ids)) { $contact_ids = []; }
        foreach ($contact_ids as $contact_id) {
            $contact = $this->db->where('id', (int)$contact_id)->get(db_prefix() . 'contacts')->row();
            $this->db->insert(db_prefix() . 'google_meet_attendees', [
                'meeting_id' => (int)$meeting_id,
                'attendee_type' => 'customer',
                'contact_id' => (int)$contact_id,
                'email' => $contact->email ?? '',
                'name' => trim(($contact->firstname ?? '') . ' ' . ($contact->lastname ?? '')),
                'created_at' => $now,
            ]);
        }
    }

    public function attendees($meeting_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'google_meet_attendees')) { return []; }
        return $this->db->where('meeting_id', (int)$meeting_id)->get(db_prefix() . 'google_meet_attendees')->result_array();
    }

    public function comments($meeting_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'google_meet_comments')) { return []; }
        $this->db->select('c.*, CONCAT(s.firstname, " ", s.lastname) as staff_name');
        $this->db->from(db_prefix() . 'google_meet_comments c');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = c.created_by', 'left');
        $this->db->where('c.meeting_id', (int)$meeting_id);
        $this->db->order_by('c.id', 'DESC');
        return $this->db->get()->result_array();
    }

    public function add_comment($meeting_id, $comment)
    {
        $comment = trim((string)$comment);
        if ($comment === '' || !$this->db->table_exists(db_prefix() . 'google_meet_comments')) { return false; }
        $this->db->insert(db_prefix() . 'google_meet_comments', [
            'meeting_id' => (int)$meeting_id,
            'comment' => $comment,
            'created_by' => is_staff_logged_in() ? get_staff_user_id() : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->add_log((int)$meeting_id, 'comment', 'Meeting comment added');
        return true;
    }

    public function start($id)
    {
        $this->db->where('id', (int)$id)->update(db_prefix() . 'google_meet_meetings', ['status' => 'live', 'actual_start' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $this->add_log((int)$id, 'started', 'Meeting started');
    }

    public function finish($id)
    {
        $meeting = $this->get((int)$id);
        $end = date('Y-m-d H:i:s');
        $minutes = 0;
        if (!empty($meeting->actual_start)) {
            $minutes = max(0, (int)round((strtotime($end) - strtotime($meeting->actual_start)) / 60));
        }
        $this->db->where('id', (int)$id)->update(db_prefix() . 'google_meet_meetings', ['status' => 'completed', 'actual_end' => $end, 'duration_minutes' => $minutes, 'updated_at' => $end]);
        $this->add_log((int)$id, 'completed', 'Meeting completed after ' . $minutes . ' minutes');
    }

    public function notify_attendees($id)
    {
        $meeting = $this->get((int)$id);
        if (!$meeting) { return false; }
        $subject = $meeting->subject ?? $meeting->title ?? 'Google Meet Meeting';
        $attendees = $this->attendees((int)$id);
        foreach ($attendees as $a) {
            if (!empty($a['email'])) {
                $message = 'Meeting: ' . $subject . "\n" . 'Start: ' . $meeting->start_time . "\n" . 'Google Meet: ' . $meeting->meet_link;
                @mail($a['email'], 'Google Meet Invitation - ' . $subject, $message);
                $this->db->where('id', (int)$a['id'])->update(db_prefix() . 'google_meet_attendees', ['notified' => 1]);
            }
        }
        $this->add_log((int)$id, 'notified', 'Meeting notifications sent');
        return true;
    }

    public function add_log($meeting_id, $action, $message)
    {
        if (!$this->db->table_exists(db_prefix() . 'google_meet_logs')) { return; }
        $this->db->insert(db_prefix() . 'google_meet_logs', [
            'meeting_id' => $meeting_id ?: null,
            'action' => $action,
            'message' => $message,
            'created_by' => is_staff_logged_in() ? get_staff_user_id() : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function report_summary()
    {
        $table = db_prefix() . 'google_meet_meetings';
        $total = (int)$this->db->count_all($table);
        $completed = (int)$this->db->where('status', 'completed')->count_all_results($table);
        $minutes = $this->db->select_sum('duration_minutes')->get($table)->row()->duration_minutes ?? 0;
        return ['total' => $total, 'completed' => $completed, 'minutes' => (int)$minutes];
    }

    private function create_google_calendar_event($meeting, $data)
    {
        if ((string)get_option('google_meet_use_google_calendar_api') !== '1') {
            return ['success' => false, 'meet_link' => '', 'event_id' => '', 'error' => 'api_disabled'];
        }

        $accessToken = trim((string)get_option('google_meet_google_access_token'));
        if ($accessToken === '' || !function_exists('curl_init')) {
            return ['success' => false, 'meet_link' => '', 'event_id' => '', 'error' => 'missing_token_or_curl'];
        }

        $calendarId = trim((string)get_option('google_meet_calendar_id')) ?: 'primary';
        $timezone = trim((string)get_option('google_meet_timezone')) ?: 'America/New_York';
        $attendees = [];

        foreach ((array)($data['staff_ids'] ?? []) as $staff_id) {
            $staff = $this->db->where('staffid', (int)$staff_id)->get(db_prefix() . 'staff')->row();
            if (!empty($staff->email)) { $attendees[] = ['email' => $staff->email]; }
        }

        foreach ((array)($data['contact_ids'] ?? []) as $contact_id) {
            $contact = $this->db->where('id', (int)$contact_id)->get(db_prefix() . 'contacts')->row();
            if (!empty($contact->email)) { $attendees[] = ['email' => $contact->email]; }
        }

        $requestId = 'perfex-' . time() . '-' . mt_rand(1000, 9999);
        $payload = [
            'summary' => $meeting['subject'] ?? $meeting['title'] ?? 'Google Meet Meeting',
            'description' => $meeting['description'] ?? '',
            'start' => ['dateTime' => date('c', strtotime($meeting['start_time'])), 'timeZone' => $timezone],
            'end' => ['dateTime' => date('c', strtotime($meeting['end_time'])), 'timeZone' => $timezone],
            'attendees' => $attendees,
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => $requestId,
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
        ];

        $url = 'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($calendarId) . '/events?conferenceDataVersion=1&sendUpdates=all';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        if (PHP_VERSION_ID < 80000) { @curl_close($ch); }

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            log_activity('Google Meet API failed: HTTP ' . $httpCode . ' ' . $curlError . ' ' . substr((string)$response, 0, 500));
            return ['success' => false, 'meet_link' => '', 'event_id' => '', 'error' => 'api_failed'];
        }

        $json = json_decode($response, true);
        $meetLink = $json['hangoutLink'] ?? '';
        if ($meetLink === '' && isset($json['conferenceData']['entryPoints'])) {
            foreach ($json['conferenceData']['entryPoints'] as $entry) {
                if (($entry['entryPointType'] ?? '') === 'video' && !empty($entry['uri'])) {
                    $meetLink = $entry['uri'];
                    break;
                }
            }
        }

        if ($meetLink === '') {
            return ['success' => false, 'meet_link' => '', 'event_id' => $json['id'] ?? '', 'error' => 'missing_meet_link'];
        }

        return ['success' => true, 'meet_link' => $meetLink, 'event_id' => $json['id'] ?? '', 'error' => ''];
    }

    private function normalize_meet_link($link)
    {
        $link = trim((string)$link);
        if ($link === '') { return ''; }
        if (!preg_match('#^https?://#i', $link)) { $link = 'https://' . $link; }
        return $link;
    }

    private function generate_placeholder_meet_link()
    {
        return 'https://meet.google.com/new';
    }
}
