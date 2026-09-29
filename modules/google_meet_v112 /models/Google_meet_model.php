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
            'access_mode' => (string)($data['access_mode'] ?? get_option('google_meet_access_mode') ?: 'trusted'),
            'quick_access' => !empty($data['quick_access']) ? 1 : ((string)get_option('google_meet_quick_access') === '1' ? 1 : 0),
            'waiting_room' => !empty($data['waiting_room']) ? 1 : ((string)get_option('google_meet_waiting_room') === '1' ? 1 : 0),
            'allow_chat' => !empty($data['allow_chat']) ? 1 : ((string)get_option('google_meet_allow_chat') === '1' ? 1 : 0),
            'allow_screen_sharing' => !empty($data['allow_screen_sharing']) ? 1 : ((string)get_option('google_meet_allow_screen_sharing') === '1' ? 1 : 0),
            'allow_recording' => !empty($data['allow_recording']) ? 1 : ((string)get_option('google_meet_allow_recording') === '1' ? 1 : 0),
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
                $meeting['meet_link'] = '';
                $meeting['google_api_status'] = 'link_required_' . ($apiResult['error'] ?? 'unknown');
            }
        }

        if (empty($meeting['meet_link'])) {
            $meeting['meet_link'] = '';
            $meeting['google_api_status'] = 'link_required';
            $meeting['status'] = 'link_required';
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
            'access_mode' => (string)($data['access_mode'] ?? $existing->access_mode ?? get_option('google_meet_access_mode') ?: 'trusted'),
            'quick_access' => !empty($data['quick_access']) ? 1 : 0,
            'waiting_room' => !empty($data['waiting_room']) ? 1 : 0,
            'allow_chat' => !empty($data['allow_chat']) ? 1 : 0,
            'allow_screen_sharing' => !empty($data['allow_screen_sharing']) ? 1 : 0,
            'allow_recording' => !empty($data['allow_recording']) ? 1 : 0,
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
                $meeting['meet_link'] = '';
                $meeting['google_api_status'] = 'link_required_' . ($apiResult['error'] ?? 'unknown');
            }
        }

        if (empty($meeting['meet_link'])) {
            $meeting['meet_link'] = '';
            $meeting['google_api_status'] = 'link_required';
            $meeting['status'] = 'link_required';
        }

        $this->db->where('id', (int)$id)->update(db_prefix() . 'google_meet_meetings', $meeting);
        $this->sync_attendees((int)$id, $data);
        $this->add_log((int)$id, 'updated', 'Meeting updated');
        return true;
    }


    public function update_meet_link($id, $link)
    {
        $link = $this->normalize_meet_link($link);
        if (!$this->is_real_meet_link($link)) {
            return false;
        }
        $this->db->where('id', (int)$id)->update(db_prefix() . 'google_meet_meetings', [
            'meet_link' => $link,
            'google_api_status' => 'shared_link_saved',
            'status' => 'scheduled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->add_log((int)$id, 'link_saved', 'Shared Google Meet link saved for all attendees.');
        return true;
    }

    public function is_real_meet_link($link)
    {
        $link = trim((string)$link);
        if ($link === '' || rtrim($link, '/') === 'https://meet.google.com/new') {
            return false;
        }
        return (bool) preg_match('~^https://meet\\.google\\.com/[a-z]{3}-[a-z]{4}-[a-z]{3}(?:[/?#].*)?$~i', $link);
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
        if (!$this->is_real_meet_link($meeting->meet_link ?? '')) {
            $this->add_log((int)$id, 'notification_skipped', 'Invitations were not sent because a shared Google Meet URL has not been saved.');
            return false;
        }

        $subject = $meeting->subject ?? $meeting->title ?? 'Google Meet Meeting';
        $attendees = $this->attendees((int)$id);
        $results = ['email' => 0, 'sms' => 0, 'crm' => 0, 'failed' => 0];

        foreach ($attendees as $a) {
            $name = trim((string)($a['name'] ?? '')) ?: 'Team Member';
            $email = trim((string)($a['email'] ?? ''));
            $phone = $this->resolve_attendee_phone($a);
            $staffId = !empty($a['staff_id']) ? (int)$a['staff_id'] : 0;
            $messageText = $this->build_invitation_plain_text($meeting, $name);
            $language = 'english';
            if (!empty($a['contact_id']) && $this->db->table_exists(db_prefix() . 'contacts') && $this->db->field_exists('default_language', db_prefix() . 'contacts')) {
                $language = (string)$this->db->where('id', (int)$a['contact_id'])->get(db_prefix() . 'contacts')->row('default_language') ?: 'english';
            } elseif (!empty($a['staff_id']) && $this->db->field_exists('default_language', db_prefix() . 'staff')) {
                $language = (string)$this->db->where('staffid', (int)$a['staff_id'])->get(db_prefix() . 'staff')->row('default_language') ?: 'english';
            }
            list($templateSubject, $messageHtml) = $this->invitation_template($meeting, $a, $language);

            if (get_option('google_meet_email_enabled') === '1' && $email !== '') {
                if ($this->send_crm_email($email, $templateSubject, $messageHtml, $messageText)) {
                    $results['email']++;
                    $this->record_notification((int)$id, $a, 'email', $email, 'sent', $messageText);
                } else {
                    $results['failed']++;
                    $this->record_notification((int)$id, $a, 'email', $email, 'failed', $messageText);
                }
            }

            if (get_option('google_meet_push_enabled') === '1' && $staffId > 0) {
                if ($this->create_crm_notification((int)$id, $staffId, $subject, $meeting->meet_link ?? '')) {
                    $results['crm']++;
                    $this->record_notification((int)$id, $a, 'crm', (string)$staffId, 'sent', $messageText);
                }
            }

            if ((get_option('google_meet_twilio_enabled') === '1' || get_option('google_meet_sms_enabled') === '1') && $phone !== '') {
                if ($this->send_crm_sms($phone, $messageText, (int)$id, $a)) {
                    $results['sms']++;
                    $this->record_notification((int)$id, $a, 'sms', $phone, 'sent', $messageText);
                } else {
                    $this->record_notification((int)$id, $a, 'sms', $phone, 'failed', $messageText);
                }
            }

            if (!empty($a['id'])) {
                $this->db->where('id', (int)$a['id'])->update(db_prefix() . 'google_meet_attendees', ['notified' => 1]);
            }
        }

        if ($this->db->field_exists('notification_status', db_prefix() . 'google_meet_meetings')) {
            $update = ['notification_status' => json_encode($results)];
            if ($this->db->field_exists('last_notified_at', db_prefix() . 'google_meet_meetings')) {
                $update['last_notified_at'] = date('Y-m-d H:i:s');
            }
            $this->db->where('id', (int)$id)->update(db_prefix() . 'google_meet_meetings', $update);
        }

        $this->add_log((int)$id, 'notified', 'Meeting notifications sent. Email: ' . $results['email'] . ', CRM: ' . $results['crm'] . ', SMS: ' . $results['sms'] . ', Failed: ' . $results['failed']);
        return true;
    }

    public function send_test_notification($payload)
    {
        $payload = is_array($payload) ? $payload : [];
        $staffId = !empty($payload['staff_id']) ? (int)$payload['staff_id'] : 0;
        $email = trim((string)($payload['email'] ?? ''));
        $phone = trim((string)($payload['phone'] ?? ''));
        $message = trim((string)($payload['message'] ?? '')) ?: 'This is a Smart Choice Google Meet notification test.';
        $link = trim((string)($payload['link'] ?? '')) ?: 'https://meet.google.com/new';
        $result = ['email' => 0, 'sms' => 0, 'crm' => 0, 'failed' => 0];

        if ($email !== '') {
            $html = '<div style="font-family:Arial,sans-serif;background:#f8fafc;padding:20px;color:#111827"><div style="max-width:620px;margin:auto;background:#fff;border:1px solid #d1d5db;border-radius:12px;overflow:hidden"><div style="background:#169179;color:#fff;padding:16px 20px"><h2 style="margin:0;color:#fff">Smart Choice Google Meet Test</h2></div><div style="padding:20px"><p>' . html_escape($message) . '</p><p><a href="' . html_escape($link) . '" style="display:inline-block;background:#169179;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:bold">Open Test Meet Link</a></p><p style="font-size:12px;color:#4b5563">' . html_escape($link) . '</p></div></div></div>';
            if ($this->send_crm_email($email, 'Smart Choice Google Meet Test Notification', $html, $message . "\n" . $link)) {
                $result['email']++;
            } else {
                $result['failed']++;
            }
        }

        if ($staffId > 0) {
            if ($this->create_crm_notification(0, $staffId, 'Google Meet Test Notification', $link)) {
                $result['crm']++;
            } else {
                $result['failed']++;
            }
        }

        if ($phone !== '') {
            if ($this->send_crm_sms($phone, $message . "\n" . $link, 0, ['attendee_type' => 'test', 'staff_id' => $staffId])) {
                $result['sms']++;
            } else {
                $result['failed']++;
            }
        }

        return $result;
    }



    private function invitation_template($meeting, $attendee, $language = 'english')
    {
        $language = $language === 'spanish' ? 'spanish' : 'english';
        $slug = 'google-meet-invitation';
        $table = db_prefix() . 'emailtemplates';
        $subject = 'Google Meet Invitation - ' . ($meeting->subject ?? $meeting->title ?? 'Meeting');
        $message = $this->build_invitation_html($meeting, $attendee['name'] ?? 'Team Member');
        if ($this->db->table_exists($table)) {
            $this->db->where('slug', $slug);
            if ($this->db->field_exists('language', $table)) {
                $this->db->where('language', $language);
            }
            $row = $this->db->get($table)->row();
            if ($row) {
                $subject = $row->subject ?: $subject;
                $message = $row->message ?: $message;
            }
        }
        $replacements = [
            '{meeting_name}' => $meeting->subject ?? $meeting->title ?? 'Google Meet Meeting',
            '{meeting_start}' => !empty($meeting->start_time) ? _dt($meeting->start_time) : '',
            '{meeting_link}' => $meeting->meet_link ?? '',
            '{recipient_name}' => $attendee['name'] ?? 'Team Member',
            '{email_signature}' => get_option('email_signature'),
        ];
        return [strtr($subject, $replacements), strtr($message, $replacements)];
    }

    private function build_invitation_plain_text($meeting, $name)
    {
        $subject = $meeting->subject ?? $meeting->title ?? 'Google Meet Meeting';
        $start = !empty($meeting->start_time) ? _dt($meeting->start_time) : '';
        return trim((get_option('google_meet_default_notification_message') ?: 'You have been invited to a Smart Choice Contractors USA Google Meet meeting.') . "\n\n" .
            'Hello ' . $name . ",\n" .
            'Meeting: ' . $subject . "\n" .
            'Start: ' . $start . "\n" .
            'Join: ' . ($meeting->meet_link ?? '') . "\n");
    }

    private function build_invitation_html($meeting, $name)
    {
        $subject = html_escape($meeting->subject ?? $meeting->title ?? 'Google Meet Meeting');
        $start = !empty($meeting->start_time) ? _dt($meeting->start_time) : '';
        $link = html_escape($meeting->meet_link ?? '');
        return '<div style="font-family:Arial,sans-serif;color:#111827;background:#f8fafc;padding:20px">'
            . '<div style="max-width:640px;margin:auto;background:#fff;border:1px solid #d1d5db;border-radius:12px;overflow:hidden">'
            . '<div style="background:#169179;color:#fff;padding:18px 22px"><h2 style="margin:0;color:#fff">Smart Choice Contractors USA</h2><p style="margin:4px 0 0;color:#eef7f4">Google Meet Invitation</p></div>'
            . '<div style="padding:22px"><p>Hello ' . html_escape($name) . ',</p><p>You have been invited to a video meeting.</p>'
            . '<p><strong>Meeting:</strong> ' . $subject . '<br><strong>Start:</strong> ' . html_escape($start) . '</p>'
            . '<p><a href="' . $link . '" style="display:inline-block;background:#169179;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:bold">Join Google Meet</a></p>'
            . '<p style="font-size:12px;color:#4b5563">If the button does not work, copy this link: ' . $link . '</p></div></div></div>';
    }

    private function send_crm_email($to, $subject, $html, $plain)
    {
        $CI =& get_instance();
        if (!isset($CI->email)) {
            $CI->load->library('email');
        }
        $fromEmail = get_option('smtp_email') ?: get_option('companyemail') ?: get_option('email_from_address');
        $fromName = get_option('companyname') ?: 'Smart Choice Contractors USA';
        if (method_exists($CI->email, 'clear')) { $CI->email->clear(true); }
        $CI->email->from($fromEmail, $fromName);
        $CI->email->to($to);
        $CI->email->subject($subject);
        $CI->email->message($html);
        if (method_exists($CI->email, 'set_alt_message')) { $CI->email->set_alt_message($plain); }
        return (bool)$CI->email->send(false);
    }

    private function resolve_attendee_phone($attendee)
    {
        if (!empty($attendee['phone'])) { return trim((string)$attendee['phone']); }
        if (!empty($attendee['staff_id']) && $this->db->table_exists(db_prefix() . 'staff')) {
            $staff = $this->db->select('phonenumber')->where('staffid', (int)$attendee['staff_id'])->get(db_prefix() . 'staff')->row();
            if (!empty($staff->phonenumber)) { return trim((string)$staff->phonenumber); }
        }
        if (!empty($attendee['contact_id']) && $this->db->table_exists(db_prefix() . 'contacts')) {
            $contact = $this->db->select('phonenumber')->where('id', (int)$attendee['contact_id'])->get(db_prefix() . 'contacts')->row();
            if (!empty($contact->phonenumber)) { return trim((string)$contact->phonenumber); }
        }
        return '';
    }

    private function send_crm_sms($phone, $message, $meetingId, $attendee)
    {
        $sent = false;
        if (function_exists('app_sms')) {
            try { $sent = (bool)app_sms()->send($phone, $message); } catch (Throwable $e) { $sent = false; }
        }
        hooks()->do_action('google_meet_send_sms_notification', [
            'phone' => $phone,
            'message' => $message,
            'meeting_id' => $meetingId,
            'attendee' => $attendee,
            'sent_by_builtin' => $sent,
        ]);
        return $sent;
    }

    private function create_crm_notification($meetingId, $staffId, $subject, $link)
    {
        if (!$this->db->table_exists(db_prefix() . 'notifications')) { return false; }
        $description = 'Google Meet invitation: ' . $subject;
        $data = [
            'isread' => 0,
            'isread_inline' => 0,
            'date' => date('Y-m-d H:i:s'),
            'description' => $description,
            'fromuserid' => is_staff_logged_in() ? get_staff_user_id() : 0,
            'fromclientid' => 0,
            'from_fullname' => get_option('companyname') ?: 'Smart Choice Contractors USA',
            'touserid' => $staffId,
            'fromcompany' => 1,
            'link' => $meetingId > 0 ? 'google_meet/view/' . $meetingId : $link,
            'additional_data' => serialize([$link]),
        ];
        $this->db->insert(db_prefix() . 'notifications', $data);
        return $this->db->insert_id() > 0;
    }

    private function record_notification($meetingId, $attendee, $channel, $destination, $status, $message)
    {
        if (!$this->db->table_exists(db_prefix() . 'google_meet_notifications')) { return; }
        $this->db->insert(db_prefix() . 'google_meet_notifications', [
            'meeting_id' => $meetingId,
            'recipient_type' => $attendee['attendee_type'] ?? 'staff',
            'recipient_id' => !empty($attendee['staff_id']) ? (int)$attendee['staff_id'] : (!empty($attendee['contact_id']) ? (int)$attendee['contact_id'] : null),
            'channel' => $channel,
            'destination' => $destination,
            'status' => $status,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
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
        @curl_close($ch);

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
        return '';
    }

    public function delete_many($ids)
    {
        if (!$this->db->table_exists(db_prefix() . 'google_meet_meetings')) { return 0; }
        $clean = [];
        foreach ((array)$ids as $id) {
            $id = (int)$id;
            if ($id > 0) { $clean[] = $id; }
        }
        if (!$clean) { return 0; }
        if ($this->db->table_exists(db_prefix() . 'google_meet_attendees')) {
            $this->db->where_in('meeting_id', $clean)->delete(db_prefix() . 'google_meet_attendees');
        }
        if ($this->db->table_exists(db_prefix() . 'google_meet_comments')) {
            $this->db->where_in('meeting_id', $clean)->delete(db_prefix() . 'google_meet_comments');
        }
        if ($this->db->table_exists(db_prefix() . 'google_meet_logs')) {
            $this->db->where_in('meeting_id', $clean)->delete(db_prefix() . 'google_meet_logs');
        }
        $this->db->where_in('id', $clean)->delete(db_prefix() . 'google_meet_meetings');
        return $this->db->affected_rows();
    }

    public function health_checks()
    {
        return [
            ['name' => 'Meetings Table', 'status' => $this->db->table_exists(db_prefix() . 'google_meet_meetings'), 'detail' => 'Stores meeting topic, link, timing, status, recording URL, and AI summary fields.'],
            ['name' => 'Attendees Table', 'status' => $this->db->table_exists(db_prefix() . 'google_meet_attendees'), 'detail' => 'Stores assigned staff/customer recipients.'],
            ['name' => 'Comments Table', 'status' => $this->db->table_exists(db_prefix() . 'google_meet_comments'), 'detail' => 'Stores meeting notes and comments.'],
            ['name' => 'Notifications Table', 'status' => $this->db->table_exists(db_prefix() . 'google_meet_notifications'), 'detail' => 'Tracks email, CRM notification, and SMS delivery attempts.'],
            ['name' => 'CRM Email Enabled', 'status' => get_option('google_meet_email_enabled') === '1', 'detail' => 'Uses the CRM configured email library.'],
            ['name' => 'CRM Push Enabled', 'status' => get_option('google_meet_push_enabled') === '1', 'detail' => 'Creates staff screen notifications inside Perfex CRM.'],
            ['name' => 'Twilio SMS Bridge Enabled', 'status' => get_option('google_meet_twilio_enabled') === '1' || get_option('google_meet_sms_enabled') === '1', 'detail' => 'Uses existing CRM SMS infrastructure when available and fires a hook for Twilio modules.'],
            ['name' => 'Client Portal Enabled', 'status' => get_option('google_meet_client_portal_enabled') === '1', 'detail' => 'Customer portal route: ' . site_url('google-meet-client')],
            ['name' => 'Google Calendar API Option', 'status' => get_option('google_meet_use_google_calendar_api') === '1', 'detail' => 'Automatic Meet creation requires Google Calendar API and a valid OAuth token.'],
            ['name' => 'Google Access Token', 'status' => trim((string)get_option('google_meet_google_access_token')) !== '', 'detail' => 'Required only for automatic Meet creation. Manual/fallback links still work without this.'],
            ['name' => 'Recording Preference', 'status' => get_option('google_meet_allow_recording') === '1', 'detail' => 'Actual recording availability is controlled by Google Workspace permissions.'],
            ['name' => 'AI Notes Preference', 'status' => get_option('google_meet_ai_notes_enabled') === '1', 'detail' => 'Stored preference for SAMI/AI meeting summary workflow. SAMI AI is not modified by this module.'],
        ];
    }

}
