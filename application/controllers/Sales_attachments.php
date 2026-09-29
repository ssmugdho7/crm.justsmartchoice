<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_attachments extends ClientsController
{
    public function upload()
    {
        if (!is_client_logged_in() && !is_staff_logged_in()) {
            return $this->json(false, _l('access_denied'));
        }

        if (!$this->input->is_ajax_request() && strtolower($this->input->method()) !== 'post') {
            return $this->json(false, _l('invalid_request'));
        }

        $type = strtolower((string) $this->input->post('rel_type'));
        $id   = (int) $this->input->post('rel_id');
        if (!in_array($type, ['invoice', 'estimate', 'proposal'], true) || $id < 1) {
            return $this->json(false, _l('invalid_request'));
        }

        if (!$this->canAccess($type, $id)) {
            return $this->json(false, _l('access_denied'));
        }

        if (empty($_FILES['sales_attachments']['name'])) {
            return $this->json(false, _l('no_file_selected'));
        }

        $this->load->helper('upload');
        if (!handle_smart_choice_sales_attachments($type, $id, 'sales_attachments')) {
            log_message('error', 'Customer sales attachment upload failed ['.$type.':'.$id.']');
            return $this->json(false, _l('problem_uploading_file'));
        }

        return $this->json(true, ucfirst($type) . ' attachment added successfully.');
    }

    private function canAccess(string $type, int $id): bool
    {
        if (is_staff_logged_in()) {
            return true;
        }
        $clientId = get_client_user_id();
        if ($type === 'proposal') {
            return $this->db->where('id', $id)->where('rel_type', 'customer')->where('rel_id', $clientId)
                ->count_all_results(db_prefix().'proposals') > 0;
        }
        $table = $type === 'invoice' ? 'invoices' : 'estimates';
        return $this->db->where('id', $id)->where('clientid', $clientId)
            ->count_all_results(db_prefix().$table) > 0;
    }

    private function json(bool $success, string $message)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'success' => $success,
            'message' => $message,
            'csrfHash' => $this->security->get_csrf_hash(),
        ]));
    }
}
