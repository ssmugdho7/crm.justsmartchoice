<?php

defined('BASEPATH') or exit('No direct script access allowed');

set_time_limit(0);

class Client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('feedbacks/feedbacks_model');
    }

    public function feedback_list()
    {
        $client_id = get_contact_user_id();

        if (!$client_id) {
            show_404();
            return;
        }

        $email_array = $this->feedbacks_model->get_client_email($client_id);

        if (empty($email_array) || !isset($email_array[0]['email']) || $email_array[0]['email'] == '') {
            set_alert('warning', 'No feedback email found for this client.');
            redirect(site_url());
            return;
        }

        $email = $email_array[0]['email'];

        $feedback_array = $this->feedbacks_model->get_client_feedback_id($email);

        if (empty($feedback_array) || !isset($feedback_array[0]['feedbackid']) || $feedback_array[0]['feedbackid'] == '') {
            set_alert('warning', 'No feedback questionnaire found for this client.');
            redirect(site_url());
            return;
        }

        $feedbackid = $feedback_array[0]['feedbackid'];

        $data['questions_list'] = $this->feedbacks_model->get_client_feedback_questions($feedbackid);
        $data['title'] = 'Questionnaire';

        $this->data($data);
        $this->view('client_feedback_list', $data);
        $this->layout();
    }
}