<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authenticated customer portal meetings controller.
 * Uses Perfex ClientsController so every result renders inside the normal
 * customer portal header, navigation, content area, and footer.
 */
class Meeting_clients extends ClientsController
{
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
            $hasLink = $this->gmm->is_real_meet_link($meeting['meet_link'] ?? '');

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
        $this->view('client/list');
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
        $this->view('client/view');
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

        if (!$meeting || !$this->gmm->is_real_meet_link($meetLink)) {
            set_alert('warning', google_meet_lang('google_meet_link_not_available', 'The Google Meet link has not been added yet.'));
            redirect(site_url('google_meet/meeting_clients/view/' . $id));
            exit;
        }

        redirect($meetLink);
    }
}
