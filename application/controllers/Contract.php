<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract extends ClientsController
{
    public function index($id = '', $hash = '')
    {
        check_contract_restrictions($id, $hash);
        $contract = $this->contracts_model->get($id);

        if (!$contract) {
            show_404();
        }

        if (!is_client_logged_in()) {
            load_client_language($contract->client);
        }

        if ($this->input->post()) {
            $action = $this->input->post('action');

            switch ($action) {
            case 'contract_pdf':
                    $pdf = contract_pdf($contract);
                    $pdf->Output(slug_it($contract->subject . '-' . get_option('companyname')) . '.pdf', 'D');

                    break;
            case 'sign_contract':
                    try {
                        $signatureData = (string) $this->input->post('signature', false);
                        $initialsData  = (string) $this->input->post('initials_signature', false);
                        if (empty($signatureData) || empty($initialsData)) {
                            throw new RuntimeException('Both the full signature and initials are required.');
                        }
                        $uploadPath = CONTRACTS_UPLOADS_FOLDER . $id;
                        if (!process_digital_signature_image($signatureData, $uploadPath)) {
                            throw new RuntimeException('The contract signature could not be saved. Please sign again.');
                        }
                        $initialsFile = process_digital_initials_image($initialsData, $uploadPath);
                        if (!$initialsFile) {
                            throw new RuntimeException('The initials could not be saved. Please initial again.');
                        }
                        $this->db->trans_begin();
                        $signed = $this->contracts_model->add_signature(
                            $id,
                            trim((string) $this->input->post('contract_initials')),
                            $initialsFile
                        );
                        if (!$signed || $this->db->trans_status() === false) {
                            $this->db->trans_rollback();
                            throw new RuntimeException('The contract could not be signed. No information was lost.');
                        }
                        $this->db->trans_commit();
                        set_alert('success', _l('document_signed_successfully'));
                    } catch (Throwable $e) {
                        if ($this->db->trans_status() !== false) {
                            $this->db->trans_rollback();
                        }
                        log_message('error', 'Contract signing failed for contract ' . (int) $id . ': ' . $e->getMessage());
                        set_alert('danger', $e->getMessage());
                    }
                    redirect(site_url('contract/' . $id . '/' . $hash));

            break;
             case 'contract_comment':
                    // comment is blank
                    if (!$this->input->post('content')) {
                        redirect($this->uri->uri_string());
                    }
                    $data                = $this->input->post();
                    $data['contract_id'] = $id;
                    $this->contracts_model->add_comment($data, true);
                    redirect($this->uri->uri_string() . '?tab=discussion');

                    break;
            }
        }

        $this->disableNavigation();
        $this->disableSubMenu();

        $data['title']     = $contract->subject;
        $data['contract']  = hooks()->apply_filters('contract_html_pdf_data', $contract);
        $data['bodyclass'] = 'contract contract-view';

        $data['identity_confirmation_enabled'] = true;
        $data['bodyclass'] .= ' identity-confirmation';
        $this->app_scripts->theme('sticky-js','assets/plugins/sticky/sticky.js');
        $data['comments'] = $this->contracts_model->get_comments($id);
        //add_views_tracking('proposal', $id);
        hooks()->do_action('contract_html_viewed', $id);
        $this->app_css->remove('reset-css','customers-area-default');
        $data                      = hooks()->apply_filters('contract_customers_area_view_data', $data);
        $this->data($data);
        no_index_customers_area();
        $this->view('contracthtml');
        $this->layout();
    }
}
