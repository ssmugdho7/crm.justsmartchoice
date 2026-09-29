<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchasing_hub_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_items($filters = [])
    {
        $this->apply_common_filters($filters, ['item_name','item_code','sku','category','unit_name']);
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'purchasing_hub_items')->result_array();
    }


    private function apply_common_filters($filters, $search_columns = [])
    {
        $filters = is_array($filters) ? $filters : [];
        if (!empty($filters['q']) && $search_columns) {
            $q = trim((string)$filters['q']);
            $this->db->group_start();
            foreach ($search_columns as $col) { $this->db->or_like($col, $q); }
            $this->db->group_end();
        }
        if (!empty($filters['status'])) { $this->db->where('status', $filters['status']); }
        if (!empty($filters['payment_status'])) { $this->db->where('payment_status', $filters['payment_status']); }
        if (!empty($filters['project_name'])) { $this->db->like('project_name', $filters['project_name']); }
        if (!empty($filters['created_from'])) { $this->db->where('datecreated >=', to_sql_date($filters['created_from']) . ' 00:00:00'); }
        if (!empty($filters['created_to'])) { $this->db->where('datecreated <=', to_sql_date($filters['created_to']) . ' 23:59:59'); }
    }

    public function get_item($id)
    {
        return $this->db->where('id', (int)$id)->get(db_prefix() . 'purchasing_hub_items')->row_array();
    }

    public function save_item($data, $id = null)
    {
        $row = [
            'item_code'      => $data['item_code'] ?? '',
            'item_name'      => $data['item_name'] ?? '',
            'sku'            => $data['sku'] ?? '',
            'barcode'        => $data['barcode'] ?? '',
            'description'    => $data['description'] ?? '',
            'category'       => $data['category'] ?? '',
            'sub_category'   => $data['sub_category'] ?? '',
            'unit_name'      => $data['unit_name'] ?? '',
            'purchase_price' => (float)($data['purchase_price'] ?? 0),
            'sales_rate'     => (float)($data['sales_rate'] ?? 0),
            'tax_1'          => $data['tax_1'] ?? '',
            'tax_2'          => $data['tax_2'] ?? '',
            'is_active'      => isset($data['is_active']) ? 1 : 0,
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['image'])) {
            $row['image'] = $data['image'];
        }

        if ($id) {
            $this->db->where('id', (int)$id)->update(db_prefix() . 'purchasing_hub_items', $row);
            return $id;
        }

        $row['created_by']  = get_staff_user_id();
        $row['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'purchasing_hub_items', $row);
        return $this->db->insert_id();
    }

    public function get_vendors($filters = [])
    {
        $this->apply_common_filters($filters, ['vendor_name','contact_name','email','phone','trade','status','insurance_status']);
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'purchasing_hub_vendors')->result_array();
    }

    public function get_vendor($id)
    {
        return $this->db->where('id', (int)$id)->get(db_prefix() . 'purchasing_hub_vendors')->row_array();
    }

    public function save_vendor($data, $id = null)
    {
        $row = [
            'vendor_name'      => $data['vendor_name'] ?? '',
            'contact_name'     => $data['contact_name'] ?? '',
            'email'            => $data['email'] ?? '',
            'phone'            => $data['phone'] ?? '',
            'trade'            => $data['trade'] ?? '',
            'status'           => $data['status'] ?? 'Active',
            'insurance_status' => $data['insurance_status'] ?? 'Not Verified',
            'address'          => $data['address'] ?? '',
            'notes'            => $data['notes'] ?? '',
            'is_active'        => isset($data['is_active']) ? 1 : 0,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $this->db->where('id', (int)$id)->update(db_prefix() . 'purchasing_hub_vendors', $row);
            return $id;
        }

        $row['created_by']  = get_staff_user_id();
        $row['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'purchasing_hub_vendors', $row);
        return $this->db->insert_id();
    }

    public function get_simple($table, $filters = [])
    {
        $searchable = [
            'orders' => ['order_number','project_name','client_name','invoice_number','status','shipping_address'],
            'bills' => ['bill_number','project_name','status','payment_status'],
            'quotes' => ['quote_number','project_name','status'],
            'contracts' => ['subject','project_name','contract_type','status'],
        ];
        $this->apply_common_filters($filters, $searchable[$table] ?? []);
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'purchasing_hub_' . $table)->result_array();
    }

    public function save_simple($table, $data, $id = null)
    {
        $allowed = ['orders', 'bills', 'quotes', 'contracts'];
        if (!in_array($table, $allowed, true)) {
            return false;
        }
        $dbtable = db_prefix() . 'purchasing_hub_' . $table;
        $row = $data;
        unset($row['id']);
        foreach ($row as $key => $value) {
            if (is_array($value)) {
                unset($row[$key]);
            }
        }
        if ($table === 'bills') {
            $amount = isset($row['amount']) ? (float)$row['amount'] : 0;
            $paid = isset($row['amount_paid']) ? (float)$row['amount_paid'] : 0;
            $row['payment_status'] = ($amount > 0 && $paid >= $amount) ? 'Paid' : 'Pending';
            if ($row['payment_status'] === 'Paid' && empty($row['paid_at'])) { $row['paid_at'] = date('Y-m-d H:i:s'); }
        }
        if ($table === 'orders' && empty($row['footer_note'])) { $row['footer_note'] = $this->default_po_footer(); }
        $row['updated_at'] = date('Y-m-d H:i:s');
        if ($this->db->field_exists('shipping_address', $dbtable) && isset($data['shipping_address'])) { $row['shipping_address'] = $data['shipping_address']; }
        if ($this->db->field_exists('body', $dbtable) && isset($data['body'])) { $row['body'] = $data['body']; }
        if ($id) {
            $this->db->where('id', (int)$id)->update($dbtable, $row);
            return $id;
        }
        $row['created_by'] = get_staff_user_id();
        $row['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert($dbtable, $row);
        return $this->db->insert_id();
    }


    public function get_simple_row($table, $id)
    {
        $allowed = ['orders', 'bills', 'quotes', 'contracts'];
        if (!in_array($table, $allowed, true)) { return []; }
        return $this->db->where('id', (int)$id)->get(db_prefix() . 'purchasing_hub_' . $table)->row_array() ?: [];
    }

    public function copy_simple($table, $id)
    {
        $row = $this->get_simple_row($table, $id);
        if (!$row) { return false; }
        unset($row['id']);
        if (isset($row['order_number'])) { $row['order_number'] .= '-COPY'; }
        if (isset($row['quote_number'])) { $row['quote_number'] .= '-COPY'; }
        if (isset($row['bill_number'])) { $row['bill_number'] .= '-COPY'; }
        if (isset($row['subject'])) { $row['subject'] .= ' - Copy'; }
        $row['status'] = 'Draft';
        return $this->save_simple($table, $row);
    }

    public function mark_sent($table, $id, $email)
    {
        $dbtable = db_prefix() . 'purchasing_hub_' . $table;
        $row = ['updated_at' => date('Y-m-d H:i:s')];
        if ($this->db->field_exists('sent_to', $dbtable)) { $row['sent_to'] = $email; }
        if ($this->db->field_exists('sent_at', $dbtable)) { $row['sent_at'] = date('Y-m-d H:i:s'); }
        return $this->db->where('id', (int)$id)->update($dbtable, $row);
    }

    public function merge_fields($table, $row = [])
    {
        $staff = get_staff(get_staff_user_id());
        $fields = [
            '{company_name}' => get_option('companyname'),
            '{crm_url}' => site_url(),
            '{current_date}' => date('Y-m-d'),
            '{current_time}' => date('H:i'),
            '{staff_firstname}' => $staff ? $staff->firstname : '',
            '{staff_lastname}' => $staff ? $staff->lastname : '',
        ];
        foreach ((array)$row as $key => $value) {
            if (!is_array($value)) { $fields['{' . $key . '}'] = (string)$value; }
        }
        if (!empty($row['vendor_id'])) {
            $vendor = $this->get_vendor($row['vendor_id']);
            foreach ((array)$vendor as $key => $value) {
                if (!is_array($value)) { $fields['{vendor_' . $key . '}'] = (string)$value; }
            }
        }
        return $fields;
    }

    public function apply_merge_fields($content, $table, $row)
    {
        return strtr((string)$content, $this->merge_fields($table, $row));
    }

    public function get_attachments($type, $id)
    {
        $dir = module_dir_path('purchasing_hub') . 'uploads/attachments/' . $type . '/' . (int)$id . '/';
        if (!is_dir($dir)) { return []; }
        $out = [];
        foreach (glob($dir . '*') ?: [] as $file) {
            if (is_file($file) && basename($file) !== 'index.html') {
                $out[] = ['file_name'=>basename($file),'path'=>$file,'url'=>module_dir_url('purchasing_hub', 'uploads/attachments/'.$type.'/'.(int)$id.'/'.basename($file))];
            }
        }
        return $out;
    }

    public function upload_attachment($type, $id, $file)
    {
        $allowed = ['orders','bills','quotes','contracts'];
        if (!in_array($type, $allowed, true) || empty($file['tmp_name'])) { return false; }
        $dir = module_dir_path('purchasing_hub') . 'uploads/attachments/' . $type . '/' . (int)$id . '/';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        $name = preg_replace('/[^A-Za-z0-9_.-]/', '_', $file['name']);
        return move_uploaded_file($file['tmp_name'], $dir . time() . '_' . $name);
    }

    public function delete_record($table, $id)
    {
        $allowed = ['items', 'vendors', 'orders', 'bills', 'quotes', 'contracts'];
        if (!in_array($table, $allowed, true)) {
            return false;
        }
        return $this->db->where('id', (int)$id)->delete(db_prefix() . 'purchasing_hub_' . $table);
    }

    public function health()
    {
        $tables = ['items', 'vendors', 'orders', 'bills', 'quotes', 'contracts'];
        $out = [];
        foreach ($tables as $table) {
            $name = db_prefix() . 'purchasing_hub_' . $table;
            $out[] = [
                'label' => ucwords(str_replace('_', ' ', $table)) . ' Table',
                'status' => $this->db->table_exists($name) ? 'OK' : 'Missing',
            ];
        }
        $upload_dir = module_dir_path('purchasing_hub') . 'uploads/item_images/';
        $out[] = ['label' => 'Item Image Upload Folder', 'status' => is_dir($upload_dir) && is_writable($upload_dir) ? 'OK' : 'Check Permissions'];
        return $out;
    }

    public function report_totals()
    {
        $bills = $this->db->select('COALESCE(SUM(amount),0) AS total, COALESCE(SUM(amount_paid),0) AS paid')->get(db_prefix().'purchasing_hub_bills')->row_array();
        $orders = $this->db->select('COALESCE(SUM(total),0) AS total')->get(db_prefix().'purchasing_hub_orders')->row_array();
        $quotes = $this->db->select('COALESCE(SUM(amount),0) AS total')->get(db_prefix().'purchasing_hub_quotes')->row_array();
        return [
            'open_bills' => (float)$bills['total'] - (float)$bills['paid'],
            'paid_bills' => (float)$bills['paid'],
            'purchase_orders' => (float)$orders['total'],
            'vendor_quotes' => (float)$quotes['total'],
        ];
    }


    public function import_headers($type)
    {
        $map = [
            'items' => ['item_code','item_name','sku','barcode','description','category','sub_category','unit_name','purchase_price','sales_rate','tax_1','tax_2','is_active'],
            'vendors' => ['vendor_name','contact_name','email','phone','trade','status','insurance_status','address','notes','is_active'],
            'orders' => ['order_number','vendor_id','project_id','project_name','client_id','client_name','invoice_id','invoice_number','shipping_address','order_date','expected_date','status','subtotal','tax_total','total','footer_note','receipt_requested','notes','body'],
            'bills' => ['bill_number','vendor_id','project_name','bill_date','due_date','status','payment_status','amount','amount_paid','paid_at','notes'],
            'quotes' => ['quote_number','vendor_id','project_name','quote_date','status','amount','notes'],
            'contracts' => ['subject','vendor_id','project_name','contract_type','status','contract_value','notes','body'],
        ];
        return $map[$type] ?? [];
    }

    public function import_csv($type, $file)
    {
        $headers = $this->import_headers($type);
        if (!$headers || !is_readable($file)) {
            return ['success' => false, 'message' => 'Import file could not be read.'];
        }
        $handle = fopen($file, 'r');
        $first = fgetcsv($handle);
        if (!$first) {
            return ['success' => false, 'message' => 'Import file is empty.'];
        }
        $first = array_map('trim', $first);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, static function ($v) { return trim((string)$v) !== ''; })) === 0) { continue; }
            $data = [];
            foreach ($first as $index => $key) {
                if (in_array($key, $headers, true)) { $data[$key] = $row[$index] ?? ''; }
            }
            if ($type === 'items' && !empty($data['item_name'])) { $this->save_item($data); $count++; }
            if ($type === 'vendors' && !empty($data['vendor_name'])) { $this->save_vendor($data); $count++; }
            if (in_array($type, ['orders','bills','quotes','contracts'], true)) { $this->save_simple($type, $data); $count++; }
        }
        fclose($handle);
        return ['success' => true, 'message' => $count . ' records imported successfully.'];
    }




    public function can_delete()
    {
        return is_admin() || (function_exists('has_permission') && has_permission('purchasing_hub', '', 'delete'));
    }

    public function get_crm_projects()
    {
        if (!$this->db->table_exists(db_prefix().'projects')) { return []; }
        return $this->db->select('id, name, clientid')->order_by('name','ASC')->get(db_prefix().'projects')->result_array();
    }

    public function get_crm_clients()
    {
        if (!$this->db->table_exists(db_prefix().'clients')) { return []; }
        return $this->db->select('userid, company')->order_by('company','ASC')->get(db_prefix().'clients')->result_array();
    }

    public function get_crm_invoices()
    {
        if (!$this->db->table_exists(db_prefix().'invoices')) { return []; }
        return $this->db->select('id, number, clientid, total, status')->order_by('id','DESC')->limit(250)->get(db_prefix().'invoices')->result_array();
    }

    public function default_po_footer()
    {
        return 'Please review this purchase order carefully. If there is any issue with inventory, product availability, pricing, delivery date, delivery address, backorders, damaged material, substitutions, lead time, or any change that can affect the project schedule, contact Smart Choice Contractors USA immediately before processing or shipping this order. Please confirm receipt of this purchase order by replying to the email or contacting our office.';
    }

    public function prepare_import_preview($type, $file, $format, $has_header)
    {
        $headers = $this->import_headers($type);
        if (!$headers || empty($file['tmp_name'])) { return ['success'=>false,'message'=>'Import file could not be read.']; }
        if ($format === 'auto') { $format = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); }
        if ($format === 'pdf') { return ['success'=>false,'message'=>'PDF import is not supported for table data. Please convert the PDF to CSV or Excel first.']; }
        $rows = $this->read_import_rows($file['tmp_name'], $format);
        if (!$rows) { return ['success'=>false,'message'=>'No importable rows were found. Use CSV, XLS, or XLSX.']; }
        $source_header = $has_header ? array_map('trim', array_shift($rows)) : $headers;
        $mapped = [];
        foreach ($rows as $line) {
            $clean = [];
            foreach ($headers as $i => $field) {
                $source_index = array_search($field, $source_header, true);
                if ($source_index === false) { $source_index = $i; }
                $clean[$field] = isset($line[$source_index]) ? trim((string)$line[$source_index]) : '';
            }
            if (count(array_filter($clean, static function($v){ return trim((string)$v) !== ''; })) > 0) { $mapped[] = $clean; }
        }
        if (!$mapped) { return ['success'=>false,'message'=>'The file was readable, but no valid data rows matched the import headers.']; }
        $token = md5(uniqid('ph_import_', true));
        $dir = module_dir_path('purchasing_hub') . 'uploads/import_preview/';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        $payload = ['type'=>$type,'headers'=>$headers,'rows'=>$mapped,'created_by'=>get_staff_user_id(),'created_at'=>date('Y-m-d H:i:s'),'inserted_ids'=>[]];
        file_put_contents($dir . $token . '.json', json_encode($payload));
        return ['success'=>true,'message'=>'Preview generated successfully. Review the table before importing.','token'=>$token,'headers'=>$headers,'rows'=>$mapped,'count'=>count($mapped)];
    }

    private function read_import_rows($file, $format)
    {
        $format = strtolower((string)$format);
        if (in_array($format, ['csv','txt',''], true)) {
            $rows = []; $h = fopen($file, 'r'); if (!$h) { return []; }
            while (($r = fgetcsv($h)) !== false) { $rows[] = $r; } fclose($h); return $rows;
        }
        if (in_array($format, ['xls','xlsx'], true)) {
            $phpExcel = module_dir_path('purchasing_hub') . 'third_party/excel/PHPExcel.php';
            if (file_exists($phpExcel)) { require_once($phpExcel); }
            if (!class_exists('PHPExcel_IOFactory')) { return []; }
            $reader = PHPExcel_IOFactory::createReaderForFile($file);
            $excel = $reader->load($file);
            return $excel->getActiveSheet()->toArray(null, true, true, false);
        }
        return [];
    }

    public function commit_import_preview($type, $token)
    {
        $payload = $this->get_import_payload($token);
        if (!$payload || $payload['type'] !== $type) { return ['success'=>false,'message'=>'Import preview expired or does not match this section.']; }
        $count = 0; $ids = [];
        foreach ((array)$payload['rows'] as $data) {
            if ($type === 'items' && empty($data['item_name'])) { continue; }
            if ($type === 'vendors' && empty($data['vendor_name'])) { continue; }
            if ($type === 'items') { $id = $this->save_item($data); }
            elseif ($type === 'vendors') { $id = $this->save_vendor($data); }
            else { $id = $this->save_simple($type, $data); }
            if ($id) { $ids[] = $id; $count++; }
        }
        $payload['inserted_ids'] = $ids; $payload['imported_at'] = date('Y-m-d H:i:s');
        $dir = module_dir_path('purchasing_hub') . 'uploads/import_preview/';
        file_put_contents($dir . $token . '.json', json_encode($payload));
        return ['success'=>true,'message'=>$count . ' records imported successfully. Click Verify Data to review the imported records.','inserted_ids'=>$ids];
    }

    private function get_import_payload($token)
    {
        $token = preg_replace('/[^a-f0-9]/', '', strtolower((string)$token));
        $file = module_dir_path('purchasing_hub') . 'uploads/import_preview/' . $token . '.json';
        if (!$token || !is_file($file)) { return null; }
        return json_decode(file_get_contents($file), true);
    }

    public function get_import_result($type, $token)
    {
        $payload = $this->get_import_payload($token);
        if (!$payload || $payload['type'] !== $type) { return ['rows'=>[], 'message'=>'No imported data was found for this verification token.']; }
        $ids = array_map('intval', (array)($payload['inserted_ids'] ?? []));
        if (!$ids) { return ['rows'=>[], 'message'=>'The preview exists, but no records have been imported yet.']; }
        if ($type === 'items') { $table = db_prefix().'purchasing_hub_items'; }
        elseif ($type === 'vendors') { $table = db_prefix().'purchasing_hub_vendors'; }
        else { $table = db_prefix().'purchasing_hub_'.$type; }
        $rows = $this->db->where_in('id', $ids)->get($table)->result_array();
        return ['rows'=>$rows, 'message'=>count($rows).' imported records verified successfully.'];
    }

    public function massive_delete($type, $ids)
    {
        if (!$this->can_delete()) { return 0; }
        $allowed = ['items','vendors','orders','bills','quotes','contracts'];
        if (!in_array($type, $allowed, true)) { return 0; }
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) { return 0; }
        $this->db->where_in('id', $ids)->delete(db_prefix().'purchasing_hub_'.$type);
        return $this->db->affected_rows();
    }

    public function request_po_receipt($id)
    {
        $table = db_prefix().'purchasing_hub_orders';
        if (!$this->db->table_exists($table)) { return false; }
        return $this->db->where('id',(int)$id)->update($table, ['receipt_requested'=>1, 'updated_at'=>date('Y-m-d H:i:s')]);
    }

    public function mark_po_received($id)
    {
        $table = db_prefix().'purchasing_hub_orders';
        if (!$this->db->table_exists($table)) { return false; }
        $data = ['receipt_confirmed'=>1, 'receipt_confirmed_at'=>date('Y-m-d H:i:s'), 'receipt_confirmed_by'=>get_staff_user_id(), 'updated_at'=>date('Y-m-d H:i:s')];
        return $this->db->where('id',(int)$id)->update($table,$data);
    }

    public function available_reports()
    {
        return [
            'material_costs'      => 'Material Cost Summary',
            'vendor_balances'     => 'Vendor Balance Summary',
            'purchase_orders'     => 'Purchase Order Summary',
            'materials_payment'   => 'Material Payments',
            'vendor_quotes'       => 'Vendor Quote Comparison',
            'accounts_payable'    => 'Accounts Payable Aging',
            'crm_estimates'       => 'CRM Estimates Summary',
            'crm_proposals'       => 'CRM Proposals Summary',
            'crm_invoices'        => 'CRM Invoices Summary',
            'crm_payments'        => 'CRM Payments Summary',
            'crm_credit_notes'    => 'CRM Credit Notes Summary',
            'project_costs'       => 'Project Cost Summary',
            'project_profit_loss' => 'Project Profit And Loss',
            'sales_summary'       => 'Sales Summary',
            'po_received'         => 'Purchase Orders Received Confirmation',
        ];
    }

    public function get_custom_reports()
    {
        if (!$this->db->table_exists(db_prefix() . 'purchasing_hub_custom_reports')) {
            return [];
        }
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'purchasing_hub_custom_reports')->result_array();
    }

    public function save_custom_report($data)
    {
        if (!$this->db->table_exists(db_prefix() . 'purchasing_hub_custom_reports')) {
            require_once(module_dir_path('purchasing_hub') . 'install.php');
        }

        $reports = $this->available_reports();
        $report_type = $data['report_type'] ?? '';
        if (!isset($reports[$report_type])) {
            return false;
        }

        $row = [
            'report_name' => $data['report_name'] ?? $reports[$report_type],
            'report_type' => $report_type,
            'description' => $data['description'] ?? '',
            'is_active'   => isset($data['is_active']) ? 1 : 0,
            'created_by'  => get_staff_user_id(),
            'datecreated' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'purchasing_hub_custom_reports', $row);
        return $this->db->insert_id();
    }

    private function table_exists($table)
    {
        return $this->db->table_exists(db_prefix() . $table);
    }

    private function safe_rows($table, $select, $order = '')
    {
        if (!$this->table_exists($table)) {
            return [];
        }
        $this->db->select($select, false);
        if ($order !== '') {
            $this->db->order_by($order);
        }
        return $this->db->get(db_prefix() . $table)->result_array();
    }

    public function report_rows($report)
    {
        switch ($report) {
            case 'material_costs':
                return $this->safe_rows('purchasing_hub_items', 'item_code,item_name,category,unit_name,purchase_price,sales_rate,is_active', 'category ASC');
            case 'vendor_balances':
                if (!$this->table_exists('purchasing_hub_vendors') || !$this->table_exists('purchasing_hub_bills')) { return []; }
                return $this->db->query('SELECT v.vendor_name, v.trade, COALESCE(SUM(b.amount),0) AS total_billed, COALESCE(SUM(b.amount_paid),0) AS total_paid, COALESCE(SUM(b.amount-b.amount_paid),0) AS balance FROM '.db_prefix().'purchasing_hub_vendors v LEFT JOIN '.db_prefix().'purchasing_hub_bills b ON b.vendor_id=v.id GROUP BY v.id ORDER BY balance DESC')->result_array();
            case 'purchase_orders':
                return $this->safe_rows('purchasing_hub_orders', 'order_number,project_name,client_name,invoice_number,order_date,expected_date,status,receipt_requested,receipt_confirmed,total', 'id DESC');
            case 'materials_payment':
                return $this->safe_rows('purchasing_hub_bills', 'bill_number,project_name,bill_date,due_date,status,payment_status,amount,amount_paid,(amount-amount_paid) AS balance', 'due_date ASC');
            case 'vendor_quotes':
                return $this->safe_rows('purchasing_hub_quotes', 'quote_number,project_name,quote_date,status,amount,notes', 'amount ASC');
            case 'accounts_payable':
                if (!$this->table_exists('purchasing_hub_bills')) { return []; }
                return $this->db->select('bill_number,project_name,bill_date,due_date,status,payment_status,amount,amount_paid,(amount-amount_paid) AS balance', false)->where('(amount-amount_paid) >', 0)->order_by('due_date','ASC')->get(db_prefix().'purchasing_hub_bills')->result_array();
            case 'crm_estimates':
                return $this->safe_rows('estimates', 'id,clientid,project_id,date,expirydate,status,total,total_tax,subtotal', 'id DESC');
            case 'crm_proposals':
                return $this->safe_rows('proposals', 'id,subject,rel_type,rel_id,date,open_till,status,total', 'id DESC');
            case 'crm_invoices':
                return $this->safe_rows('invoices', 'id,clientid,project_id,date,duedate,status,subtotal,total,total_tax,total_left_to_pay', 'id DESC');
            case 'crm_payments':
                return $this->safe_rows('invoicepaymentrecords', 'id,invoiceid,amount,paymentmode,date,daterecorded', 'id DESC');
            case 'crm_credit_notes':
                return $this->safe_rows('creditnotes', 'id,clientid,date,status,subtotal,total,total_tax', 'id DESC');
            case 'project_costs':
                if (!$this->table_exists('projects')) { return []; }
                $prefix = db_prefix();
                $sql = 'SELECT p.id, p.name AS project_name, COALESCE(SUM(b.amount),0) AS purchasing_bills, COALESCE(SUM(o.total),0) AS purchase_orders FROM '.$prefix.'projects p LEFT JOIN '.$prefix.'purchasing_hub_bills b ON b.project_name=p.name LEFT JOIN '.$prefix.'purchasing_hub_orders o ON o.project_name=p.name GROUP BY p.id ORDER BY p.id DESC';
                return $this->db->query($sql)->result_array();
            case 'project_profit_loss':
                if (!$this->table_exists('projects')) { return []; }
                $prefix = db_prefix();
                $invoiceJoin = $this->table_exists('invoices') ? 'LEFT JOIN '.$prefix.'invoices i ON i.project_id=p.id' : '';
                $sql = 'SELECT p.id, p.name AS project_name, COALESCE(SUM(i.total),0) AS crm_revenue, COALESCE(SUM(b.amount),0) AS purchasing_cost, (COALESCE(SUM(i.total),0)-COALESCE(SUM(b.amount),0)) AS estimated_profit_loss FROM '.$prefix.'projects p '.$invoiceJoin.' LEFT JOIN '.$prefix.'purchasing_hub_bills b ON b.project_name=p.name GROUP BY p.id ORDER BY p.id DESC';
                return $this->db->query($sql)->result_array();
            case 'po_received':
                return $this->safe_rows('purchasing_hub_orders', 'order_number,project_name,client_name,invoice_number,sent_to,sent_at,receipt_requested,receipt_confirmed,receipt_confirmed_at,receipt_confirmed_by,total', 'id DESC');
            case 'sales_summary':
                $rows = [];
                foreach (['estimates' => 'Estimates', 'proposals' => 'Proposals', 'invoices' => 'Invoices', 'creditnotes' => 'Credit Notes'] as $table => $label) {
                    if ($this->table_exists($table)) {
                        $row = $this->db->select('COUNT(*) AS records, COALESCE(SUM(total),0) AS total', false)->get(db_prefix() . $table)->row_array();
                        $rows[] = ['section' => $label, 'records' => $row['records'] ?? 0, 'total' => $row['total'] ?? 0];
                    }
                }
                return $rows;
            default:
                return [];
        }
    }


}
