<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authenticated customer portal controller.
 *
 * Uses the native ClientsController rendering pipeline so the module page is
 * displayed inside the Perfex customer portal layout without loading admin
 * header/footer helpers.
 */
class Client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('google_meet/google_meet_model');
        hooks()->do_action('after_clients_area_init', $this);
    }

    private function require_customer_login()
    {
        if (!is_client_logged_in() || (int) get_contact_user_id() <= 0) {
            redirect(site_url('authentication/login'));
            exit;
        }
    }

    private function schema_ready()
    {
        $meetingsTable  = db_prefix() . 'google_meet_meetings';
        $attendeesTable = db_prefix() . 'google_meet_attendees';

        return $this->db->table_exists($meetingsTable)
            && $this->db->table_exists($attendeesTable)
            && $this->db->field_exists('meeting_id', $attendeesTable)
            && $this->db->field_exists('contact_id', $attendeesTable);
    }

    private function contact_can_access_meeting($meetingId)
    {
        if (!$this->schema_ready()) {
            return false;
        }

        return $this->db
            ->where('meeting_id', (int) $meetingId)
            ->where('contact_id', (int) get_contact_user_id())
            ->count_all_results(db_prefix() . 'google_meet_attendees') > 0;
    }

    private function render_customer_view($view, array $data)
    {
        $this->data($data);
        $this->view('client/' . $view);
        $this->layout();
    }

    public function index()
    {
        $this->require_customer_login();

        $meetings = [];
        if ($this->schema_ready()) {
            $contactId = (int) get_contact_user_id();
            $contactEmail = '';

            if ($this->db->table_exists(db_prefix() . 'contacts')) {
                $contact = $this->db->select('email')->where('id', $contactId)->get(db_prefix() . 'contacts')->row();
                $contactEmail = $contact && !empty($contact->email) ? trim((string) $contact->email) : '';
            }

            $previousDebug = isset($this->db->db_debug) ? $this->db->db_debug : true;
            $this->db->db_debug = false;
            $this->db->select('m.*');
            $this->db->from(db_prefix() . 'google_meet_meetings m');
            $this->db->join(db_prefix() . 'google_meet_attendees a', 'a.meeting_id = m.id', 'inner');
            $this->db->group_start();
            $this->db->where('a.contact_id', $contactId);
            if ($contactEmail !== '' && $this->db->field_exists('email', db_prefix() . 'google_meet_attendees')) {
                $this->db->or_where('LOWER(a.email)', strtolower($contactEmail));
            }
            $this->db->group_end();
            $this->db->group_by('m.id');
            $this->db->order_by('m.start_time', 'ASC');
            $query = $this->db->get();
            $this->db->db_debug = $previousDebug;

            if ($query !== false) {
                $meetings = $query->result_array();
            } else {
                log_message('error', 'Google Meet customer list query failed: ' . json_encode($this->db->error()));
            }
        }

        $this->render_customer_view('list', [
            'meetings' => $meetings,
            'title'    => google_meet_lang('google_meet_my_meetings', 'Meetings'),
        ]);
    }

    public function view($id)
    {
        $this->require_customer_login();
        $id = (int) $id;

        if ($id <= 0 || !$this->contact_can_access_meeting($id)) {
            set_alert('warning', google_meet_lang('google_meet_access_denied', 'This meeting is not assigned to your account.'));
            redirect(site_url('google_meet/client'));
            exit;
        }

        $meeting = $this->google_meet_model->get($id);
        if (!$meeting) {
            set_alert('warning', google_meet_lang('google_meet_not_found', 'The meeting could not be found.'));
            redirect(site_url('google_meet/client'));
            exit;
        }

        $this->render_customer_view('view', [
            'meeting' => $meeting,
            'title'   => !empty($meeting->subject) ? $meeting->subject : (!empty($meeting->title) ? $meeting->title : 'Google Meet'),
        ]);
    }

    public function join($id)
    {
        $this->require_customer_login();
        $id = (int) $id;

        if ($id <= 0 || !$this->contact_can_access_meeting($id)) {
            set_alert('warning', google_meet_lang('google_meet_access_denied', 'This meeting is not assigned to your account.'));
            redirect(site_url('google_meet/client'));
            exit;
        }

        $meeting = $this->google_meet_model->get($id);
        $meetLink = $meeting && isset($meeting->meet_link) ? trim((string) $meeting->meet_link) : '';

        if (!$meeting || !$this->google_meet_model->is_real_meet_link($meetLink)) {
            set_alert('warning', google_meet_lang('google_meet_link_not_available', 'The Google Meet link has not been added yet.'));
            redirect(site_url('google_meet/client/view/' . $id));
            exit;
        }

        redirect($meetLink);
    }
}
