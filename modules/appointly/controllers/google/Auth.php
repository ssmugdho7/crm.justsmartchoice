<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('googlecalendar');
    }

    private function allowedStaff()
    {
        if ((int) get_staff_user_id() < 1) {
            show_error('A staff account is required to connect Google Calendar.', 403);
            return false;
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function login()
    {
        if (!$this->allowedStaff()) return;
        $calendarId = $this->googlecalendar->calendarId();
        if (!preg_match('/^[a-z0-9_-]+@group\.calendar\.google\.com$/iD', $calendarId)) {
            set_alert('info', 'Choose your own private calendar before connecting Google.');
            redirect(site_url('appointly/google/auth/settings'));
            return;
        }
        $state = bin2hex(random_bytes(32));
        $this->session->set_userdata('appointly_google_oauth', [
            'state' => $state,
            'staff_id' => (int) get_staff_user_id(),
            'expires' => time() + 600,
            'calendar_id' => $calendarId,
        ]);
        $this->googleplus->client->setState($state);
        $data = array('loginUrl' => $this->googlecalendar->loginUrl());

        // Connecting a personal grant must never test another user's team grant.
        if (!$this->googlecalendar->getAccountDetails()) {
            redirect($data['loginUrl']);
        } else {
            redirect('admin/appointly/appointments');
        }
    }

    /**
     * @throws Exception
     */
    public function oauth()
    {
        if (!$this->allowedStaff()) return;
        $transaction = $this->session->userdata('appointly_google_oauth');
        $this->session->unset_userdata('appointly_google_oauth');
        $state = $this->input->get('state', false);
        $code = $this->input->get('code', false);
        if (!is_array($transaction) || !is_string($state) || !is_string($code) || $code === ''
            || !isset($transaction['state'], $transaction['staff_id'], $transaction['expires'], $transaction['calendar_id'])
            || !is_string($transaction['state']) || !hash_equals($transaction['state'], $state)
            || (int) $transaction['staff_id'] !== (int) get_staff_user_id()
            || (int) $transaction['expires'] < time()
            || !is_string($transaction['calendar_id']) || $transaction['calendar_id'] === ''
            || $transaction['calendar_id'] !== $this->googlecalendar->calendarId()) {
            show_error('Invalid or expired Google authorization. Please connect your calendar again.', 403);
            return;
        }

        if (!$this->googlecalendar->login($code)) {
            set_alert('danger', 'Google Calendar authorization failed. Please reconnect your calendar.');
        }
        redirect(admin_url() . APPOINTLY_MODULE_NAME . '/appointments');
    }

    public function logout()
    {
        if (!$this->allowedStaff()) return;
        if ($this->input->method() !== 'post') {
            show_error('Use the Disconnect button on your Calendar settings page.', 405);
            return;
        }
        if ($this->googlecalendar->sharedMode()
            && (int) get_option('appointly_google_team_owner_id') === (int) get_staff_user_id()) {
            set_alert('danger', 'Disable team calendar sync before disconnecting its owner.');
            redirect(site_url('appointly/google/auth/settings'));
            return;
        }
        $this->session->unset_userdata('appointly_google_oauth');
        try {
            if (!$this->googleplus->revokeToken()) throw new RuntimeException();
            set_alert('success', 'Your Google Calendar connection was disconnected. Existing events were retained.');
        } catch (Throwable $e) {
            set_alert('danger', 'Google could not disconnect this account. Please try again.');
        }
        redirect(site_url('appointly/google/auth/settings'));
    }

    public function settings()
    {
        if (!$this->allowedStaff()) return;
        if ($this->input->method() === 'post') {
            try {
                if ($this->input->post('action') === 'team') {
                    $this->googlecalendar->saveTeamSettings($this->input->post('team_enabled') === '1', $this->input->post('team_owner_id'));
                } else {
                    $this->googlecalendar->saveCalendarId($this->input->post('calendar_id', false));
                }
                $this->session->unset_userdata('appointly_google_oauth');
                set_alert('success', 'Your Calendar settings were saved.');
            } catch (InvalidArgumentException | RuntimeException $e) {
                set_alert('danger', $e->getMessage());
            }
            redirect(site_url('appointly/google/auth/settings'));
            return;
        }
        $this->load->view('appointly/google_calendar_settings', [
            'title' => 'My Google Calendar',
            'calendar_id' => $this->googlecalendar->calendarId(),
            'connected' => (bool) $this->googlecalendar->getAccountDetails(),
            'team_enabled' => $this->googlecalendar->sharedMode(),
            'team_owner_id' => (int) get_option('appointly_google_team_owner_id'),
            'team_ready' => $this->googlecalendar->sharedMode() && (bool) $this->googlecalendar->getAccountDetails(true),
        ]);
    }
}
