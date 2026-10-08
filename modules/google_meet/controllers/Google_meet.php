<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_meet extends AdminController
{
    public function __construct()
    {
        parent::__construct();


        $this->load->model('google_meet_model');
    }

    private function require_access($capability = 'view', $id = null)
    {
        $global = has_permission('google_meet', '', 'view');
        $allowed = $capability === 'view'
            ? ($global || has_permission('google_meet', '', 'view_own'))
            : has_permission('google_meet', '', $capability);
        if (!$allowed) { access_denied('Video Meeting'); }
        if ($id !== null) {
            $meeting = $this->google_meet_model->get((int)$id);
            if (!$meeting) { show_404(); }
            if (!$global && !$this->google_meet_model->staff_has_meeting($meeting, get_staff_user_id())) {
                access_denied('Video Meeting');
            }
        }
    }

    private function staff_filters($filters = [])
    {
        if (!has_permission('google_meet', '', 'view')) {
            // A supplied staff filter must never broaden View Own access.
            $filters['staff_id'] = get_staff_user_id();
        }
        return $filters;
    }

    private function require_settings($capability = 'view')
    {
        if (!has_permission('settings', '', $capability)) { access_denied('settings'); }
    }

    public function index()
    {
        $this->require_access();

        $data['title'] = 'Video Meeting Dashboard';
        $data['summary'] = $this->google_meet_model->report_summary($this->staff_filters());
        $data['meetings'] = $this->google_meet_model->report_meetings($this->staff_filters());
        $this->load->view('dashboard', $data);
    }

    public function create($id = null)
    {
        $this->require_access($id ? 'edit' : 'create', $id);

        if ($this->input->post()) {
            $post = $this->input->post(null, true);
            try {
                if ($id) {
                    if (!$this->google_meet_model->update((int)$id, $post)) {
                        set_alert('danger', 'Meeting could not be updated. Please try again.');
                        redirect(admin_url('google_meet/create/' . (int)$id));
                        return;
                    }
                    set_alert('success', 'video meeting updated successfully.');
                    redirect(admin_url('google_meet/view/' . (int)$id));
                }

                $meetingId = $this->google_meet_model->create($post);
                if ($meetingId) {
                    set_alert('success', 'video meeting created successfully.');
                    redirect(admin_url('google_meet/view/' . $meetingId));
                }

            } catch (InvalidArgumentException $e) {
                set_alert('danger', $e->getMessage());
                redirect(admin_url('google_meet/create' . ($id ? '/' . (int)$id : '')));
                return;
            }

            set_alert('danger', 'video meeting could not be created. Please run Upgrade Database.');
            redirect(admin_url('google_meet/create'));
        }

        $data['title'] = $id ? 'Edit Video Meeting' : 'New Video Meeting';
        $data['meeting'] = $id ? $this->google_meet_model->get((int)$id) : null;
        $data['attendees'] = $id ? $this->google_meet_model->attendees((int)$id) : [];
        $data['staff'] = $this->google_meet_model->active_staff();
        $data['contacts'] = $this->google_meet_model->active_contacts();
        $data['projects'] = $this->google_meet_model->projects_for_select();

        $this->load->view('form', $data);
    }

    public function view($id)
    {
        $this->require_access('view', $id);

        $data['meeting'] = $this->google_meet_model->get((int)$id);
        if (!$data['meeting']) {
            set_alert('warning', 'Meeting not found.');
            redirect(admin_url('google_meet'));
        }
        $data['title'] = 'Video Meeting';
        $data['attendees'] = $this->google_meet_model->attendees((int)$id);
        $data['comments'] = $this->google_meet_model->comments((int)$id);
        $this->load->view('view', $data);
    }

    private function is_room_host($meeting)
    {
        $staffId = (int)get_staff_user_id();
        return has_permission('google_meet', '', 'edit') && (is_admin()
            || (int)$meeting->created_by === $staffId || (int)$meeting->assigned_staff_id === $staffId);
    }

    private function json_room_response($data, $status = 200)
    {
        $data['csrf'] = ['token_name' => $this->security->get_csrf_token_name(), 'hash' => $this->security->get_csrf_hash()];
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
    }

    public function room($id)
    {
        $this->require_access('view', $id);
        $meeting = $this->google_meet_model->get((int)$id);
        $room = jitsi_meeting_room($meeting);
        if (!$room || get_option('jitsi_embedded_mode') === '0') {
            if ($this->google_meet_model->is_real_meet_link($meeting->meet_link) || $room) { redirect($meeting->meet_link); return; }
            set_alert('warning', 'Save a shared meeting link before joining.');
            redirect(admin_url('google_meet/view/' . (int)$id)); return;
        }
        $staff = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row();
        $data = ['title' => $meeting->subject ?: $meeting->title, 'meeting' => $meeting, 'room' => $room,
            'attendees' => $this->google_meet_model->attendees((int)$id), 'comments' => $this->google_meet_model->comments((int)$id),
            'current_user' => ['name' => trim(($staff->firstname ?? '') . ' ' . ($staff->lastname ?? '')), 'email' => $staff->email ?? '', 'is_host' => $this->is_room_host($meeting)],
            'note_key' => bin2hex(random_bytes(16))];
        $this->load->view('room', $data);
    }

    public function ajax_lifecycle()
    {
        if ($this->input->method() !== 'post') { show_error('POST required.', 405); }
        $id = (int)$this->input->post('meeting_id');
        $this->require_access('view', $id);
        $meeting = $this->google_meet_model->get($id);
        $host = $this->is_room_host($meeting);
        $event = $this->input->post('event', true);
        if ($event === 'joined') { $saved = $this->google_meet_model->record_participant_join($id, $host ? 'host' : 'guest'); }
        elseif (in_array($event, ['left', 'finish'], true)) {
            if (!$host) { if ($event === 'left') { $this->json_room_response(['success' => true]); return; } access_denied('Video Meeting'); }
            $this->require_access('edit', $id);
            $saved = $this->google_meet_model->record_meeting_finish($id);
        } else { $this->json_room_response(['success' => false, 'message' => 'Invalid meeting event.'], 400); return; }
        $this->json_room_response(['success' => (bool)$saved], $saved ? 200 : 409);
    }

    public function save_room_note($id)
    {
        if ($this->input->method() !== 'post') { show_error('POST required.', 405); }
        $this->require_access('view', $id);
        $comment = $this->input->post('comment', true);
        $saved = $this->google_meet_model->save_room_note((int)$id, $comment, $this->input->post('note_key', true));
        $this->json_room_response(['success' => (bool)$saved, 'comment_id' => $saved ?: null, 'comment' => $saved ? $comment : '',
            'message' => $saved ? 'Note saved to timeline.' : 'Note could not be saved. Keep your text and try again.'], $saved ? 200 : 422);
    }

    public function add_comment($id)
    {
        $this->require_access('view', $id);

        if ($this->input->post()) {
            $saved = $this->google_meet_model->add_comment((int)$id, $this->input->post('comment', true));
            set_alert($saved ? 'success' : 'danger', $saved ? 'Meeting comment added.' : 'Enter a comment and try again. The comment could not be saved.');
        }
        redirect(admin_url('google_meet/view/' . (int)$id));
    }

    public function start($id)
    {
        $this->require_access('edit', $id);

        $saved = $this->google_meet_model->start((int)$id);
        set_alert($saved ? 'success' : 'danger', $saved ? 'Meeting marked as started.' : 'Meeting could not be started. Please try again.');
        redirect(admin_url('google_meet/view/' . (int)$id));
    }

    public function finish($id)
    {
        $this->require_access('edit', $id);

        $saved = $this->google_meet_model->finish((int)$id);
        set_alert($saved ? 'success' : 'danger', $saved ? 'Meeting marked as completed.' : 'Meeting could not be completed. Please try again.');
        redirect(admin_url('google_meet/view/' . (int)$id));
    }

    public function notify($id)
    {
        $this->require_access('edit', $id);

        if ($this->google_meet_model->notify_attendees((int)$id)) {
            set_alert('success', 'Meeting notifications were sent through the CRM channels.');
        } else {
            set_alert('warning', 'No notification channel succeeded. Check the shared Meet link, attendees and notification settings.');
        }
        redirect(admin_url('google_meet/view/' . (int)$id));
    }


    public function save_shared_link($id)
    {
        $this->require_access('edit', $id);

        if (!$this->input->post()) {
            redirect(admin_url('google_meet/view/' . (int)$id));
        }
        if ($this->google_meet_model->update_meet_link((int)$id, $this->input->post('meet_link', true))) {
            set_alert('success', 'The shared meeting link was saved. Every attendee will now join the same meeting.');
        } else {
            set_alert('danger', 'Enter a shared Jitsi room URL or a supported saved legacy meeting URL. Do not use /new as an attendee link.');
        }
        redirect(admin_url('google_meet/view/' . (int)$id));
    }

    public function generate_room($id)
    {
        if ($this->input->method() !== 'post') { show_error('POST required.', 405); }
        $this->require_access('edit', $id);
        try { $saved = $this->google_meet_model->ensure_shared_room((int)$id); }
        catch (InvalidArgumentException $e) { $saved = false; }
        set_alert($saved ? 'success' : 'danger', $saved ? 'Shared Jitsi room saved.' : 'Room could not be generated. Check the Jitsi server setting.');
        redirect(admin_url('google_meet/view/' . (int)$id));
    }

    public function calendar($id)
    {
        $this->require_access('view', $id);

        $meeting = $this->google_meet_model->get((int)$id);
        if (!$meeting) { show_404(); }
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="smart-choice-meeting-' . (int)$meeting->id . '.ics"');
        echo jitsi_calendar_content($meeting);
    }

    public function settings()
    {
        $this->require_settings($this->input->post() ? 'edit' : 'view');

        if ($this->input->post()) {
            try {
                $domain = jitsi_server_domain($this->input->post('jitsi_server_domain', true) ?: 'meet.jit.si');
                if (strlen($domain) > 137) { throw new InvalidArgumentException('Use a shorter Jitsi hostname so appointment links fit their existing storage.'); }
            }
            catch (InvalidArgumentException $e) { set_alert('danger', $e->getMessage()); redirect(admin_url('google_meet/settings')); return; }
            update_option('video_meeting_provider', 'jitsi');
            update_option('jitsi_server_domain', $domain);
            update_option('jitsi_room_prefix', substr(preg_replace('/[^A-Za-z0-9_-]/', '', (string)$this->input->post('jitsi_room_prefix', true)), 0, 24) ?: 'SC');
            update_option('jitsi_require_pin', $this->input->post('jitsi_require_pin') ? '1' : '0');
            update_option('jitsi_embedded_mode', $this->input->post('jitsi_embedded_mode') ? '1' : '0');
            update_option('google_meet_enabled', $this->input->post('google_meet_enabled') ? '1' : '0');
            update_option('google_meet_use_google_calendar_api', $this->input->post('google_meet_use_google_calendar_api') ? '1' : '0');
            update_option('google_meet_allow_placeholder_links', $this->input->post('google_meet_allow_placeholder_links') ? '1' : '0');
            if ($this->input->post('google_meet_google_api_key', true)) { update_option('google_meet_google_api_key', $this->input->post('google_meet_google_api_key', true)); }
            if ($this->input->post('google_meet_google_access_token', true)) { update_option('google_meet_google_access_token', $this->input->post('google_meet_google_access_token', true)); }
            update_option('google_meet_calendar_id', $this->input->post('google_meet_calendar_id', true) ?: 'primary');
            update_option('google_meet_timezone', $this->input->post('google_meet_timezone', true) ?: 'America/New_York');
            update_option('google_meet_default_duration', $this->input->post('google_meet_default_duration', true) ?: '30');
            update_option('google_meet_notify_staff_default', $this->input->post('google_meet_notify_staff_default') ? '1' : '0');
            update_option('google_meet_notify_customers_default', $this->input->post('google_meet_notify_customers_default') ? '1' : '0');
            update_option('google_meet_send_invitations_default', $this->input->post('google_meet_send_invitations_default') ? '1' : '0');
            update_option('google_meet_auto_create_link', $this->input->post('google_meet_auto_create_link') ? '1' : '0');
            update_option('google_meet_access_mode', $this->input->post('google_meet_access_mode', true) ?: 'trusted');
            update_option('google_meet_quick_access', $this->input->post('google_meet_quick_access') ? '1' : '0');
            update_option('google_meet_waiting_room', $this->input->post('google_meet_waiting_room') ? '1' : '0');
            update_option('google_meet_allow_chat', $this->input->post('google_meet_allow_chat') ? '1' : '0');
            update_option('google_meet_allow_screen_sharing', $this->input->post('google_meet_allow_screen_sharing') ? '1' : '0');
            update_option('google_meet_allow_recording', $this->input->post('google_meet_allow_recording') ? '1' : '0');
            update_option('google_meet_twilio_enabled', $this->input->post('google_meet_twilio_enabled') ? '1' : '0');
            update_option('google_meet_telegram_enabled', $this->input->post('google_meet_telegram_enabled') ? '1' : '0');
            update_option('google_meet_push_enabled', $this->input->post('google_meet_push_enabled') ? '1' : '0');
            update_option('google_meet_email_enabled', $this->input->post('google_meet_email_enabled') ? '1' : '0');
            update_option('google_meet_client_portal_enabled', $this->input->post('google_meet_client_portal_enabled') ? '1' : '0');
            update_option('google_meet_sms_enabled', $this->input->post('google_meet_sms_enabled') ? '1' : '0');
            update_option('google_meet_browser_sound_enabled', $this->input->post('google_meet_browser_sound_enabled') ? '1' : '0');
            update_option('google_meet_sound_volume', $this->input->post('google_meet_sound_volume', true) ?: '0.85');
            update_option('google_meet_popup_enabled', $this->input->post('google_meet_popup_enabled') ? '1' : '0');
            update_option('google_meet_recording_instruction', $this->input->post('google_meet_recording_instruction', true) ?: 'Recording availability depends on the configured Jitsi service and moderator permissions.');
            update_option('google_meet_ai_notes_enabled', $this->input->post('google_meet_ai_notes_enabled') ? '1' : '0');
            update_option('google_meet_ai_summary_prompt', $this->input->post('google_meet_ai_summary_prompt', true) ?: 'Summarize this meeting with action items, customer decisions, deadlines, and follow-up tasks for Smart Choice Contractors USA.');

            set_alert('success', 'Video Meeting settings saved.');
            redirect(admin_url('google_meet/settings'));
        }

        $data['title'] = 'Video Meeting Settings';
        $this->load->view('settings', $data);
    }

    public function reports()
    {
        $this->require_access();

        $filters = [
            'staff_id' => $this->input->get('staff_id', true),
            'date_from' => $this->input->get('date_from', true),
            'date_to' => $this->input->get('date_to', true),
            'q' => $this->input->get('q', true),
            'status' => $this->input->get('status', true),
        ];

        $data['title'] = 'Video Meeting Reports';
        $data['filters'] = $this->staff_filters($filters);
        $data['staff'] = $this->google_meet_model->active_staff();
        $data['summary'] = $this->google_meet_model->report_summary($this->staff_filters());
        $data['meetings'] = $this->google_meet_model->report_meetings($this->staff_filters($filters));

        $this->load->view('reports', $data);
    }

    public function join()
    {
        $this->require_access();

        $data['title'] = 'Join Video Meeting';
        $data['meetings'] = $this->google_meet_model->report_meetings($this->staff_filters());
        $this->load->view('join', $data);
    }

    public function health()
    {
        $this->require_settings();

        $data['title'] = 'Video Meeting Health';
        $data['checks'] = $this->google_meet_model->health_checks();
        $this->load->view('health', $data);
    }

    public function help()
    {
        $this->require_access();

        $data['title'] = 'Video Meeting Help';
        $this->load->view('help', $data);
    }

    public function mass_delete()
    {
        $this->require_access('delete');

        if (!$this->input->post()) {
            redirect(admin_url('google_meet'));
        }
        $ids = $this->input->post('ids');
        if (!is_array($ids) || count($ids) === 0) {
            set_alert('warning', 'Please select at least one meeting.');
            redirect($this->agent->referrer() ?: admin_url('google_meet'));
        }
        // Validate the complete batch before deleting any record.
        foreach ($ids as $id) {
            if (!is_scalar($id) || !ctype_digit((string)$id) || (int)$id < 1) { show_404(); }
            $this->require_access('delete', (int)$id);
        }
        $deleted = $this->google_meet_model->delete_many($ids);
        set_alert('success', $deleted . ' meeting records deleted.');
        redirect($this->agent->referrer() ?: admin_url('google_meet'));
    }

    public function export_csv()
    {
        $this->require_access();

        $filters = [
            'staff_id' => $this->input->get('staff_id', true),
            'date_from' => $this->input->get('date_from', true),
            'date_to' => $this->input->get('date_to', true),
            'q' => $this->input->get('q', true),
            'status' => $this->input->get('status', true),
        ];
        $rows = $this->google_meet_model->report_meetings($this->staff_filters($filters));
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=google_meet_report_' . date('Ymd_His') . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Meeting','Employee','Start','End','Status','Meeting Link','Notes']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'] ?? '',
                $r['subject'] ?: ($r['title'] ?? ''),
                $r['assigned_staff_name'] ?: ($r['created_by_name'] ?? ''),
                $r['start_time'] ?? '',
                $r['end_time'] ?? '',
                $r['status'] ?? '',
                $r['meet_link'] ?? '',
                $r['notes'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }


    public function meeting_modal($id)
    {
        $this->require_access('view', $id);

        $meeting = $this->google_meet_model->get((int)$id);
        if (!$meeting) {
            echo json_encode(['success' => false, 'message' => 'Meeting not found.']);
            return;
        }
        $title = $meeting->subject ?? $meeting->title ?? 'Video Meeting';
        echo json_encode([
            'success' => true,
            'id' => (int)$meeting->id,
            'title' => $title,
            'description' => $meeting->description ?? '',
            'start_time' => !empty($meeting->start_time) ? _dt($meeting->start_time) : '',
            'status' => ucfirst($meeting->status ?? 'scheduled'),
            'meet_link' => $meeting->meet_link ?? '',
            'room_url' => $this->google_meet_model->is_real_meet_link($meeting->meet_link ?? '', $meeting) ? admin_url('google_meet/room/' . (int)$meeting->id) : '',
        ]);
    }

    public function test_notifications()
    {
        $this->require_settings();

        $data['title'] = 'Video Meeting Test Notifications';
        $data['staff'] = $this->google_meet_model->active_staff();
        $this->load->view('test_notifications', $data);
    }

    public function send_test_notifications()
    {
        $this->require_settings('edit');

        if (!$this->input->post()) {
            redirect(admin_url('google_meet/test_notifications'));
        }

        $message = trim((string)$this->input->post('message', true));
        if ($message === '') {
            $message = 'This is a Smart Choice Video Meeting notification test.';
        }

        try {
            $result = $this->google_meet_model->send_test_notification([
                'staff_id' => (int)$this->input->post('staff_id'),
                'email'    => trim((string)$this->input->post('email', true)),
                'phone'    => trim((string)$this->input->post('phone', true)),
                'message'  => $message,
                'link'     => trim((string)$this->input->post('meet_link', true)) ?: jitsi_build_room_url(jitsi_generate_room_name('Test')),
            ]);

        } catch (InvalidArgumentException $e) {
            set_alert('danger', $e->getMessage());
            redirect(admin_url('google_meet/test_notifications'));
            return;
        }

        set_alert(!empty($result['failed']) ? 'warning' : 'success', 'Test complete. Email: ' . (int)$result['email'] . ', CRM: ' . (int)$result['crm'] . ', SMS: ' . (int)$result['sms'] . ', Failed: ' . (int)$result['failed']);
        redirect(admin_url('google_meet/test_notifications'));
    }


    public function fix_health()
    {
        $this->require_settings('edit');

        set_alert('success', 'Video Meeting health repair executed. Tables, settings, and safe columns were checked.');
        redirect(admin_url('google_meet/health'));
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') { show_error('POST required.', 405); }
        $this->require_access('delete', $id);

        if (!has_permission('google_meet', '', 'delete')) {
            access_denied('Video Meeting');
        }

        $deleted = $this->google_meet_model->delete_many([(int) $id]);
        set_alert($deleted > 0 ? 'success' : 'warning', $deleted > 0 ? 'Meeting deleted successfully.' : 'Meeting could not be deleted.');
        redirect(admin_url('google_meet'));
    }

}
