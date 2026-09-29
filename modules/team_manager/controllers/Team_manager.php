<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Team_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('team_manager_model');
        $this->load->model('staff_model'); // Load the staff model to retrieve staff details
    }

    // List all teams
    public function index()
    {
        $data['title'] = _l('team_manager');
        $data['teams'] = $this->team_manager_model->get_teams();

        // Get staff list for converting IDs to names
        $staff = $this->staff_model->get();
        $staff_dropdown = [];
        foreach ($staff as $s) {
            // Using array syntax since $s is an array
            $staff_dropdown[$s['staffid']] = $s['firstname'] . ' ' . $s['lastname'];
        }
        $data['staff_dropdown'] = $staff_dropdown;

        $this->load->view('teams/index', $data);
    }

    // Add new team
    public function add_team()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            // Data includes leader and optionally subleader selected from dropdowns
            $team_id = $this->team_manager_model->add_team($data);
            if ($team_id) {
                set_alert('success', _l('added_successfully', _l('team')));
                redirect(admin_url('team_manager'));
            }
        }
        $data['title'] = _l('add_team');

        // Get staff dropdown for leader and subleader fields
        $staff = $this->staff_model->get();
        $staff_dropdown = [];
        foreach ($staff as $s) {
            $staff_dropdown[$s['staffid']] = $s['firstname'] . ' ' . $s['lastname'];
        }
        $data['staff_dropdown'] = $staff_dropdown;

        $this->load->view('teams/add_edit', $data);
    }

    // Edit team
    public function edit_team($id)
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $success = $this->team_manager_model->update_team($data, $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('team')));
                redirect(admin_url('team_manager'));
            }
        }
        $data['team'] = $this->team_manager_model->get_team($id);
        $data['title'] = _l('edit_team');

        // Get staff dropdown for leader and subleader fields
        $staff = $this->staff_model->get();
        $staff_dropdown = [];
        foreach ($staff as $s) {
            $staff_dropdown[$s['staffid']] = $s['firstname'] . ' ' . $s['lastname'];
        }
        $data['staff_dropdown'] = $staff_dropdown;

        $this->load->view('teams/add_edit', $data);
    }

    // Delete team
    public function delete_team($id)
    {
        $response = $this->team_manager_model->delete_team($id);
        if ($response) {
            set_alert('success', _l('deleted', _l('team')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('team')));
        }
        redirect(admin_url('team_manager'));
    }

    // View team details and list its members
    public function view_team($id)
    {
        $data['team'] = $this->team_manager_model->get_team($id);
        $data['members'] = $this->team_manager_model->get_members_by_team($id);
        $data['title'] = _l('team_details');

        // Get staff list for converting staff_id to name
        $staff = $this->staff_model->get();
        $staff_dropdown = [];
        foreach ($staff as $s) {
            $staff_dropdown[$s['staffid']] = $s['firstname'] . ' ' . $s['lastname'];
        }
        $data['staff_dropdown'] = $staff_dropdown;

        $this->load->view('teams/view_team', $data);
    }

    // Add team member
    public function add_member($team_id)
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['team_id'] = $team_id;
            // Data expects only the 'staff_id' field from the dropdown
            $member_id = $this->team_manager_model->add_member($data);
            if ($member_id) {
                set_alert('success', _l('added_successfully', _l('team_member')));
                redirect(admin_url('team_manager/view_team/' . $team_id));
            }
        }
        $data['team_id'] = $team_id;
        $data['title'] = _l('add_team_member');

        // Get staff dropdown for selecting the member
        $staff = $this->staff_model->get();
        $staff_dropdown = [];
        foreach ($staff as $s) {
            $staff_dropdown[$s['staffid']] = $s['firstname'] . ' ' . $s['lastname'];
        }
        $data['staff_dropdown'] = $staff_dropdown;

        $this->load->view('teams/add_edit_member', $data);
    }

    // Edit team member
    public function edit_member($member_id)
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $success = $this->team_manager_model->update_member($data, $member_id);
            $member = $this->team_manager_model->get_member($member_id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('team_member')));
                redirect(admin_url('team_manager/view_team/' . $member->team_id));
            }
        }
        $data['member'] = $this->team_manager_model->get_member($member_id);
        $data['title'] = _l('edit_team_member');

        // Get staff dropdown for selecting the member
        $staff = $this->staff_model->get();
        $staff_dropdown = [];
        foreach ($staff as $s) {
            $staff_dropdown[$s['staffid']] = $s['firstname'] . ' ' . $s['lastname'];
        }
        $data['staff_dropdown'] = $staff_dropdown;

        $this->load->view('teams/add_edit_member', $data);
    }

    // Delete team member
    public function delete_member($member_id)
    {
        $member = $this->team_manager_model->get_member($member_id);
        $response = $this->team_manager_model->delete_member($member_id);
        if ($response) {
            set_alert('success', _l('deleted', _l('team_member')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('team_member')));
        }
        redirect(admin_url('team_manager/view_team/' . $member->team_id));
    }
}
