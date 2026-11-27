<?php

defined('BASEPATH') or exit('No direct script access allowed');
set_time_limit(0);

class Feedbacks extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('feedbacks_model');
    }

    private function can_view_feedback_record($id)
    {
        if (has_permission('feedbacks', '', 'view')) { return true; }
        if (!has_permission('feedbacks', '', 'view_own')) { return false; }
        if (!$this->db->field_exists('created_by', db_prefix().'feedbacks')) { return false; }
        return $this->db->where('feedbackid',(int)$id)->where('created_by',(int)get_staff_user_id())->count_all_results(db_prefix().'feedbacks') > 0;
    }

    private function can_edit_question($id) {
        $q = $this->db->where('questionid',(int)$id)->where('rel_type','feedback')->get(db_prefix().'form_questions')->row();
        return $q && $this->can_view_feedback_record($q->rel_id);
    }
    private function can_edit_box_description($id) {
        $q = $this->db->where('questionboxdescriptionid',(int)$id)->get(db_prefix().'form_question_box_description')->row();
        return $q && $this->can_edit_question($q->questionid);
    }
    /* List all feedbacks */
    public function index()
    {
        if (!has_permission('feedbacks', '', 'view') && !has_permission('feedbacks', '', 'view_own')) {
            access_denied('feedbacks');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('feedbacks', 'tables/feedbacks'));
        }
        $data['title'] = _l('feedbacks');
        $this->load->view('feedbacks/all', $data);
    }

    /* Add new feedback or update existing */
    public function feedback($id = '')
    {
        if ($id === '') {
            if (!has_permission('feedbacks', '', 'create')) { access_denied('feedbacks'); }
        } elseif (!$this->can_view_feedback_record($id)) { access_denied('feedbacks'); }
        if ($this->input->post()) {
            $data                    = $this->input->post();
            $data['description']     = html_purify($this->input->post('description', false));
            $data['viewdescription'] = html_purify($this->input->post('viewdescription', false));

            if ($id == '') {
                if (!has_permission('feedbacks', '', 'create')) {
                    access_denied('feedbacks');
                }
                $id = $this->feedbacks_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('feedback')));
                    redirect(admin_url('feedbacks/feedback/' . $id));
                }
            } else {
                if (!has_permission('feedbacks', '', 'edit')) {
                    access_denied('feedbacks');
                }
                $success = $this->feedbacks_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('feedback')));
                }
                redirect(admin_url('feedbacks/feedback/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('feedback_lowercase'));
        } else {
            $this->load->model('leads_model');
            $data['leads_statuses']   = $this->leads_model->get_status();
            $data['customers_groups'] = $this->clients_model->get_groups();
            $feedback                   = $this->feedbacks_model->get($id);
            $data['send_log']         = $this->feedbacks_model->get_feedback_send_log($id);
            $data['feedback']           = $feedback;
            $title                    = $feedback ? $feedback->subject : 'Questionnaire not found';
            if (!$feedback) { show_404(); return; }
        }
        $this->load->model('feedbacks_model');
        $data['mail_lists']          = $this->feedbacks_model->get_mail_lists();
        $data['found_custom_fields'] = false;
        $i                           = 0;
        foreach ($data['mail_lists'] as $mail_list) {
            $fields = $this->feedbacks_model->get_list_custom_fields($mail_list['listid']);
            if (count($fields) > 0) {
                $data['found_custom_fields'] = true;
            }
            $data['mail_lists'][$i]['customfields'] = $fields;
            $i++;
        }

        if (is_gdpr() && (get_option('gdpr_enable_consent_for_contacts') == '1' || get_option('gdpr_enable_consent_for_leads') == '1')) {
            $this->load->model('gdpr_model');
            $data['purposes'] = $this->gdpr_model->get_consent_purposes();
        }

        $data['title'] = $title;
        $this->app_css->add('feedbacks-css', module_dir_url('feedbacks', 'assets/css/feedbacks.css'), 'admin', ['app-css']);

        $this->load->view('feedbacks/feedback', $data);
    }

    /* Send feedback to mail list */
    public function send($feedbackid)
    {
        if (!has_permission('feedbacks', '', 'edit') || !$this->can_view_feedback_record($feedbackid)) {
            access_denied('feedbacks');
        }
        if (!$feedbackid) {
            redirect(admin_url('feedbacks'));
        }
        $this->load->model('feedbacks_model');

        $_lists      = [];
        $_all_emails = [];
        if ($this->input->post('send_feedback_to') || $this->input->post('audience_ids')) {
            $lists = (array) $this->input->post('send_feedback_to');
            if (!has_permission('feedbacks','','view')) { $lists = []; }
            foreach ($lists as $key => $val) {
                // is mail list
                if (is_int($key)) {
                    $list   = $this->feedbacks_model->get_mail_lists($key);
                    $emails = $this->feedbacks_model->get_mail_list_emails($key);
                    foreach ($emails as $email) {
                        // We don't need to validate emails becuase email are already validated when added to mail list
                        array_push($_all_emails, [
                            'listid'  => $key,
                            'emailid' => $email['emailid'],
                            'email'   => $email['email'],
                        ]);
                    }

                    if (count($emails) > 0) {
                        array_push($_lists, $list->name);
                    }
                } else {
                    if ($key == 'staff') {
                        // Pass second paramter to get all active staff, we don't need inactive staff
                        // If you want adjustments feel free to pass 0 or '' for all
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        foreach ($staff as $email) {
                            array_push($_all_emails, $email['email']);
                        }
                        if (count($staff) > 0) {
                            array_push($_lists, 'feedback_send_mail_list_staff');
                        }
                    } elseif ($key == 'clients') {
                        $whereConsent = '';
                        $where        = 'active=1';

                        if ($this->input->post('contacts_consent') && is_array($this->input->post('contacts_consent'))) {
                            $consents = array_map(function ($attr) {
                                return get_instance()->db->escape_str($attr);
                            }, $this->input->post('contacts_consent'));

                            $whereConsent = ' AND ' . db_prefix() . 'contacts.id IN (SELECT contact_id FROM ' . db_prefix() . 'consents WHERE purpose_id IN (' . implode(',', $consents) . ') and action="opt-in" AND date IN (SELECT MAX(date) FROM ' . db_prefix() . 'consents WHERE purpose_id IN (' . implode(', ', $consents) . ') AND contact_id=' . db_prefix() . 'contacts.id))';
                        }
						if($this->input->post('clientid')){
							$clientid=$this->input->post('clientid');
						   $where        = 'active=1';      
						   $clients = $this->clients_model->get_contacts($clientid, $where);
						   foreach ($clients as $email) {
                                $added = true;
								
                                array_push($_all_emails, $email['email']);
                            }
						}	
                        if ($this->input->post('ml_customers_all')) {
                            $where .= $whereConsent;
                            $clients = $this->clients_model->get_contacts('', $where);

                            foreach ($clients as $email) {
                                $added = true;
								
                                array_push($_all_emails, $email['email']);
                            }
                        } else {
                            foreach ($this->input->post('customer_group') as $group_id => $val) {
                                $clients = $this->clients_model->get_contacts('', 'active=1 AND userid IN (select customer_id from ' . db_prefix() . 'customer_groups where groupid =' . $this->db->escape_str($group_id) . ')' . $whereConsent);
                                foreach ($clients as $email) {
                                    $added = true;
                                    array_push($_all_emails, $email['email']);
                                }
                            }
                            $_all_emails = array_unique($_all_emails, SORT_REGULAR);
                        }

                        if (isset($added) > 0) {
                            array_push($_lists, 'feedback_send_mail_list_clients');
                        }
                    } elseif ($key == 'leads') {
                        $this->load->model('leads_model');
                        if ($this->input->post('leads_status')) {
                            $whereConsent = '';
                            if ($this->input->post('leads_consent') && is_array($this->input->post('leads_consent'))) {
                                $consents = array_map(function ($attr) {
                                    return get_instance()->db->escape_str($attr);
                                }, $this->input->post('leads_consent'));

                                $whereConsent = ' AND ' . db_prefix() . 'leads.id IN (SELECT lead_id FROM ' . db_prefix() . 'consents WHERE purpose_id IN (' . implode(',', $consents) . ') and action="opt-in" AND date IN (SELECT MAX(date) FROM ' . db_prefix() . 'consents WHERE purpose_id IN (' . implode(', ', $consents) . ') AND lead_id=' . db_prefix() . 'leads.id))';
                            }

                            $statuses = [];
                            foreach ($this->input->post('leads_status') as $status_id => $val) {
                                array_push($statuses, $this->db->escape_str($status_id));
                            }

                            $where = 'status IN (' . implode(',', $statuses) . ')' . $whereConsent;

                            $leads = $this->leads_model->get('', $where);
                            foreach ($leads as $lead) {
                                $added = true;
                                if (!empty($lead['email']) && filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
                                    array_push($_all_emails, $lead['email']);
                                }
                            }
                            if (isset($added)) {
                                array_push($_lists, _l('leads'));
                            }
                        } elseif ($this->input->post('leads_all')) {
                            $where = 'lost=0' . $whereConsent;
                            $leads = $this->leads_model->get('', $where);
                            foreach ($leads as $lead) {
                                if (!empty($lead['email']) && filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
                                    array_push($_all_emails, $lead['email']);
                                }
                            }
                            if (count($leads)) {
                                array_push($_lists, 'leads');
                            }
                        }
                    }
                }
            }
            $audienceIds = $this->input->post('audience_ids');
            if (is_array($audienceIds)) {
                foreach (array_unique(array_map('intval', $audienceIds)) as $audienceId) {
                    if ($audienceId < 1 || !has_permission('feedbacks','','view') || !$this->db->table_exists(db_prefix().'feedbacks_audience_members')) { continue; }
                    $members = $this->db->where('audience_id', $audienceId)->get(db_prefix().'feedbacks_audience_members')->result_array();
                    foreach ($members as $member) { if (filter_var($member['email'], FILTER_VALIDATE_EMAIL)) { $_all_emails[] = $member['email']; } }
                    if ($members) { $_lists[] = 'Audience #' . $audienceId; }
                }
            }
            $_all_emails = array_values(array_unique($_all_emails, SORT_REGULAR));
        } else {
            set_alert('warning', _l('feedback_no_mail_lists_selected'));
            redirect(admin_url('feedbacks/feedback/' . $feedbackid));
        }

        // We don't need to include in query CRON if 0 emails found
        $iscronfinished = 0;
        if (count($_all_emails) == 0) {
            $iscronfinished = 1;
        }
        $log_id = $this->feedbacks_model->init_feedback_send_log($feedbackid, $iscronfinished, $_lists);

        foreach ($_all_emails as $email) {
            // Is not from email lists
            if (!is_array($email)) {
                $this->db->insert(db_prefix() . 'feedbacksemailsendcron', [
                    'email'    => $email,
                    'feedbackid' => $feedbackid,
                    'log_id'   => $log_id,
                ]);
            } else {
                // Yay its a mail list
                // We will need this info for the custom fields when sending the feedback
                $this->db->insert(db_prefix() . 'feedbacksemailsendcron', [
                    'email'    => $email['email'],
                    'feedbackid' => $feedbackid,
                    'listid'   => $email['listid'],
                    'emailid'  => $email['emailid'],
                    'log_id'   => $log_id,
                ]);
            }
        }

        $total = count($_all_emails);
        if ($total > 0) {
            set_alert('success', _l('feedback_send_success_note', $total));
        } else {
            set_alert('warning', 'No emails found to send the feedback based on the selections.');
        }
        redirect(admin_url('feedbacks/feedback/' . $feedbackid . '?tab=feedback_send_tab'));
    }

    public function remove_feedback_send($id)
    {
        if (!has_permission('feedbacks', '', 'delete')) {
            access_denied('feedbacks');
        }
        $log = $this->db->where('id',(int)$id)->get(db_prefix().'feedbacksendlog')->row();
        if (!$log || !$this->can_view_feedback_record($log->feedbackid)) { access_denied('feedbacks'); }
        $this->feedbacks_model->remove_feedback_send($id);
        redirect($_SERVER['HTTP_REFERER']);
    }

    /* View feedback participating results*/
    public function results($id)
    {
        if (!$this->can_view_feedback_record($id)) { access_denied('feedbacks'); }
        if (!$id) {
            redirect(admin_url('feedbacks'));
        }
        $data['feedbackid']  = $id;
        $data['bodyclass'] = 'feedback_results';
        $feedback            = $this->feedbacks_model->get($id);
        $data['feedback']    = $feedback;
        $data['title']     = _l('feedback_result', $feedback->subject);
        $this->load->view('feedbacks/results', $data);
    }

    /* Delete feedback from database */
    public function delete($id)
    {
        if (!has_permission('feedbacks', '', 'delete') || !$this->can_view_feedback_record($id)) {
            access_denied('feedbacks');
        }
        if (!$id) {
            redirect(admin_url('feedbacks'));
        }
        $success = $this->feedbacks_model->delete($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('feedback')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('feedback')));
        }
        redirect(admin_url('feedbacks'));
    }

    // Ajax
    /* Remove feedback question */
    public function remove_question($questionid)
    {
        if (!$this->can_edit_question($questionid)) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'edit')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die();
        }
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'success' => $this->feedbacks_model->remove_question($questionid),
            ]);
        }
    }

    /* Removes feedback checkbox/radio description*/
    public function remove_box_description($questionboxdescriptionid)
    {
        if (!$this->can_edit_box_description($questionboxdescriptionid)) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'edit')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die();
        }
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'success' => $this->feedbacks_model->remove_box_description($questionboxdescriptionid),
            ]);
        }
    }

    /* Add box description */
    public function add_box_description($questionid, $boxid)
    {
        if (!$this->can_edit_question($questionid) || !$this->db->where('boxid',(int)$boxid)->where('questionid',(int)$questionid)->count_all_results(db_prefix().'form_question_box')) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'edit')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die();
        }
        if ($this->input->is_ajax_request()) {
            $boxdescriptionid = $this->feedbacks_model->add_box_description($questionid, $boxid);
            echo json_encode([
                'boxdescriptionid' => $boxdescriptionid,
            ]);
        }
    }

    /* New feedback question */
    public function add_feedback_question()
    {
        if (!$this->can_view_feedback_record((int)$this->input->post('feedbackid')) || !in_array($this->input->post('type'), ['checkbox','radio','input','textarea'], true)) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'edit')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die();
        }
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                echo json_encode([
                    'data'                             => $this->feedbacks_model->add_feedback_question($this->input->post()),
                    'feedback_question_only_for_preview' => _l('feedback_question_only_for_preview'),
                    'feedback_question_required'         => _l('feedback_question_required'),
                    'feedback_question_string'           => _l('question_string'),
                ]);
                die();
            }
        }
    }

    /* Update question */
    public function update_question()
    {
        if (!$this->can_edit_question((int)$this->input->post('questionid'))) { access_denied('feedbacks'); }
        foreach ((array)$this->input->post('boxes_description') as $box) { if (!$this->can_edit_box_description($box[0])) { access_denied('feedbacks'); } }
        if (!has_permission('feedbacks', '', 'edit')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die();
        }
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $this->feedbacks_model->update_question($this->input->post());
            }
        }
    }

    /* Reorder feedbacks */
    public function update_feedback_questions_orders()
    {
        foreach ((array)$this->input->post('data') as $q) { if (!$this->can_edit_question($q[0])) { access_denied('feedbacks'); } }
        if (has_permission('feedbacks', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                if ($this->input->post()) {
                    $this->feedbacks_model->update_feedback_questions_orders($this->input->post());
                }
            }
        }
    }

    /* Change feedback status active or inactive*/
    public function change_feedback_status($id, $status)
    {
        if (!$this->can_view_feedback_record($id)) { access_denied('feedbacks'); }
        if (has_permission('feedbacks', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->feedbacks_model->change_feedback_status($id, $status);
            }
        }
    }

    // MAIL LISTS
    /* List all mail lists */
    public function mail_lists()
    {
        if (!has_permission('feedbacks', '', 'view')) {
            access_denied('feedbacks');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('feedbacks', 'tables/mail_lists'));
        }
        $data['title'] = _l('mail_lists');
        $this->load->view('feedbacks/mail_lists/manage', $data);
    }

    /* Add or update mail list */
    public function mail_list($id = '')
    {
        if (!has_permission('feedbacks','','view')) { access_denied('feedbacks'); }
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('feedbacks', '', 'create')) {
                    access_denied('feedbacks');
                }
                $id = $this->feedbacks_model->add_mail_list($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('mail_list')));
                    redirect(admin_url('feedbacks/mail_list/' . $id));
                }
            } else {
                if (!has_permission('feedbacks', '', 'edit')) {
                    access_denied('feedbacks');
                }
                $success = $this->feedbacks_model->update_mail_list($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('mail_list')), 'refresh');
                }
                redirect(admin_url('feedbacks/mail_list/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('mail_list_lowercase'));
        } else {
            $list                  = $this->feedbacks_model->get_mail_lists($id);
            $data['list']          = $list;
            $data['custom_fields'] = $this->feedbacks_model->get_list_custom_fields($list->listid);
            $title                 = _l('edit', _l('mail_list_lowercase')) . ' ' . $list->name;
        }
        $data['title'] = $title;
        $this->load->view('feedbacks/mail_lists/list', $data);
    }

    /* View mail list all added emails */
    public function mail_list_view($id)
    {
        if (!has_permission('feedbacks', '', 'view')) {
            access_denied('feedbacks');
        }
        if (!$id) {
            redirect(admin_url('feedbacks/mail_lists'));
        }
        $data       = [];
        $data['id'] = $id;
        if (is_numeric($id)) {
            $data['custom_fields'] = $this->feedbacks_model->get_list_custom_fields($id);
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('feedbacks', 'tables/mail_list_view'), [
                'id'   => $id,
                'data' => $data,
            ]);
        }
        if ($id == 'staff' || $id == 'clients' || $id == 'leads') {
            $list  = new stdClass();
            $title = _l('clients_mail_lists');
            if ($id == 'clients') {
                if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
                    $this->load->model('gdpr_model');
                    $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
                }

                $emails         = $this->clients_model->get_contacts();
                $data['groups'] = $this->clients_model->get_groups();
            } elseif ($id == 'staff') {
                $title = _l('staff_mail_lists');

                $emails = $this->staff_model->get();
            } elseif ($id == 'leads') {
                $title = _l('leads');
                if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                    $this->load->model('gdpr_model');
                    $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
                }
                $this->load->model('leads_model');

                $data['statuses'] = $this->leads_model->get_status();
                $data['sources']  = $this->leads_model->get_source();

                $emails = $this->leads_model->get('', ['lost' => 0]);
            }
            $list->emails = [];
            $i            = 0;
            foreach ($emails as $email) {
                if (empty($email['email'])) {
                    continue;
                }
                if ($id == 'leads') {
                    $list->emails[$i]['dateadded'] = $email['dateadded'];
                } else {
                    $list->emails[$i]['dateadded'] = $email['datecreated'];
                }
                $list->emails[$i]['email'] = $email['email'];
                $i++;
            }
            $data['list']  = $list;
            $data['title'] = $title;
            $fixed_list    = true;
        } else {
            $list          = $this->feedbacks_model->get_data_for_view_list($id);
            $data['title'] = $list->name;
            $data['list']  = $list;
            $fixed_list    = false;
        }
        $data['fixedlist'] = $fixed_list;
        $this->load->view('feedbacks/mail_lists/list_view', $data);
    }

    /* Add single email to mail list / ajax*/
    public function add_email_to_list()
    {
        if (!has_permission('feedbacks','','view')) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'create')) {
            echo json_encode([
                'success'       => false,
                'error_message' => _l('access_denied'),
            ]);
            die();
        }
        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {
                echo json_encode($this->feedbacks_model->add_email_to_list($this->input->post()));
                die();
            }
        }
    }

    /* Remove single email from mail list / ajax */
    public function remove_email_from_mail_list($emailid)
    {
        if (!has_permission('feedbacks','','view')) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'delete')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die();
        }
        if (!$emailid) {
            echo json_encode([
                'success' => false,
            ]);
            die();
        }
        echo json_encode($this->feedbacks_model->remove_email_from_mail_list($emailid));
        die();
    }

    /* Remove mail list custom field */
    public function remove_list_custom_field($fieldid)
    {
        if (!has_permission('feedbacks','','view')) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'delete')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->feedbacks_model->remove_list_custom_field($fieldid));
            die();
        }
    }

    /* Import .xls file with emails */
    public function import_emails()
    {
        if (!has_permission('feedbacks','','view')) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'create')) {
            access_denied('feedbacks');
        }

        // Using composer
        // require_once(APPPATH . 'third_party/Excel_reader/php-excel-reader/excel_reader2.php');
        // require_once(APPPATH . 'third_party/Excel_reader/SpreadsheetReader.php');

        $filename = uniqid() . '_' . $_FILES['file_xls']['name'];
        $temp_url = TEMP_FOLDER . $filename;
        if ((\function_exists('sc_recovery_move_uploaded_file') ? '\sc_recovery_move_uploaded_file' : '\move_uploaded_file')($_FILES['file_xls']['tmp_name'], $temp_url)) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($temp_url);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($temp_url, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
            $total_duplicate_emails = 0;
            $total_invalid_address  = 0;
            $total_added_emails     = 0;
            $mails_failed_to_insert = 0;
            $listid                 = $this->input->post('listid');

            foreach ($spreadsheet->getActiveSheet()->toArray() as $email) {
                if (isset($email[0]) && $email[0] !== '') {
                    $data['email'] = $email[0];
                    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                        $total_invalid_address++;

                        continue;
                    }
                    $data['listid'] = $listid;
                    if (count($email) > 1) {
                        $custom_fields       = $this->feedbacks_model->get_list_custom_fields($listid);
                        $total_custom_fields = count($custom_fields);
                        for ($i = 0; $i < $total_custom_fields; $i++) {
                            if ($email[$i + 1] !== '') {
                                $data['customfields'][$custom_fields[$i]['customfieldid']] = $email[$i + 1] ?? '';
                            }
                        }
                    }
                    $success = $this->feedbacks_model->add_email_to_list($data);
                    if ($success['success'] == false && $success['duplicate'] == true) {
                        $total_duplicate_emails++;
                    } elseif ($success['success'] == false) {
                        $mails_failed_to_insert++;
                    } else {
                        $total_added_emails++;
                    }
                }
                if ($total_added_emails > 0 && $mails_failed_to_insert == 0) {
                    $_alert_type = 'success';
                } elseif ($total_added_emails == 0 && $mails_failed_to_insert > 0) {
                    $_alert_type = 'danger';
                } elseif ($total_added_emails > 0 && $mails_failed_to_insert > 0) {
                    $_alert_type = 'warning';
                } else {
                    $_alert_type = 'success';
                }
            }
            // Delete uploaded file
            (\function_exists('sc_recovery_unlink') ? '\sc_recovery_unlink' : '\unlink')($temp_url);
            set_alert($_alert_type, _l('mail_list_total_imported', $total_added_emails) . '<br />' . _l('mail_list_total_duplicate', $total_duplicate_emails) . '<br />' . _l('mail_list_total_failed_to_insert', $mails_failed_to_insert) . '<br />' . _l('mail_list_total_invalid', $total_invalid_address));
        } else {
            set_alert('danger', _l('error_uploading_file'));
        }
        redirect(admin_url('feedbacks/mail_list_view/' . $listid));
    }

    /* Delete mail list from database */
    public function delete_mail_list($id)
    {
        if (!has_permission('feedbacks','','view')) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'delete')) {
            access_denied('feedbacks');
        }
        if (!$id) {
            redirect(admin_url('feedbacks/mail_lists'));
        }
        $success = $this->feedbacks_model->delete_mail_list($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('mail_list')));
        }
        redirect(admin_url('feedbacks/mail_lists'));
    }

    /** Built-in editable construction-business template library. */
    public function templates()
    {
        if (!has_permission('feedbacks', '', 'view') && !has_permission('feedbacks', '', 'view_own') && !has_permission('feedbacks', '', 'view_all_templates') && !has_permission('feedbacks', '', 'create')) {
            access_denied('feedbacks');
        }
        $data['templates'] = require(__DIR__ . '/../libraries/questionnaire_templates.php');
        $data['title'] = _l('fb_template_library');
        $this->load->view('feedbacks/templates', $data);
    }

    /** Create a normal editable questionnaire from a built-in template. */
    public function create_from_template($template_index)
    {
        if (!has_permission('feedbacks', '', 'create')) {
            access_denied('feedbacks');
        }
        $templates = require(__DIR__ . '/../libraries/questionnaire_templates.php');
        $index = (int) $template_index;
        if (!isset($templates[$index])) {
            show_404();
        }
        $template = $templates[$index];
        $this->db->trans_begin();
        $feedbackId = $this->feedbacks_model->add([
            'subject' => $template['title'],
            'description' => '<p>' . html_escape($template['description']) . '</p>',
            'viewdescription' => '<p>' . html_escape($template['description']) . '</p>',
            'redirect_url' => '',
            'fromname' => get_option('companyname'),
        ]);
        if (!$feedbackId) {
            $this->db->trans_rollback();
            set_alert('danger', _l('fb_template_create_failed'));
            redirect(admin_url('feedbacks/templates'));
        }
        foreach ($template['questions'] as $order => $question) {
            $created = $this->feedbacks_model->add_feedback_question([
                'feedbackid' => $feedbackId,
                'type' => in_array($question['type'], ['radio', 'checkbox', 'input', 'textarea'], true) ? $question['type'] : 'input',
            ]);
            if (!$created || empty($created['questionid'])) {
                $this->db->trans_rollback();
                set_alert('danger', _l('fb_template_create_failed'));
                redirect(admin_url('feedbacks/templates'));
            }
            $this->feedbacks_model->update_question([
                'questionid' => $created['questionid'],
                'question' => ['value' => $question['question'], 'required' => 'false'],
            ]);
            $this->db->where('questionid', $created['questionid']);
            $this->db->update(db_prefix() . 'form_questions', ['question_order' => $order + 1]);
            if (in_array($question['type'], ['radio', 'checkbox'], true) && !empty($question['options'])) {
                // The legacy model creates one blank option; remove it before adding the template choices.
                $this->db->where('questionid', $created['questionid']);
                $this->db->where('description', '');
                $this->db->delete(db_prefix() . 'form_question_box_description');
                foreach ($question['options'] as $option) {
                    $this->feedbacks_model->add_box_description($created['questionid'], $created['boxid'], $option);
                }
            }
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            set_alert('danger', _l('fb_template_create_failed'));
            redirect(admin_url('feedbacks/templates'));
        }
        $this->db->trans_commit();
        log_activity('Questionnaire created from built-in template [ID: ' . $feedbackId . ', Template: ' . $template['title'] . ']');
        set_alert('success', _l('fb_template_created'));
        redirect(admin_url('feedbacks/feedback/' . $feedbackId));
    }

    /** Secure bulk deletion of selected questionnaires. */
    public function bulk_delete()
    {
        if (!has_permission('feedbacks', '', 'delete')) {
            access_denied('feedbacks');
        }
        $ids = $this->input->post('ids');
        if (!is_array($ids) || empty($ids)) {
            set_alert('warning', _l('fb_select_records'));
            redirect(admin_url('feedbacks'));
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) { return $id > 0; })));
        $deleted = 0;
        $this->db->trans_begin();
        foreach ($ids as $id) {
            if (!$this->can_view_feedback_record($id)) { continue; }
            if ($this->feedbacks_model->delete($id)) {
                $deleted++;
            }
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            set_alert('danger', _l('problem_deleting', _l('feedbacks')));
        } else {
            $this->db->trans_commit();
            log_activity('Bulk deleted questionnaires [Count: ' . $deleted . ']');
            set_alert('success', _l('fb_bulk_deleted', $deleted));
        }
        redirect(admin_url('feedbacks'));
    }

    /** Initial reporting overview, based on existing questionnaire and participation tables. */
    public function reports()
    {
        if (!has_permission('feedbacks', '', 'view') && !has_permission('feedbacks', '', 'view_own')) { access_denied('feedbacks'); }
        $ownOnly = !has_permission('feedbacks', '', 'view') && has_permission('feedbacks', '', 'view_own');
        $data['title'] = _l('fb_reports');
        if ($ownOnly) { $this->db->where('created_by',(int)get_staff_user_id()); }
        $data['total_questionnaires'] = $this->db->count_all_results(db_prefix() . 'feedbacks');
        if ($ownOnly) { $this->db->where('created_by',(int)get_staff_user_id()); }
        $data['active_questionnaires'] = $this->db->where('active', 1)->count_all_results(db_prefix() . 'feedbacks');
        $this->db->from(db_prefix().'feedbackresultsets');
        if ($ownOnly) { $this->db->where('feedbackid IN (SELECT feedbackid FROM '.db_prefix().'feedbacks WHERE created_by='.(int)get_staff_user_id().')', null, false); }
        if ($this->input->get('date_from')) { $this->db->where('date >=', $this->input->get('date_from').' 00:00:00'); }
        if ($this->input->get('date_to')) { $this->db->where('date <=', $this->input->get('date_to').' 23:59:59'); }
        if ($this->input->get('feedbackid')) { $this->db->where('feedbackid',(int)$this->input->get('feedbackid')); }
        if ($this->input->get('staff_id') && $this->db->table_exists(db_prefix().'feedbacks_participants')) { $this->db->where('resultsetid IN (SELECT resultsetid FROM '.db_prefix().'feedbacks_participants WHERE participant_type=\'staff\' AND participant_id='.(int)$this->input->get('staff_id').')', null, false); }
        $data['total_participation'] = $this->db->count_all_results();
        if ($ownOnly) { $this->db->where('created_by',(int)get_staff_user_id()); }
        $data['questionnaires'] = $this->db->select('feedbackid,subject')->order_by('subject','ASC')->get(db_prefix().'feedbacks')->result_array();
        $this->load->model('staff_model');
        $data['staff_members'] = $this->staff_model->get('', ['active'=>1]);
        $data['questions'] = [];
        if ($this->input->get('feedbackid')) { $data['questions'] = $this->db->where('rel_type','feedback')->where('rel_id',(int)$this->input->get('feedbackid'))->order_by('question_order','ASC')->get(db_prefix().'form_questions')->result_array(); }
        $this->db->select('f.feedbackid,f.subject,COUNT(r.resultsetid) AS responses');
        $this->db->from(db_prefix().'feedbacks f');
        $this->db->join(db_prefix().'feedbackresultsets r','r.feedbackid=f.feedbackid','left');
        if ($ownOnly) { $this->db->where('f.created_by',(int)get_staff_user_id()); }
        if ($this->input->get('date_from')) { $this->db->where('r.date >=',$this->input->get('date_from').' 00:00:00'); }
        if ($this->input->get('date_to')) { $this->db->where('r.date <=',$this->input->get('date_to').' 23:59:59'); }
        if ($this->input->get('feedbackid')) { $this->db->where('f.feedbackid',(int)$this->input->get('feedbackid')); }
        if ($this->input->get('staff_id')) { $this->db->join(db_prefix().'feedbacks_participants p','p.resultsetid=r.resultsetid AND p.participant_type=\'staff\' AND p.participant_id='.(int)$this->input->get('staff_id'),'inner'); }
        $data['response_breakdown']=$this->db->group_by('f.feedbackid')->order_by('responses','DESC')->get()->result_array();
        $data['question_breakdown'] = [];
        if ($this->input->get('questionid')) { $this->db->select('COALESCE(opt.description, fr.answer) AS answer,COUNT(*) AS total', false)->from(db_prefix().'form_results fr')->join(db_prefix().'form_question_box_description opt','opt.questionboxdescriptionid=fr.boxdescriptionid','left')->where('fr.questionid',(int)$this->input->get('questionid'))->group_by('answer')->order_by('total','DESC'); $data['question_breakdown']=$this->db->get()->result_array(); }
        $this->load->view('feedbacks/reports', $data);
    }


    /** Download a questionnaire interchange JSON file. */
    public function export_questionnaire($id)
    {
        if (!$this->can_view_feedback_record($id)) { access_denied('feedbacks'); }
        $feedback = $this->feedbacks_model->get((int) $id);
        if (!$feedback) { show_404(); }
        $payload = ['format' => 'smartchoice-feedbacks', 'format_version' => 1, 'questionnaire' => [
            'subject' => $feedback->subject, 'description' => $feedback->description,
            'viewdescription' => $feedback->viewdescription, 'questions' => []
        ]];
        foreach ($feedback->questions as $q) {
            $payload['questionnaire']['questions'][] = [
                'question' => $q['question'], 'type' => !empty($q['advanced_type']) ? $q['advanced_type'] : $q['boxtype'],
                'required' => (bool) $q['required'], 'options' => !empty($q['box_descriptions']) ? array_column($q['box_descriptions'], 'description') : [],
                'settings' => !empty($q['advanced_settings']) ? $q['advanced_settings'] : []
            ];
        }
        $this->output->set_content_type('application/json')->set_header('Content-Disposition: attachment; filename="questionnaire-' . (int)$id . '.json"')->set_output(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /** Import a questionnaire JSON export while preserving the legacy response engine. */
    public function import_questionnaire()
    {
        if (!has_permission('feedbacks', '', 'create')) { access_denied('feedbacks'); }
        if (empty($_FILES['questionnaire_file']['tmp_name']) || !is_uploaded_file($_FILES['questionnaire_file']['tmp_name']) || !in_array(strtolower(pathinfo($_FILES['questionnaire_file']['name'], PATHINFO_EXTENSION)), ['json','csv'], true)) {
            set_alert('warning', _l('fb_import_choose_file')); redirect(admin_url('feedbacks'));
        }
        $raw = file_get_contents($_FILES['questionnaire_file']['tmp_name']);
        $data = json_decode($raw, true);
        if (is_array($data) && ($data['format'] ?? '') === 'smartchoice-feedbacks') {
            $q = $data['questionnaire'] ?? [];
        } else {
            // CSV template format: subject,description,question,type,required,options.
            $fh = fopen($_FILES['questionnaire_file']['tmp_name'], 'r');
            $headers = array_map(function($v){ return strtolower(trim((string)$v)); }, fgetcsv($fh) ?: []);
            $rows = []; $subject = ''; $description = '';
            while (($row = fgetcsv($fh)) !== false) {
                $item = []; foreach ($headers as $i => $key) { $item[$key] = $row[$i] ?? ''; }
                if ($subject === '' && !empty($item['subject'])) { $subject = trim($item['subject']); }
                if ($description === '' && !empty($item['description'])) { $description = trim($item['description']); }
                if (!empty($item['question'])) { $rows[] = ['question'=>$item['question'],'type'=>$item['type'] ?: 'input','required'=>in_array(strtolower($item['required'] ?? ''),['1','yes','true'],true),'options'=>array_values(array_filter(array_map('trim',explode('|',$item['options'] ?? '')))),'settings'=>[]]; }
            }
            fclose($fh); $q = ['subject'=>$subject,'description'=>$description,'viewdescription'=>$description,'questions'=>$rows];
        }
        if (!is_array($q) || empty($q['subject']) || !is_array($q['questions'] ?? null)) {
            set_alert('danger', _l('fb_import_invalid')); redirect(admin_url('feedbacks'));
        }
        $this->db->trans_begin();
        $id = $this->feedbacks_model->add(['subject' => $q['subject'], 'description' => html_purify($q['description'] ?? ''), 'viewdescription' => html_purify($q['viewdescription'] ?? ''), 'redirect_url' => '', 'fromname' => get_option('companyname')]);
        $allowed = ['radio','checkbox','input','textarea','dropdown','toggle','rating','number','currency','percentage','date','time','email','phone','url','linear_scale'];
        foreach ($q['questions'] as $order => $item) {
            $type = in_array($item['type'] ?? '', $allowed, true) ? $item['type'] : 'input';
            $legacy = in_array($type, ['radio','checkbox','textarea'], true) ? $type : 'input';
            $created = $this->feedbacks_model->add_feedback_question(['feedbackid' => $id, 'type' => $legacy]);
            if (!$created) { $this->db->trans_rollback(); set_alert('danger', _l('fb_import_failed')); redirect(admin_url('feedbacks')); }
            $this->feedbacks_model->update_question(['questionid' => $created['questionid'], 'question' => ['value' => $item['question'] ?? '', 'required' => !empty($item['required']) ? 'true' : 'false']]);
            $this->db->where('questionid', $created['questionid'])->update(db_prefix().'form_questions', ['question_order' => $order + 1]);
            if (in_array($type, ['radio','checkbox'], true)) {
                $this->db->where('questionid', $created['questionid'])->where('description', '')->delete(db_prefix().'form_question_box_description');
                foreach (($item['options'] ?? []) as $option) { $this->feedbacks_model->add_box_description($created['questionid'], $created['boxid'], $option); }
            }
            $this->db->insert(db_prefix().'feedbacks_question_meta', ['feedbackid'=>$id,'questionid'=>$created['questionid'],'field_type'=>$type,'options_json'=>json_encode($item['options'] ?? []),'settings_json'=>json_encode($item['settings'] ?? []),'datecreated'=>date('Y-m-d H:i:s')]);
        }
        if ($this->db->trans_status() === false) { $this->db->trans_rollback(); set_alert('danger', _l('fb_import_failed')); redirect(admin_url('feedbacks')); }
        $this->db->trans_commit();
        $this->db->insert(db_prefix().'feedbacks_import_log', ['staff_id'=>get_staff_user_id(),'filename'=>basename($_FILES['questionnaire_file']['name']),'status'=>'success','details'=>'Imported questionnaire ID '.$id,'datecreated'=>date('Y-m-d H:i:s')]);
        set_alert('success', _l('fb_import_success')); redirect(admin_url('feedbacks/feedback/'.$id));
    }

    /** Download CSV column headers and a sample row for audience imports. */
    public function sample_header()
    {
        if (!has_permission('feedbacks', '', 'create')) { access_denied('feedbacks'); }
        $this->output->set_content_type('text/csv')->set_header('Content-Disposition: attachment; filename="smartchoice-questionnaire-import-template.csv"');
        $this->output->set_output("subject,description,question,type,required,options\r\n\"Customer Feedback\",\"Tell us about your experience\",\"How would you rate our service?\",rating,1,\"1|2|3|4|5\"\r\n");
    }

    /** Create advanced field through the compatible legacy input storage plus typed metadata. */
    public function add_advanced_question()
    {
        if (!has_permission('feedbacks', '', 'edit')) { access_denied('feedbacks'); }
        $feedbackId = (int) $this->input->post('feedbackid');
        $type = (string) $this->input->post('field_type');
        $allowed = ['dropdown','toggle','rating','number','currency','percentage','date','time','email','phone','url','linear_scale'];
        if (!$feedbackId || !$this->can_view_feedback_record($feedbackId) || !in_array($type, $allowed, true)) { show_error('Invalid questionnaire field.', 400); }
        $created = $this->feedbacks_model->add_feedback_question(['feedbackid'=>$feedbackId,'type'=>'input']);
        if (!$created) { show_error('Unable to create question.', 500); }
        $this->db->insert(db_prefix().'feedbacks_question_meta', ['feedbackid'=>$feedbackId,'questionid'=>$created['questionid'],'field_type'=>$type,'options_json'=>json_encode(array_values(array_filter(array_map('trim', preg_split('/\r?\n|\|/', (string)$this->input->post('options_text', true)))) ?: ['1','2','3','4','5'])),'settings_json'=>json_encode(['min'=>max(0,(int)$this->input->post('min_value')),'max'=>max(1,(int)$this->input->post('max_value')),'step'=>1,'placeholder'=>'']),'datecreated'=>date('Y-m-d H:i:s')]);
        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true,'questionid'=>$created['questionid'],'type'=>$type]));
        } else {
            set_alert('success', _l('fb_advanced_field_added'));
            redirect(admin_url('feedbacks/feedback/'.$feedbackId));
        }
    }

    /** Save appearance and public-form header/footer settings. */
    public function save_design($id)
    {
        if (!has_permission('feedbacks', '', 'edit') || !$this->can_view_feedback_record($id)) { access_denied('feedbacks'); }
        $id = (int)$id; if (!$this->feedbacks_model->get($id)) { show_404(); }
        $settings = ['theme'=>$this->input->post('theme', true) ?: 'smartchoice','footer_mode'=>$this->input->post('footer_mode', true) === 'custom' ? 'custom' : 'crm','primary_color'=>$this->input->post('primary_color', true) ?: '#2f7d32','background_color'=>$this->input->post('background_color', true) ?: '#ffffff','corner_radius'=>max(0,min(32,(int)$this->input->post('corner_radius'))),'font_size'=>max(12,min(22,(int)$this->input->post('font_size')))];
        $header = html_purify($this->input->post('header_html', false) ?: ''); $footer = html_purify($this->input->post('footer_html', false) ?: '');
        if (!$this->db->table_exists(db_prefix().'feedbacks_design')) { show_error('Questionnaire design table is unavailable. Complete the database upgrade first.', 503); return; }
        $existing = $this->db->where('feedbackid',$id)->get(db_prefix().'feedbacks_design')->row();
        $record = ['settings_json'=>json_encode($settings),'header_html'=>$header,'footer_html'=>$footer,'dateupdated'=>date('Y-m-d H:i:s')];
        if ($existing) { $this->db->where('feedbackid',$id)->update(db_prefix().'feedbacks_design',$record); }
        else { $record['feedbackid']=$id; $this->db->insert(db_prefix().'feedbacks_design',$record); }
        set_alert('success', _l('fb_design_saved')); redirect(admin_url('feedbacks/feedback/'.$id));
    }

    /** Audience segments manager with manually maintained members; CRM segments can be extended later. */
    public function audiences()
    {
        if (!$this->db->table_exists(db_prefix().'feedbacks_audiences') || !$this->db->table_exists(db_prefix().'feedbacks_audience_members')) { set_alert('warning',_l('fb_upgrade_required')); redirect(admin_url('feedbacks')); }
        if (!has_permission('feedbacks', '', 'view')) { access_denied('feedbacks'); }
        if ($this->input->post('name')) {
            if (!has_permission('feedbacks', '', 'create')) { access_denied('feedbacks'); }
            $this->db->insert(db_prefix().'feedbacks_audiences',['name'=>$this->input->post('name',true),'description'=>$this->input->post('description',true),'criteria_json'=>json_encode(['source'=>'manual_or_import']),'created_by'=>get_staff_user_id(),'datecreated'=>date('Y-m-d H:i:s')]);
            set_alert('success', _l('fb_audience_created')); redirect(admin_url('feedbacks/audiences'));
        }
        $data['audiences']=$this->db->order_by('name','ASC')->get(db_prefix().'feedbacks_audiences')->result_array();
        $data['title']=_l('fb_audiences'); $this->load->view('feedbacks/audiences',$data);
    }

    public function audience_add_member($id)
    {
        if (!has_permission('feedbacks','','view')) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'create') && !has_permission('feedbacks', '', 'edit')) { access_denied('feedbacks'); }
        $email=strtolower(trim((string)$this->input->post('email',true))); $name=trim((string)$this->input->post('name',true));
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { set_alert('danger',_l('fb_invalid_email')); redirect(admin_url('feedbacks/audiences')); }
        $exists=$this->db->where('audience_id',(int)$id)->where('email',$email)->count_all_results(db_prefix().'feedbacks_audience_members');
        if (!$exists) { $this->db->insert(db_prefix().'feedbacks_audience_members',['audience_id'=>(int)$id,'name'=>$name,'email'=>$email,'source'=>'manual','datecreated'=>date('Y-m-d H:i:s')]); }
        set_alert('success',_l('fb_audience_member_saved')); redirect(admin_url('feedbacks/audiences'));
    }

    /** CSV member import into an audience. */
    public function audience_import($id)
    {
        if (!has_permission('feedbacks','','view')) { access_denied('feedbacks'); }
        if (!has_permission('feedbacks', '', 'create') && !has_permission('feedbacks', '', 'edit')) { access_denied('feedbacks'); }
        if (empty($_FILES['audience_csv']['tmp_name'])) { set_alert('warning',_l('fb_import_choose_file')); redirect(admin_url('feedbacks/audiences')); }
        $fh=fopen($_FILES['audience_csv']['tmp_name'],'r'); $header=fgetcsv($fh); $map=array_flip(array_map('strtolower',$header ?: [])); $added=0;
        while (($row=fgetcsv($fh)) !== false) { $email=trim($row[$map['email'] ?? -1] ?? ''); $name=trim($row[$map['name'] ?? -1] ?? ''); if (filter_var($email,FILTER_VALIDATE_EMAIL)) { $exists=$this->db->where('audience_id',(int)$id)->where('email',$email)->count_all_results(db_prefix().'feedbacks_audience_members'); if (!$exists) { $this->db->insert(db_prefix().'feedbacks_audience_members',['audience_id'=>(int)$id,'name'=>$name,'email'=>strtolower($email),'source'=>'csv','datecreated'=>date('Y-m-d H:i:s')]); $added++; } } }
        fclose($fh); set_alert('success',_l('fb_audience_imported',$added)); redirect(admin_url('feedbacks/audiences'));
    }

}
