<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Public subcontractor contract portal.
 *
 * This controller intentionally does not extend AdminController. Customer
 * digital views, signatures, and comments must remain accessible without an
 * authenticated staff session, exactly like the CRM core Contract controller.
 */
class Subcontractor_contract extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('subcontractors/smartsource_subcontractors_model');
        $this->load->helper('subcontractors/smartsource_subcontractors');
    }

    public function index($token = '')
    {
        $contract = $this->get_public_contract($token);

        if ($this->input->post()) {
            $action = (string) $this->input->post('action');
            if ($action === 'sign_contract') {
                $this->process_signature($contract, $token);
            } elseif ($action === 'contract_comment') {
                $this->process_comment($contract, $token);
            }
        }

        $data['title'] = $contract->subject;
        $data['contract'] = $contract;
        $data['content'] = $this->smartsource_subcontractors_model->render_contract_content($contract);
        $data['cover'] = $this->smartsource_subcontractors_model->render_contract_cover($contract);
        $data['files'] = $this->smartsource_subcontractors_model->get_files($contract->id, 'contract');
        $data['comments'] = $this->smartsource_subcontractors_model->get_contract_comments($contract->id, false);
        $data['public_url'] = site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token));
        $data['is_public_contract'] = true;

        no_index_customers_area();
        $this->load->view('admin/contracts/client_view', $data);
    }

    public function sign($token = '')
    {
        $contract = $this->get_public_contract($token);
        if (!$this->input->post()) {
            redirect(site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token)));
        }
        $this->process_signature($contract, $token);
    }

    public function comment($token = '')
    {
        $contract = $this->get_public_contract($token);
        if (!$this->input->post()) {
            redirect(site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token)) . '#contract-comments');
        }
        $this->process_comment($contract, $token);
    }

    private function get_public_contract($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            show_404();
        }

        $contract = $this->smartsource_subcontractors_model->get_contract_by_token($token);
        if (!$contract || !empty($contract->hidden_from_customer) || !empty($contract->is_trash)) {
            show_404();
        }

        return $contract;
    }

    private function process_signature($contract, $token)
    {
        $identity = [
            'acceptance_firstname' => trim((string) $this->input->post('acceptance_firstname')),
            'acceptance_lastname'  => trim((string) $this->input->post('acceptance_lastname')),
            'acceptance_email'     => trim((string) $this->input->post('acceptance_email')),
        ];
        $initials = strtoupper(trim((string) $this->input->post('initials')));
        $signatureData = (string) $this->input->post('signature_data', false);
        $initialsData = (string) $this->input->post('initials_signature_data', false);
        $accepted = (string) $this->input->post('accept_terms') === '1';

        if (!$accepted || $identity['acceptance_firstname'] === '' ||
            !filter_var($identity['acceptance_email'], FILTER_VALIDATE_EMAIL) ||
            $initials === '' || $signatureData === '' || $initialsData === '') {
            set_alert('danger', 'First name, valid email, typed initials, drawn initials, full signature, and acceptance are required.');
            redirect(site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token)) . '#contract-signature');
        }

        try {
            $this->smartsource_subcontractors_model->save_contract_signature(
                (int) $contract->id,
                'subcontractor',
                $initials,
                $signatureData,
                $initialsData,
                $identity
            );
            log_activity('Subcontractor Contract Signed [ID: ' . (int) $contract->id . ']');
            set_alert('success', 'Contract signed successfully.');
        } catch (Throwable $e) {
            log_message('error', 'Public subcontractor contract signing failed [Contract ID: ' . (int) $contract->id . ']: ' . $e->getMessage());
            set_alert('danger', $e->getMessage());
        }

        redirect(site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token)) . '#contract-signature');
    }

    private function process_comment($contract, $token)
    {
        $comment = trim((string) $this->input->post('comment'));
        $name = trim((string) $this->input->post('customer_name'));
        $email = trim((string) $this->input->post('customer_email'));

        if ($comment === '') {
            set_alert('warning', 'Write a comment before submitting.');
            redirect(site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token)) . '#contract-comments');
        }

        try {
            $this->smartsource_subcontractors_model->add_contract_comment(
                (int) $contract->id,
                $comment,
                0,
                $name,
                $email
            );
            set_alert('success', 'Comment added successfully.');
        } catch (Throwable $e) {
            log_message('error', 'Public subcontractor contract comment failed [Contract ID: ' . (int) $contract->id . ']: ' . $e->getMessage());
            set_alert('danger', 'The comment could not be added.');
        }

        redirect(site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($token)) . '#contract-comments');
    }
}
