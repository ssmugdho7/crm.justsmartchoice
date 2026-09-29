<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Supplier extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('supplier_model');
        $this->supplier_model->ensure_schema();
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(SUPPLIER_MODULE_NAME, 'tables/suppliers'));
        }
        $data['title'] = _l('supplier_menu_name');
        $data['locations'] = $this->supplier_model->get_locations();
        $data['trades'] = $this->supplier_model->get_trades();
        $this->load->view('suppliers', $data);
    }

    public function save()
    {
        if (!has_permission('supplier', '', 'create') && !has_permission('supplier', '', 'edit')) {
            access_denied('supplier');
        }
        $data = $this->input->post();
        $id = empty($data['id']) ? $this->supplier_model->add($data) : (int)$data['id'];
        if (!empty($data['id'])) {
            $this->supplier_model->update($data, $id);
        }
        if ($id && isset($_FILES['supplier_document']) && !empty($_FILES['supplier_document']['name'])) {
            $this->supplier_model->add_document($id, $_FILES['supplier_document'], $data['supplier_document_description'] ?? '');
        }
        set_alert('success', empty($data['id']) ? _l('supplier_added_successfully') : _l('supplier_updated_successfully'));
        redirect(admin_url('supplier'));
    }

    public function delete($id = '')
    {
        if (!has_permission('supplier', '', 'delete')) {
            access_denied('supplier');
        }
        if ($id) {
            $this->supplier_model->delete($id);
            set_alert('success', _l('supplier_deleted_successfully'));
        }
        redirect(admin_url('supplier'));
    }

    public function supplier_form($id = '')
    {
        $supplier = $this->supplier_model->get($id);
        $locations = $this->supplier_model->get_locations();
        $trades = array_map(static function ($t) { return ['id' => $t, 'name' => $t]; }, $this->supplier_model->get_trades());
        echo '<div class="row"><div class="col-md-6">';
        echo render_input('supplier_name', 'supplier_name', !empty($supplier) ? $supplier->supplier_name : '', 'text');
        echo '</div><div class="col-md-6">';
        echo render_input('website', 'supplier_website', !empty($supplier) ? $supplier->website : '', 'text', ['placeholder' => 'supplier.com or https://supplier.com']);
        echo '</div></div>';
        echo '<div class="row"><div class="col-md-4">';
        echo render_input('phone', 'supplier_phone', !empty($supplier) ? $supplier->phone : '', 'text');
        echo '</div><div class="col-md-4">';
        echo render_input('email', 'supplier_email', !empty($supplier) ? $supplier->email : '', 'email');
        echo '</div><div class="col-md-4">';
        echo render_select('trade', $trades, ['id', 'name'], 'supplier_trade', !empty($supplier) ? $supplier->trade : '');
        echo '</div></div>';
        echo '<div class="row"><div class="col-md-6">';
        echo render_input('supplier_type', 'supplier_type', !empty($supplier) ? $supplier->supplier_type : '', 'text', ['placeholder' => 'Supplier, manufacturer, rental, service provider']);
        echo '</div><div class="col-md-6">';
        echo render_input('registration_url', 'supplier_registration_url', !empty($supplier) ? $supplier->registration_url : '', 'text', ['placeholder' => 'Sunbiz, DBPR, catalog, registration link']);
        echo '</div></div>';
        echo render_textarea('short_description', 'supplier_short_description', !empty($supplier) ? $supplier->short_description : '', ['rows' => 2, 'maxlength' => 220]);
        echo render_textarea('notes', 'supplier_notes', !empty($supplier) ? $supplier->notes : '', ['rows' => 4]);
        echo render_select('location_id', $locations, ['id', 'location_name'], 'supplier_location', !empty($supplier) ? $supplier->location_id : '');
        $onlyme = !empty($supplier) ? (int)$supplier->only_me : 0;
        echo '<div class="form-group"><label class="control-label clearfix">' . _l('supplier_visibility') . '</label><div class="radio radio-primary radio-inline"><input ' . ($onlyme ? 'checked' : '') . ' type="radio" id="supplier_privacy_1" name="only_me" value="1"><label for="supplier_privacy_1">' . _l('supplier_private') . '</label></div><div class="radio radio-primary radio-inline"><input ' . (!$onlyme ? 'checked' : '') . ' type="radio" id="supplier_privacy_0" name="only_me" value="0"><label for="supplier_privacy_0">' . _l('supplier_public') . '</label></div></div>';
        $tags = !empty($supplier) ? get_tags_in($supplier->id, 'supplier') : [];
        echo '<div class="form-group no-mbot"><label for="tags" class="control-label"><i class="fa fa-tag"></i> ' . _l('tags') . '</label><input type="text" class="tagsinput" id="tags" name="tags" value="' . html_escape(implode(', ', $tags)) . '" data-role="tagsinput"></div>';
        echo '<hr><h5><i class="fa fa-paperclip"></i> ' . _l('supplier_documents') . '</h5>';
        echo render_input('supplier_document_description', 'supplier_document_description', '', 'text', ['placeholder' => 'Catalog, W9, insurance, price list']);
        echo '<div class="form-group"><input type="file" name="supplier_document" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.webp,.zip,.rar"></div>';
        if (!empty($supplier)) {
            $docs = $this->supplier_model->get_documents($supplier->id);
            if ($docs) {
                echo '<div class="supplier-doc-list">';
                foreach ($docs as $doc) {
                    echo '<div><i class="fa fa-paperclip"></i> <a target="_blank" href="' . base_url($doc['file_path']) . '">' . html_escape($doc['original_name']) . '</a> <small>' . html_escape($doc['description']) . '</small></div>';
                }
                echo '</div>';
            }
        }
        echo form_hidden('id', $id);
    }

    public function locations()
    {
        $data['title'] = _l('supplier_locations');
        $data['locations'] = $this->supplier_model->get_locations();
        $this->load->view('locations', $data);
    }

    public function save_location()
    {
        $data = $this->input->post();
        empty($data['id']) ? $this->supplier_model->add_location($data) : $this->supplier_model->update_location($data, $data['id']);
        set_alert('success', _l('supplier_updated_successfully'));
        redirect(admin_url('supplier/locations'));
    }


    public function import()
    {
        if (!has_permission('supplier', '', 'create')) {
            access_denied('supplier');
        }

        $this->supplier_model->ensure_schema();

        if ($this->input->method() === 'post') {
            if (empty($_FILES['file_csv']['tmp_name'])) {
                set_alert('warning', _l('supplier_import_file_required'));
                redirect(admin_url('supplier/import'));
            }

            $handle = fopen($_FILES['file_csv']['tmp_name'], 'r');
            if (!$handle) {
                set_alert('danger', _l('supplier_import_failed'));
                redirect(admin_url('supplier/import'));
            }

            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                set_alert('danger', _l('supplier_import_failed'));
                redirect(admin_url('supplier/import'));
            }

            $header = array_map([$this, 'normalize_import_header'], (array)$header);
            $required = array_search('supplier_name', $header, true);
            if ($required === false) {
                fclose($handle);
                set_alert('danger', _l('supplier_import_missing_supplier_name'));
                redirect(admin_url('supplier/import'));
            }

            $imported = 0;
            $updatedOrSkipped = 0;
            while (($row = fgetcsv($handle)) !== false) {
                $record = [];
                foreach ($header as $index => $column) {
                    if ($column === '') {
                        continue;
                    }
                    $record[$column] = isset($row[$index]) ? trim((string)$row[$index]) : '';
                }

                if (trim((string)($record['supplier_name'] ?? '')) === '') {
                    $updatedOrSkipped++;
                    continue;
                }

                $id = $this->supplier_model->import_supplier_record([
                    'supplier_name'      => $record['supplier_name'] ?? '',
                    'website'            => $record['website'] ?? '',
                    'phone'              => $record['phone'] ?? '',
                    'email'              => $record['email'] ?? '',
                    'trade'              => $record['trade'] ?? '',
                    'supplier_type'      => $record['supplier_type'] ?? '',
                    'registration_url'   => $record['registration_url'] ?? '',
                    'short_description'  => $record['short_description'] ?? '',
                    'notes'              => $record['notes'] ?? '',
                    'only_me'            => 0,
                    'location_id'        => 0,
                ]);

                if ($id) {
                    $imported++;
                } else {
                    $updatedOrSkipped++;
                }
            }
            fclose($handle);

            set_alert('success', sprintf(_l('supplier_import_success'), $imported));
            redirect(admin_url('supplier'));
        }

        $data['title'] = _l('supplier_import');
        $this->load->view('import', $data);
    }

    private function normalize_import_header($value)
    {
        $value = strtolower(trim((string)$value));
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim($value, '_');

        $aliases = [
            'name' => 'supplier_name',
            'supplier' => 'supplier_name',
            'company' => 'supplier_name',
            'company_name' => 'supplier_name',
            'url' => 'website',
            'site' => 'website',
            'telephone' => 'phone',
            'contact_phone' => 'phone',
            'mail' => 'email',
            'contact_email' => 'email',
            'category' => 'trade',
            'type' => 'supplier_type',
            'description' => 'short_description',
            'short_desc' => 'short_description',
            'memo' => 'notes',
        ];

        return $aliases[$value] ?? $value;
    }

    public function sample_header()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="supplier_sample_header.csv"');
        echo "supplier_name,website,phone,email,trade,supplier_type,short_description,notes\n";
        echo "Home Depot,homedepot.com,800-430-3376,customercare@homedepot.com,Building Materials,Supplier,Material supplier,Verify local store pricing\n";
        exit;
    }


    public function standard_suppliers_file()
    {
        $file = module_dir_path(SUPPLIER_MODULE_NAME, 'assets/samples/smart_choice_standard_suppliers.csv');
        if (!file_exists($file)) {
            show_404();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="smart_choice_standard_suppliers.csv"');
        readfile($file);
        exit;
    }

    private function selected_export_ids()
    {
        $ids = $this->input->get('ids');
        if (!$ids) {
            $ids = $this->input->post('ids');
        }
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        return array_filter(array_map('intval', (array)$ids));
    }

    public function export($type = 'csv')
    {
        $type = strtolower((string)$type);
        $ids  = $this->selected_export_ids();
        $rows = $this->supplier_model->export_rows($ids);
        $filename = 'suppliers_export_' . date('Ymd_His');
        $headers = ['Supplier Name', 'Website', 'Phone', 'Email', 'Trade', 'Type', 'Description', 'Notes'];

        if ($type === 'excel' || $type === 'xls') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
            echo '<table border="1"><thead><tr>';
            foreach ($headers as $header) {
                echo '<th>' . html_escape($header) . '</th>';
            }
            echo '</tr></thead><tbody>';
            foreach ($rows as $row) {
                echo '<tr>';
                foreach (['supplier_name','website','phone','email','trade','supplier_type','short_description','notes'] as $field) {
                    echo '<td>' . html_escape($row[$field] ?? '') . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table>';
            exit;
        }

        if ($type === 'pdf') {
            $data['suppliers'] = $rows;
            $data['selected_count'] = count($rows);
            $this->load->view('supplier_pdf', $data);
            return;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, [$row['supplier_name'], $row['website'], $row['phone'], $row['email'], $row['trade'], $row['supplier_type'], $row['short_description'], $row['notes']]);
        }
        fclose($out);
        exit;
    }

    public function pdf()
    {
        $data['suppliers'] = $this->supplier_model->export_rows($this->selected_export_ids());
        $data['selected_count'] = count($data['suppliers']);
        $this->load->view('supplier_pdf', $data);
    }

    public function email_form($id = '')
    {
        $supplier = $this->supplier_model->get((int)$id);
        if (!$supplier) {
            echo '<div class="alert alert-danger">' . _l('supplier_not_found') . '</div>';
            return;
        }
        echo form_hidden('supplier_id', (int)$supplier->id);
        $savedEmail = trim((string)$supplier->email);
        if ($savedEmail === '') {
            echo '<div class="alert alert-warning"><strong>' . html_escape($supplier->supplier_name) . '</strong><br>' . _l('supplier_email_missing_add_now') . '</div>';
        } else {
            echo '<div class="alert alert-info"><strong>' . html_escape($supplier->supplier_name) . '</strong><br>' . html_escape($savedEmail) . '</div>';
        }
        echo render_input('supplier_email_to', 'supplier_email_to', $savedEmail, 'email', ['required' => true]);
        echo '<div class="checkbox checkbox-primary"><input type="checkbox" name="save_supplier_email" id="save_supplier_email" value="1" ' . ($savedEmail === '' ? 'checked' : '') . '><label for="save_supplier_email">' . _l('supplier_save_email_to_record') . '</label></div>';
        echo render_input('email_subject', 'supplier_email_subject', '', 'text', ['required' => true]);
        echo render_textarea('email_message', 'supplier_email_message', '', ['rows' => 7, 'required' => true]);
        echo '<div class="checkbox checkbox-primary"><input type="checkbox" name="send_copy_to_me" id="send_copy_to_me" value="1" checked><label for="send_copy_to_me">Send copy to me for delivery confirmation</label></div>';
        echo '<div class="form-group"><label for="supplier_email_attachments" class="control-label"><i class="fa fa-paperclip"></i> ' . _l('supplier_email_attachments') . '</label><input type="file" name="supplier_email_attachments[]" id="supplier_email_attachments" class="form-control" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.webp,.zip,.rar,.txt"></div>';
    }

    public function send_email()
    {
        if (!has_permission('supplier', '', 'view')) {
            access_denied('supplier');
        }

        $supplier_id = (int)$this->input->post('supplier_id');
        $supplier = $this->supplier_model->get($supplier_id);
        $to = trim((string)$this->input->post('supplier_email_to'));

        if (!$supplier) {
            set_alert('danger', _l('supplier_not_found'));
            redirect(admin_url('supplier'));
        }

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            set_alert('danger', _l('supplier_email_missing'));
            redirect(admin_url('supplier'));
        }

        if ($this->input->post('save_supplier_email')) {
            $this->supplier_model->update_email($supplier_id, $to);
        }

        $subject = trim((string)$this->input->post('email_subject'));
        $message = trim((string)$this->input->post('email_message'));

        if ($subject === '' || $message === '') {
            set_alert('warning', _l('supplier_email_required'));
            redirect(admin_url('supplier'));
        }

        $attachments = $this->supplier_prepare_email_attachments($supplier_id);
        $copyToMe = (int)$this->input->post('send_copy_to_me') === 1;
        $sent = $this->supplier_send_crm_email($to, $subject, $message, $attachments, $copyToMe);

        if ($sent) {
            set_alert('success', _l('supplier_email_sent'));
        } else {
            set_alert('danger', _l('supplier_email_failed'));
        }

        redirect(admin_url('supplier'));
    }

    private function supplier_prepare_email_attachments($supplier_id)
    {
        $attachments = [];

        if (empty($_FILES['supplier_email_attachments']['name'][0])) {
            return $attachments;
        }

        $allowed = ['pdf','doc','docx','xls','xlsx','csv','jpg','jpeg','png','webp','zip','rar','txt'];
        $uploadDir = FCPATH . 'uploads/suppliers/email_attachments/' . (int)$supplier_id . '/';

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $count = count($_FILES['supplier_email_attachments']['name']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['supplier_email_attachments']['tmp_name'][$i]) || !is_uploaded_file($_FILES['supplier_email_attachments']['tmp_name'][$i])) {
                continue;
            }

            $original = (string)$_FILES['supplier_email_attachments']['name'][$i];
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

            if ($ext !== '' && !in_array($ext, $allowed, true)) {
                continue;
            }

            $safe = preg_replace('/[^A-Za-z0-9\-_]/', '_', pathinfo($original, PATHINFO_FILENAME));
            $target = $uploadDir . $safe . '_' . date('Ymd_His') . '_' . $i . ($ext ? '.' . $ext : '');

            if (@move_uploaded_file($_FILES['supplier_email_attachments']['tmp_name'][$i], $target)) {
                $attachments[] = [
                    'path' => $target,
                    'name' => $original,
                ];
            }
        }

        return $attachments;
    }

    private function supplier_send_crm_email($to, $subject, $message, $attachments = [], $copyToMe = false)
    {
        $this->load->library('email');

        $mailer = null;
        if (function_exists('app_init_mail')) {
            $maybeMailer = app_init_mail();
            if (is_object($maybeMailer) && method_exists($maybeMailer, 'send')) {
                $mailer = $maybeMailer;
            }
        }

        if (!$mailer) {
            $mailer = $this->email;
            $mailer->clear(true);
            $mailer->initialize($this->supplier_email_config());
        } else {
            $mailer->clear(true);
        }

        $from = get_option('smtp_email');
        if (!$from) {
            $from = get_option('companyemail');
        }
        if (!$from) {
            $from = get_option('admin_email');
        }

        $company = get_option('companyname') ?: 'Smart Choice Contractors USA';
        $body = $this->supplier_email_body($message);

        $mailer->set_newline("\r\n");
        $mailer->set_crlf("\r\n");
        $mailer->from($from, $company);
        $mailer->reply_to($from, $company);
        $mailer->to($to);

        if ($copyToMe && function_exists('get_staff_user_id')) {
            $staffId = get_staff_user_id();
            $staff = $staffId ? $this->db->where('staffid', $staffId)->get(db_prefix() . 'staff')->row() : null;
            if ($staff && !empty($staff->email) && filter_var($staff->email, FILTER_VALIDATE_EMAIL) && strtolower($staff->email) !== strtolower($to)) {
                $mailer->bcc($staff->email);
            }
        }

        $mailer->subject($subject);
        $mailer->message($body);
        $mailer->set_alt_message(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body)));

        foreach ($attachments as $attachment) {
            if (!empty($attachment['path']) && file_exists($attachment['path'])) {
                $mailer->attach($attachment['path'], 'attachment', $attachment['name'] ?? basename($attachment['path']));
            }
        }

        $sent = (bool)$mailer->send(false);

        if (!$sent && method_exists($mailer, 'print_debugger')) {
            log_message('error', 'Supplier email failed: ' . strip_tags((string)$mailer->print_debugger(['headers', 'subject'])));
        }

        if ($sent) {
            log_message('info', 'Supplier email accepted by CRM mailer for: ' . $to);
        }

        return $sent;
    }

    private function supplier_email_config()
    {
        $protocol = get_option('email_protocol') ?: (get_option('smtp_host') ? 'smtp' : 'mail');
        $config = [
            'protocol'  => $protocol,
            'mailtype'  => 'html',
            'charset'   => get_option('email_charset') ?: 'utf-8',
            'wordwrap'  => false,
            'newline'   => "\r\n",
            'crlf'      => "\r\n",
            'useragent' => get_option('companyname') ?: 'Smart Choice Contractors USA',
        ];

        if ($protocol === 'smtp') {
            $config['smtp_host'] = get_option('smtp_host');
            $config['smtp_port'] = get_option('smtp_port') ?: 587;
            $config['smtp_user'] = get_option('smtp_username');
            $config['smtp_pass'] = function_exists('get_smtp_password') ? get_smtp_password() : get_option('smtp_password');
            $config['smtp_timeout'] = 30;
            $config['smtp_crypto'] = get_option('smtp_encryption') ?: '';
        }

        return $config;
    }

    private function supplier_email_body($message)
    {
        $company = html_escape(get_option('companyname') ?: 'Smart Choice Contractors USA');
        $logo = get_option('company_logo');
        $logoHtml = '';

        if ($logo) {
            $logoHtml = '<div style="margin-bottom:15px;"><img src="' . base_url('uploads/company/' . $logo) . '" alt="' . $company . '" style="max-height:70px;max-width:260px;width:auto;height:auto;"></div>';
        }

        return '<div style="font-family:Arial,sans-serif;font-size:14px;line-height:1.6;color:#111111;background:#ffffff;">'
            . $logoHtml
            . '<div style="border-top:4px solid #00A651;padding-top:15px;">'
            . nl2br(html_escape($message))
            . '</div>'
            . '<div style="margin-top:20px;padding-top:12px;border-top:1px solid #e5e5e5;color:#555555;font-size:12px;">'
            . $company
            . '</div>'
            . '</div>';
    }

    public function bulk_action()
    {
        $ids = $this->input->post('ids');
        if ($this->input->post('mass_delete') && has_permission('supplier', '', 'delete')) {
            $this->supplier_model->bulk_delete($ids);
        }
        echo json_encode(['success' => true]);
    }
}
