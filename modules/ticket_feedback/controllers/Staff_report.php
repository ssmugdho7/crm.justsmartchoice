<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Staff_report extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ticket_feedback_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        $data['title'] = _l('Staff Report');
        // Fetch all staff members
        $data['staff'] = $this->staff_model->get();

        // Fetch feedback data grouped by staff
        $data['staff_feedback'] = $this->ticket_feedback_model->get_feedback_by_staff();

        // Load the view
        $this->load->view('ticket_feedback/admin/staff_report', $data);
    }
}
