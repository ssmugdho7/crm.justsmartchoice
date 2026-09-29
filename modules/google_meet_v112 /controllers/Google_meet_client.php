<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_meet_client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('google_meet/google_meet_model');
    }

    public function index()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('google-meet-access'));
        }
        $contact_id = get_contact_user_id();
        $this->db->select('m.*');
        $this->db->from(db_prefix() . 'google_meet_meetings m');
        $this->db->join(db_prefix() . 'google_meet_attendees a', 'a.meeting_id = m.id', 'inner');
        $this->db->where('a.contact_id', (int)$contact_id);
        $this->db->order_by('m.start_time', 'DESC');
        $data['meetings'] = $this->db->get()->result_array();
        $data['title'] = google_meet_lang('google_meet_my_meetings', 'My Meetings');
        $this->load->view('google_meet/client/list', $data);
    }

    public function view($id)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }
        $meeting = $this->google_meet_model->get((int)$id);
        if (!$meeting) {
            redirect(site_url('google-meet-access'));
        }
        $contact_id = get_contact_user_id();
        $allowed = $this->db->where('meeting_id', (int)$id)->where('contact_id', (int)$contact_id)->count_all_results(db_prefix() . 'google_meet_attendees');
        if (!$allowed) {
            redirect(site_url('google-meet-access'));
        }
        $data['meeting'] = $meeting;
        $data['title'] = $meeting->subject;
        $this->load->view('google_meet/client/view', $data);
    }

    public function join($id)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }
        $meeting = $this->google_meet_model->get((int)$id);
        if (!$meeting) {
            show_404();
        }
        if (empty($meeting->meet_link) || rtrim($meeting->meet_link, '/') === 'https://meet.google.com/new') { redirect(site_url('google-meet-access')); }
        redirect($meeting->meet_link);
    }
}
