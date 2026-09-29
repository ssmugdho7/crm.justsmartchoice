<?php

class Feedbacks_module
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('feedbacks/feedbacks_model');
    }

    public function send($cronManuallyInvoked = false)
    {
        $last_feedback_cron = get_option('last_feedback_send_cron');
        if ($last_feedback_cron == '' || (time() > ($last_feedback_cron + 3600)) || $cronManuallyInvoked === true) {
            $found_emails = $this->ci->db->count_all_results(db_prefix().'feedbacksemailsendcron');
            if ($found_emails > 0) {
                $total_emails_per_cron = get_option('feedback_send_emails_per_cron_run');
                // Initialize mail library
                $this->ci->email->initialize();
                $this->ci->load->library('email');
                // Load feedback model
                $this->ci->load->model('feedbacks_model');
                // Get all feedbacks send log where sending emails is not finished
                $this->ci->db->where('iscronfinished', 0);
                $unfinished_feedbacks_send_log = $this->ci->db->get(db_prefix().'feedbacksendlog')->result_array();
                foreach ($unfinished_feedbacks_send_log as $_feedback) {
                    $feedbackid = $_feedback['feedbackid'];
                    // Get feedback emails that has been not sent yet.
                    $this->ci->db->where('feedbackid', $feedbackid);
                    $this->ci->db->limit($total_emails_per_cron);
                    $emails = $this->ci->db->get(db_prefix().'feedbacksemailsendcron')->result_array();
                    $feedback = $this->ci->feedbacks_model->get($feedbackid);
                    if ($feedback->fromname == '' || $feedback->fromname == null) {
                        $feedback->fromname = get_option('companyname');
                    }
                    if (stripos($feedback->description, '{feedback_link}') !== false) {
                        $feedback->description = str_ireplace('{feedback_link}', '<a href="' . site_url('feedbacks/feedback/' . $feedback->feedbackid . '/' . $feedback->hash) . '" target="_blank">' . $feedback->subject . '</a>', $feedback->description);
                    }
                    $total = $_feedback['total'];
                    foreach ($emails as $data) {
                        $emailDescription = $feedback->description;

                        if (isset($data['emailid']) && isset($data['listid'])) {
                            $customfields = $this->ci->feedbacks_model->get_list_custom_fields($data['listid']);

                            foreach ($customfields as $custom_field) {
                                $value = $this->ci->feedbacks_model->get_email_custom_field_value($data['emailid'], $data['listid'], $custom_field['customfieldid']);

                                $custom_field['fieldslug'] = '{' . $custom_field['fieldslug'] . '}';
                                if (stripos($emailDescription, $custom_field['fieldslug']) !== false) {
                                    $emailDescription = str_ireplace($custom_field['fieldslug'], $value, $emailDescription);
                                }
                            }
                        }
                        $this->ci->email->clear(true);
                        $this->ci->email->from(get_option('smtp_email'), $feedback->fromname);
                        $this->ci->email->to($data['email']);
                        $this->ci->email->subject($feedback->subject);
                        $this->ci->email->message($emailDescription);

                        if ($this->ci->email->send(true)) {
                            $total++;
                        }

                        $this->ci->db->where('id', $data['id']);
                        $this->ci->db->delete(db_prefix().'feedbacksemailsendcron');
                    }
                    // Update feedback send log
                    $this->ci->db->where('id', $_feedback['id']);
                    $this->ci->db->update(db_prefix().'feedbacksendlog', [
                        'total' => $total,
                    ]);
                    // Check if all emails send
                    $this->ci->db->where('feedbackid', $feedbackid);
                    $found_emails = $this->ci->db->count_all_results(db_prefix().'feedbacksemailsendcron');
                    if ($found_emails == 0) {
                        // Update that feedback send is finished
                        $this->ci->db->where('id', $_feedback['id']);
                        $this->ci->db->update(db_prefix().'feedbacksendlog', [
                            'iscronfinished' => 1,
                        ]);
                    }
                }
                update_option('last_feedback_send_cron', time());
            }
        }
    }
}
