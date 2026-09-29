<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Subcontractor_portal extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('subcontractors/smartsource_subcontractors_model');
        $this->load->helper('subcontractors/smartsource_subcontractors');
    }

    public function register()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $fieldErrors = $this->validate_portal_submission($data);
            if (!$this->validate_portal_security()) {
                $fieldErrors['recaptcha'] = _l('smartsource_security_verification_failed');
            }
            if (!empty($fieldErrors)) {
                $this->store_portal_form_state($data, $fieldErrors);
                redirect(smartsource_portal_register_url() . '?error=validation');
            }

            if (!isset($_POST['custom_fields']) || !is_array($_POST['custom_fields'])) {
                $_POST['custom_fields'] = [];
            }
            $data['extra_answers'] = json_encode($this->collect_portal_answers($data));
            unset($data['portal_questions'], $data['custom_fields'], $data['g-recaptcha-response'], $data['smartsource_website']);
            $data['status'] = 'pending';
            $data['portal_enabled'] = 1;
            $data['portal_token'] = bin2hex(random_bytes(24));

            try {
                $existing = $this->smartsource_subcontractors_model->find_existing_portal_subcontractor($data);
                if ($existing) {
                    $duplicateErrors = [];
                    if (strtolower(trim((string) $existing->email)) === strtolower(trim((string) ($data['email'] ?? '')))) {
                        $duplicateErrors['email'] = _l('smartsource_duplicate_email');
                    }
                    if (preg_replace('/[^0-9]/', '', (string) $existing->phone) === preg_replace('/[^0-9]/', '', (string) ($data['phone'] ?? ''))) {
                        $duplicateErrors['phone'] = _l('smartsource_duplicate_phone');
                    }
                    $this->store_portal_form_state($data, $duplicateErrors ?: ['form' => _l('smartsource_duplicate_application')]);
                    redirect(smartsource_portal_register_url() . '?error=duplicate');
                    return;
                }

                $id = (int) $this->smartsource_subcontractors_model->add($data);
                $token = (string) $data['portal_token'];
                if ($id <= 0) {
                    $dbError = $this->db->error();
                    throw new RuntimeException(!empty($dbError['message']) ? $dbError['message'] : 'Subcontractor record could not be created.');
                }

                $profileImage = $this->handle_profile_image_upload($id);
                if ($profileImage !== '') {
                    $this->smartsource_subcontractors_model->update(['profile_image' => $profileImage], $id);
                }
                $uploaded = $this->handle_portal_files($id);
                $staffId = $this->smartsource_subcontractors_model->provision_staff_account($id, $data);

                $this->complete_registration_response(
                    smartsource_portal_success_url($token),
                    function () use ($id, $data, $uploaded, $staffId) {
                        $this->safe_post_registration_actions($id, $data, 'new_application', $uploaded, $staffId);
                    }
                );
            } catch (Throwable $e) {
                log_message('error', 'Subcontractor portal registration failed: ' . $e->getMessage());
                log_activity('Subcontractor Portal Registration Failed: ' . $e->getMessage());
                $this->store_portal_form_state($data, ['form' => $this->public_registration_error($e)]);
                redirect(smartsource_portal_register_url() . '?error=save_failed');
            }
        }

        $savedState = $this->consume_portal_form_state();
        $data['submitted_data'] = $savedState['values'];
        $data['field_errors'] = $savedState['errors'];
        $data['title'] = _l('smartsource_portal_registration_title');
        $data['token'] = '';
        $data['subcontractor'] = null;
        $data['files'] = [];
        $data['is_register'] = true;
        $data['portal_questions'] = $this->get_portal_questions();
        $this->load->view('portal/profile', $data);
    }

    public function profile($token = '')
    {
        $subcontractor = $this->smartsource_subcontractors_model->get_by_portal_token($token);
        if (!$subcontractor) {
            show_404();
        }

        if ($this->input->get('saved')) {
            $data['title'] = 'Submission Received';
            $data['token'] = $token;
            $data['subcontractor'] = $subcontractor;
            $this->load->view('portal/success', $data);
            return;
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $fieldErrors = $this->validate_portal_submission($data);
            if (!$this->validate_portal_security()) {
                $fieldErrors['recaptcha'] = _l('smartsource_security_verification_failed');
            }
            if (!empty($fieldErrors)) {
                $this->store_portal_form_state($data, $fieldErrors);
                redirect(smartsource_portal_profile_url($token) . '?error=validation');
            }

            if (!isset($_POST['custom_fields']) || !is_array($_POST['custom_fields'])) {
                $_POST['custom_fields'] = [];
            }
            $data['extra_answers'] = json_encode($this->collect_portal_answers($data));
            unset($data['portal_questions'], $data['custom_fields'], $data['g-recaptcha-response'], $data['smartsource_website']);
            $profileImage = $this->handle_profile_image_upload((int) $subcontractor->id);
            if ($profileImage !== '') {
                $data['profile_image'] = $profileImage;
            }
            $id = $this->smartsource_subcontractors_model->update_from_portal($token, $data);
            if ($id) {
                $uploaded = $this->handle_portal_files($id);
                $this->notify_staff(
                    'Subcontractor Portal Profile Updated',
                    'A subcontractor updated their portal profile: ' . ($data['company'] ?? $subcontractor->company),
                    'subcontractors/view/' . $id
                );
                if ($uploaded > 0) {
                    $this->notify_staff(
                        'Subcontractor Uploaded New Documents',
                        'A subcontractor uploaded ' . $uploaded . ' new document(s): ' . ($data['company'] ?? $subcontractor->company),
                        'subcontractors/view/' . $id
                    );
                }
                redirect(smartsource_portal_success_url($token));
            }
            $this->store_portal_form_state($data, ['form' => _l('smartsource_registration_failed')]);
            redirect(smartsource_portal_profile_url($token) . '?error=save_failed');
        }

        $savedState = $this->consume_portal_form_state();
        $data['submitted_data'] = $savedState['values'];
        $data['field_errors'] = $savedState['errors'];
        $data['title'] = _l('smartsource_subcontractor_portal');
        $data['token'] = $token;
        $data['subcontractor'] = $subcontractor;
        $data['files'] = $this->smartsource_subcontractors_model->get_files($subcontractor->id, 'subcontractor');
        $data['is_register'] = false;
        $data['portal_questions'] = $this->get_portal_questions();
        $this->load->view('portal/profile', $data);
    }


    public function success($token = '')
    {
        $token = $token !== '' ? $token : (string) $this->input->get('token');
        if ($token !== '') {
            $subcontractor = $this->smartsource_subcontractors_model->get_by_portal_token($token);
            if ($subcontractor) {
                redirect(smartsource_portal_profile_url($token) . '?saved=1');
            }
        }

        $data['title'] = 'Submission Received';
        $data['token'] = $token;
        $data['subcontractor'] = null;
        $this->load->view('portal/success', $data);
    }

    private function handle_profile_image_upload($id)
    {
        if (empty($_FILES['profile_image_file']['name'])) {
            return '';
        }
        $path = SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER . 'profiles/';
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $original = $_FILES['profile_image_file']['name'];
        $ext = strtolower((string) pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || (int) ($_FILES['profile_image_file']['size'] ?? 0) > 10485760) {
            log_message('error', 'Rejected subcontractor profile image: invalid type or size.');
            return '';
        }
        $fileName = 'subcontractor-profile-' . (int) $id . '-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.' . strtolower($ext ?: 'png');
        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
        if (move_uploaded_file($_FILES['profile_image_file']['tmp_name'], $path . $fileName)) {
            return 'uploads/smartsource_subcontractors/profiles/' . $fileName;
        }
        return '';
    }

    private function handle_portal_files($id)
    {
        if (empty($_FILES['file']['name'])) {
            return 0;
        }

        $path = SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER . 'subcontractor/' . (int) $id . '/';
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }

        $uploaded = 0;
        if (is_array($_FILES['file']['name'])) {
            foreach ($_FILES['file']['name'] as $index => $original) {
                if ($original === '') {
                    continue;
                }
                $tmpName = $_FILES['file']['tmp_name'][$index] ?? '';
                $size = (int) ($_FILES['file']['size'][$index] ?? 0);
                $error = (int) ($_FILES['file']['error'][$index] ?? UPLOAD_ERR_NO_FILE);
                if ($error !== UPLOAD_ERR_OK || $tmpName === '' || $size <= 0 || $size > 26214400) {
                    log_message('error', 'Subcontractor document upload rejected for ' . $original . ' with upload error ' . $error . '.');
                    continue;
                }
                $fileName = time() . '_' . $index . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
                if (move_uploaded_file($tmpName, $path . $fileName)) {
                    $this->smartsource_subcontractors_model->add_file($id, 'subcontractor', [
                        'file_name' => $fileName,
                        'original_file_name' => $original,
                        'filetype' => $_FILES['file']['type'][$index] ?? '',
                        'visible_to_customer' => 0,
                    ]);
                    $uploaded++;
                }
            }
        }
        return $uploaded;
    }

    private function safe_post_registration_actions($id, array $data, $eventType, $uploaded, $staffId)
    {
        try {
            $this->notify_staff(
                $eventType === 'new_application' ? _l('smartsource_new_registration_subject') : _l('smartsource_updated_registration_subject'),
                _l('smartsource_registration_notification', [$data['company'] ?? _l('smartsource_unknown_company')]),
                'subcontractors/view/' . (int) $id
            );
        } catch (Throwable $e) {
            log_message('error', 'Subcontractor notification failed: ' . $e->getMessage());
        }

        try {
            $this->handle_application_automation((int) $id, $data, $eventType);
        } catch (Throwable $e) {
            log_message('error', 'Subcontractor application automation failed: ' . $e->getMessage());
        }

        if ((int) $staffId > 0) {
            try {
                $token = $this->smartsource_subcontractors_model->ensure_portal_token((int) $id);
                $portalLink = smartsource_portal_profile_url($token);
                smartsource_send_registered_template_email(
                    'smartsource-subcontractor-welcome',
                    (string) ($data['email'] ?? ''),
                    [
                        '{subcontractor_name}' => (string) ($data['contact_name'] ?? $data['company'] ?? ''),
                        '{company}' => (string) ($data['company'] ?? ''),
                        '{portal_link}' => $portalLink,
                    ]
                );
            } catch (Throwable $e) {
                log_message('error', 'Subcontractor welcome email failed: ' . $e->getMessage());
            }
        }
    }

    private function notify_staff($subject, $description, $link = '')
    {
        $staffIds = $this->db->select('staffid')
            ->where('active', 1)
            ->get(db_prefix() . 'staff')
            ->result_array();

        $recipients = [];
        foreach ($staffIds as $member) {
            $staffId = (int) ($member['staffid'] ?? 0);
            if ($staffId <= 0) {
                continue;
            }
            $recipients[] = $staffId;
            if (function_exists('add_notification')) {
                add_notification([
                    'description' => $description,
                    'touserid' => $staffId,
                    'fromcompany' => 1,
                    'link' => $link,
                ]);
            }
        }

        if (!empty($recipients) && function_exists('pusher_trigger_notification')) {
            pusher_trigger_notification($recipients);
        }
    }

    /**
     * Return the success redirect to the browser before noncritical email,
     * notification, automation, SMS, or provider work is executed.
     */
    private function complete_registration_response($url, callable $afterResponse)
    {
        $url = (string) $url;
        if ($url === '') {
            $url = smartsource_portal_register_url();
        }

        ignore_user_abort(true);
        @set_time_limit(60);

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $this->output->set_status_header(303);
        $this->output->set_header('Location: ' . $url);
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Content-Length: 0');
        $this->output->_display('');

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            @flush();
        }

        try {
            $afterResponse();
        } catch (Throwable $e) {
            log_message('error', 'Subcontractor post-response processing failed: ' . $e->getMessage());
        }
        exit;
    }


    private function handle_application_automation($subcontractor_id, $data, $event_type = 'new_application')
    {
        $subcontractor_id = (int) $subcontractor_id;
        if ($subcontractor_id <= 0) {
            return;
        }

        if (!$this->db->table_exists(db_prefix() . 'smartsource_subcontractor_application_alerts')) {
            require_once module_dir_path('subcontractors', 'install.php');
        }

        $company = trim((string) ($data['company'] ?? 'Unknown Company'));
        $contact = trim((string) ($data['contact_name'] ?? ''));
        $email   = trim((string) ($data['email'] ?? ''));
        $phone   = trim((string) ($data['phone'] ?? ''));
        $trade   = trim((string) ($data['trade'] ?? ''));
        $token   = trim((string) ($data['portal_token'] ?? ''));

        if ($token === '') {
            $row = $this->smartsource_subcontractors_model->get($subcontractor_id);
            if ($row && !empty($row->portal_token)) {
                $token = $row->portal_token;
            } elseif (method_exists($this->smartsource_subcontractors_model, 'ensure_portal_token')) {
                $token = $this->smartsource_subcontractors_model->ensure_portal_token($subcontractor_id);
            }
        }

        $portal_link = $token !== '' && function_exists('smartsource_portal_profile_url')
            ? smartsource_portal_profile_url($token)
            : site_url('subcontractor-portal');

        $title = $event_type === 'updated_application'
            ? 'Subcontractor Application Updated'
            : 'New Subcontractor Application Received';

        $message = $company . ' submitted subcontractor application information.';
        if ($trade !== '') {
            $message .= ' Trade: ' . $trade . '.';
        }
        if ($contact !== '') {
            $message .= ' Contact: ' . $contact . '.';
        }

        $alert_id = 0;
        if ($this->db->table_exists(db_prefix() . 'smartsource_subcontractor_application_alerts')) {
            $this->db->insert(db_prefix() . 'smartsource_subcontractor_application_alerts', [
                'subcontractor_id' => $subcontractor_id,
                'event_type'       => $event_type,
                'title'            => $title,
                'message'          => $message,
                'link'             => 'subcontractors/view/' . $subcontractor_id,
                'is_acknowledged'  => 0,
                'datecreated'      => date('Y-m-d H:i:s'),
            ]);
            $alert_id = (int) $this->db->insert_id();
        }

        $task_id = 0;
        if ((string) get_option('smartsource_application_create_task') === '1') {
            $task_id = $this->create_application_followup_task($subcontractor_id, $company, $message);
            if ($alert_id > 0 && $task_id > 0 && $this->db->field_exists('task_id', db_prefix() . 'smartsource_subcontractor_application_alerts')) {
                $this->db->where('id', $alert_id)->update(db_prefix() . 'smartsource_subcontractor_application_alerts', ['task_id' => $task_id]);
            }
        }

        $email_sent = 0;
        if ((string) get_option('smartsource_application_send_email') === '1' && $email !== '' && function_exists('smartsource_send_basic_email')) {
            $subject = $this->replace_application_tokens((string) get_option('smartsource_application_email_subject'), $company, $contact, $email, $phone, $trade, $portal_link);
            $body = $this->replace_application_tokens((string) get_option('smartsource_application_email_body'), $company, $contact, $email, $phone, $trade, $portal_link);
            $email_sent = smartsource_send_basic_email($email, $subject, $body) ? 1 : 0;
        }

        $sms_sent = 0;
        if ((string) get_option('smartsource_application_send_sms') === '1' && $phone !== '' && function_exists('smartsource_send_basic_sms')) {
            $sms = $this->replace_application_tokens((string) get_option('smartsource_application_sms_body'), $company, $contact, $email, $phone, $trade, $portal_link);
            $sms_sent = smartsource_send_basic_sms($phone, $sms) ? 1 : 0;
        }

        if ($alert_id > 0) {
            $update = [];
            if ($this->db->field_exists('email_sent', db_prefix() . 'smartsource_subcontractor_application_alerts')) {
                $update['email_sent'] = $email_sent;
            }
            if ($this->db->field_exists('sms_sent', db_prefix() . 'smartsource_subcontractor_application_alerts')) {
                $update['sms_sent'] = $sms_sent;
            }
            if (!empty($update)) {
                $this->db->where('id', $alert_id)->update(db_prefix() . 'smartsource_subcontractor_application_alerts', $update);
            }
        }

        $this->send_application_staff_notifications($title, $message, 'subcontractors/view/' . $subcontractor_id);
    }

    private function create_application_followup_task($subcontractor_id, $company, $message)
    {
        if (!$this->db->table_exists(db_prefix() . 'tasks')) {
            return 0;
        }

        $taskTable = db_prefix() . 'tasks';
        $data = [
            'name'        => 'Review Subcontractor Application - ' . $company,
            'description' => $message . "\n\nReview the subcontractor profile, license, insurance, uploaded documents, and next steps.",
            'dateadded'   => date('Y-m-d H:i:s'),
            'startdate'   => date('Y-m-d'),
            'duedate'     => date('Y-m-d', strtotime('+3 days')),
            'addedfrom'   => function_exists('get_staff_user_id') ? (int) get_staff_user_id() : 0,
            'status'      => 1,
            'priority'    => 2,
            'rel_id'      => (int) $subcontractor_id,
            'rel_type'    => 'smartsource_subcontractors',
            'is_public'   => 0,
        ];

        foreach (array_keys($data) as $field) {
            if (!$this->db->field_exists($field, $taskTable)) {
                unset($data[$field]);
            }
        }

        if (empty($data)) {
            return 0;
        }

        $this->db->insert($taskTable, $data);
        $task_id = (int) $this->db->insert_id();
        if ($task_id <= 0) {
            return 0;
        }

        $staff_ids = $this->get_application_staff_ids('smartsource_application_task_assigned_staff');
        if (empty($staff_ids)) {
            $staff_ids = $this->get_application_staff_ids('smartsource_application_notify_staff');
        }
        if (!empty($staff_ids) && $this->db->table_exists(db_prefix() . 'task_assigned')) {
            foreach ($staff_ids as $staff_id) {
                $assigned = ['taskid' => $task_id, 'staffid' => $staff_id, 'dateadded' => date('Y-m-d H:i:s')];
                foreach (array_keys($assigned) as $field) {
                    if (!$this->db->field_exists($field, db_prefix() . 'task_assigned')) {
                        unset($assigned[$field]);
                    }
                }
                if (!empty($assigned)) {
                    $this->db->insert(db_prefix() . 'task_assigned', $assigned);
                }
            }
        }

        return $task_id;
    }

    private function send_application_staff_notifications($title, $message, $link)
    {
        $staff_ids = $this->get_application_staff_ids('smartsource_application_notify_staff');
        if (empty($staff_ids)) {
            $staff = $this->db->select('staffid')->where('active', 1)->get(db_prefix() . 'staff')->result_array();
            foreach ($staff as $member) {
                $staff_ids[] = (int) $member['staffid'];
            }
        }

        foreach (array_unique(array_filter($staff_ids)) as $staff_id) {
            if (function_exists('add_notification')) {
                add_notification([
                    'description' => $title . ': ' . $message,
                    'touserid'    => (int) $staff_id,
                    'fromcompany' => 1,
                    'link'        => $link,
                ]);
            }
        }

        if (function_exists('pusher_trigger_notification') && !empty($staff_ids)) {
            pusher_trigger_notification(array_values(array_unique(array_filter($staff_ids))));
        }
        if ((string) get_option('smartsource_application_notify_staff_sms') === '1' && function_exists('smartsource_send_basic_sms')) {
            $rows = $this->db->select('staffid, phonenumber')->where_in('staffid', array_values(array_unique(array_filter($staff_ids))))->where('active', 1)->get(db_prefix() . 'staff')->result_array();
            foreach ($rows as $row) {
                if (trim((string) ($row['phonenumber'] ?? '')) !== '') {
                    try { smartsource_send_basic_sms($row['phonenumber'], $title . ': ' . $message); } catch (Throwable $e) { log_message('error', 'Subcontractor staff SMS failed: ' . $e->getMessage()); }
                }
            }
        }
        $this->send_telegram_manager_notification($title, $message, $link);
    }

    private function send_telegram_manager_notification($title, $message, $link)
    {
        if ((string) get_option('smartsource_telegram_enabled') !== '1') { return; }
        $token = trim((string) get_option('smartsource_telegram_bot_token'));
        $chatIds = preg_split('/[\s,;]+/', trim((string) get_option('smartsource_telegram_chat_ids')), -1, PREG_SPLIT_NO_EMPTY);
        if ($token === '' || empty($chatIds)) { return; }
        $text = $title . "\n" . $message . "\n" . admin_url($link);
        foreach ($chatIds as $chatId) {
            $url = 'https://api.telegram.org/bot' . rawurlencode($token) . '/sendMessage';
            $payload = http_build_query(['chat_id' => trim($chatId), 'text' => $text, 'disable_web_page_preview' => 'true']);
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_TIMEOUT => 4]);
            $response = curl_exec($ch);
            if ($response === false) { log_message('error', 'Subcontractor Telegram notification failed: ' . curl_error($ch)); }
            curl_close($ch);
        }
    }

    private function get_application_staff_ids($option_name)
    {
        $raw = (string) get_option($option_name);
        $ids = [];
        foreach (explode(',', $raw) as $id) {
            $id = (int) trim($id);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return array_values(array_unique($ids));
    }

    private function replace_application_tokens($text, $company, $contact, $email, $phone, $trade, $portal_link)
    {
        if (trim((string) $text) === '') {
            $text = 'Smart Choice Contractors USA received your subcontractor application. Please allow 24 to 72 hours for review. Portal: {portal_link}';
        }

        return str_replace(
            ['{company}', '{subcontractor_name}', '{contact_name}', '{email}', '{phone}', '{trade}', '{portal_link}'],
            [$company, $company, $contact !== '' ? $contact : $company, $email, $phone, $trade, $portal_link],
            (string) $text
        );
    }


    private function validate_portal_submission(array $data)
    {
        $errors = [];
        if (trim((string) ($data['company'] ?? '')) === '') {
            $errors['company'] = _l('smartsource_field_company_required');
        }
        if (trim((string) ($data['contact_name'] ?? '')) === '') {
            $errors['contact_name'] = _l('smartsource_field_contact_required');
        }
        $email = trim((string) ($data['email'] ?? ''));
        if ($email === '') {
            $errors['email'] = _l('smartsource_field_email_required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = _l('smartsource_field_email_invalid');
        }
        if (trim((string) ($data['phone'] ?? '')) === '') {
            $errors['phone'] = _l('smartsource_field_phone_required');
        }
        foreach ($this->get_portal_questions() as $question) {
            if (empty($question['active'])) { continue; }
            $key = (string) ($question['key'] ?? '');
            $value = trim((string) (($data['portal_questions'][$key] ?? '')));
            if (!empty($question['required']) && $value === '') {
                $errors['portal_questions_' . $key] = _l('smartsource_dynamic_question_required');
            }
        }
        return $errors;
    }

    private function get_portal_questions()
    {
        $questions = json_decode((string) get_option('smartsource_portal_questions_json'), true);
        return is_array($questions) ? $questions : [];
    }

    private function collect_portal_answers(array $data)
    {
        $answers = [];
        $posted = isset($data['portal_questions']) && is_array($data['portal_questions']) ? $data['portal_questions'] : [];
        foreach ($this->get_portal_questions() as $question) {
            if (empty($question['active'])) { continue; }
            $key = (string) ($question['key'] ?? '');
            $answers[$key] = trim((string) ($posted[$key] ?? ''));
        }
        return $answers;
    }

    private function store_portal_form_state(array $values, array $errors)
    {
        unset($values['g-recaptcha-response'], $values['smartsource_website'], $values[$this->security->get_csrf_token_name()]);
        $this->session->set_flashdata('smartsource_portal_values', $values);
        $this->session->set_flashdata('smartsource_portal_errors', $errors);
    }

    private function consume_portal_form_state()
    {
        $values = $this->session->flashdata('smartsource_portal_values');
        $errors = $this->session->flashdata('smartsource_portal_errors');
        return [
            'values' => is_array($values) ? $values : [],
            'errors' => is_array($errors) ? $errors : [],
        ];
    }

    private function public_registration_error(Throwable $e)
    {
        $message = strtolower((string) $e->getMessage());
        if (strpos($message, 'duplicate') !== false || strpos($message, 'unique') !== false) {
            return _l('smartsource_duplicate_application');
        }
        if (strpos($message, 'email') !== false) {
            return _l('smartsource_save_error_email');
        }
        if (strpos($message, 'phone') !== false) {
            return _l('smartsource_save_error_phone');
        }
        if (strpos($message, 'unknown column') !== false || strpos($message, 'database') !== false || strpos($message, 'field') !== false) {
            return _l('smartsource_save_error_database');
        }
        return _l('smartsource_registration_failed');
    }

    private function validate_portal_security()
    {
        if (function_exists('show_recaptcha') && show_recaptcha()) {
            $captcha = $this->input->post('g-recaptcha-response');
            if (function_exists('do_recaptcha_validation') && !do_recaptcha_validation($captcha)) {
                return false;
            }
        }

        return true;
    }
}
