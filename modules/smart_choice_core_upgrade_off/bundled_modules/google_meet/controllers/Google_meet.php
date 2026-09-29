<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_meet extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $install = module_dir_path('google_meet') . 'install.php';
        if (file_exists($install)) {
            require_once($install);
        }

        $this->load->model('google_meet_model');
    }

    public function index()
    {
        $data['title'] = 'Google Meet Dashboard';
        $data['meetings'] = $this->google_meet_model->get();
        $this->load->view('dashboard', $data);
    }

    public function create($id = null)
    {
        if ($this->input->post()) {
            $post = $this->input->post(null, true);
            if ($id) {
                $this->google_meet_model->update((int)$id, $post);
                set_alert('success', 'Google Meet meeting updated successfully.');
                redirect(admin_url('google_meet/view/' . (int)$id));
            }

            $meetingId = $this->google_meet_model->create($post);
            if ($meetingId) {
                set_alert('success', 'Google Meet meeting created successfully.');
                redirect(admin_url('google_meet/view/' . $meetingId));
            }

            set_alert('danger', 'Google Meet meeting could not be created. Please run Upgrade Database.');
            redirect(admin_url('google_meet/create'));
        }

        $data['title'] = $id ? 'Edit Google Meet Meeting' : 'New Google Meet Meeting';
        $data['meeting'] = $id ? $this->google_meet_model->get((int)$id) : null;
        $data['attendees'] = $id ? $this->google_meet_model->attendees((int)$id) : [];
        $data['staff'] = $this->google_meet_model->active_staff();
        $data['contacts'] = $this->google_meet_model->active_contacts();
        $data['projects'] = $this->google_meet_model->projects_for_select();

        $this->load->view('form', $data);
    }

    public function view($id)
    {
        $data['meeting'] = $this->google_meet_model->get((int)$id);
        if (!$data['meeting']) {
            set_alert('warning', 'Meeting not found.');
            redirect(admin_url('google_meet'));
        }
        $data['title'] = 'Google Meet Meeting';
        $data['attendees'] = $this->google_meet_model->attendees((int)$id);
        $data['comments'] = $this->google_meet_model->comments((int)$id);
        $this->load->view('view', $data);
    }

    public function add_comment($id)
    {
        if ($this->input->post()) {
            $this->google_meet_model->add_comment((int)$id, $this->input->post('comment', true));
            set_alert('success', 'Meeting comment added.');
        }
        redirect(admin_url('google_meet/view/' . (int)$id));
    }

    public function start($id)
    {
        $this->google_meet_model->start((int)$id);
        set_alert('success', 'Meeting marked as started.');
        redirect(admin_url('google_meet/view/' . (int)$id));
    }

    public function finish($id)
    {
        $this->google_meet_model->finish((int)$id);
        set_alert('success', 'Meeting marked as completed.');
        redirect(admin_url('google_meet/view/' . (int)$id));
    }

    public function notify($id)
    {
        $this->google_meet_model->notify_attendees((int)$id);
        set_alert('success', 'Meeting notifications sent.');
        redirect(admin_url('google_meet/view/' . (int)$id));
    }

    public function settings()
    {
        if ($this->input->post()) {
            update_option('google_meet_enabled', $this->input->post('google_meet_enabled') ? '1' : '0');
            update_option('google_meet_use_google_calendar_api', $this->input->post('google_meet_use_google_calendar_api') ? '1' : '0');
            update_option('google_meet_allow_placeholder_links', $this->input->post('google_meet_allow_placeholder_links') ? '1' : '0');
            update_option('google_meet_google_api_key', $this->input->post('google_meet_google_api_key', true) ?: '');
            update_option('google_meet_google_access_token', $this->input->post('google_meet_google_access_token', true) ?: '');
            update_option('google_meet_calendar_id', $this->input->post('google_meet_calendar_id', true) ?: 'primary');
            update_option('google_meet_timezone', $this->input->post('google_meet_timezone', true) ?: 'America/New_York');
            update_option('google_meet_default_duration', $this->input->post('google_meet_default_duration', true) ?: '30');
            update_option('google_meet_notify_staff_default', $this->input->post('google_meet_notify_staff_default') ? '1' : '0');
            update_option('google_meet_notify_customers_default', $this->input->post('google_meet_notify_customers_default') ? '1' : '0');
            update_option('google_meet_send_invitations_default', $this->input->post('google_meet_send_invitations_default') ? '1' : '0');
            update_option('google_meet_auto_create_link', $this->input->post('google_meet_auto_create_link') ? '1' : '0');

            set_alert('success', 'Google Meet settings saved.');
            redirect(admin_url('google_meet/settings'));
        }

        $data['title'] = 'Google Meet Settings';
        $this->load->view('settings', $data);
    }

    public function reports()
    {
        $filters = [
            'staff_id' => $this->input->get('staff_id', true),
            'date_from' => $this->input->get('date_from', true),
            'date_to' => $this->input->get('date_to', true),
            'q' => $this->input->get('q', true),
            'status' => $this->input->get('status', true),
        ];

        $data['title'] = 'Google Meet Reports';
        $data['filters'] = $filters;
        $data['staff'] = $this->google_meet_model->active_staff();
        $data['summary'] = $this->google_meet_model->report_summary();
        $data['meetings'] = $this->google_meet_model->report_meetings($filters);

        $this->load->view('reports', $data);
    }
}
