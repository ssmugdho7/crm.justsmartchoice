<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Participate extends ClientsController
{
    public function index($id, $hash)
    {
        $this->load->model('feedbacks_model');
        $feedback = $this->feedbacks_model->get($id);

        // Last statement is for
        if (!$feedback
            || ($feedback->hash != $hash)
            || (!$hash || !$id)
            // Users with permission manage feedbacks to preview the feedback even if is not active
            || ($feedback->active == 0 && !has_permission('feedbacks', '', 'view'))
        ) {
            show_404();
        }

        // Ip Restrict Check
        if ($feedback->iprestrict == 1) {
            $this->db->where('feedbackid', $id);
            $this->db->where('ip', $this->input->ip_address());
            $total = $this->db->count_all_results(db_prefix() . 'feedbackresultsets');
            if ($total > 0) {
                show_404();
            }
        }

        // Check if feedback is only for logged in participants / staff / clients
        if ($feedback->onlyforloggedin == 1 && !is_logged_in()) {
            redirect_after_login_to_current_url();
            redirect(site_url('login'));
        }

        if ($this->input->post()) {
            $validAnswers = true;
            foreach ($feedback->questions as $question) {
                $type = $question['advanced_type'] ?? '';
                if ($type === '') { continue; }
                $answer = $this->input->post('question');
                $value = isset($answer[$question['questionid']][0]) ? trim((string)$answer[$question['questionid']][0]) : '';
                if ((int)$question['required'] === 1 && $value === '') { $validAnswers = false; break; }
                if ($value === '') { continue; }
                if (in_array($type, ['number','currency','percentage'], true) && !is_numeric($value)) { $validAnswers = false; break; }
                if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) { $validAnswers = false; break; }
                if ($type === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) { $validAnswers = false; break; }
                if ($type === 'dropdown' && !in_array($value, $question['advanced_options'] ?? [], true)) { $validAnswers = false; break; }
                if (in_array($type, ['rating','linear_scale'], true)) { $settings=$question['advanced_settings'] ?? []; $min=(int)($settings['min'] ?? 1); $max=(int)($settings['max'] ?? 5); if (!is_numeric($value) || (float)$value<$min || (float)$value>$max) { $validAnswers=false; break; } }
            }
            if (!$validAnswers) { set_alert('warning', 'Please review the required fields and answer formats.'); }
            $success = $validAnswers ? $this->feedbacks_model->add_feedback_result($id, $this->input->post()) : false;
            if ($success) {
                $feedback = $this->feedbacks_model->get($id);
                if ($feedback->redirect_url !== '') {
                    redirect($feedback->redirect_url);
                }
                // Message is by default in English because there is no easy way to know the customer language
                set_alert('success', hooks()->apply_filters('feedback_success_message', 'Thank you for participating in this feedback. Your answers are very important to us.'));

                redirect(hooks()->apply_filters('feedback_default_redirect', site_url('feedbacks/participate/index/' . $id . '/' . $hash . '?participated=yes')));
            }
        }

        $this->app_css->theme('feedbacks-css', module_dir_url('feedbacks', 'assets/css/feedbacks.css'));

        $this->disableNavigation()
        ->disableSubMenu();

        $this->data(['feedback' => $feedback]);
        $this->title($feedback->subject);
        no_index_customers_area();
        $this->view('participate');
        $this->layout();
    }
}
