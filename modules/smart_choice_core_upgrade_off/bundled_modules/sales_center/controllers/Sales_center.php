<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_center extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sales_center_model');
        $this->load->helper('sales_center/sales_center');
    }

    private function ensure_enabled()
    {
        if (get_option('sales_center_enabled') !== '1' && $this->uri->segment(3) !== 'settings') {
            set_alert('warning', _l('sales_center_module_disabled_notice'));
            redirect(admin_url('sales_center/settings'));
        }
    }

    public function index()
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'view') && !has_permission('sales_center', '', 'view_own')) {
            access_denied('sales_center');
        }

        $data['title'] = 'Sales Representatives';
        $data['salespersons'] = $this->sales_center_model->get();
        $data['stats'] = $this->sales_center_model->get_stats();
        $this->load->view('admin/salespersons/manage', $data);
    }

    public function salesperson($id = '')
    {
        $this->ensure_enabled();
        if ($id === '') {
            if (!has_permission('sales_center', '', 'create')) {
                access_denied('sales_center');
            }
        } else {
            if (!has_permission('sales_center', '', 'edit') && !has_permission('sales_center', '', 'view')) {
                access_denied('sales_center');
            }
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $profileImage = $this->handle_profile_image_upload($id);
            if ($profileImage !== '') {
                $data['profile_image'] = $profileImage;
            }
            if ($id === '') {
                $id = $this->sales_center_model->add($data);
                set_alert('success', _l('added_successfully', _l('sales_center_salesperson')));
            } else {
                $this->sales_center_model->update($data, $id);
                set_alert('success', _l('updated_successfully', _l('sales_center_salesperson')));
            }
            $this->sales_center_model->get_or_create_portal_token($id);
            redirect(admin_url('sales_center/view/' . $id));
        }

        $data['title'] = $id === '' ? 'New Salesperson' : 'Edit Salesperson';
        $data['salesperson'] = $id === '' ? null : $this->sales_center_model->get($id);
        $data['categories'] = $this->sales_center_model->get_categories();
        $data['statuses'] = $this->sales_center_model->get_salesperson_statuses_db();
        $data['staff_members'] = $this->db->select('staffid, firstname, lastname, email')->where('active', 1)->order_by('firstname', 'ASC')->get(db_prefix() . 'staff')->result_array();
        $this->load->view('admin/salespersons/form', $data);
    }

    public function view($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'view') && !has_permission('sales_center', '', 'view_own')) {
            access_denied('sales_center');
        }

        $salesperson = $this->sales_center_model->get($id);
        if (!$salesperson) {
            show_404();
        }

        $data['title'] = $salesperson->company;
        $data['salesperson'] = $salesperson;
        $data['contracts'] = $this->sales_center_model->get_contract('', ['salesperson_id' => (int) $id]);
        $data['files'] = $this->sales_center_model->get_files($id, 'salesperson');
        $this->load->view('admin/salespersons/view', $data);
    }

    public function delete($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'delete')) {
            access_denied('sales_center');
        }
        $this->sales_center_model->delete($id);
        set_alert('success', _l('deleted', _l('sales_center_salesperson')));
        redirect(admin_url('sales_center'));
    }

    public function contracts()
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center_contracts', '', 'view') && !has_permission('sales_center_contracts', '', 'view_own')) {
            access_denied('sales_center_contracts');
        }

        $filters = [
            'status' => $this->input->get('status'),
            'contract_type' => $this->input->get('contract_type'),
            'include_trash' => $this->input->get('include_trash'),
            'stat' => $this->input->get('stat'),
        ];

        $data['title'] = 'Sales Agreements';
        $data['contracts'] = $this->sales_center_model->get_contract('', $filters);
        $data['stats'] = $this->sales_center_model->get_stats();
        $data['chart_data'] = $this->sales_center_model->get_chart_data();
        $data['statuses'] = $this->sales_center_model->get_contract_statuses_db();
        $this->load->view('admin/contracts/manage', $data);
    }

    public function contract($id = '')
    {
        $this->ensure_enabled();
        if ($id === '') {
            if (!has_permission('sales_center_contracts', '', 'create')) {
                access_denied('sales_center_contracts');
            }
        } else {
            if (!has_permission('sales_center_contracts', '', 'edit') && !has_permission('sales_center_contracts', '', 'view')) {
                access_denied('sales_center_contracts');
            }
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id === '') {
                $id = $this->sales_center_model->add_contract($data);
                set_alert('success', _l('added_successfully', _l('sales_center_contract')));
            } else {
                $this->sales_center_model->update_contract($data, $id);
                set_alert('success', _l('updated_successfully', _l('sales_center_contract')));
            }
            $this->send_contract_notifications($id, $data);
            redirect(admin_url('sales_center/contract_view/' . $id));
        }

        $data['title'] = $id === '' ? 'New Salesperson Contract' : 'Edit Salesperson Contract';
        $data['contract'] = $id === '' ? null : $this->sales_center_model->get_contract($id);
        $data['salespersons'] = $this->sales_center_model->get();
        $data['projects'] = $this->db->select('id,name')->order_by('name', 'ASC')->get(db_prefix() . 'projects')->result_array();
        $data['statuses'] = $this->sales_center_model->get_contract_statuses_db();
        $data['templates'] = $this->sales_center_model->get_templates();
        $this->load->view('admin/contracts/form', $data);
    }

    public function contract_view($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center_contracts', '', 'view') && !has_permission('sales_center_contracts', '', 'view_own')) {
            access_denied('sales_center_contracts');
        }

        $contract = $this->sales_center_model->get_contract($id);
        if (!$contract) {
            show_404();
        }

        $data['title'] = $contract->subject;
        $data['contract'] = $contract;
        $data['files'] = $this->sales_center_model->get_files($id, 'contract');
        $this->load->view('admin/contracts/view', $data);
    }


    public function sign_contract($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center_contracts', '', 'edit')) {
            access_denied('sales_center_contracts');
        }

        if ($this->input->post()) {
            $role = $this->input->post('signature_role') === 'company' ? 'company' : 'salesperson';
            $initials = trim((string) $this->input->post('initials'));
            $signature = (string) $this->input->post('signature_data');
            $this->sales_center_model->save_contract_signature($id, $role, $initials, $signature);
            set_alert('success', _l('sales_center_signature_saved'));
        }
        redirect(admin_url('sales_center/contract_view/' . (int) $id));
    }

    public function contract_pdf($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center_contracts', '', 'view') && !has_permission('sales_center_contracts', '', 'view_own')) {
            access_denied('sales_center_contracts');
        }
        $contract = $this->sales_center_model->get_contract($id);
        if (!$contract) {
            show_404();
        }
        $data['contract'] = $contract;
        $data['content'] = $this->sales_center_model->render_contract_content($contract);
        $this->load->view('admin/contracts/pdf', $data);
    }

    public function contract_print($id)
    {
        $this->contract_pdf($id);
    }

    public function contract_delete($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center_contracts', '', 'delete')) {
            access_denied('sales_center_contracts');
        }
        $this->sales_center_model->trash_contract($id);
        set_alert('success', _l('sales_center_contract_sent_to_trash'));
        redirect(admin_url('sales_center/contracts'));
    }

    public function restore_contract($id)
    {
        if (!has_permission('sales_center_contracts', '', 'delete')) {
            access_denied('sales_center_contracts');
        }
        $this->sales_center_model->restore_contract($id);
        set_alert('success', _l('sales_center_contract_restored'));
        redirect(admin_url('sales_center/contracts?include_trash=1'));
    }

    public function permanent_delete_contract($id)
    {
        if (!has_permission('sales_center_contracts', '', 'delete')) {
            access_denied('sales_center_contracts');
        }
        $this->sales_center_model->permanent_delete_contract($id);
        set_alert('success', _l('deleted', _l('sales_center_contract')));
        redirect(admin_url('sales_center/contracts?include_trash=1'));
    }

    public function upload_file($rel_type, $rel_id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'edit') && !has_permission('sales_center_contracts', '', 'edit')) {
            access_denied('sales_center');
        }

        $rel_type = in_array($rel_type, ['salesperson', 'contract'], true) ? $rel_type : 'salesperson';
        $path = SALES_CENTER_UPLOAD_FOLDER . $rel_type . '/' . (int) $rel_id . '/';

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
                    $this->sales_center_model->add_file($rel_id, $rel_type, [
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
                $this->sales_center_model->add_file($rel_id, $rel_type, [
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

        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('sales_center'));
    }

    public function delete_file($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'delete') && !has_permission('sales_center_contracts', '', 'delete')) {
            access_denied('sales_center');
        }
        $this->sales_center_model->delete_file($id);
        set_alert('success', _l('deleted', _l('file')));
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('sales_center'));
    }

    public function templates($id = '')
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center_contracts', '', 'view')) {
            access_denied('sales_center_contracts');
        }
        if ($this->input->post()) {
            if ($id === '') {
                $this->sales_center_model->add_template($this->input->post());
                set_alert('success', _l('added_successfully', _l('sales_center_template')));
            } else {
                $this->sales_center_model->update_template($this->input->post(), $id);
                set_alert('success', _l('updated_successfully', _l('sales_center_template')));
            }
            redirect(admin_url('sales_center/templates'));
        }
        $data['title'] = 'Salesperson Templates';
        $data['templates'] = $this->sales_center_model->get_templates();
        $data['template'] = $id === '' ? null : $this->sales_center_model->get_templates($id);
        $this->load->view('admin/templates/manage', $data);
    }

    public function template_view($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center_contracts', '', 'view')) {
            access_denied('sales_center_contracts');
        }
        $data['title'] = 'View Salesperson Template';
        $data['template'] = $this->sales_center_model->get_templates($id);
        if (!$data['template']) {
            show_404();
        }
        $this->load->view('admin/templates/view', $data);
    }

    public function copy_template($id)
    {
        $this->sales_center_model->copy_template($id);
        set_alert('success', _l('sales_center_template_copied'));
        redirect(admin_url('sales_center/templates'));
    }

    public function delete_template($id)
    {
        $this->sales_center_model->delete_template($id);
        set_alert('success', _l('deleted', _l('sales_center_template')));
        redirect(admin_url('sales_center/templates'));
    }

    public function settings()
    {
        if (!has_permission('sales_center', '', 'view')) {
            access_denied('sales_center');
        }

        if ($this->input->post('sales_center_test_email')) {
            $this->send_test_email();
            return;
        }

        if ($this->input->post('sales_center_test_sms')) {
            $this->send_test_sms();
            return;
        }

        if ($this->input->post('settings')) {
            update_option('sales_center_enabled', $this->input->post('sales_center_enabled') ? '1' : '0');
            update_option('sales_center_delete_data_on_uninstall', $this->input->post('sales_center_delete_data_on_uninstall') ? '1' : '0');
            update_option('sales_center_hide_core_sales_menu', $this->input->post('sales_center_hide_core_sales_menu') ? '1' : '0');
            update_option('sales_center_hide_core_proposals', $this->input->post('sales_center_hide_core_proposals') ? '1' : '0');
            update_option('sales_center_hide_core_estimates', $this->input->post('sales_center_hide_core_estimates') ? '1' : '0');
            update_option('sales_center_hide_core_invoices', $this->input->post('sales_center_hide_core_invoices') ? '1' : '0');
            update_option('sales_center_hide_core_payments', $this->input->post('sales_center_hide_core_payments') ? '1' : '0');
            update_option('sales_center_hide_core_credit_notes', $this->input->post('sales_center_hide_core_credit_notes') ? '1' : '0');
            update_option('sales_center_hide_core_items', $this->input->post('sales_center_hide_core_items') ? '1' : '0');
            update_option('sales_center_view_mode', $this->input->post('sales_center_view_mode') ?: 'combined');
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('sales_center/settings'));
        }

        if ($this->input->post('category_name')) {
            $this->sales_center_model->save_simple_record('sales_center_categories', [
                'name' => $this->input->post('category_name'),
                'color' => $this->input->post('category_color') ?: '#169179',
            ]);
            redirect(admin_url('sales_center/settings'));
        }

        if ($this->input->post('status_name')) {
            $this->sales_center_model->save_simple_record('sales_center_statuses', [
                'name' => $this->input->post('status_name'),
                'slug' => url_title($this->input->post('status_name'), '_', true),
                'color' => $this->input->post('status_color') ?: '#169179',
            ]);
            redirect(admin_url('sales_center/settings'));
        }

        if ($this->input->post('contract_status_name')) {
            $this->sales_center_model->save_simple_record('sales_center_contract_statuses', [
                'name' => $this->input->post('contract_status_name'),
                'slug' => url_title($this->input->post('contract_status_name'), '_', true),
                'color' => $this->input->post('contract_status_color') ?: '#169179',
            ]);
            redirect(admin_url('sales_center/settings'));
        }

        $data['title'] = 'Sales Center Settings';
        $data['categories'] = $this->sales_center_model->get_categories();
        $data['statuses'] = $this->sales_center_model->get_salesperson_statuses_db();
        $data['staff_members'] = $this->db->select('staffid, firstname, lastname, email')->where('active', 1)->order_by('firstname', 'ASC')->get(db_prefix() . 'staff')->result_array();
        $data['contract_statuses'] = $this->sales_center_model->get_contract_statuses_db();
        $data['health'] = $this->sales_center_model->health_check();
        $this->load->view('admin/settings/manage', $data);
    }

    public function delete_setting_record($table, $id)
    {
        $allowed = ['sales_center_categories', 'sales_center_statuses', 'sales_center_contract_statuses'];
        if (in_array($table, $allowed, true)) {
            $this->sales_center_model->delete_simple_record($table, $id);
        }
        redirect(admin_url('sales_center/settings'));
    }


    public function health_check()
    {
        redirect(admin_url('sales_center/settings#sales-center-health-check'));
    }

    public function repair_database()
    {
        if (!is_admin()) {
            access_denied('sales_center');
        }

        require_once module_dir_path('sales_center', 'install.php');
        set_alert('success', 'Salesperson module database tables repaired successfully.');
        redirect(admin_url('sales_center/settings'));
    }

    public function portal_links()
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'view')) {
            access_denied('sales_center');
        }
        $salespersons = $this->sales_center_model->get();
        foreach ($salespersons as &$salesperson) {
            if (empty($salesperson['portal_token'])) {
                $salesperson['portal_token'] = $this->sales_center_model->get_or_create_portal_token($salesperson['id']);
            }
        }
        $data['title'] = 'Salesperson Portal';
        $data['salespersons'] = $salespersons;
        $data['portal_index_link'] = sales_center_portal_register_url();
        $this->load->view('admin/salespersons/portal_links', $data);
    }

    private function send_contract_notifications($id, $data)
    {
        $recipients = [];
        if (!empty($data['send_to_salesperson'])) {
            $contract = $this->sales_center_model->get_contract($id);
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
            $subject = 'Smart Choice Salesperson Contract: ' . ($contract->subject ?? ('Contract #' . (int) $id));
            $link = admin_url('sales_center/contract_view/' . (int) $id);
            $message = '<p>A salesperson contract has been created or updated.</p>';
            $message .= '<p><strong>Subject:</strong> ' . html_escape($contract->subject ?? '') . '</p>';
            $message .= '<p><strong>Salesperson:</strong> ' . html_escape($contract->salesperson_company ?? '') . '</p>';
            $message .= '<p><a href="' . $link . '">Open Contract In CRM</a></p>';
            if (function_exists('sales_center_send_basic_email')) {
                sales_center_send_basic_email($email, $subject, $message);
            }
            log_activity('Smartsource Salesperson Contract Notification Sent [Contract ID: ' . (int) $id . ', Email: ' . $email . ']');
        }
    }

    private function handle_profile_image_upload($id = '')
    {
        if (empty($_FILES['profile_image_file']['name'])) {
            return '';
        }
        $relId = is_numeric($id) ? (int) $id : 0;
        $path = SALES_CENTER_UPLOAD_FOLDER . 'profiles/';
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $original = $_FILES['profile_image_file']['name'];
        $ext = pathinfo($original, PATHINFO_EXTENSION);
        $fileName = 'salesperson-profile-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.' . strtolower($ext ?: 'png');
        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
        if (move_uploaded_file($_FILES['profile_image_file']['tmp_name'], $path . $fileName)) {
            return 'uploads/sales_center/profiles/' . $fileName;
        }
        return '';
    }

    private function send_test_email()
    {
        $to = trim((string) $this->input->post('test_email_to'));
        if ($to === '') {
            set_alert('danger', 'Please enter an email address to test.');
            redirect(admin_url('sales_center/settings'));
        }

        $subject = 'SmartSource Salespersons Email Test';
        $message = '<p>This is a test email from the SmartSource Salespersons module.</p>';
        $message .= '<p>If you received this message, the CRM email delivery path is working for this module.</p>';
        $sent = false;
        if (function_exists('sales_center_send_basic_email')) {
            $sent = sales_center_send_basic_email($to, $subject, $message);
        }

        if ($sent) {
            set_alert('success', 'Test email sent to ' . $to . '. Check inbox and spam folder.');
        } else {
            set_alert('danger', 'Test email was not sent. Check SMTP settings under Setup > Settings > Email.');
        }
        redirect(admin_url('sales_center/settings'));
    }

    private function send_test_sms()
    {
        $to = trim((string) $this->input->post('test_sms_to'));
        $message = trim((string) $this->input->post('test_sms_message'));

        if ($to === '') {
            set_alert('danger', 'Please enter a phone number to test SMS.');
            redirect(admin_url('sales_center/settings'));
        }

        if ($message === '') {
            $message = 'SmartSource Salespersons SMS test from Smart Choice CRM.';
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

        redirect(admin_url('sales_center/settings'));
    }

    public function help()
    {
        if (!has_permission('sales_center', '', 'view')) {
            access_denied('sales_center');
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

            update_option('sales_center_help_sections_json', json_encode($sections));
            set_alert('success', 'Help Guide instructions updated successfully.');
            redirect(admin_url('sales_center/help'));
        }

        $sections = json_decode((string) get_option('sales_center_help_sections_json'), true);
        if (!is_array($sections) || empty($sections)) {
            $sections = $this->default_help_sections();
        }

        $data['title'] = 'Salespersons Help Guide';
        $data['help_sections'] = $sections;
        $this->load->view('admin/help/manual', $data);
    }

    private function default_help_sections()
    {
        return [
            [
                'title' => '1. Salesperson Records',
                'content' => '<p>Create a salesperson profile for every company, independent installer, crew leader, supplier representative, or trade partner. Add company name, contact name, phone, email, trade, status, license number, DBPR link, county license verification link, insurance expiration, address, assigned staff, notes, profile photo, and portal access status.</p>',
            ],
            [
                'title' => '2. Public Salesperson Portal',
                'content' => '<p>Use the Salesperson Portal page to copy the public registration link and send it to a new salesperson. The salesperson can create a profile, upload multiple documents, select English or Spanish, upload a profile picture, and submit license and insurance information. Portal submissions create staff notifications and email alerts when CRM email is configured.</p>',
            ],
            [
                'title' => '3. Documents And Files',
                'content' => '<p>Upload W-9 forms, insurance certificates, DBPR license copies, county registrations, photos, driver license copies, OSHA cards, signed agreements, project photos, and other supporting documents. File names appear in the salesperson profile so staff can quickly see what documents are available.</p>',
            ],
            [
                'title' => '4. Salesperson Contracts',
                'content' => '<p>Create salesperson agreements using templates. Select salesperson, project, contract type, value, dates, status, customer visibility, and attachments. Contracts support merge fields, initials, company signature, salesperson signature, signed date, signed time, IP stamp, PDF view, print view, and internal notes.</p>',
            ],
            [
                'title' => '5. Templates',
                'content' => '<p>Templates are reusable agreement layouts. Use templates for general salesperson agreements, trade-specific agreements, lien waivers, final affidavits, document requests, permit cancellation, and credit card authorization forms. Templates can be viewed, edited, copied, deleted, and inserted into new contracts.</p>',
            ],
            [
                'title' => '6. Filters And Tables',
                'content' => '<p>Use filters to narrow salespersons by company, contact, trade, status, insurance expiration, project, contract type, value, and trash status. Use the table search and page length controls to review 10, 25, 50, 100, or all records depending on your work session.</p>',
            ],
            [
                'title' => '7. Email And SMS Testing',
                'content' => '<p>Use Settings > Email And SMS Testing to confirm the CRM can send salesperson notifications. Email uses the CRM email configuration. SMS uses the active Perfex SMS gateway. If SMS fails, confirm phone number, message body, Twilio/SMS provider settings, and gateway activation.</p>',
            ],
            [
                'title' => '8. Health Verification And Repair',
                'content' => '<p>Use Settings > Repair Database Tables when an upgrade adds new fields or when a table is missing. Health Verification confirms required tables, upload folders, module status, email testing, SMS testing, and portal availability.</p>',
            ],
        ];
    }

    public function project_widget($project_id)
    {
        if (!has_permission('sales_center', '', 'view') && !has_permission('sales_center', '', 'view_own')) {
            echo '';
            return;
        }
        $data['project_id'] = (int) $project_id;
        $data['salespersons'] = $this->sales_center_model->get_by_project($project_id);
        $data['contracts'] = $this->sales_center_model->get_contracts_for_project($project_id);
        $this->load->view('admin/projects/widget', $data);
    }

    public function reports()
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'view') && !has_permission('sales_center', '', 'view_own') && !has_permission('sales_center', '', 'view_department')) {
            access_denied('sales_center');
        }

        $filters = [
            'salesperson_id' => $this->input->get('salesperson_id'),
            'status' => $this->input->get('status'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'manager_id' => $this->input->get('manager_id'),
            'department_id' => $this->input->get('department_id'),
        ];

        $data['title'] = 'Sales Reports';
        $data['salespeople'] = $this->sales_center_model->get();
        $data['sales_managers'] = method_exists($this->sales_center_model, 'get_sales_managers') ? $this->sales_center_model->get_sales_managers() : [];
        $data['departments'] = method_exists($this->sales_center_model, 'get_departments_list') ? $this->sales_center_model->get_departments_list() : [];
        $data['invoices'] = method_exists($this->sales_center_model, 'get_invoice_options') ? $this->sales_center_model->get_invoice_options() : [];
        $data['filters'] = $filters;
        $data['summary'] = method_exists($this->sales_center_model, 'get_sales_summary') ? $this->sales_center_model->get_sales_summary($filters) : [];
        $data['rows'] = method_exists($this->sales_center_model, 'get_sales_report_rows') ? $this->sales_center_model->get_sales_report_rows($filters) : [];
        $this->load->view('admin/reports/manage', $data);
    }


    public function save_commission()
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'edit') && !has_permission('sales_center', '', 'create')) {
            access_denied('sales_center');
        }
        if ($this->input->post()) {
            $id = $this->sales_center_model->save_commission_record($this->input->post());
            if ($id) {
                set_alert('success', 'Sales commission assignment saved successfully.');
            } else {
                set_alert('danger', 'Sales commission assignment was not saved. Select an invoice and a salesperson.');
            }
        }
        redirect(admin_url('sales_center/reports'));
    }

    public function delete_commission($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'delete')) {
            access_denied('sales_center');
        }
        $this->sales_center_model->delete_commission_record($id);
        set_alert('success', 'Sales commission assignment deleted.');
        redirect(admin_url('sales_center/reports'));
    }


    public function documents()
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'view') && !has_permission('sales_center', '', 'view_own') && !has_permission('sales_center', '', 'view_department')) {
            access_denied('sales_center');
        }

        $filters = [
            'salesperson_id' => $this->input->get('salesperson_id'),
            'rel_type' => $this->input->get('rel_type'),
            'status' => $this->input->get('status'),
        ];

        $data['title'] = 'Sales Documents';
        $data['salespeople'] = $this->sales_center_model->get();
        $data['sales_managers'] = method_exists($this->sales_center_model, 'get_sales_managers') ? $this->sales_center_model->get_sales_managers() : [];
        $data['departments'] = method_exists($this->sales_center_model, 'get_departments_list') ? $this->sales_center_model->get_departments_list() : [];
        $data['document_options'] = method_exists($this->sales_center_model, 'get_sales_document_options') ? $this->sales_center_model->get_sales_document_options($filters['rel_type'] ?? '') : [];
        $data['documents'] = method_exists($this->sales_center_model, 'get_sales_documents') ? $this->sales_center_model->get_sales_documents($filters) : [];
        $data['links'] = method_exists($this->sales_center_model, 'get_sales_document_links') ? $this->sales_center_model->get_sales_document_links($filters) : [];
        $data['filters'] = $filters;
        $this->load->view('admin/reports/documents', $data);
    }

    public function save_document_link()
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'edit') && !has_permission('sales_center', '', 'create')) {
            access_denied('sales_center');
        }
        if ($this->input->post() && method_exists($this->sales_center_model, 'save_sales_document_link')) {
            $id = $this->sales_center_model->save_sales_document_link($this->input->post());
            set_alert($id ? 'success' : 'danger', $id ? 'Sales document assignment saved successfully.' : 'Sales document assignment was not saved. Select a document and a sales representative.');
        }
        redirect(admin_url('sales_center/documents'));
    }

    public function delete_document_link($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'delete')) {
            access_denied('sales_center');
        }
        if (method_exists($this->sales_center_model, 'delete_sales_document_link')) {
            $this->sales_center_model->delete_sales_document_link($id);
        }
        set_alert('success', 'Sales document assignment deleted.');
        redirect(admin_url('sales_center/documents'));
    }

    public function document_pdf($type, $id, $mode = 'view')
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'view')) {
            access_denied('sales_center');
        }
        $download = $mode === 'download';
        if (method_exists($this->sales_center_model, 'sales_document_pdf_url')) {
            redirect($this->sales_center_model->sales_document_pdf_url($type, (int)$id, $download));
        }
        redirect(admin_url('sales_center/documents'));
    }

    public function view_as_salesperson($id)
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'view') && !has_permission('sales_center', '', 'view_own')) {
            access_denied('sales_center');
        }
        redirect(admin_url('sales_center/view/' . (int)$id . '?salesperson_view=1'));
    }


    public function table_export($target = 'documents')
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'view') && !has_permission('sales_center_contracts', '', 'view')) {
            access_denied('sales_center');
        }
        $target = $this->normalize_table_target($target);
        $rows = $this->sales_center_table_rows($target);
        $filename = 'sales-hub-' . $target . '-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        if (!empty($rows)) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
        } else {
            fputcsv($out, ['message']);
            fputcsv($out, ['No records found.']);
        }
        fclose($out);
        exit;
    }

    public function sample_file($target = 'documents')
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'view') && !has_permission('sales_center_contracts', '', 'view')) {
            access_denied('sales_center');
        }
        $target = $this->normalize_table_target($target);
        $headers = $this->sales_center_sample_headers($target);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="sales-hub-' . $target . '-sample-header.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        fputcsv($out, array_map(function ($header) { return 'sample_' . strtolower(preg_replace('/[^a-z0-9]+/i', '_', $header)); }, $headers));
        fclose($out);
        exit;
    }

    public function import($target = 'documents')
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'create') && !has_permission('sales_center', '', 'edit') && !has_permission('sales_center_contracts', '', 'create')) {
            access_denied('sales_center');
        }
        $target = $this->normalize_table_target($target);
        $data['title'] = 'Import ' . ucwords(str_replace('_', ' ', $target));
        $data['target'] = $target;
        $data['headers'] = $this->sales_center_sample_headers($target);
        $data['preview_rows'] = [];
        if (!empty($_FILES['import_file']['tmp_name'])) {
            $extension = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));
            if ($extension !== 'csv') {
                set_alert('danger', 'Only CSV files are supported in this safe importer. Download the sample header and save your Excel file as CSV before importing.');
                redirect(admin_url('sales_center/import/' . $target));
            }
            $hasHeader = (int)$this->input->post('has_header') === 1;
            $rows = $this->read_csv_preview($_FILES['import_file']['tmp_name'], $hasHeader, 50);
            $data['preview_rows'] = $rows;
            $data['has_header'] = $hasHeader;
            $data['uploaded_file_name'] = $_FILES['import_file']['name'];
            $cacheKey = 'sales_center_import_' . $target . '_' . get_staff_user_id();
            $this->session->set_userdata($cacheKey, $rows);
        }
        $this->load->view('admin/import/manage', $data);
    }

    public function import_confirm($target = 'documents')
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'create') && !has_permission('sales_center', '', 'edit') && !has_permission('sales_center_contracts', '', 'create')) {
            access_denied('sales_center');
        }
        $target = $this->normalize_table_target($target);
        $cacheKey = 'sales_center_import_' . $target . '_' . get_staff_user_id();
        $rows = $this->session->userdata($cacheKey);
        $count = 0;
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if ($this->save_import_row($target, $row)) {
                    $count++;
                }
            }
        }
        $this->session->unset_userdata($cacheKey);
        set_alert('success', $count . ' records imported successfully.');
        redirect($this->target_redirect_url($target));
    }

    public function mass_delete($target = 'documents')
    {
        $this->ensure_enabled();
        if (!has_permission('sales_center', '', 'delete') && !has_permission('sales_center_contracts', '', 'delete')) {
            access_denied('sales_center');
        }
        $target = $this->normalize_table_target($target);
        $ids = $this->input->post('ids');
        if (!is_array($ids)) {
            $ids = [];
        }
        $deleted = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) { continue; }
            if ($target === 'documents' && method_exists($this->sales_center_model, 'delete_sales_document_link')) {
                $this->sales_center_model->delete_sales_document_link($id); $deleted++;
            } elseif ($target === 'reports') {
                $this->sales_center_model->delete_commission_record($id); $deleted++;
            } elseif ($target === 'contracts') {
                $this->sales_center_model->trash_contract($id); $deleted++;
            } elseif ($target === 'salespersons') {
                $this->sales_center_model->delete($id); $deleted++;
            } elseif ($target === 'templates') {
                $this->sales_center_model->delete_template($id); $deleted++;
            }
        }
        set_alert('success', $deleted . ' selected records processed successfully.');
        redirect($this->target_redirect_url($target));
    }

    private function normalize_table_target($target)
    {
        $target = strtolower(preg_replace('/[^a-z_]/', '', (string)$target));
        $allowed = ['documents','reports','contracts','salespersons','templates','proposals','estimates'];
        return in_array($target, $allowed, true) ? $target : 'documents';
    }

    private function target_redirect_url($target)
    {
        if ($target === 'reports') { return admin_url('sales_center/reports'); }
        if ($target === 'contracts') { return admin_url('sales_center/contracts'); }
        if ($target === 'salespersons') { return admin_url('sales_center'); }
        if ($target === 'templates') { return admin_url('sales_center/templates'); }
        if ($target === 'proposals') { return admin_url('proposals'); }
        if ($target === 'estimates') { return admin_url('estimates'); }
        return admin_url('sales_center/documents');
    }

    private function sales_center_sample_headers($target)
    {
        $headers = [
            'documents' => ['salesperson_id','rel_type','rel_id','document_total','amount_collected','commission_rate','commission_paid','status','issue_notes'],
            'reports' => ['invoice_id','salesperson_id','invoice_total','amount_collected','commission_rate','commission_paid','status','issue_notes'],
            'contracts' => ['salesperson_id','subject','contract_type','project_id','contract_value','start_date','end_date','status','notes'],
            'salespersons' => ['company','contact_name','email','phone','trade','status','license_number','insurance_expiration','address','notes'],
            'templates' => ['name','contract_type','content'],
            'proposals' => ['id','subject','rel_type','rel_id','status','total'],
            'estimates' => ['id','number','clientid','status','total'],
        ];
        return $headers[$target] ?? $headers['documents'];
    }

    private function read_csv_preview($path, $hasHeader, $limit = 50)
    {
        $rows = [];
        $headers = [];
        $handle = fopen($path, 'r');
        if (!$handle) { return $rows; }
        $line = 0;
        while (($data = fgetcsv($handle)) !== false && count($rows) < $limit) {
            $line++;
            if ($line === 1 && $hasHeader) { $headers = $data; continue; }
            if (empty($headers)) { $headers = $this->sales_center_sample_headers($this->normalize_table_target($this->uri->segment(4))); }
            $row = [];
            foreach ($headers as $i => $header) {
                $clean = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', trim((string)$header)));
                $row[$clean] = $data[$i] ?? '';
            }
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function save_import_row($target, $row)
    {
        $now = date('Y-m-d H:i:s');
        if ($target === 'documents') {
            $rel = explode(':', (string)($row['sales_document'] ?? ''));
            $relType = $row['rel_type'] ?? ($rel[0] ?? 'invoice');
            $relId = (int)($row['rel_id'] ?? ($rel[1] ?? 0));
            if ($relId <= 0 || empty($row['salesperson_id'])) { return false; }
            return (bool)$this->sales_center_model->save_sales_document_link([
                'salesperson_id' => (int)$row['salesperson_id'],
                'manager_id' => (int)($row['manager_id'] ?? 0),
                'department_id' => (int)($row['department_id'] ?? 0),
                'sales_document' => $relType . ':' . $relId,
                'document_total' => (float)($row['document_total'] ?? 0),
                'amount_collected' => (float)($row['amount_collected'] ?? 0),
                'commission_rate' => (float)($row['commission_rate'] ?? 0),
                'commission_paid' => (float)($row['commission_paid'] ?? 0),
                'status' => $row['status'] ?? 'pending',
                'issue_notes' => $row['issue_notes'] ?? '',
            ]);
        }
        if ($target === 'reports') {
            if (empty($row['invoice_id']) || empty($row['salesperson_id'])) { return false; }
            return (bool)$this->sales_center_model->save_commission_record($row);
        }
        if ($target === 'contracts') {
            if (empty($row['subject']) || empty($row['salesperson_id'])) { return false; }
            return (bool)$this->sales_center_model->add_contract($row);
        }
        if ($target === 'salespersons') {
            if (empty($row['company'])) { return false; }
            return (bool)$this->sales_center_model->add($row);
        }
        if ($target === 'templates') {
            if (empty($row['name'])) { return false; }
            return (bool)$this->sales_center_model->add_template($row);
        }
        return false;
    }

    private function sales_center_table_rows($target)
    {
        if ($target === 'documents' && method_exists($this->sales_center_model, 'get_sales_document_links')) { return $this->sales_center_model->get_sales_document_links([]); }
        if ($target === 'reports' && method_exists($this->sales_center_model, 'get_sales_report_rows')) { return $this->sales_center_model->get_sales_report_rows([]); }
        if ($target === 'contracts') { return $this->sales_center_model->get_contract('', []); }
        if ($target === 'salespersons') { return $this->sales_center_model->get(); }
        if ($target === 'templates') { return $this->sales_center_model->get_templates(); }
        return [];
    }

}
