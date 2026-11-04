<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('googlecalendar');
    }

    private function allowedCalendarOwner()
    {
        if ((get_option('appointly_google_client_id') || get_option('appointly_google_calendar_id'))
            && ((int) get_option('appointly_google_calendar_staff_id') < 1
                || (int) get_option('appointly_google_calendar_staff_id') !== (int) get_staff_user_id())) {
            show_error('Only the configured calendar owner may connect this Google Calendar integration.', 403);
            return false;
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function login()
    {
        if (!$this->allowedCalendarOwner()) return;
        $state = bin2hex(random_bytes(32));
        $this->session->set_userdata('appointly_google_oauth', [
            'state' => $state,
            'staff_id' => (int) get_staff_user_id(),
            'expires' => time() + 600,
        ]);
        $this->googleplus->client->setState($state);
        $data = array('loginUrl' => $this->googlecalendar->loginUrl());

        if (!appointlyGoogleAuth()) {
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
        if (!$this->allowedCalendarOwner()) return;
        $transaction = $this->session->userdata('appointly_google_oauth');
        $this->session->unset_userdata('appointly_google_oauth');
        $state = $this->input->get('state', false);
        $code = $this->input->get('code', false);
        if (!is_array($transaction) || !is_string($state) || !is_string($code) || $code === ''
            || !isset($transaction['state'], $transaction['staff_id'], $transaction['expires'])
            || !is_string($transaction['state']) || !hash_equals($transaction['state'], $state)
            || (int) $transaction['staff_id'] !== (int) get_staff_user_id()
            || (int) $transaction['expires'] < time()) {
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
        $this->googleplus->revokeToken();
        redirect('admin/appointly/appointments');
    }
}
