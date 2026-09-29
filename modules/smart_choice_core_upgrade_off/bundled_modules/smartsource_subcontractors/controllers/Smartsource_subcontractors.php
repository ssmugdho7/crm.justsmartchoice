<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smartsource_subcontractors extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smartsource_subcontractors_model');
        $this->load->helper('smartsource_subcontractors/smartsource_subcontractors');
    }

    private function ensure_enabled()
    {
        if (get_option('smartsource_subcontractors_enabled') !== '1' && $this->uri->segment(3) !== 'settings') {
            set_alert('warning', _l('smartsource_module_disabled_notice'));
            redirect(admin_url('smartsource_subcontractors/settings'));
        }
    }

    public function index()
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractors', '', 'view') && !has_permission('smartsource_subcontractors', '', 'view_own')) {
            access_denied('smartsource_subcontractors');
        }

        $data['title'] = 'Subcontractors';
        $data['subcontractors'] = $this->smartsource_subcontractors_model->get();
        $data['stats'] = $this->smartsource_subcontractors_model->get_stats();
        $this->load->view('admin/subcontractors/manage', $data);
    }

    public function subcontractor($id = '')
    {
        $this->ensure_enabled();
        if ($id === '') {
            if (!has_permission('smartsource_subcontractors', '', 'create')) {
                access_denied('smartsource_subcontractors');
            }
        } else {
            if (!has_permission('smartsource_subcontractors', '', 'edit') && !has_permission('smartsource_subcontractors', '', 'view')) {
                access_denied('smartsource_subcontractors');
            }
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $profileImage = $this->handle_profile_image_upload($id);
            if ($profileImage !== '') {
                $data['profile_image'] = $profileImage;
            }
            if ($id === '') {
                $id = $this->smartsource_subcontractors_model->add($data);
                set_alert('success', _l('added_successfully', _l('smartsource_subcontractor')));
            } else {
                $this->smartsource_subcontractors_model->update($data, $id);
                set_alert('success', _l('updated_successfully', _l('smartsource_subcontractor')));
            }
            $this->smartsource_subcontractors_model->get_or_create_portal_token($id);
            redirect(admin_url('smartsource_subcontractors/view/' . $id));
        }

        $data['title'] = $id === '' ? 'New Subcontractor' : 'Edit Subcontractor';
        $data['subcontractor'] = $id === '' ? null : $this->smartsource_subcontractors_model->get($id);
        $data['categories'] = $this->smartsource_subcontractors_model->get_categories();
        $data['statuses'] = $this->smartsource_subcontractors_model->get_subcontractor_statuses_db();
        $data['staff_members'] = $this->db->select('staffid, firstname, lastname, email')->where('active', 1)->order_by('firstname', 'ASC')->get(db_prefix() . 'staff')->result_array();
        $this->load->view('admin/subcontractors/form', $data);
    }

    public function view($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractors', '', 'view') && !has_permission('smartsource_subcontractors', '', 'view_own')) {
            access_denied('smartsource_subcontractors');
        }

        $subcontractor = $this->smartsource_subcontractors_model->get($id);
        if (!$subcontractor) {
            show_404();
        }

        $data['title'] = $subcontractor->company;
        $data['subcontractor'] = $subcontractor;
        $data['contracts'] = $this->smartsource_subcontractors_model->get_contract('', ['subcontractor_id' => (int) $id]);
        $data['files'] = $this->smartsource_subcontractors_model->get_files($id, 'subcontractor');
        $this->load->view('admin/subcontractors/view', $data);
    }

    public function delete($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractors', '', 'delete')) {
            access_denied('smartsource_subcontractors');
        }
        $this->smartsource_subcontractors_model->delete($id);
        set_alert('success', _l('deleted', _l('smartsource_subcontractor')));
        redirect(admin_url('smartsource_subcontractors'));
    }

    public function contracts()
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'view') && !has_permission('smartsource_subcontractor_contracts', '', 'view_own')) {
            access_denied('smartsource_subcontractor_contracts');
        }

        $filters = [
            'status' => $this->input->get('status'),
            'contract_type' => $this->input->get('contract_type'),
            'include_trash' => $this->input->get('include_trash'),
            'stat' => $this->input->get('stat'),
        ];

        $data['title'] = 'Subcontractor Contracts';
        $data['contracts'] = $this->smartsource_subcontractors_model->get_contract('', $filters);
        $data['stats'] = $this->smartsource_subcontractors_model->get_stats();
        $data['chart_data'] = $this->smartsource_subcontractors_model->get_chart_data();
        $data['statuses'] = $this->smartsource_subcontractors_model->get_contract_statuses_db();
        $this->load->view('admin/contracts/manage', $data);
    }

    public function contract($id = '')
    {
        $this->ensure_enabled();
        if ($id === '') {
            if (!has_permission('smartsource_subcontractor_contracts', '', 'create')) {
                access_denied('smartsource_subcontractor_contracts');
            }
        } else {
            if (!has_permission('smartsource_subcontractor_contracts', '', 'edit') && !has_permission('smartsource_subcontractor_contracts', '', 'view')) {
                access_denied('smartsource_subcontractor_contracts');
            }
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id === '') {
                $id = $this->smartsource_subcontractors_model->add_contract($data);
                set_alert('success', _l('added_successfully', _l('smartsource_subcontractor_contract')));
            } else {
                $this->smartsource_subcontractors_model->update_contract($data, $id);
                set_alert('success', _l('updated_successfully', _l('smartsource_subcontractor_contract')));
            }
            $this->send_contract_notifications($id, $data);
            redirect(admin_url('smartsource_subcontractors/contract_view/' . $id));
        }

        $data['title'] = $id === '' ? 'New Subcontractor Contract' : 'Edit Subcontractor Contract';
        $data['contract'] = $id === '' ? null : $this->smartsource_subcontractors_model->get_contract($id);
        $data['subcontractors'] = $this->smartsource_subcontractors_model->get();
        $data['projects'] = $this->db->select('id,name')->order_by('name', 'ASC')->get(db_prefix() . 'projects')->result_array();
        $data['statuses'] = $this->smartsource_subcontractors_model->get_contract_statuses_db();
        $data['templates'] = $this->smartsource_subcontractors_model->get_templates();
        $this->load->view('admin/contracts/form', $data);
    }

    public function contract_view($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'view') && !has_permission('smartsource_subcontractor_contracts', '', 'view_own')) {
            access_denied('smartsource_subcontractor_contracts');
        }

        $contract = $this->smartsource_subcontractors_model->get_contract($id);
        if (!$contract) {
            show_404();
        }

        $data['title'] = $contract->subject;
        $data['contract'] = $contract;
        $data['files'] = $this->smartsource_subcontractors_model->get_files($id, 'contract');
        $this->load->view('admin/contracts/view', $data);
    }


    public function sign_contract($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'edit')) {
            access_denied('smartsource_subcontractor_contracts');
        }

        if ($this->input->post()) {
            $role = $this->input->post('signature_role') === 'company' ? 'company' : 'subcontractor';
            $initials = trim((string) $this->input->post('initials'));
            $signature = (string) $this->input->post('signature_data');
            $this->smartsource_subcontractors_model->save_contract_signature($id, $role, $initials, $signature);
            set_alert('success', _l('smartsource_signature_saved'));
        }
        redirect(admin_url('smartsource_subcontractors/contract_view/' . (int) $id));
    }

    public function contract_pdf($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'view') && !has_permission('smartsource_subcontractor_contracts', '', 'view_own')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $contract = $this->smartsource_subcontractors_model->get_contract($id);
        if (!$contract) {
            show_404();
        }
        $data['contract'] = $contract;
        $data['content'] = $this->smartsource_subcontractors_model->render_contract_content($contract);
        $this->load->view('admin/contracts/pdf', $data);
    }

    public function contract_print($id)
    {
        $this->contract_pdf($id);
    }

    public function contract_delete($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'delete')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $this->smartsource_subcontractors_model->trash_contract($id);
        set_alert('success', _l('smartsource_contract_sent_to_trash'));
        redirect(admin_url('smartsource_subcontractors/contracts'));
    }

    public function restore_contract($id)
    {
        if (!has_permission('smartsource_subcontractor_contracts', '', 'delete')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $this->smartsource_subcontractors_model->restore_contract($id);
        set_alert('success', _l('smartsource_contract_restored'));
        redirect(admin_url('smartsource_subcontractors/contracts?include_trash=1'));
    }

    public function permanent_delete_contract($id)
    {
        if (!has_permission('smartsource_subcontractor_contracts', '', 'delete')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $this->smartsource_subcontractors_model->permanent_delete_contract($id);
        set_alert('success', _l('deleted', _l('smartsource_subcontractor_contract')));
        redirect(admin_url('smartsource_subcontractors/contracts?include_trash=1'));
    }

    public function upload_file($rel_type, $rel_id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractors', '', 'edit') && !has_permission('smartsource_subcontractor_contracts', '', 'edit')) {
            access_denied('smartsource_subcontractors');
        }

        $rel_type = in_array($rel_type, ['subcontractor', 'contract'], true) ? $rel_type : 'subcontractor';
        $path = SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER . $rel_type . '/' . (int) $rel_id . '/';

        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }

        $uploaded = 0;
        if (!empty($_FILES['file']['name']) && is_array($_FILES['file']['name'])) {
            foreach ($_FILES['file']['name'] as $index => $original) {
                if ($original === '') {
                    continue;
                }
                $fileName = time() . '_' . $index . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
                if (move_uploaded_file($_FILES['file']['tmp_name'][$index], $path . $fileName)) {
                    $this->smartsource_subcontractors_model->add_file($rel_id, $rel_type, [
                        'file_name' => $fileName,
                        'original_file_name' => $original,
                        'filetype' => $_FILES['file']['type'][$index] ?? '',
                        'visible_to_customer' => $this->input->post('visible_to_customer'),
                    ]);
                    $uploaded++;
                }
            }
        } elseif (!empty($_FILES['file']['name'])) {
            $original = $_FILES['file']['name'];
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $path . $fileName)) {
                $this->smartsource_subcontractors_model->add_file($rel_id, $rel_type, [
                    'file_name' => $fileName,
                    'original_file_name' => $original,
                    'filetype' => $_FILES['file']['type'] ?? '',
                    'visible_to_customer' => $this->input->post('visible_to_customer'),
                ]);
                $uploaded++;
            }
        }

        if ($uploaded > 0) {
            set_alert('success', _l('file_uploaded_successfully'));
        }

        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('smartsource_subcontractors'));
    }

    public function delete_file($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractors', '', 'delete') && !has_permission('smartsource_subcontractor_contracts', '', 'delete')) {
            access_denied('smartsource_subcontractors');
        }
        $this->smartsource_subcontractors_model->delete_file($id);
        set_alert('success', _l('deleted', _l('file')));
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('smartsource_subcontractors'));
    }

    public function templates($id = '')
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'view')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        if ($this->input->post()) {
            if ($id === '') {
                $this->smartsource_subcontractors_model->add_template($this->input->post());
                set_alert('success', _l('added_successfully', _l('smartsource_template')));
            } else {
                $this->smartsource_subcontractors_model->update_template($this->input->post(), $id);
                set_alert('success', _l('updated_successfully', _l('smartsource_template')));
            }
            redirect(admin_url('smartsource_subcontractors/templates'));
        }
        $data['title'] = 'Subcontractor Templates';
        $data['templates'] = $this->smartsource_subcontractors_model->get_templates();
        $data['template'] = $id === '' ? null : $this->smartsource_subcontractors_model->get_templates($id);
        $this->load->view('admin/templates/manage', $data);
    }

    public function template_view($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'view')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $data['title'] = 'View Subcontractor Template';
        $data['template'] = $this->smartsource_subcontractors_model->get_templates($id);
        if (!$data['template']) {
            show_404();
        }
        $this->load->view('admin/templates/view', $data);
    }

    public function copy_template($id)
    {
        $this->smartsource_subcontractors_model->copy_template($id);
        set_alert('success', _l('smartsource_template_copied'));
        redirect(admin_url('smartsource_subcontractors/templates'));
    }

    public function delete_template($id)
    {
        $this->smartsource_subcontractors_model->delete_template($id);
        set_alert('success', _l('deleted', _l('smartsource_template')));
        redirect(admin_url('smartsource_subcontractors/templates'));
    }

    public function settings()
    {
        if (!has_permission('smartsource_subcontractors', '', 'view')) {
            access_denied('smartsource_subcontractors');
        }

        if ($this->input->post('smartsource_test_email')) {
            $this->send_test_email();
            return;
        }

        if ($this->input->post('smartsource_test_sms')) {
            $this->send_test_sms();
            return;
        }

        if ($this->input->post('settings')) {
            update_option('smartsource_subcontractors_enabled', $this->input->post('smartsource_subcontractors_enabled') ? '1' : '0');
            update_option('smartsource_subcontractors_delete_data_on_uninstall', $this->input->post('smartsource_subcontractors_delete_data_on_uninstall') ? '1' : '0');
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('smartsource_subcontractors/settings'));
        }

        if ($this->input->post('category_name')) {
            $this->smartsource_subcontractors_model->save_simple_record('smartsource_subcontractor_categories', [
                'name' => $this->input->post('category_name'),
                'color' => $this->input->post('category_color') ?: '#169179',
            ]);
            redirect(admin_url('smartsource_subcontractors/settings'));
        }

        if ($this->input->post('status_name')) {
            $this->smartsource_subcontractors_model->save_simple_record('smartsource_subcontractor_statuses', [
                'name' => $this->input->post('status_name'),
                'slug' => url_title($this->input->post('status_name'), '_', true),
                'color' => $this->input->post('status_color') ?: '#169179',
            ]);
            redirect(admin_url('smartsource_subcontractors/settings'));
        }

        if ($this->input->post('contract_status_name')) {
            $this->smartsource_subcontractors_model->save_simple_record('smartsource_subcontractor_contract_statuses', [
                'name' => $this->input->post('contract_status_name'),
                'slug' => url_title($this->input->post('contract_status_name'), '_', true),
                'color' => $this->input->post('contract_status_color') ?: '#169179',
            ]);
            redirect(admin_url('smartsource_subcontractors/settings'));
        }

        $data['title'] = 'Subcontractor Settings';
        $data['categories'] = $this->smartsource_subcontractors_model->get_categories();
        $data['statuses'] = $this->smartsource_subcontractors_model->get_subcontractor_statuses_db();
        $data['staff_members'] = $this->db->select('staffid, firstname, lastname, email')->where('active', 1)->order_by('firstname', 'ASC')->get(db_prefix() . 'staff')->result_array();
        $data['contract_statuses'] = $this->smartsource_subcontractors_model->get_contract_statuses_db();
        $data['health'] = $this->smartsource_subcontractors_model->health_check();
        $this->load->view('admin/settings/manage', $data);
    }

    public function delete_setting_record($table, $id)
    {
        $allowed = ['smartsource_subcontractor_categories', 'smartsource_subcontractor_statuses', 'smartsource_subcontractor_contract_statuses'];
        if (in_array($table, $allowed, true)) {
            $this->smartsource_subcontractors_model->delete_simple_record($table, $id);
        }
        redirect(admin_url('smartsource_subcontractors/settings'));
    }


    public function repair_database()
    {
        if (!is_admin()) {
            access_denied('smartsource_subcontractors');
        }

        require_once module_dir_path('smartsource_subcontractors', 'install.php');
        set_alert('success', 'Subcontractor module database tables repaired successfully.');
        redirect(admin_url('smartsource_subcontractors/settings'));
    }

    public function portal_links()
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractors', '', 'view')) {
            access_denied('smartsource_subcontractors');
        }
        $subcontractors = $this->smartsource_subcontractors_model->get();
        foreach ($subcontractors as &$subcontractor) {
            if (empty($subcontractor['portal_token'])) {
                $subcontractor['portal_token'] = $this->smartsource_subcontractors_model->get_or_create_portal_token($subcontractor['id']);
            }
        }
        $data['title'] = 'Subcontractor Portal';
        $data['subcontractors'] = $subcontractors;
        $data['portal_index_link'] = smartsource_portal_register_url();
        $this->load->view('admin/subcontractors/portal_links', $data);
    }

    private function send_contract_notifications($id, $data)
    {
        $recipients = [];
        if (!empty($data['send_to_subcontractor'])) {
            $contract = $this->smartsource_subcontractors_model->get_contract($id);
            if ($contract && !empty($contract->email)) {
                $recipients[] = $contract->email;
            }
        }
        if (!empty($data['send_to_staff']) && function_exists('get_staff')) {
            $staff = get_staff(get_staff_user_id());
            if ($staff && !empty($staff->email)) {
                $recipients[] = $staff->email;
            }
        }
        foreach (array_unique($recipients) as $email) {
            $subject = 'Smart Choice Subcontractor Contract: ' . ($contract->subject ?? ('Contract #' . (int) $id));
            $link = admin_url('smartsource_subcontractors/contract_view/' . (int) $id);
            $message = '<p>A subcontractor contract has been created or updated.</p>';
            $message .= '<p><strong>Subject:</strong> ' . html_escape($contract->subject ?? '') . '</p>';
            $message .= '<p><strong>Subcontractor:</strong> ' . html_escape($contract->subcontractor_company ?? '') . '</p>';
            $message .= '<p><a href="' . $link . '">Open Contract In CRM</a></p>';
            if (function_exists('smartsource_send_basic_email')) {
                smartsource_send_basic_email($email, $subject, $message);
            }
            log_activity('Smartsource Subcontractor Contract Notification Sent [Contract ID: ' . (int) $id . ', Email: ' . $email . ']');
        }
    }

    private function handle_profile_image_upload($id = '')
    {
        if (empty($_FILES['profile_image_file']['name'])) {
            return '';
        }
        $relId = is_numeric($id) ? (int) $id : 0;
        $path = SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER . 'profiles/';
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $original = $_FILES['profile_image_file']['name'];
        $ext = pathinfo($original, PATHINFO_EXTENSION);
        $fileName = 'subcontractor-profile-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.' . strtolower($ext ?: 'png');
        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
        if (move_uploaded_file($_FILES['profile_image_file']['tmp_name'], $path . $fileName)) {
            return 'uploads/smartsource_subcontractors/profiles/' . $fileName;
        }
        return '';
    }

    private function send_test_email()
    {
        $to = trim((string) $this->input->post('test_email_to'));
        if ($to === '') {
            set_alert('danger', 'Please enter an email address to test.');
            redirect(admin_url('smartsource_subcontractors/settings'));
        }

        $subject = 'SmartSource Subcontractors Email Test';
        $message = '<p>This is a test email from the SmartSource Subcontractors module.</p>';
        $message .= '<p>If you received this message, the CRM email delivery path is working for this module.</p>';
        $sent = false;
        if (function_exists('smartsource_send_basic_email')) {
            $sent = smartsource_send_basic_email($to, $subject, $message);
        }

        if ($sent) {
            set_alert('success', 'Test email sent to ' . $to . '. Check inbox and spam folder.');
        } else {
            set_alert('danger', 'Test email was not sent. Check SMTP settings under Setup > Settings > Email.');
        }
        redirect(admin_url('smartsource_subcontractors/settings'));
    }

    private function send_test_sms()
    {
        $to = trim((string) $this->input->post('test_sms_to'));
        $message = trim((string) $this->input->post('test_sms_message'));

        if ($to === '') {
            set_alert('danger', 'Please enter a phone number to test SMS.');
            redirect(admin_url('smartsource_subcontractors/settings'));
        }

        if ($message === '') {
            $message = 'SmartSource Subcontractors SMS test from Smart Choice CRM.';
        }

        $sent = false;
        $error = '';

        if (isset($this->app_sms)) {
            $gateway = $this->app_sms->get_active_gateway();
            if ($gateway !== false && !empty($gateway['id'])) {
                $className = 'sms_' . $gateway['id'];
                if (isset($this->{$className}) && method_exists($this->{$className}, 'send')) {
                    if (method_exists($this->{$className}, 'set_test_mode')) {
                        $this->{$className}->set_test_mode(true);
                    }
                    $sent = $this->{$className}->send($to, clear_textarea_breaks(nl2br($message)));
                    if (method_exists($this->{$className}, 'set_test_mode')) {
                        $this->{$className}->set_test_mode(false);
                    }
                } else {
                    $error = 'Active SMS gateway library is not loaded.';
                }
            } else {
                $error = 'No active SMS gateway found under Setup > Settings > SMS.';
            }
        } else {
            $error = 'CRM SMS library is not available.';
        }

        if ($sent) {
            set_alert('success', 'Test SMS sent to ' . $to . '.');
        } else {
            if (isset($GLOBALS['sms_error']) && $GLOBALS['sms_error']) {
                $error = is_array($GLOBALS['sms_error']) ? json_encode($GLOBALS['sms_error']) : (string) $GLOBALS['sms_error'];
            }
            set_alert('warning', 'SMS test could not be sent. ' . ($error ?: 'Check SMS gateway configuration.'));
        }

        redirect(admin_url('smartsource_subcontractors/settings'));
    }

    public function help()
    {
        if (!has_permission('smartsource_subcontractors', '', 'view')) {
            access_denied('smartsource_subcontractors');
        }

        if ($this->input->post('save_help_sections')) {
            $titles = $this->input->post('help_title');
            $contents = $this->input->post('help_content', false);
            $sections = [];

            if (is_array($titles) && is_array($contents)) {
                foreach ($titles as $index => $title) {
                    $title = trim((string) $title);
                    $content = isset($contents[$index]) ? (string) $contents[$index] : '';
                    if ($title === '' && trim(strip_tags($content)) === '') {
                        continue;
                    }
                    $sections[] = [
                        'title' => $title !== '' ? $title : 'Instruction Section',
                        'content' => $content,
                    ];
                }
            }

            if (empty($sections)) {
                $sections = $this->default_help_sections();
            }

            update_option('smartsource_help_sections_json', json_encode($sections));
            set_alert('success', 'Help Guide instructions updated successfully.');
            redirect(admin_url('smartsource_subcontractors/help'));
        }

        $sections = json_decode((string) get_option('smartsource_help_sections_json'), true);
        if (!is_array($sections) || empty($sections)) {
            $sections = $this->default_help_sections();
        }

        $data['title'] = 'Subcontractors Help Guide';
        $data['help_sections'] = $sections;
        $this->load->view('admin/help/manual', $data);
    }

    private function default_help_sections()
    {
        return [
            [
                'title' => '1. Subcontractor Records',
                'content' => '<p>Create a subcontractor profile for every company, independent installer, crew leader, supplier representative, or trade partner. Add company name, contact name, phone, email, trade, status, license number, DBPR link, county license verification link, insurance expiration, address, assigned staff, notes, profile photo, and portal access status.</p>',
            ],
            [
                'title' => '2. Public Subcontractor Portal',
                'content' => '<p>Use the Subcontractor Portal page to copy the public registration link and send it to a new subcontractor. The subcontractor can create a profile, upload multiple documents, select English or Spanish, upload a profile picture, and submit license and insurance information. Portal submissions create staff notifications and email alerts when CRM email is configured.</p>',
            ],
            [
                'title' => '3. Documents And Files',
                'content' => '<p>Upload W-9 forms, insurance certificates, DBPR license copies, county registrations, photos, driver license copies, OSHA cards, signed agreements, project photos, and other supporting documents. File names appear in the subcontractor profile so staff can quickly see what documents are available.</p>',
            ],
            [
                'title' => '4. Subcontractor Contracts',
                'content' => '<p>Create subcontractor agreements using templates. Select subcontractor, project, contract type, value, dates, status, customer visibility, and attachments. Contracts support merge fields, initials, company signature, subcontractor signature, signed date, signed time, IP stamp, PDF view, print view, and internal notes.</p>',
            ],
            [
                'title' => '5. Templates',
                'content' => '<p>Templates are reusable agreement layouts. Use templates for general subcontractor agreements, trade-specific agreements, lien waivers, final affidavits, document requests, permit cancellation, and credit card authorization forms. Templates can be viewed, edited, copied, deleted, and inserted into new contracts.</p>',
            ],
            [
                'title' => '6. Filters And Tables',
                'content' => '<p>Use filters to narrow subcontractors by company, contact, trade, status, insurance expiration, project, contract type, value, and trash status. Use the table search and page length controls to review 10, 25, 50, 100, or all records depending on your work session.</p>',
            ],
            [
                'title' => '7. Email And SMS Testing',
                'content' => '<p>Use Settings > Email And SMS Testing to confirm the CRM can send subcontractor notifications. Email uses the CRM email configuration. SMS uses the active Perfex SMS gateway. If SMS fails, confirm phone number, message body, Twilio/SMS provider settings, and gateway activation.</p>',
            ],
            [
                'title' => '8. Health Verification And Repair',
                'content' => '<p>Use Settings > Repair Database Tables when an upgrade adds new fields or when a table is missing. Health Verification confirms required tables, upload folders, module status, email testing, SMS testing, and portal availability.</p>',
            ],
        ];
    }

    public function project_widget($project_id)
    {
        if (!has_permission('smartsource_subcontractors', '', 'view') && !has_permission('smartsource_subcontractors', '', 'view_own')) {
            echo '';
            return;
        }
        $data['project_id'] = (int) $project_id;
        $data['subcontractors'] = $this->smartsource_subcontractors_model->get_by_project($project_id);
        $data['contracts'] = $this->smartsource_subcontractors_model->get_contracts_for_project($project_id);
        $this->load->view('admin/projects/widget', $data);
    }
}
