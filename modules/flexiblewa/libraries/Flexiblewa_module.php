<?php

class Flexiblewa_module{

    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function send_email(string $email, string $subject, string $body, $rel_data = null, $rel_type = 'lead'){
        $this->ci->load->library('email');
        $this->ci->email->set_newline(config_item('newline'));
        $this->ci->email->from(get_option('smtp_email'), get_option('companyname'));
        $subject = $this->_subject($subject, $rel_data, $rel_type);
        $this->ci->email->subject($subject);
        $message = get_option('email_header') . $this->_body($body, $rel_data, $rel_type) . get_option('email_footer');
        $this->ci->email->message($message);
        $this->ci->email->to($email);
        $this->ci->email->bcc($this->_bcc());
        if($this->ci->email->send()){
            log_activity('Email Sent To [Email: ' . $email . ', Subject: ' . $subject . ']');
            return true;
        }
        return false;
    }

    private function _bcc()
    {
        $bcc = '';
        $systemBCC = get_option('bcc_emails');

        if ($systemBCC != '') {
            if ($bcc != '') {
                $bcc .= ', ' . $systemBCC;
            } else {
                $bcc .= $systemBCC;
            }
        }

        if ($bcc != '') {
            $bcc = array_map('trim', explode(',', $bcc));
            $bcc = array_unique($bcc);
            $bcc = implode(', ', $bcc);
           
        }
        return $bcc;
    }

    private function _subject(string $subject, $rel_data = null, $rel_type = 'lead'){
        $subject = str_replace('{company_name}', get_option('companyname'), $subject);
        if($rel_type == 'lead'){
            $subject = str_replace('{lead_name}', $rel_data->name, $subject);
            $subject = str_replace('{lead_email}', $rel_data->email, $subject);
            $subject = str_replace('{lead_website}', $rel_data->website, $subject);
            $subject = str_replace('{lead_description}', $rel_data->description, $subject);
            $subject = str_replace('{lead_phonenumber}', $rel_data->phonenumber, $subject);
            $subject = str_replace('{lead_company}', $rel_data->company, $subject);
        }elseif($rel_type == 'project'){
            $subject = str_replace('{project_name}', $rel_data->name, $subject);
            $subject = str_replace('{project_description}', $rel_data->description, $subject);
            $subject = str_replace('{project_start_date}', _d($rel_data->start_date), $subject);
            $subject = str_replace('{project_deadline}', _d($rel_data->deadline), $subject);
            $subject = str_replace('{project_status}', get_project_status_by_id($rel_data->status)['name'], $subject);
            $subject = str_replace('{project_staff_link}', admin_url('projects/view/' . $rel_data->id), $subject);
            $subject = str_replace('{project_client_link}', site_url('clients/project/' . $rel_data->id), $subject);
        }
        return $subject;
    }

    private function _body(string $body, $rel_data = null, $rel_type = 'lead'){
        $body = str_replace('{company_name}', get_option('companyname'), $body);
        if($rel_type == 'lead'){
            $body = str_replace('{lead_name}', $rel_data->name, $body);
            $body = str_replace('{lead_email}', $rel_data->email, $body);
            $body = str_replace('{lead_website}', $rel_data->website, $body);
            $body = str_replace('{lead_description}', $rel_data->description, $body);
            $body = str_replace('{lead_phonenumber}', $rel_data->phonenumber, $body);
            $body = str_replace('{lead_company}', $rel_data->company, $body);
        }elseif($rel_type == 'project'){
            $body = str_replace('{project_name}', $rel_data->name, $body);
            $body = str_replace('{project_description}', $rel_data->description, $body);
            $body = str_replace('{project_start_date}', _d($rel_data->start_date), $body);
            $body = str_replace('{project_deadline}', _d($rel_data->deadline), $body);
            $body = str_replace('{project_status}', get_project_status_by_id($rel_data->status)['name'], $body);
            $body = str_replace('{project_staff_link}', admin_url('projects/view/' . $rel_data->id), $body);
            $body = str_replace('{project_client_link}', site_url('clients/project/' . $rel_data->id), $body);
        }
        //check for links in body
        $body = check_for_links($body);
        return $body;
    }

}