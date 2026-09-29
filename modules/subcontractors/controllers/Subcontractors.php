<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Subcontractors extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smartsource_subcontractors_model');
        $this->load->helper('subcontractors/smartsource_subcontractors');
    }

    private function ensure_enabled()
    {
        if (get_option('smartsource_subcontractors_enabled') !== '1' && $this->uri->segment(3) !== 'settings') {
            set_alert('warning', _l('smartsource_module_disabled_notice'));
            redirect(admin_url('settings?group=subcontractors'));
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
            redirect(admin_url('subcontractors/view/' . $id));
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
        redirect(admin_url('subcontractors'));
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
            $data = $this->input->post(null, false);
            try {
                if ($id === '') {
                    $id = $this->smartsource_subcontractors_model->add_contract($data);
                    if (!$id) {
                        throw new RuntimeException('The subcontractor contract could not be created.');
                    }
                    set_alert('success', _l('added_successfully', _l('smartsource_subcontractor_contract')));
                } else {
                    if (!$this->smartsource_subcontractors_model->update_contract($data, $id)) {
                        throw new RuntimeException('The subcontractor contract could not be updated.');
                    }
                    set_alert('success', _l('updated_successfully', _l('smartsource_subcontractor_contract')));
                }
                $this->send_contract_notifications($id, $data);
                redirect(admin_url('subcontractors/contract_view/' . (int) $id));
            } catch (Throwable $e) {
                log_message('error', 'Smartsource subcontractor contract save failed: ' . $e->getMessage());
                set_alert('danger', $e->getMessage());
                redirect($id === '' ? admin_url('subcontractors/contract') : admin_url('subcontractors/contract/' . (int) $id));
            }
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
        $data['comments'] = $this->smartsource_subcontractors_model->get_contract_comments($id, true);
        $data['public_url'] = site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($this->smartsource_subcontractors_model->ensure_contract_public_token($id)));
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
            try {
                $this->smartsource_subcontractors_model->save_contract_signature(
                    (int) $id,
                    $role,
                    trim((string) $this->input->post('initials')),
                    (string) $this->input->post('signature_data', false),
                    (string) $this->input->post('initials_signature_data', false),
                    [
                        'acceptance_firstname' => trim((string) $this->input->post('acceptance_firstname')),
                        'acceptance_lastname'  => trim((string) $this->input->post('acceptance_lastname')),
                        'acceptance_email'     => trim((string) $this->input->post('acceptance_email')),
                    ]
                );
                set_alert('success', _l('smartsource_signature_saved'));
            } catch (Throwable $e) {
                log_message('error', 'Smartsource subcontractor signature failed [Contract ID: ' . (int) $id . ']: ' . $e->getMessage());
                set_alert('danger', $e->getMessage());
            }
        }
        redirect(admin_url('subcontractors/contract_view/' . (int) $id));
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
        $data['cover'] = $this->smartsource_subcontractors_model->render_contract_cover($contract);
        $this->load->view('admin/contracts/pdf', $data);
    }

    public function contract_print($id)
    {
        $this->contract_pdf($id);
    }


    public function contract_client_view($id)
    {
        $this->ensure_enabled();
        $contract = $this->smartsource_subcontractors_model->get_contract($id);
        if (!$contract) {
            show_404();
        }
        $token = $this->smartsource_subcontractors_model->ensure_contract_public_token($id);
        $data['title'] = $contract->subject;
        $data['contract'] = $contract;
        $data['content'] = $this->smartsource_subcontractors_model->render_contract_content($contract);
        $data['cover'] = $this->smartsource_subcontractors_model->render_contract_cover($contract);
        $data['files'] = $this->smartsource_subcontractors_model->get_files($id, 'contract');
        $data['comments'] = $this->smartsource_subcontractors_model->get_contract_comments($id, false);
        $data['public_url'] = site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token));
        $this->load->view('admin/contracts/client_view', $data);
    }

    public function contract_public($token = '')
    {
        $contract = $this->smartsource_subcontractors_model->get_contract_by_token($token);
        if (!$contract || !empty($contract->hidden_from_customer) || !empty($contract->is_trash)) {
            show_404();
        }
        $data['title'] = $contract->subject;
        $data['contract'] = $contract;
        $data['content'] = $this->smartsource_subcontractors_model->render_contract_content($contract);
        $data['cover'] = $this->smartsource_subcontractors_model->render_contract_cover($contract);
        $data['files'] = $this->smartsource_subcontractors_model->get_files($contract->id, 'contract');
        $data['comments'] = $this->smartsource_subcontractors_model->get_contract_comments($contract->id, false);
        $this->load->view('admin/contracts/client_view', $data);
    }

    public function contract_public_sign($token = '')
    {
        $contract = $this->smartsource_subcontractors_model->get_contract_by_token($token);
        if (!$contract || !empty($contract->hidden_from_customer) || !empty($contract->is_trash) || !$this->input->post()) {
            show_404();
        }

        $identity = [
            'acceptance_firstname' => trim((string) $this->input->post('acceptance_firstname')),
            'acceptance_lastname'  => trim((string) $this->input->post('acceptance_lastname')),
            'acceptance_email'     => trim((string) $this->input->post('acceptance_email')),
        ];
        $initials = strtoupper(trim((string) $this->input->post('initials')));
        $accepted = (string) $this->input->post('accept_terms') === '1';

        if (!$accepted || $identity['acceptance_firstname'] === '' || $identity['acceptance_lastname'] === '' || !filter_var($identity['acceptance_email'], FILTER_VALIDATE_EMAIL) || $initials === '') {
            set_alert('danger', 'Name, valid email, typed initials, drawn initials, full signature, and acceptance are required.');
            redirect(site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token)));
        }

        try {
            $this->smartsource_subcontractors_model->save_contract_signature(
                (int) $contract->id,
                'subcontractor',
                $initials,
                (string) $this->input->post('signature_data', false),
                (string) $this->input->post('initials_signature_data', false),
                $identity
            );
            log_activity('Smartsource Subcontractor Contract Signed [ID: ' . (int) $contract->id . ']');
            set_alert('success', 'Contract signed successfully.');
        } catch (Throwable $e) {
            log_message('error', 'Public smartsource subcontractor contract signing failed [Contract ID: ' . (int) $contract->id . ']: ' . $e->getMessage());
            set_alert('danger', $e->getMessage());
        }

        redirect(site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token)));
    }

    public function email_contract($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'view')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $contract = $this->smartsource_subcontractors_model->get_contract($id);
        if (!$contract) {
            show_404();
        }
        $to = trim((string)($this->input->post('email_to') ?: ($contract->email ?? '')));
        if ($this->input->post()) {
            if ($to === '') {
                set_alert('warning', 'No recipient email was found.');
            } else {
                $message = $this->input->post('message') ?: 'Please review the attached subcontractor contract.';
                $publicUrl = site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($this->smartsource_subcontractors_model->ensure_contract_public_token($id)));
                $body = nl2br(html_escape($message)) . '<br><br><a href="' . $publicUrl . '">View Contract</a>';
                $this->load->config('email');
                $this->email->from(get_option('smtp_email'), get_option('companyname'));
                $this->email->to($to);
                $this->email->subject($this->input->post('subject') ?: $contract->subject);
                $this->email->message($body);
                if ($this->email->send()) {
                    $this->smartsource_subcontractors_model->mark_contract_emailed($id);
                    set_alert('success', 'Contract email sent.');
                } else {
                    set_alert('danger', 'The contract email could not be sent. Check CRM email settings.');
                }
            }
            redirect(admin_url('subcontractors/contract_view/' . (int)$id));
        }
        $data['title'] = 'Email Subcontractor Contract';
        $data['contract'] = $contract;
        $data['to'] = $to;
        $data['public_url'] = site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($this->smartsource_subcontractors_model->ensure_contract_public_token($id)));
        $this->load->view('admin/contracts/email', $data);
    }

    public function contract_xml($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'view')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $contract = $this->smartsource_subcontractors_model->get_contract($id);
        if (!$contract) { show_404(); }
        $this->smartsource_subcontractors_model->mark_contract_xml_generated($id);
        $xml = $this->smartsource_subcontractors_model->contract_to_xml($contract);
        $this->output->set_content_type('application/xml')->set_header('Content-Disposition: attachment; filename="subcontractor-contract-' . (int)$id . '.xml"')->set_output($xml);
    }

    public function add_contract_comment($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'edit')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $comment = trim((string)$this->input->post('comment'));
        $is_internal = $this->input->post('is_internal') ? 1 : 0;
        if ($comment !== '') {
            $this->smartsource_subcontractors_model->add_contract_comment($id, $comment, $is_internal);
            set_alert('success', $is_internal ? 'Internal note added.' : 'Customer-visible comment added.');
        }
        redirect(admin_url('subcontractors/contract_view/' . (int)$id));
    }

    public function contract_public_comment($token = '')
    {
        $contract = $this->smartsource_subcontractors_model->get_contract_by_token($token);
        if (!$contract || !empty($contract->hidden_from_customer) || !empty($contract->is_trash)) {
            show_404();
        }
        $comment = trim((string)$this->input->post('comment'));
        $name = trim((string)$this->input->post('customer_name'));
        $email = trim((string)$this->input->post('customer_email'));
        if ($comment !== '') {
            $this->smartsource_subcontractors_model->add_contract_comment($contract->id, $comment, 0, $name, $email);
        }
        redirect(site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token)) . '#contract-comments');
    }

    public function contract_delete($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'delete')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $this->smartsource_subcontractors_model->trash_contract($id);
        set_alert('success', _l('smartsource_contract_sent_to_trash'));
        redirect(admin_url('subcontractors/contracts'));
    }

    public function restore_contract($id)
    {
        if (!has_permission('smartsource_subcontractor_contracts', '', 'delete')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $this->smartsource_subcontractors_model->restore_contract($id);
        set_alert('success', _l('smartsource_contract_restored'));
        redirect(admin_url('subcontractors/contracts?include_trash=1'));
    }

    public function permanent_delete_contract($id)
    {
        if (!has_permission('smartsource_subcontractor_contracts', '', 'delete')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $this->smartsource_subcontractors_model->permanent_delete_contract($id);
        set_alert('success', _l('deleted', _l('smartsource_subcontractor_contract')));
        redirect(admin_url('subcontractors/contracts?include_trash=1'));
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

        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('subcontractors'));
    }

    public function delete_file($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractors', '', 'delete') && !has_permission('smartsource_subcontractor_contracts', '', 'delete')) {
            access_denied('smartsource_subcontractors');
        }
        $this->smartsource_subcontractors_model->delete_file($id);
        set_alert('success', _l('deleted', _l('file')));
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('subcontractors'));
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
            redirect(admin_url('subcontractors/templates'));
        }
        $data['title'] = _l('smartsource_subcontractor_templates');
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
        $data['title'] = _l('smartsource_view_template');
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
        redirect(admin_url('subcontractors/templates'));
    }

    public function delete_template($id)
    {
        $this->smartsource_subcontractors_model->delete_template($id);
        set_alert('success', _l('deleted', _l('smartsource_template')));
        redirect(admin_url('subcontractors/templates'));
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
            update_option('smartsource_application_screen_alerts', $this->input->post('smartsource_application_screen_alerts') ? '1' : '0');
            update_option('smartsource_application_alert_sound', $this->input->post('smartsource_application_alert_sound') ? '1' : '0');
            update_option('smartsource_application_create_task', $this->input->post('smartsource_application_create_task') ? '1' : '0');
            update_option('smartsource_application_send_email', $this->input->post('smartsource_application_send_email') ? '1' : '0');
            update_option('smartsource_application_send_sms', $this->input->post('smartsource_application_send_sms') ? '1' : '0');
            update_option('smartsource_application_alert_poll_seconds', max(10, (int) $this->input->post('smartsource_application_alert_poll_seconds')));
            update_option('smartsource_application_notify_staff', implode(',', array_map('intval', (array) $this->input->post('smartsource_application_notify_staff'))));
            update_option('smartsource_application_task_assigned_staff', implode(',', array_map('intval', (array) $this->input->post('smartsource_application_task_assigned_staff'))));
            update_option('smartsource_application_email_subject', trim((string) $this->input->post('smartsource_application_email_subject')));
            update_option('smartsource_application_email_body', (string) $this->input->post('smartsource_application_email_body', false));
            update_option('smartsource_application_sms_body', trim((string) $this->input->post('smartsource_application_sms_body')));
            update_option('smartsource_application_notify_staff_sms', $this->input->post('smartsource_application_notify_staff_sms') ? '1' : '0');
            update_option('smartsource_telegram_enabled', $this->input->post('smartsource_telegram_enabled') ? '1' : '0');
            update_option('smartsource_telegram_bot_token', trim((string) $this->input->post('smartsource_telegram_bot_token')));
            update_option('smartsource_telegram_chat_ids', trim((string) $this->input->post('smartsource_telegram_chat_ids')));
            update_option('smartsource_portal_primary_color', trim((string) $this->input->post('smartsource_portal_primary_color')) ?: '#F28C28');
            update_option('smartsource_portal_secondary_color', trim((string) $this->input->post('smartsource_portal_secondary_color')) ?: '#3598DB');
            update_option('smartsource_portal_success_color', trim((string) $this->input->post('smartsource_portal_success_color')) ?: '#169179');
            update_option('smartsource_portal_logo_animation', trim((string) $this->input->post('smartsource_portal_logo_animation')) ?: 'particles');
            update_option('smartsource_portal_success_animation', trim((string) $this->input->post('smartsource_portal_success_animation')) ?: 'paper');
            $questions = [];
            $labelsEn = (array) $this->input->post('portal_question_label_en');
            $labelsEs = (array) $this->input->post('portal_question_label_es');
            $types = (array) $this->input->post('portal_question_type');
            $required = (array) $this->input->post('portal_question_required');
            $active = (array) $this->input->post('portal_question_active');
            foreach ($labelsEn as $index => $labelEn) {
                $labelEn = trim((string) $labelEn);
                $labelEs = trim((string) ($labelsEs[$index] ?? ''));
                if ($labelEn === '' && $labelEs === '') { continue; }
                $questions[] = [
                    'key' => 'q_' . substr(sha1($labelEn . '|' . $labelEs . '|' . $index), 0, 12),
                    'label_en' => $labelEn,
                    'label_es' => $labelEs !== '' ? $labelEs : $labelEn,
                    'type' => in_array(($types[$index] ?? 'text'), ['text','textarea','email','phone','date','select'], true) ? $types[$index] : 'text',
                    'required' => isset($required[$index]) ? 1 : 0,
                    'active' => isset($active[$index]) ? 1 : 0,
                ];
            }
            update_option('smartsource_portal_questions_json', json_encode($questions));
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('settings?group=subcontractors'));
        }

        if ($this->input->post('category_name')) {
            $this->smartsource_subcontractors_model->save_simple_record('smartsource_subcontractor_categories', [
                'name' => $this->input->post('category_name'),
                'color' => $this->input->post('category_color') ?: '#169179',
            ]);
            redirect(admin_url('settings?group=subcontractors'));
        }

        if ($this->input->post('status_name')) {
            $this->smartsource_subcontractors_model->save_simple_record('smartsource_subcontractor_statuses', [
                'name' => $this->input->post('status_name'),
                'slug' => url_title($this->input->post('status_name'), '_', true),
                'color' => $this->input->post('status_color') ?: '#169179',
            ]);
            redirect(admin_url('settings?group=subcontractors'));
        }

        if ($this->input->post('contract_status_name')) {
            $this->smartsource_subcontractors_model->save_simple_record('smartsource_subcontractor_contract_statuses', [
                'name' => $this->input->post('contract_status_name'),
                'slug' => url_title($this->input->post('contract_status_name'), '_', true),
                'color' => $this->input->post('contract_status_color') ?: '#169179',
            ]);
            redirect(admin_url('subcontractors/settings'));
        }

        $data['title'] = _l('smartsource_subcontractor_settings');
        $data['portal_questions'] = json_decode((string) get_option('smartsource_portal_questions_json'), true) ?: [];
        $data['categories'] = $this->smartsource_subcontractors_model->get_categories();
        $data['statuses'] = $this->smartsource_subcontractors_model->get_subcontractor_statuses_db();
        $data['staff_members'] = $this->db->select('staffid, firstname, lastname, email')->where('active', 1)->order_by('firstname', 'ASC')->get(db_prefix() . 'staff')->result_array();
        $data['contract_statuses'] = $this->smartsource_subcontractors_model->get_contract_statuses_db();
        $data['health'] = $this->smartsource_subcontractors_model->health_check();
        $data['pending_application_alerts'] = $this->get_pending_application_alert_count();
        $this->load->view('admin/settings/manage', $data);
    }

    public function delete_setting_record($table, $id)
    {
        $allowed = ['smartsource_subcontractor_categories', 'smartsource_subcontractor_statuses', 'smartsource_subcontractor_contract_statuses'];
        if (in_array($table, $allowed, true)) {
            $this->smartsource_subcontractors_model->delete_simple_record($table, $id);
        }
        redirect(admin_url('subcontractors/settings'));
    }


    public function repair_database()
    {
        if (!is_admin()) {
            access_denied('smartsource_subcontractors');
        }

        require_once module_dir_path('subcontractors', 'install.php');
        set_alert('success', 'Subcontractor module database tables repaired successfully.');
        redirect(admin_url('subcontractors/settings'));
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


    public function contract_pdf_download($id)
    {
        $this->contract_pdf($id);
    }

    public function contract_pdf_print($id)
    {
        $this->contract_pdf($id);
    }

    public function contract_mark_sent($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'edit')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $this->smartsource_subcontractors_model->update_contract_status($id, 'sent');
        set_alert('success', 'Contract marked as sent.');
        redirect(admin_url('subcontractors/contract_view/' . (int) $id));
    }

    public function contract_mark_cancelled($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'edit')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $this->smartsource_subcontractors_model->update_contract_status($id, 'cancelled');
        set_alert('success', 'Contract marked as cancelled.');
        redirect(admin_url('subcontractors/contract_view/' . (int) $id));
    }

    public function contract_mark_signed($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'edit')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $this->smartsource_subcontractors_model->update_contract_status($id, 'signed', ['signed_date' => date('Y-m-d')]);
        set_alert('success', 'Contract marked as signed.');
        redirect(admin_url('subcontractors/contract_view/' . (int) $id));
    }

    public function contract_copy($id)
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'create')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $newId = $this->smartsource_subcontractors_model->copy_contract($id);
        if ($newId) {
            set_alert('success', 'Contract copied successfully.');
            redirect(admin_url('subcontractors/contract/' . (int) $newId));
        }
        set_alert('danger', 'The contract could not be copied.');
        redirect(admin_url('subcontractors/contract_view/' . (int) $id));
    }

    public function contract_bulk_action()
    {
        $this->ensure_enabled();
        $ids = $this->input->post('ids');
        $action = (string) $this->input->post('bulk_action');
        if (!is_array($ids) || empty($ids)) {
            set_alert('warning', 'Select at least one contract first.');
            redirect(admin_url('subcontractors/contracts'));
        }
        $ids = array_map('intval', $ids);
        if ($action === 'delete') {
            if (!has_permission('smartsource_subcontractor_contracts', '', 'delete')) {
                access_denied('smartsource_subcontractor_contracts');
            }
            foreach ($ids as $id) { $this->smartsource_subcontractors_model->trash_contract($id); }
            set_alert('success', 'Selected contracts were moved to trash.');
        } elseif ($action === 'mark_sent') {
            if (!has_permission('smartsource_subcontractor_contracts', '', 'edit')) { access_denied('smartsource_subcontractor_contracts'); }
            foreach ($ids as $id) { $this->smartsource_subcontractors_model->update_contract_status($id, 'sent'); }
            set_alert('success', 'Selected contracts were marked as sent.');
        } elseif ($action === 'mark_cancelled') {
            if (!has_permission('smartsource_subcontractor_contracts', '', 'edit')) { access_denied('smartsource_subcontractor_contracts'); }
            foreach ($ids as $id) { $this->smartsource_subcontractors_model->update_contract_status($id, 'cancelled'); }
            set_alert('success', 'Selected contracts were marked as cancelled.');
        } else {
            set_alert('warning', 'Choose a valid bulk action.');
        }
        redirect(admin_url('subcontractors/contracts'));
    }

    public function contracts_export_csv()
    {
        $this->ensure_enabled();
        if (!has_permission('smartsource_subcontractor_contracts', '', 'view')) {
            access_denied('smartsource_subcontractor_contracts');
        }
        $contracts = $this->smartsource_subcontractors_model->get_contract('', ['include_trash' => '1']);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="subcontractor-contracts-export-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Subject','Subcontractor','Project','Contract Type','Status','Value','Start Date','End Date']);
        foreach ($contracts as $contract) {
            fputcsv($out, [
                $contract['id'], $contract['subject'], $contract['subcontractor_company'], $contract['project_name'] ?? $contract['project_id'],
                $contract['contract_type'], $contract['status'], $contract['contract_value'], $contract['start_date'], $contract['end_date']
            ]);
        }
        fclose($out);
        exit;
    }

    public function contracts_sample_header()
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="subcontractor-contracts-sample-header.csv"');
        echo "subject,subcontractor_id,project_id,clientid,contract_type,contract_value,start_date,end_date,status,description
";
        exit;
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
            $link = admin_url('subcontractors/contract_view/' . (int) $id);
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
            redirect(admin_url('subcontractors/settings'));
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
        redirect(admin_url('subcontractors/settings'));
    }

    private function send_test_sms()
    {
        $to = trim((string) $this->input->post('test_sms_to'));
        $message = trim((string) $this->input->post('test_sms_message'));

        if ($to === '') {
            set_alert('danger', 'Please enter a phone number to test SMS.');
            redirect(admin_url('subcontractors/settings'));
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

        redirect(admin_url('subcontractors/settings'));
    }


    public function application_alerts_json()
    {
        if (!is_staff_logged_in()) {
            echo json_encode(['success' => false, 'alerts' => []]);
            return;
        }

        if (!has_permission('smartsource_subcontractors', '', 'view') && !has_permission('smartsource_subcontractors', '', 'view_own')) {
            echo json_encode(['success' => false, 'alerts' => []]);
            return;
        }

        $alerts = [];
        if ($this->db->table_exists(db_prefix() . 'smartsource_subcontractor_application_alerts')) {
            $rows = $this->db->where('is_acknowledged', 0)
                ->order_by('id', 'DESC')
                ->limit(5)
                ->get(db_prefix() . 'smartsource_subcontractor_application_alerts')
                ->result_array();

            foreach ($rows as $row) {
                $alerts[] = [
                    'id'       => (int) $row['id'],
                    'title'    => $row['title'] ?? 'New Subcontractor Application',
                    'message'  => $row['message'] ?? '',
                    'open_url' => !empty($row['link']) ? admin_url($row['link']) : admin_url('subcontractors'),
                ];
            }
        }

        echo json_encode(['success' => true, 'alerts' => $alerts, 'count' => count($alerts)]);
    }

    public function acknowledge_application_alert($id)
    {
        if (!is_staff_logged_in() || (!has_permission('smartsource_subcontractors', '', 'view') && !has_permission('smartsource_subcontractors', '', 'view_own'))) {
            echo json_encode(['success' => false]);
            return;
        }

        $id = (int) $id;
        if ($id > 0 && $this->db->table_exists(db_prefix() . 'smartsource_subcontractor_application_alerts')) {
            $this->db->where('id', $id)->update(db_prefix() . 'smartsource_subcontractor_application_alerts', [
                'is_acknowledged' => 1,
                'acknowledged_by' => get_staff_user_id(),
                'acknowledged_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo json_encode(['success' => true]);
    }

    private function get_pending_application_alert_count()
    {
        if (!$this->db->table_exists(db_prefix() . 'smartsource_subcontractor_application_alerts')) {
            return 0;
        }

        return (int) $this->db->where('is_acknowledged', 0)
            ->count_all_results(db_prefix() . 'smartsource_subcontractor_application_alerts');
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
            redirect(admin_url('subcontractors/help'));
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
                'title' => _l('smartsource_help_records_title'),
                'content' => _l('smartsource_help_records_content'),
            ],
            [
                'title' => _l('smartsource_help_portal_title'),
                'content' => _l('smartsource_help_portal_content'),
            ],
            [
                'title' => _l('smartsource_help_documents_title'),
                'content' => _l('smartsource_help_documents_content'),
            ],
            [
                'title' => _l('smartsource_help_contracts_title'),
                'content' => _l('smartsource_help_contracts_content'),
            ],
            [
                'title' => _l('smartsource_help_templates_title'),
                'content' => _l('smartsource_help_templates_content'),
            ],
            [
                'title' => _l('smartsource_help_notifications_title'),
                'content' => _l('smartsource_help_notifications_content'),
            ],
            [
                'title' => _l('smartsource_help_settings_title'),
                'content' => _l('smartsource_help_settings_content'),
            ],
            [
                'title' => _l('smartsource_help_troubleshooting_title'),
                'content' => _l('smartsource_help_troubleshooting_content'),
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
