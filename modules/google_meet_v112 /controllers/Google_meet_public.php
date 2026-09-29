<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_meet_public extends App_Controller
{
    public function index()
    {
        $data['title'] = 'My Video Meetings';
        $data['logged_in'] = is_client_logged_in();
        $data['meetings'] = [];
        if ($data['logged_in']) {
            $contactId = get_contact_user_id();
            $this->db->select('m.*')->from(db_prefix().'google_meet_meetings m');
            $this->db->join(db_prefix().'google_meet_attendees a', 'a.meeting_id=m.id', 'inner');
            $this->db->where('a.contact_id', (int)$contactId)->order_by('m.start_time','DESC');
            $data['meetings'] = $this->db->get()->result_array();
        }
        $this->load->view('google_meet/public_access', $data);
    }
}
