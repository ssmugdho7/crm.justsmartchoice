<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Procurement_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('procurement_manager_model');
        $this->load->model('projects_model');
    }

    /**
     * Main dashboard – shows procurement items, filters by project, and summary charts.
     */
    public function index()
    {
        if (!has_permission('procurement_manager', '', 'view')) {
            access_denied('procurement_manager');
        }

        $project_id = $this->input->get('project_id');

        $data['title']        = _l('procurement_manager');
        $data['projects']     = $this->projects_model->get(); // all projects
        $data['project_id']   = $project_id;
        $data['items']        = $this->procurement_manager_model->get_items($project_id);
        $data['summary_by_supplier'] = $this->procurement_manager_model->get_summary_by_supplier($project_id);
        $data['summary_by_category'] = $this->procurement_manager_model->get_summary_by_category($project_id);

        $this->load->view('tracker/index', $data);
    }

    /**
     * Create or edit an item
     */
    public function item($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('procurement_manager', '', 'create')) {
                    access_denied('procurement_manager');
                }
                $data = $this->input->post();
                $insert_id = $this->procurement_manager_model->add_item($data);
                if ($insert_id) {
                    set_alert('success', _l('added_successfully', _l('procurement_item')));
                }
            } else {
                if (!has_permission('procurement_manager', '', 'edit')) {
                    access_denied('procurement_manager');
                }
                $data = $this->input->post();
                $success = $this->procurement_manager_model->update_item($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('procurement_item')));
                }
            }
            redirect(admin_url('procurement_manager'));
        }

        $data['projects'] = $this->projects_model->get();
        if ($id != '') {
            $data['item'] = $this->procurement_manager_model->get_item($id);
        }
        $data['title'] = $id == '' ? _l('add_new', _l('procurement_item')) : _l('edit', _l('procurement_item'));
        $this->load->view('tracker/item_form', $data);
    }

    /**
     * Delete item
     */
    public function delete($id)
    {
        if (!has_permission('procurement_manager', '', 'delete')) {
            access_denied('procurement_manager');
        }
        $this->procurement_manager_model->delete_item($id);
        set_alert('success', _l('deleted', _l('procurement_item')));
        redirect(admin_url('procurement_manager'));
    }

    /**
     * Export CSV of current project items
     */
    public function export_csv()
    {
        if (!has_permission('procurement_manager', '', 'view')) {
            access_denied('procurement_manager');
        }

        $project_id = $this->input->get('project_id');
        $items      = $this->procurement_manager_model->get_items($project_id);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=procurement_items.csv');

        $output = fopen('php://output', 'w');

        // Header row
        fputcsv($output, [
            'Project ID', 'Category', 'Item Name', 'Unit', 'Qty', 'Quoted Price',
            'Alt Price', 'Marketplace Price', 'Supplier Name', 'Supplier Email',
            'Source', 'Status', 'Notes'
        ]);

        foreach ($items as $item) {
            fputcsv($output, [
                $item['project_id'],
                $item['category'],
                $item['item_name'],
                $item['unit'],
                $item['qty'],
                $item['quoted_price'],
                $item['alt_price'],
                $item['marketplace_price'],
                $item['supplier_name'],
                $item['supplier_email'],
                $item['source'],
                $item['status'],
                $item['notes'],
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Import CSV into procurement items
     */
    public function import_csv()
    {
        if (!has_permission('procurement_manager', '', 'create')) {
            access_denied('procurement_manager');
        }

        if (isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['name'] != '') {
            $tmp_name = $_FILES['csv_file']['tmp_name'];
            $handle   = fopen($tmp_name, 'r');
            if ($handle !== false) {
                // Skip header row
                fgetcsv($handle);
                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    $data = [];
                    $data['project_id']        = $row[0] ?: null;
                    $data['category']          = $row[1] ?? null;
                    $data['item_name']         = $row[2] ?? null;
                    $data['unit']              = $row[3] ?? null;
                    $data['qty']               = $row[4] ?? null;
                    $data['quoted_price']      = $row[5] ?? null;
                    $data['alt_price']         = $row[6] ?? null;
                    $data['marketplace_price'] = $row[7] ?? null;
                    $data['supplier_name']     = $row[8] ?? null;
                    $data['supplier_email']    = $row[9] ?? null;
                    $data['source']            = $row[10] ?? null;
                    $data['status']            = $row[11] ?? null;
                    $data['notes']             = $row[12] ?? null;
                    $this->procurement_manager_model->add_item($data);
                }
                fclose($handle);
                set_alert('success', _l('import_success'));
            } else {
                set_alert('danger', _l('file_not_found'));
            }
        }

        redirect(admin_url('procurement_manager'));
    }

    /**
     * Send email request for quote to supplier
     */
    public function send_request($id)
    {
        if (!has_permission('procurement_manager', '', 'view')) {
            access_denied('procurement_manager');
        }

        $item = $this->procurement_manager_model->get_item($id);
        if (!$item || empty($item['supplier_email'])) {
            set_alert('danger', 'Supplier email not set for this item.');
            redirect(admin_url('procurement_manager'));
        }

        $this->load->model('emails_model');

        $subject = 'Request for Quote - ' . $item['item_name'];
        $message = 'Please provide your best price for the following item:' . PHP_EOL . PHP_EOL;
        $message .= 'Item: ' . $item['item_name'] . PHP_EOL;
        $message .= 'Category: ' . $item['category'] . PHP_EOL;
        $message .= 'Quantity: ' . $item['qty'] . ' ' . $item['unit'] . PHP_EOL;
        $message .= 'Project ID: ' . $item['project_id'] . PHP_EOL;
        $message .= PHP_EOL . 'Thank you.';

        $success = $this->emails_model->send_simple_email($item['supplier_email'], $subject, nl2br($message));

        if ($success) {
            set_alert('success', 'Quote request email sent to supplier.');
        } else {
            set_alert('danger', 'Failed to send email.');
        }

        redirect(admin_url('procurement_manager'));
    }
}
