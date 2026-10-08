<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authenticated Video Meeting customer portal controller.
 *
 * This controller is routed explicitly through the google_meet module and
 * renders with Perfex's native ClientsController header/footer pipeline.
 */
class Google_meet_client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('google_meet/google_meet_model');
    }

    /**
     * Return the active customer contact ID without issuing a second login
     * redirect. Perfex already owns customer authentication and return URLs.
     */
    private function current_contact_id()
    {
        if (!function_exists('is_client_logged_in') || !is_client_logged_in()) {
            return 0;
        }

        return (int) get_contact_user_id();
    }

    private function current_contact_email($contactId)
    {
        if ($contactId <= 0 || !$this->db->table_exists(db_prefix() . 'contacts')) {
            return '';
        }

        $contact = $this->db
            ->select('email')
            ->where('id', (int) $contactId)
            ->get(db_prefix() . 'contacts')
            ->row();

        return $contact && isset($contact->email) ? trim((string) $contact->email) : '';
    }

    private function schema_ready()
    {
        $meetingsTable  = db_prefix() . 'google_meet_meetings';
        $attendeesTable = db_prefix() . 'google_meet_attendees';

        return $this->db->table_exists($meetingsTable)
            && $this->db->table_exists($attendeesTable)
            && $this->db->field_exists('id', $meetingsTable)
            && $this->db->field_exists('start_time', $meetingsTable)
            && $this->db->field_exists('meeting_id', $attendeesTable)
            && $this->db->field_exists('contact_id', $attendeesTable);
    }

    private function get_customer_meetings($contactId)
    {
        if (!$this->schema_ready()) {
            return [];
        }

        $previousDebug = isset($this->db->db_debug) ? $this->db->db_debug : true;
        $this->db->db_debug = false;

        $query = $this->db
            ->select('m.*')
            ->from(db_prefix() . 'google_meet_meetings AS m')
            ->join(db_prefix() . 'google_meet_attendees AS a', 'a.meeting_id = m.id', 'inner')
            ->group_start()
            ->where('a.contact_id', (int) $contactId)
            ->or_where('a.email', $this->current_contact_email($contactId))
            ->group_end()
            ->group_by('m.id')
            ->order_by('m.start_time', 'DESC')
            ->get();

        $this->db->db_debug = $previousDebug;

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Video Meeting customer meeting list query failed: ' . json_encode($error));
            return [];
        }

        return $query->result_array();
    }

    private function contact_can_access_meeting($meetingId, $contactId)
    {
        if (!$this->schema_ready()) {
            return false;
        }

        $previousDebug = isset($this->db->db_debug) ? $this->db->db_debug : true;
        $this->db->db_debug = false;
        $query = $this->db
            ->select('meeting_id')
            ->from(db_prefix() . 'google_meet_attendees')
            ->where('meeting_id', (int) $meetingId)
            ->group_start()
            ->where('contact_id', (int) $contactId)
            ->or_where('email', $this->current_contact_email($contactId))
            ->group_end()
            ->limit(1)
            ->get();
        $this->db->db_debug = $previousDebug;

        return $query !== false && $query->num_rows() > 0;
    }

    private function render_customer_view($view, array $data)
    {
        $this->data($data);
        $this->title(isset($data['title']) ? $data['title'] : 'Meetings');
        $this->view('client/' . $view);
        $this->layout();
    }

    public function index()
    {
        $contactId = $this->current_contact_id();

        $this->render_customer_view('list', [
            'meetings' => $contactId > 0 ? $this->get_customer_meetings($contactId) : [],
            'title'    => google_meet_lang('google_meet_my_meetings', 'Meetings'),
        ]);
    }

    public function view($id)
    {
        $id        = (int) $id;
        $contactId = $this->current_contact_id();

        if ($contactId <= 0 || $id <= 0 || !$this->contact_can_access_meeting($id, $contactId)) {
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
            'title'   => !empty($meeting->subject) ? $meeting->subject : (!empty($meeting->title) ? $meeting->title : 'Video Meeting'),
        ]);
    }

    public function join($id)
    {
        $id        = (int) $id;
        $contactId = $this->current_contact_id();

        if ($contactId <= 0 || $id <= 0 || !$this->contact_can_access_meeting($id, $contactId)) {
            set_alert('warning', google_meet_lang('google_meet_access_denied', 'This meeting is not assigned to your account.'));
            redirect(site_url('google_meet/client'));
            exit;
        }

        $meeting  = $this->google_meet_model->get($id);
        $meetLink = $meeting && isset($meeting->meet_link) ? trim((string) $meeting->meet_link) : '';

        if (!$meeting || !$this->google_meet_model->is_real_meet_link($meetLink)) {
            set_alert('warning', google_meet_lang('google_meet_link_not_available', 'The Video Meeting link has not been added yet.'));
            redirect(site_url('google_meet/client/view/' . $id));
            exit;
        }

        redirect($meetLink);
    }
}
