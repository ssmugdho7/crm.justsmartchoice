<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authenticated customer portal meetings controller.
 * Uses Perfex ClientsController so every result renders inside the normal
 * customer portal header, navigation, content area, and footer.
 */
class Meeting_clients extends ClientsController
{
    protected $gmm;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('google_meet/google_meet_model');
        $this->gmm = $this->google_meet_model;
    }

    private function check_customer_access()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
            exit;
        }

        // Never return a 404 for an authenticated customer meeting page.
        // If the portal option is disabled, send the customer home instead.
        if (get_option('google_meet_client_portal_enabled') !== '1') {
            set_alert('warning', google_meet_lang('google_meet_portal_disabled', 'Google Meet is not currently available in your portal.'));
            redirect(site_url());
            exit;
        }
    }

    private function build_summary(array $meetings)
    {
        $summary = [
            'total' => count($meetings),
            'upcoming' => 0,
            'available_now' => 0,
            'completed' => 0,
        ];

        $now = time();
        foreach ($meetings as $meeting) {
            $status = strtolower(trim((string)($meeting['status'] ?? 'scheduled')));
            $start  = !empty($meeting['start_time']) ? strtotime($meeting['start_time']) : false;
            $end    = !empty($meeting['end_time']) ? strtotime($meeting['end_time']) : false;
            $hasLink = $this->gmm->is_real_meet_link($meeting['meet_link'] ?? '', (object)$meeting);

            if (in_array($status, ['completed', 'cancelled', 'canceled'], true) || ($end && $end < $now && $status !== 'live')) {
                $summary['completed']++;
                continue;
            }

            if ($status === 'live' || ($hasLink && $start && $start <= $now && (!$end || $end >= $now))) {
                $summary['available_now']++;
                continue;
            }

            if (!$start || $start >= $now) {
                $summary['upcoming']++;
            }
        }

        return $summary;
    }

    public function index()
    {
        return $this->meetings();
    }

    public function meetings()
    {
        $this->check_customer_access();

        $clientId = (int) get_client_user_id();
        $meetings = $this->gmm->get_client_meetings($clientId);

        $data = [
            'meetings' => $meetings,
            'summary'  => $this->build_summary((array)$meetings),
            'title'    => google_meet_lang('google_meet_my_meetings', 'Meetings'),
        ];

        $this->data($data);
        if (method_exists($this, 'title')) {
            $this->title($data['title']);
        }
        parent::view('client/list');
        $this->layout();
    }

    public function view($id = 0)
    {
        $this->check_customer_access();

        $id       = (int) $id;
        $clientId = (int) get_client_user_id();

        if ($id <= 0 || !$this->gmm->client_can_access_meeting($id, $clientId)) {
            set_alert('warning', google_meet_lang('google_meet_access_denied', 'This meeting is not assigned to your account.'));
            redirect(site_url('google_meet/meeting_clients/meetings'));
            exit;
        }

        $meeting = $this->gmm->get($id);
        if (!$meeting) {
            set_alert('warning', google_meet_lang('google_meet_not_found', 'The meeting could not be found.'));
            redirect(site_url('google_meet/meeting_clients/meetings'));
            exit;
        }

        $title = !empty($meeting->subject)
            ? $meeting->subject
            : (!empty($meeting->title) ? $meeting->title : 'Google Meet Meeting');

        $this->data(['meeting' => $meeting, 'title' => $title]);
        if (method_exists($this, 'title')) { $this->title($title); }
        parent::view('client/view');
        $this->layout();
    }

    public function join($id = 0)
    {
        $this->check_customer_access();

        $id       = (int) $id;
        $clientId = (int) get_client_user_id();

        if ($id <= 0 || !$this->gmm->client_can_access_meeting($id, $clientId)) {
            set_alert('warning', google_meet_lang('google_meet_access_denied', 'This meeting is not assigned to your account.'));
            redirect(site_url('google_meet/meeting_clients/meetings'));
            exit;
        }

        $meeting  = $this->gmm->get($id);
        $meetLink = $meeting && isset($meeting->meet_link) ? trim((string)$meeting->meet_link) : '';

        if (!$meeting || !$this->gmm->is_real_meet_link($meetLink, $meeting)) {
            set_alert('warning', google_meet_lang('google_meet_link_not_available', 'The Google Meet link has not been added yet.'));
            redirect(site_url('google_meet/meeting_clients/view/' . $id));
            exit;
        }

        if (jitsi_meeting_room($meeting) && get_option('jitsi_embedded_mode') !== '0') { redirect(site_url('google_meet/meeting_clients/room/' . $id)); return; }
        redirect($meetLink);
    }
    private function authorized_room_meeting($id)
    {
        $this->check_customer_access();
        if ((int)$id < 1 || !$this->gmm->client_can_access_meeting((int)$id, (int)get_client_user_id())) {
            show_error('This meeting is not assigned to your account.', 403);
        }
        $meeting = $this->gmm->get((int)$id);
        if (!$meeting) { show_404(); }
        return $meeting;
    }

    public function room($id = 0)
    {
        $meeting = $this->authorized_room_meeting($id);
        $room = jitsi_meeting_room($meeting);
        if (!$room || get_option('jitsi_embedded_mode') === '0') { return $this->join($id); }
        $contact = $this->db->where('id', get_contact_user_id())->get(db_prefix() . 'contacts')->row();
        $title = $meeting->subject ?: $meeting->title;
        $this->data(['title' => $title, 'meeting' => $meeting, 'room' => $room,
            'current_user' => ['name' => trim(($contact->firstname ?? '') . ' ' . ($contact->lastname ?? '')), 'email' => $contact->email ?? '', 'is_host' => false]]);
        if (method_exists($this, 'title')) { $this->title($title); }
        parent::view('client/room');
        $this->layout();
    }

    public function ajax_lifecycle()
    {
        if ($this->input->method() !== 'post') { show_error('POST required.', 405); }
        $id = (int)$this->input->post('meeting_id');
        $this->authorized_room_meeting($id);
        $event = $this->input->post('event', true);
        $saved = $event === 'left' ? true : ($event === 'joined' && $this->gmm->record_participant_join($id, 'guest'));
        $this->output->set_status_header($saved ? 200 : 409)->set_content_type('application/json')->set_output(json_encode([
            'success' => (bool)$saved, 'csrf' => ['token_name' => $this->security->get_csrf_token_name(), 'hash' => $this->security->get_csrf_hash()]]));
    }

    public function calendar($id = 0)
    {
        $meeting = $this->authorized_room_meeting($id);
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="smart-choice-meeting-' . (int)$meeting->id . '.ics"');
        echo jitsi_calendar_content($meeting);
    }

}
