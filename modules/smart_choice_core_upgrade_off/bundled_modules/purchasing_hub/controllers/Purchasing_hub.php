<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchasing_hub extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchasing_hub_model');
    }

    public function index()
    {
        $data['title'] = 'Purchasing Hub';
        $data['totals'] = $this->purchasing_hub_model->report_totals();
        $this->load->view('dashboard', $data);
    }

    public function items()
    {
        $data['title'] = 'Items';
        $data['items'] = $this->purchasing_hub_model->get_items($this->input->get());
        $this->load->view('items', $data);
    }

    public function item($id = '')
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $image = $this->upload_item_image();
            if ($image) { $post['image'] = $image; }
            $this->purchasing_hub_model->save_item($post, $id ?: null);
            set_alert('success', 'Item saved successfully.');
            redirect(admin_url('purchasing_hub/items'));
        }
        $data['title'] = $id ? 'Edit Item' : 'Add Item';
        $data['item'] = $id ? $this->purchasing_hub_model->get_item($id) : [];
        $this->load->view('item_form', $data);
    }

    private function upload_item_image()
    {
        if (empty($_FILES['item_image']['name'])) { return ''; }
        $path = module_dir_path('purchasing_hub') . 'uploads/item_images/';
        if (!is_dir($path)) { @mkdir($path, 0755, true); }
        $config = ['upload_path'=>$path,'allowed_types'=>'jpg|jpeg|png|gif|webp','max_size'=>5120,'encrypt_name'=>true];
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('item_image')) { set_alert('warning', strip_tags($this->upload->display_errors())); return ''; }
        $data = $this->upload->data();
        return $data['file_name'];
    }

    public function vendors()
    {
        $data['title'] = 'Vendors';
        $data['vendors'] = $this->purchasing_hub_model->get_vendors($this->input->get());
        $this->load->view('vendors', $data);
    }

    public function vendor($id = '')
    {
        if ($this->input->post()) {
            $this->purchasing_hub_model->save_vendor($this->input->post(), $id ?: null);
            set_alert('success', 'Vendor saved successfully.');
            redirect(admin_url('purchasing_hub/vendors'));
        }
        $data['title'] = $id ? 'Edit Vendor' : 'Add Vendor';
        $data['vendor'] = $id ? $this->purchasing_hub_model->get_vendor($id) : [];
        $this->load->view('vendor_form', $data);
    }

    public function purchase_orders(){ $this->simple_list_page('orders', 'Purchase Orders'); }
    public function accounts_payable(){ $this->simple_list_page('bills', 'Accounts Payable'); }
    public function vendor_quotes(){ $this->simple_list_page('quotes', 'Vendor Quotes'); }
    public function contracts(){ $this->simple_list_page('contracts', 'Contracts'); }

    private function simple_list_page($type, $title)
    {
        $data['title'] = $title;
        $data['rows'] = $this->purchasing_hub_model->get_simple($type, $this->input->get());
        $data['type'] = $type;
        $this->load->view('simple_list', $data);
    }

    public function simple($type, $id = '')
    {
        $allowed = ['orders', 'bills', 'quotes', 'contracts'];
        if (!in_array($type, $allowed, true)) { show_404(); }
        if ($this->input->post()) {
            $this->purchasing_hub_model->save_simple($type, $this->input->post(), $id ?: null);
            set_alert('success', 'Record saved successfully.');
            redirect(admin_url('purchasing_hub/' . $this->type_to_method($type)));
        }
        $data['type'] = $type;
        $data['title'] = $this->type_title($type);
        $data['vendors'] = $this->purchasing_hub_model->get_vendors($this->input->get());
        $data['row'] = $id ? $this->purchasing_hub_model->get_simple_row($type, $id) : [];
        $data['projects'] = $this->purchasing_hub_model->get_crm_projects();
        $data['clients'] = $this->purchasing_hub_model->get_crm_clients();
        $data['invoices'] = $this->purchasing_hub_model->get_crm_invoices();
        $data['default_po_footer'] = $this->purchasing_hub_model->default_po_footer();
        $data['merge_fields'] = $this->purchasing_hub_model->merge_fields($type, $data['row']);
        $this->load->view('simple_form', $data);
    }

    private function type_to_method($type){ return ['orders'=>'purchase_orders','bills'=>'accounts_payable','quotes'=>'vendor_quotes','contracts'=>'contracts'][$type]; }
    private function type_title($type){ return ['orders'=>'Purchase Order','bills'=>'Accounts Payable Bill','quotes'=>'Vendor Quote','contracts'=>'Contract'][$type]; }


    public function view($type, $id)
    {
        $row = $this->purchasing_hub_model->get_simple_row($type, $id);
        if (!$row) { show_404(); }
        $data['title'] = $this->type_title($type) . ' #' . $id;
        $data['type'] = $type;
        $data['row'] = $row;
        $data['merge_fields'] = $this->purchasing_hub_model->merge_fields($type, $row);
        $data['attachments'] = $this->purchasing_hub_model->get_attachments($type, $id);
        $this->load->view('document_view', $data);
    }

    public function client_view($type, $id)
    {
        $row = $this->purchasing_hub_model->get_simple_row($type, $id);
        if (!$row) { show_404(); }
        $data['title'] = $this->type_title($type) . ' Client View';
        $data['type'] = $type;
        $data['row'] = $row;
        $data['merge_fields'] = $this->purchasing_hub_model->merge_fields($type, $row);
        $this->load->view('client_document_view', $data);
    }

    public function copy($type, $id)
    {
        $new_id = $this->purchasing_hub_model->copy_simple($type, $id);
        if ($new_id) { set_alert('success', 'Record copied successfully.'); }
        redirect(admin_url('purchasing_hub/simple/' . $type . '/' . $new_id));
    }

    public function pdf($type, $id)
    {
        $row = $this->purchasing_hub_model->get_simple_row($type, $id);
        if (!$row) { show_404(); }
        $data['type'] = $type;
        $data['row'] = $row;
        $data['title'] = $this->type_title($type);
        $html = $this->load->view('document_pdf', $data, true);
        $filename = $type . '_' . $id . '.pdf';
        if (function_exists('pdf')) {
            $pdf = pdf($html);
            $pdf->Output($filename, 'I');
            exit;
        }
        if (class_exists('TCPDF')) {
            $pdf = new TCPDF();
            $pdf->AddPage();
            $pdf->writeHTML($html);
            $pdf->Output($filename, 'I');
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function send_email($type, $id)
    {
        $row = $this->purchasing_hub_model->get_simple_row($type, $id);
        if (!$row) { show_404(); }
        if ($this->input->post()) {
            $to = trim((string)$this->input->post('email'));
            $subject = trim((string)$this->input->post('subject'));
            $message = $this->input->post('message', false);
            if ($to !== '') {
                $this->load->config('email');
                $this->load->library('email');
                $this->email->clear(true);
                $this->email->to($to);
                $this->email->subject($subject ?: $this->type_title($type));
                $this->email->message($message ?: $this->load->view('document_pdf', ['type'=>$type,'row'=>$row,'title'=>$this->type_title($type)], true));
                if ($this->email->send()) {
                    if ($type === 'orders' && $this->input->post('receipt_requested')) { $this->purchasing_hub_model->request_po_receipt($id); }
                    $this->purchasing_hub_model->mark_sent($type, $id, $to);
                    set_alert('success', 'Email sent successfully.');
                } else {
                    set_alert('warning', 'Email was not sent. Check CRM email configuration.');
                }
            }
            redirect(admin_url('purchasing_hub/view/'.$type.'/'.$id));
        }
        $data['title'] = 'Send ' . $this->type_title($type);
        $data['type'] = $type;
        $data['row'] = $row;
        $this->load->view('send_email', $data);
    }

    public function attachment($type, $id)
    {
        if (!empty($_FILES['attachment']['name'])) {
            $this->purchasing_hub_model->upload_attachment($type, $id, $_FILES['attachment']);
            set_alert('success', 'Attachment uploaded successfully.');
        }
        redirect(admin_url('purchasing_hub/view/'.$type.'/'.$id));
    }

    public function delete($table, $id)
    {
        if (!$this->purchasing_hub_model->can_delete()) { access_denied('Purchasing Hub'); }
        $this->purchasing_hub_model->delete_record($table, $id);
        set_alert('success', 'Record deleted successfully.');
        redirect($this->agent->referrer() ?: admin_url('purchasing_hub'));
    }

    public function reports($report = '')
    {
        $data['title'] = 'Purchasing Reports';
        $data['totals'] = $this->purchasing_hub_model->report_totals();
        $data['report'] = $report ?: 'center';
        $data['report_rows'] = ($report && $report !== 'custom') ? $this->purchasing_hub_model->report_rows($report) : [];
        $data['available_reports'] = $this->purchasing_hub_model->available_reports();
        $data['custom_reports'] = $this->purchasing_hub_model->get_custom_reports();
        $this->load->view('reports/index', $data);
    }



    public function add_report()
    {
        if ($this->input->post()) {
            $saved = $this->purchasing_hub_model->save_custom_report($this->input->post());
            if ($saved) {
                set_alert('success', 'Report added successfully.');
                redirect(admin_url('purchasing_hub/reports'));
            }
            set_alert('warning', 'Report could not be added. Please select a valid report type.');
        }
        $data['title'] = 'Add Purchasing Report';
        $data['available_reports'] = $this->purchasing_hub_model->available_reports();
        $this->load->view('reports/add_report', $data);
    }

    public function settings()
    {
        if ($this->input->post()) {
            $fields = ['purchasing_hub_default_tax_rate','purchasing_hub_default_terms','purchasing_hub_allow_item_images'];
            foreach ($fields as $field) { update_option($field, $this->input->post($field, false) ?: ''); }
            set_alert('success', 'Settings saved successfully.');
            redirect(admin_url('purchasing_hub/settings'));
        }
        $data['title'] = 'Purchasing Hub Settings';
        $this->load->view('manage_setting', $data);
    }

    public function health_check()
    {
        $data['title'] = 'Purchasing Hub Health Check';
        $data['checks'] = $this->purchasing_hub_model->health();
        $this->load->view('health_check', $data);
    }

    public function help_training()
    {
        $data['title'] = 'Purchasing Hub Help Guide';
        $this->load->view('help_training', $data);
    }

    public function import($type)
    {
        $allowed = ['items','vendors','orders','bills','quotes','contracts'];
        if (!in_array($type, $allowed, true)) { show_404(); }

        if ($this->input->post() && !empty($_FILES['import_file']['tmp_name'])) {
            $format = strtolower((string)$this->input->post('file_format'));
            $has_header = (int)$this->input->post('has_header') === 1;
            $result = $this->purchasing_hub_model->prepare_import_preview($type, $_FILES['import_file'], $format, $has_header);
            if (empty($result['success'])) {
                set_alert('warning', $result['message']);
                redirect(admin_url('purchasing_hub/import/' . $type));
            }
            $data['title'] = 'Preview Import ' . ucwords(str_replace('_',' ', $type));
            $data['type'] = $type;
            $data['headers'] = $this->purchasing_hub_model->import_headers($type);
            $data['preview'] = $result;
            $this->load->view('import_preview', $data);
            return;
        }

        $data['title'] = 'Import ' . ucwords(str_replace('_',' ', $type));
        $data['type'] = $type;
        $data['headers'] = $this->purchasing_hub_model->import_headers($type);
        $this->load->view('import_csv', $data);
    }

    public function confirm_import($type)
    {
        $allowed = ['items','vendors','orders','bills','quotes','contracts'];
        if (!in_array($type, $allowed, true)) { show_404(); }
        if (!$this->input->post()) { redirect(admin_url('purchasing_hub/import/' . $type)); }
        $token = (string)$this->input->post('preview_token');
        $result = $this->purchasing_hub_model->commit_import_preview($type, $token);
        set_alert(!empty($result['success']) ? 'success' : 'warning', $result['message']);
        redirect(admin_url('purchasing_hub/import_result/' . $type . '/' . rawurlencode($token)));
    }

    public function import_result($type, $token = '')
    {
        $allowed = ['items','vendors','orders','bills','quotes','contracts'];
        if (!in_array($type, $allowed, true)) { show_404(); }
        $data['title'] = 'Imported Data Verification';
        $data['type'] = $type;
        $data['result'] = $this->purchasing_hub_model->get_import_result($type, $token);
        $this->load->view('import_result', $data);
    }

    public function massive_delete($type)
    {
        $allowed = ['items','vendors','orders','bills','quotes','contracts'];
        if (!in_array($type, $allowed, true)) { show_404(); }
        if (!$this->purchasing_hub_model->can_delete()) { access_denied('Purchasing Hub'); }
        $ids = $this->input->post('ids');
        $deleted = $this->purchasing_hub_model->massive_delete($type, is_array($ids) ? $ids : []);
        set_alert('success', $deleted . ' selected records deleted successfully.');
        redirect($this->agent->referrer() ?: admin_url('purchasing_hub'));
    }

    public function mark_po_received($id)
    {
        $this->purchasing_hub_model->mark_po_received($id);
        set_alert('success', 'Purchase order receipt was recorded successfully.');
        redirect(admin_url('purchasing_hub/reports/po_received'));
    }

    public function sample($type)
    {
        $headers = $this->purchasing_hub_model->import_headers($type);
        if (!$headers) { show_404(); }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="purchasing_hub_' . $type . '_sample.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        fputcsv($out, array_fill(0, count($headers), 'Sample'));
        fclose($out);
        exit;
    }
}
