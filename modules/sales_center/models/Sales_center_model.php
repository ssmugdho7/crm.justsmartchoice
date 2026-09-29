<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_center_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', (int) $id);
            return $this->db->get(db_prefix() . 'sales_center')->row();
        }

        $this->db->order_by('company', 'ASC');
        return $this->db->get(db_prefix() . 'sales_center')->result_array();
    }

    public function get_by_project($project_id)
    {
        $this->db->select('s.*, l.trade as project_trade, l.scope as project_scope, l.datecreated as linked_date');
        $this->db->from(db_prefix() . 'sales_center_project_links l');
        $this->db->join(db_prefix() . 'sales_center s', 's.id = l.salesperson_id', 'left');
        $this->db->where('l.project_id', (int) $project_id);
        return $this->db->get()->result_array();
    }

    public function add($data)
    {
        $data = $this->prepare_salesperson_data($data);
        if (empty($data['portal_token'])) {
            $data['portal_token'] = bin2hex(random_bytes(24));
        }
        $data['created_by'] = get_staff_user_id();
        $data['datecreated'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'sales_center', $data);
        $id = $this->db->insert_id();

        if ($id) {
            $this->handle_custom_fields_safely($id, 'sales_center');
            log_activity('Smartsource Sub Salesperson Created [ID: ' . $id . ']');
        }

        return $id;
    }

    public function update($data, $id)
    {
        $data = $this->prepare_salesperson_data($data);
        $data['dateupdated'] = date('Y-m-d H:i:s');

        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'sales_center', $data);

        $this->handle_custom_fields_safely($id, 'sales_center');

        log_activity('Smartsource Sub Salesperson Updated [ID: ' . $id . ']');
        return true;
    }

    public function delete($id)
    {
        $id = (int) $id;
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'sales_center');

        if ($this->db->affected_rows() > 0) {
            $this->db->where('salesperson_id', $id)->delete(db_prefix() . 'sales_center_project_links');
            log_activity('Smartsource Sub Salesperson Deleted [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    public function add_contract($data)
    {
        $data = $this->prepare_contract_data($data);
        $data['created_by'] = get_staff_user_id();
        $data['datecreated'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'sales_center_contracts', $data);
        $id = $this->db->insert_id();

        if ($id && !empty($data['project_id'])) {
            $this->link_to_project($data['salesperson_id'], $data['project_id'], $data['contract_type'] ?? '', 'Linked from salesperson contract #' . $id);
        }

        if ($id) {
            $this->handle_custom_fields_safely($id, 'sales_center_contracts');
            log_activity('Smartsource Salesperson Contract Created [ID: ' . $id . ']');
        }

        return $id;
    }

    public function update_contract($data, $id)
    {
        $data = $this->prepare_contract_data($data);
        $data['dateupdated'] = date('Y-m-d H:i:s');

        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'sales_center_contracts', $data);

        if (!empty($data['project_id'])) {
            $this->link_to_project($data['salesperson_id'], $data['project_id'], $data['contract_type'] ?? '', 'Linked from salesperson contract #' . $id);
        }

        $this->handle_custom_fields_safely($id, 'sales_center_contracts');

        log_activity('Smartsource Salesperson Contract Updated [ID: ' . $id . ']');
        return true;
    }

    public function get_contract($id = '', $filters = [])
    {
        $this->db->select('c.*, s.company as salesperson_company, s.contact_name, s.email, s.phone, p.name as project_name');
        $this->db->from(db_prefix() . 'sales_center_contracts c');
        $this->db->join(db_prefix() . 'sales_center s', 's.id = c.salesperson_id', 'left');
        $this->db->join(db_prefix() . 'projects p', 'p.id = c.project_id', 'left');

        if (is_numeric($id)) {
            $this->db->where('c.id', (int) $id);
            return $this->db->get()->row();
        }

        if (!isset($filters['include_trash']) || (string) $filters['include_trash'] !== '1') {
            $this->db->where('c.is_trash', 0);
        }

        if (!empty($filters['status'])) {
            $this->db->where('c.status', $filters['status']);
        }

        if (!empty($filters['contract_type'])) {
            $this->db->where('c.contract_type', $filters['contract_type']);
        }

        if (!empty($filters['salesperson_id'])) {
            $this->db->where('c.salesperson_id', (int) $filters['salesperson_id']);
        }

        if (!empty($filters['stat'])) {
            $today = date('Y-m-d');
            $soon = date('Y-m-d', strtotime('+30 days'));
            if ($filters['stat'] === 'active') {
                $this->db->where('c.is_trash', 0)->where_in('c.status', ['sent', 'signed', 'completed']);
            } elseif ($filters['stat'] === 'expired') {
                $this->db->where('c.is_trash', 0)->where('c.end_date <', $today)->where('c.end_date IS NOT NULL', null, false);
            } elseif ($filters['stat'] === 'expiring') {
                $this->db->where('c.is_trash', 0)->where('c.end_date >=', $today)->where('c.end_date <=', $soon);
            } elseif ($filters['stat'] === 'trash') {
                $this->db->where('c.is_trash', 1);
            }
        }

        $this->db->order_by('c.datecreated', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_project_contracts($project_id)
    {
        return $this->get_contract('', ['include_trash' => '0', 'project_id' => (int) $project_id]);
    }

    public function get_contracts_for_project($project_id)
    {
        $this->db->select('c.*, s.company as salesperson_company');
        $this->db->from(db_prefix() . 'sales_center_contracts c');
        $this->db->join(db_prefix() . 'sales_center s', 's.id = c.salesperson_id', 'left');
        $this->db->where('c.project_id', (int) $project_id);
        $this->db->where('c.is_trash', 0);
        $this->db->order_by('c.datecreated', 'DESC');
        return $this->db->get()->result_array();
    }

    public function trash_contract($id)
    {
        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'sales_center_contracts', ['is_trash' => 1, 'hidden_from_customer' => 1, 'dateupdated' => date('Y-m-d H:i:s')]);
        log_activity('Smartsource Salesperson Contract Sent To Trash [ID: ' . (int) $id . ']');
        return true;
    }

    public function restore_contract($id)
    {
        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'sales_center_contracts', ['is_trash' => 0, 'dateupdated' => date('Y-m-d H:i:s')]);
        log_activity('Smartsource Salesperson Contract Restored [ID: ' . (int) $id . ']');
        return true;
    }

    public function permanent_delete_contract($id)
    {
        $id = (int) $id;
        $files = $this->get_files($id, 'contract');
        foreach ($files as $file) {
            $this->delete_file($file['id']);
        }
        $this->db->where('id', $id)->delete(db_prefix() . 'sales_center_contracts');
        log_activity('Smartsource Salesperson Contract Permanently Deleted [ID: ' . $id . ']');
        return true;
    }

    public function link_to_project($salesperson_id, $project_id, $trade = '', $scope = '')
    {
        if (empty($salesperson_id) || empty($project_id)) {
            return 0;
        }

        $exists = $this->db->where('salesperson_id', (int) $salesperson_id)
            ->where('project_id', (int) $project_id)
            ->get(db_prefix() . 'sales_center_project_links')
            ->row();

        if ($exists) {
            return $exists->id;
        }

        $this->db->insert(db_prefix() . 'sales_center_project_links', [
            'salesperson_id' => (int) $salesperson_id,
            'project_id'       => (int) $project_id,
            'trade'            => $trade,
            'scope'            => $scope,
            'created_by'       => get_staff_user_id(),
            'datecreated'      => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insert_id();
    }

    public function add_file($rel_id, $rel_type, $file)
    {
        $this->db->insert(db_prefix() . 'sales_center_files', [
            'rel_id'             => (int) $rel_id,
            'rel_type'           => $rel_type,
            'file_name'          => $file['file_name'],
            'original_file_name' => $file['original_file_name'] ?? $file['file_name'],
            'filetype'           => $file['filetype'] ?? '',
            'visible_to_customer'=> !empty($file['visible_to_customer']) ? 1 : 0,
            'staffid'            => get_staff_user_id(),
            'dateadded'          => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insert_id();
    }

    public function get_files($rel_id, $rel_type)
    {
        return $this->db->where('rel_id', (int) $rel_id)
            ->where('rel_type', $rel_type)
            ->order_by('dateadded', 'DESC')
            ->get(db_prefix() . 'sales_center_files')
            ->result_array();
    }

    public function delete_file($id)
    {
        $file = $this->db->where('id', (int) $id)->get(db_prefix() . 'sales_center_files')->row();
        if (!$file) {
            return false;
        }

        $path = SALES_CENTER_UPLOAD_FOLDER . $file->rel_type . '/' . $file->rel_id . '/' . $file->file_name;
        if (file_exists($path)) {
            @unlink($path);
        }

        $this->db->where('id', (int) $id)->delete(db_prefix() . 'sales_center_files');
        return $this->db->affected_rows() > 0;
    }

    public function get_stats()
    {
        $today = date('Y-m-d');
        $soon = date('Y-m-d', strtotime('+30 days'));
        return [
            'active_salespersons' => (int) $this->db->where('status', 'active')->count_all_results(db_prefix() . 'sales_center'),
            'total_salespersons'  => (int) $this->db->count_all_results(db_prefix() . 'sales_center'),
            'active_contracts'      => (int) $this->db->where('is_trash', 0)->where_in('status', ['sent','signed','completed'])->count_all_results(db_prefix() . 'sales_center_contracts'),
            'expired_contracts'     => (int) $this->db->where('is_trash', 0)->where('end_date <', $today)->where('end_date IS NOT NULL', null, false)->count_all_results(db_prefix() . 'sales_center_contracts'),
            'expiring_contracts'    => (int) $this->db->where('is_trash', 0)->where('end_date >=', $today)->where('end_date <=', $soon)->count_all_results(db_prefix() . 'sales_center_contracts'),
            'expiring_insurance'    => (int) $this->db->where('insurance_expiration >=', $today)->where('insurance_expiration <=', $soon)->count_all_results(db_prefix() . 'sales_center'),
            'trash_contracts'       => (int) $this->db->where('is_trash', 1)->count_all_results(db_prefix() . 'sales_center_contracts'),
            'total_contract_value'  => (float) ($this->db->select_sum('contract_value')->where('is_trash', 0)->get(db_prefix() . 'sales_center_contracts')->row()->contract_value ?? 0),
        ];
    }

    public function get_chart_data()
    {
        $types = $this->db->select('contract_type, COUNT(id) as total, SUM(contract_value) as value')
            ->where('is_trash', 0)
            ->group_by('contract_type')
            ->get(db_prefix() . 'sales_center_contracts')->result_array();

        return $types;
    }

    public function get_categories()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'sales_center_categories')->result_array();
    }

    public function get_salesperson_statuses_db()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'sales_center_statuses')->result_array();
    }

    public function get_contract_statuses_db()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'sales_center_contract_statuses')->result_array();
    }

    public function save_simple_record($table, $data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . $table, $data);
        return $this->db->insert_id();
    }

    public function delete_simple_record($table, $id)
    {
        $this->db->where('id', (int) $id)->delete(db_prefix() . $table);
        return true;
    }

    public function get_templates($id = '')
    {
        if (is_numeric($id)) {
            return $this->db->where('id', (int) $id)->get(db_prefix() . 'sales_center_templates')->row();
        }
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'sales_center_templates')->result_array();
    }

    public function add_template($data)
    {
        $data = [
            'name' => $data['name'] ?? '',
            'contract_type' => $data['contract_type'] ?? '',
            'content' => $data['content'] ?? '',
            'created_by' => get_staff_user_id(),
            'datecreated' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'sales_center_templates', $data);
        return $this->db->insert_id();
    }

    public function update_template($data, $id)
    {
        $data = [
            'name' => $data['name'] ?? '',
            'contract_type' => $data['contract_type'] ?? '',
            'content' => $data['content'] ?? '',
            'dateupdated' => date('Y-m-d H:i:s'),
        ];
        $this->db->where('id', (int) $id)->update(db_prefix() . 'sales_center_templates', $data);
        return true;
    }

    public function delete_template($id)
    {
        $this->db->where('id', (int) $id)->delete(db_prefix() . 'sales_center_templates');
        return true;
    }

    public function copy_template($id)
    {
        $template = $this->get_templates($id);
        if (!$template) {
            return false;
        }
        return $this->add_template([
            'name' => $template->name . ' Copy',
            'contract_type' => $template->contract_type,
            'content' => $template->content,
        ]);
    }

    public function health_check()
    {
        $checks = [];
        $tables = [
            'sales_center',
            'sales_center_contracts',
            'sales_center_files',
            'sales_center_project_links',
            'sales_center_categories',
            'sales_center_statuses',
            'sales_center_contract_statuses',
            'sales_center_templates',
        ];
        foreach ($tables as $table) {
            $checks[] = ['name' => function_exists('sales_center_clean_label') ? sales_center_clean_label($table) : ucwords(str_replace('_', ' ', $table)), 'ok' => $this->db->table_exists(db_prefix() . $table)];
        }
        $checks[] = ['name' => 'Upload Folder Writable', 'ok' => is_dir(SALES_CENTER_UPLOAD_FOLDER) && is_writable(SALES_CENTER_UPLOAD_FOLDER)];
        $checks[] = ['name' => 'Module Enabled', 'ok' => get_option('sales_center_enabled') === '1'];
        return $checks;
    }


    public function save_contract_signature($contract_id, $role, $initials, $signature)
    {
        $contract_id = (int) $contract_id;
        $role = $role === 'company' ? 'company' : 'salesperson';
        $ip = $this->input->ip_address();
        $now = date('Y-m-d H:i:s');

        $data = [];
        if ($role === 'company') {
            $data['company_initials'] = $initials;
            $data['company_signature'] = $signature;
            $data['company_signed_at'] = $now;
            $data['company_signed_ip'] = $ip;
        } else {
            $data['salesperson_initials'] = $initials;
            $data['salesperson_signature'] = $signature;
            $data['salesperson_signed_at'] = $now;
            $data['salesperson_signed_ip'] = $ip;
        }

        if ($signature !== '') {
            $data['signed_date'] = date('Y-m-d');
            $data['status'] = 'signed';
        }

        $this->db->where('id', $contract_id);
        $this->db->update(db_prefix() . 'sales_center_contracts', $data);
        log_activity('Smartsource Salesperson Contract Signature Saved [ID: ' . $contract_id . ', Role: ' . $role . ']');
        return true;
    }

    public function render_contract_content($contract)
    {
        $content = $contract->content ?? '';
        $companySignature = function_exists('sales_center_signature_img') ? sales_center_signature_img($contract->company_signature ?? '', 'Company Signature') : '';
        $subSignature = function_exists('sales_center_signature_img') ? sales_center_signature_img($contract->salesperson_signature ?? '', 'Salesperson Signature') : '';
        $stamp = '';
        if (!empty($contract->company_signed_at)) {
            $stamp .= '<p><small><strong>Company Signed:</strong> ' . html_escape($contract->company_signed_at) . ' | IP: ' . html_escape($contract->company_signed_ip) . '</small></p>';
        }
        if (!empty($contract->salesperson_signed_at)) {
            $stamp .= '<p><small><strong>Salesperson Signed:</strong> ' . html_escape($contract->salesperson_signed_at) . ' | IP: ' . html_escape($contract->salesperson_signed_ip) . '</small></p>';
        }

        $merge = [
            '{salesperson_name}' => $contract->salesperson_company ?? '',
            '{salesperson_email}' => $contract->email ?? '',
            '{salesperson_phone}' => $contract->phone ?? '',
            '{salesperson_initials}' => $contract->salesperson_initials ?? '',
            '{company_initials}' => $contract->company_initials ?? '',
            '{salesperson_signature}' => $subSignature,
            '{company_signature}' => $companySignature,
            '{contract_signed_stamp}' => $stamp,
            '{contract_id}' => $contract->id ?? '',
            '{contract_subject}' => $contract->subject ?? '',
            '{contract_value}' => $contract->contract_value ?? '',
            '{project_name}' => $contract->project_name ?? '',
        ];

        return str_replace(array_keys($merge), array_values($merge), $content);
    }

    public function get_by_portal_token($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return null;
        }
        return $this->db->where('portal_token', $token)
            ->where('portal_enabled', 1)
            ->get(db_prefix() . 'sales_center')
            ->row();
    }

    public function update_from_portal($token, $data)
    {
        $salesperson = $this->get_by_portal_token($token);
        if (!$salesperson) {
            return false;
        }
        $clean = $this->prepare_salesperson_data($data);
        unset($clean['portal_token'], $clean['portal_enabled'], $clean['assigned'], $clean['staff_id']);
        $clean['dateupdated'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $salesperson->id)->update(db_prefix() . 'sales_center', $clean);
        return (int) $salesperson->id;
    }


    public function find_existing_portal_salesperson($data)
    {
        $email = trim((string)($data['email'] ?? ''));
        $phone = preg_replace('/[^0-9]/', '', (string)($data['phone'] ?? ''));
        $company = trim((string)($data['company'] ?? ''));

        $this->db->from(db_prefix() . 'sales_center');
        $this->db->group_start();

        $hasCondition = false;
        if ($email !== '') {
            $this->db->or_where('LOWER(email)', strtolower($email));
            $hasCondition = true;
        }

        if ($phone !== '') {
            $this->db->or_like('phone', $phone, 'both');
            $hasCondition = true;
        }

        if ($company !== '') {
            $this->db->or_where('LOWER(company)', strtolower($company));
            $hasCondition = true;
        }

        $this->db->group_end();

        if (!$hasCondition) {
            return null;
        }

        return $this->db->get()->row();
    }

    public function ensure_portal_token($id)
    {
        $salesperson = $this->get((int)$id);
        if (!$salesperson) {
            return '';
        }

        if (!empty($salesperson->portal_token)) {
            if ((int)$salesperson->portal_enabled !== 1) {
                $this->db->where('id', (int)$id)->update(db_prefix() . 'sales_center', ['portal_enabled' => 1]);
            }
            return $salesperson->portal_token;
        }

        $token = bin2hex(random_bytes(24));
        $this->db->where('id', (int)$id)->update(db_prefix() . 'sales_center', [
            'portal_token' => $token,
            'portal_enabled' => 1,
        ]);

        return $token;
    }

    public function get_or_create_portal_token($id)
    {
        $salesperson = $this->get((int) $id);
        if (!$salesperson) {
            return '';
        }
        if (!empty($salesperson->portal_token)) {
            return $salesperson->portal_token;
        }
        $token = bin2hex(random_bytes(24));
        $this->db->where('id', (int) $id)->update(db_prefix() . 'sales_center', [
            'portal_token' => $token,
            'portal_enabled' => 1,
        ]);
        return $token;
    }

    private function prepare_salesperson_data($data)
    {
        $allowed = ['company', 'contact_name', 'email', 'phone', 'trade', 'position_type', 'employment_type', 'department_id', 'commission_rate', 'sales_goal', 'license_number', 'dbpr_link', 'county_license_link', 'profile_image', 'portal_enabled', 'portal_token', 'staff_id', 'insurance_expiration', 'address', 'city', 'state', 'zip', 'status', 'category', 'notes', 'assigned'];
        $clean = $this->only_allowed($data, $allowed);
        if (function_exists('sales_center_normalize_url')) {
            $clean['dbpr_link'] = sales_center_normalize_url($clean['dbpr_link'] ?? '');
            $clean['county_license_link'] = sales_center_normalize_url($clean['county_license_link'] ?? '');
        }
        $clean['portal_enabled'] = !empty($clean['portal_enabled']) ? 1 : 0;
        return $clean;
    }

    private function prepare_contract_data($data)
    {
        $allowed = ['subject', 'salesperson_id', 'project_id', 'contract_type', 'contract_value', 'start_date', 'end_date', 'signed_date', 'status', 'description', 'content', 'hidden_from_customer', 'is_trash', 'assigned', 'company_initials', 'salesperson_initials', 'company_signature', 'salesperson_signature'];
        $clean = $this->only_allowed($data, $allowed);
        $clean['hidden_from_customer'] = !empty($data['hidden_from_customer']) ? 1 : 0;
        $clean['is_trash'] = !empty($data['is_trash']) ? 1 : 0;
        return $clean;
    }

    private function handle_custom_fields_safely($id, $relType)
    {
        if (!function_exists('handle_custom_fields_post')) {
            return;
        }

        $post = $this->input->post();
        if (!isset($post['custom_fields']) || !is_array($post['custom_fields'])) {
            return;
        }

        $customFields = $post['custom_fields'];
        if (!isset($customFields[$relType]) || !is_array($customFields[$relType])) {
            return;
        }

        handle_custom_fields_post($id, [$relType => $customFields[$relType]]);
    }

    private function only_allowed($data, $allowed)
    {
        $clean = [];
        foreach ($allowed as $key) {
            if (isset($data[$key])) {
                $clean[$key] = $data[$key];
            }
        }
        return $clean;
    }

    public function get_sales_summary($filters = [])
    {
        $summary = [
            'invoice_total' => 0,
            'collected' => 0,
            'commission_earned' => 0,
            'commission_paid' => 0,
            'commission_owed' => 0,
            'open_invoices' => 0,
            'paid_invoices' => 0,
            'cancelled_invoices' => 0,
        ];

        if (!$this->db->table_exists(db_prefix() . 'sales_center_commissions')) {
            return $summary;
        }

        $this->apply_commission_filters($filters);
        $row = $this->db
            ->select('SUM(invoice_total) as invoice_total, SUM(amount_collected) as collected, SUM(commission_earned) as commission_earned, SUM(commission_paid) as commission_paid, SUM(commission_owed) as commission_owed')
            ->get(db_prefix() . 'sales_center_commissions')
            ->row();

        if ($row) {
            foreach (['invoice_total', 'collected', 'commission_earned', 'commission_paid', 'commission_owed'] as $key) {
                $summary[$key] = (float) ($row->{$key} ?? 0);
            }
        }

        $summary['open_invoices'] = $this->count_commissions_by_status($filters, ['pending', 'open', 'unpaid', 'partial']);
        $summary['paid_invoices'] = $this->count_commissions_by_status($filters, ['paid', 'completed']);
        $summary['cancelled_invoices'] = $this->count_commissions_by_status($filters, ['cancelled', 'void']);

        return $summary;
    }

    public function get_sales_report_rows($filters = [])
    {
        if (!$this->db->table_exists(db_prefix() . 'sales_center_commissions')) {
            return [];
        }

        $this->db->select('c.*, s.company as salesperson_company, s.contact_name, s.position_type, s.department_id, m.company as manager_name, i.number as invoice_number, i.status as invoice_status, cl.company as customer_name');
        $this->db->from(db_prefix() . 'sales_center_commissions c');
        $this->db->join(db_prefix() . 'sales_center s', 's.id = c.salesperson_id', 'left');
        $this->db->join(db_prefix() . 'sales_center m', 'm.id = c.manager_id', 'left');
        $this->db->join(db_prefix() . 'invoices i', 'i.id = c.invoice_id', 'left');
        $this->db->join(db_prefix() . 'clients cl', 'cl.userid = i.clientid', 'left');
        $this->apply_commission_filters($filters, false);
        $this->db->order_by('c.datecreated', 'DESC');
        return $this->db->get()->result_array();
    }

    private function apply_commission_filters($filters = [], $fromBase = true)
    {
        $prefix = $fromBase ? '' : 'c.';

        if (!empty($filters['salesperson_id'])) {
            $this->db->where($prefix . 'salesperson_id', (int) $filters['salesperson_id']);
        }

        if (!empty($filters['status'])) {
            $this->db->where($prefix . 'status', $filters['status']);
        }

        if (!empty($filters['manager_id'])) {
            $this->db->where($prefix . 'manager_id', (int) $filters['manager_id']);
        }

        if (!empty($filters['department_id'])) {
            $this->db->where($prefix . 'department_id', (int) $filters['department_id']);
        }

        if (!empty($filters['date_from'])) {
            $this->db->where($prefix . 'datecreated >=', to_sql_date($filters['date_from']) . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $this->db->where($prefix . 'datecreated <=', to_sql_date($filters['date_to']) . ' 23:59:59');
        }
    }

    private function count_commissions_by_status($filters, $statuses)
    {
        $this->apply_commission_filters($filters);
        $this->db->where_in('status', $statuses);
        return (int) $this->db->count_all_results(db_prefix() . 'sales_center_commissions');
    }

    public function get_sales_managers()
    {
        if (!$this->db->table_exists(db_prefix() . 'sales_center')) {
            return [];
        }
        $this->db->where_in('position_type', ['Sales Manager', 'Sales Director', 'Manager', 'Director']);
        $this->db->order_by('company', 'ASC');
        return $this->db->get(db_prefix() . 'sales_center')->result_array();
    }

    public function get_departments_list()
    {
        if (!$this->db->table_exists(db_prefix() . 'departments')) {
            return [];
        }
        return $this->db->select('departmentid as id, name')->order_by('name', 'ASC')->get(db_prefix() . 'departments')->result_array();
    }

    public function get_invoice_options()
    {
        if (!$this->db->table_exists(db_prefix() . 'invoices')) {
            return [];
        }

        $this->db->select('i.id, i.number, i.total, i.status, i.clientid, c.company as customer_name');
        $this->db->from(db_prefix() . 'invoices i');
        if ($this->db->table_exists(db_prefix() . 'clients')) {
            $this->db->join(db_prefix() . 'clients c', 'c.userid = i.clientid', 'left');
        }
        $this->db->order_by('i.id', 'DESC');
        $this->db->limit(250);
        $rows = $this->db->get()->result_array();

        foreach ($rows as &$row) {
            $invoiceNumber = format_invoice_number((int)$row['id']);
            $customer = trim((string)($row['customer_name'] ?? ''));
            $total = function_exists('app_format_money') ? app_format_money($row['total'] ?? 0, get_base_currency()) : number_format((float)($row['total'] ?? 0), 2);
            $row['display_name'] = $invoiceNumber . ($customer !== '' ? ' - ' . $customer : '') . ' - ' . $total;
        }

        return $rows;
    }

    public function save_commission_record($data)
    {
        if (!$this->db->table_exists(db_prefix() . 'sales_center_commissions')) {
            require_once module_dir_path('sales_center', 'install.php');
        }

        $invoiceId = (int)($data['invoice_id'] ?? 0);
        $salespersonId = (int)($data['salesperson_id'] ?? 0);
        if ($invoiceId <= 0 || $salespersonId <= 0) {
            return false;
        }

        $invoiceTotal = (float)($data['invoice_total'] ?? 0);
        if ($invoiceTotal <= 0 && $this->db->table_exists(db_prefix() . 'invoices')) {
            $invoice = $this->db->where('id', $invoiceId)->get(db_prefix() . 'invoices')->row();
            if ($invoice && isset($invoice->total)) {
                $invoiceTotal = (float)$invoice->total;
            }
        }

        $amountCollected = (float)($data['amount_collected'] ?? 0);
        if ($amountCollected <= 0 && $this->db->table_exists(db_prefix() . 'invoicepaymentrecords')) {
            $paid = $this->db->select('SUM(amount) as total_paid')->where('invoiceid', $invoiceId)->get(db_prefix() . 'invoicepaymentrecords')->row();
            if ($paid && isset($paid->total_paid)) {
                $amountCollected = (float)$paid->total_paid;
            }
        }

        $rate = (float)($data['commission_rate'] ?? 0);
        if ($rate <= 0) {
            $sp = $this->get($salespersonId);
            if ($sp && isset($sp->commission_rate)) {
                $rate = (float)$sp->commission_rate;
            }
        }

        $earned = round($amountCollected * ($rate / 100), 2);
        $paidCommission = (float)($data['commission_paid'] ?? 0);
        $owed = max(0, $earned - $paidCommission);

        $record = [
            'salesperson_id' => $salespersonId,
            'manager_id' => (int)($data['manager_id'] ?? 0),
            'department_id' => (int)($data['department_id'] ?? 0),
            'invoice_id' => $invoiceId,
            'invoice_total' => $invoiceTotal,
            'amount_collected' => $amountCollected,
            'commission_rate' => $rate,
            'commission_earned' => $earned,
            'commission_paid' => $paidCommission,
            'commission_owed' => $owed,
            'status' => $data['status'] ?? 'pending',
            'issue_notes' => $data['issue_notes'] ?? '',
            'dateupdated' => date('Y-m-d H:i:s'),
        ];

        $existing = $this->db->where('invoice_id', $invoiceId)->where('salesperson_id', $salespersonId)->get(db_prefix() . 'sales_center_commissions')->row();
        if ($existing) {
            $this->db->where('id', (int)$existing->id)->update(db_prefix() . 'sales_center_commissions', $record);
            return (int)$existing->id;
        }

        $record['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'sales_center_commissions', $record);
        return $this->db->insert_id();
    }

    public function delete_commission_record($id)
    {
        if (!$this->db->table_exists(db_prefix() . 'sales_center_commissions')) {
            return false;
        }
        $this->db->where('id', (int)$id)->delete(db_prefix() . 'sales_center_commissions');
        return $this->db->affected_rows() > 0;
    }


    public function get_sales_document_options($type = '')
    {
        $types = $type !== '' ? [$type] : ['proposal', 'estimate', 'invoice', 'payment', 'credit_note'];
        $out = [];
        foreach ($types as $docType) {
            foreach ($this->get_sales_documents(['rel_type' => $docType, 'limit' => 250]) as $row) {
                $label = strtoupper(str_replace('_', ' ', $docType)) . ' #' . ($row['number'] ?: $row['rel_id']);
                if (!empty($row['customer_name'])) { $label .= ' - ' . $row['customer_name']; }
                if ((float)$row['total'] > 0) { $label .= ' - ' . (function_exists('app_format_money') ? app_format_money($row['total'], get_base_currency()) : number_format((float)$row['total'], 2)); }
                $out[] = ['id' => $docType . ':' . (int)$row['rel_id'], 'display_name' => $label, 'rel_type' => $docType, 'rel_id' => (int)$row['rel_id'], 'total' => (float)$row['total']];
            }
        }
        return $out;
    }

    public function get_sales_documents($filters = [])
    {
        $types = [];
        $requested = isset($filters['rel_type']) ? trim((string)$filters['rel_type']) : '';
        if ($requested !== '') { $types[] = $requested; } else { $types = ['proposal', 'estimate', 'invoice', 'payment', 'credit_note']; }
        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 500;
        $results = [];
        foreach ($types as $type) {
            $results = array_merge($results, $this->get_sales_documents_by_type($type, $limit));
        }
        usort($results, function($a, $b) { return strcmp((string)($b['datecreated'] ?? ''), (string)($a['datecreated'] ?? '')); });
        return $results;
    }

    private function get_sales_documents_by_type($type, $limit = 500)
    {
        $prefix = db_prefix();
        $rows = [];
        try {
            if ($type === 'proposal' && $this->db->table_exists($prefix . 'proposals')) {
                $this->db->select('p.id as rel_id, p.proposal_to, p.hash, p.datecreated, p.date as document_date, p.total, p.status, p.subject as title, c.company as customer_name');
                $this->db->from($prefix . 'proposals p');
                if ($this->db->table_exists($prefix . 'clients')) { $this->db->join($prefix . 'clients c', 'c.userid = p.rel_id AND p.rel_type = "customer"', 'left'); }
                $this->db->order_by('p.id', 'DESC')->limit($limit);
                foreach ($this->db->get()->result_array() as $r) { $rows[] = $this->normalize_sales_document_row('proposal', $r); }
            }
            if ($type === 'estimate' && $this->db->table_exists($prefix . 'estimates')) {
                $this->db->select('e.id as rel_id, e.hash, e.datecreated, e.date as document_date, e.total, e.status, e.clientid, c.company as customer_name');
                $this->db->from($prefix . 'estimates e');
                if ($this->db->table_exists($prefix . 'clients')) { $this->db->join($prefix . 'clients c', 'c.userid = e.clientid', 'left'); }
                $this->db->order_by('e.id', 'DESC')->limit($limit);
                foreach ($this->db->get()->result_array() as $r) { $rows[] = $this->normalize_sales_document_row('estimate', $r); }
            }
            if ($type === 'invoice' && $this->db->table_exists($prefix . 'invoices')) {
                $this->db->select('i.id as rel_id, i.hash, i.datecreated, i.date as document_date, i.total, i.status, i.clientid, c.company as customer_name');
                $this->db->from($prefix . 'invoices i');
                if ($this->db->table_exists($prefix . 'clients')) { $this->db->join($prefix . 'clients c', 'c.userid = i.clientid', 'left'); }
                $this->db->order_by('i.id', 'DESC')->limit($limit);
                foreach ($this->db->get()->result_array() as $r) { $rows[] = $this->normalize_sales_document_row('invoice', $r); }
            }
            if ($type === 'payment' && $this->db->table_exists($prefix . 'invoicepaymentrecords')) {
                $this->db->select('p.id as rel_id, p.invoiceid, p.amount as total, p.date as document_date, p.daterecorded as datecreated, i.clientid, i.hash, c.company as customer_name');
                $this->db->from($prefix . 'invoicepaymentrecords p');
                if ($this->db->table_exists($prefix . 'invoices')) { $this->db->join($prefix . 'invoices i', 'i.id = p.invoiceid', 'left'); }
                if ($this->db->table_exists($prefix . 'clients')) { $this->db->join($prefix . 'clients c', 'c.userid = i.clientid', 'left'); }
                $this->db->order_by('p.id', 'DESC')->limit($limit);
                foreach ($this->db->get()->result_array() as $r) { $rows[] = $this->normalize_sales_document_row('payment', $r); }
            }
            if ($type === 'credit_note' && $this->db->table_exists($prefix . 'creditnotes')) {
                $this->db->select('cn.id as rel_id, cn.datecreated, cn.date as document_date, cn.total, cn.status, cn.clientid, c.company as customer_name');
                $this->db->from($prefix . 'creditnotes cn');
                if ($this->db->table_exists($prefix . 'clients')) { $this->db->join($prefix . 'clients c', 'c.userid = cn.clientid', 'left'); }
                $this->db->order_by('cn.id', 'DESC')->limit($limit);
                foreach ($this->db->get()->result_array() as $r) { $rows[] = $this->normalize_sales_document_row('credit_note', $r); }
            }
        } catch (Throwable $e) {
            log_activity('Sales Hub Document Query Error: ' . $e->getMessage());
        }
        return $rows;
    }

    private function normalize_sales_document_row($type, $row)
    {
        $id = (int)($row['rel_id'] ?? 0);
        $number = $id;
        if ($type === 'invoice' && function_exists('format_invoice_number')) { $number = format_invoice_number($id); }
        if ($type === 'estimate' && function_exists('format_estimate_number')) { $number = format_estimate_number($id); }
        if ($type === 'proposal' && function_exists('format_proposal_number')) { $number = format_proposal_number($id); }
        if ($type === 'credit_note' && function_exists('format_credit_note_number')) { $number = format_credit_note_number($id); }
        return [
            'rel_type' => $type,
            'rel_id' => $id,
            'number' => (string)$number,
            'hash' => (string)($row['hash'] ?? ''),
            'title' => (string)($row['title'] ?? ''),
            'customer_name' => (string)($row['customer_name'] ?? ($row['proposal_to'] ?? '')),
            'status' => (string)($row['status'] ?? ''),
            'total' => (float)($row['total'] ?? 0),
            'datecreated' => (string)($row['datecreated'] ?? ($row['document_date'] ?? '')),
            'document_date' => (string)($row['document_date'] ?? ''),
            'admin_url' => $this->sales_document_admin_url($type, $id),
            'public_url' => $this->sales_document_public_url($type, $id, (string)($row['hash'] ?? '')),
            'pdf_url' => $this->sales_document_pdf_url($type, $id),
            'download_url' => $this->sales_document_pdf_url($type, $id, true),
            'email_url' => $this->sales_document_email_url($type, $id),
            'email_js_function' => $this->sales_document_email_js_function($type),
        ];
    }

    public function sales_document_admin_url($type, $id)
    {
        $id = (int)$id;
        $map = [
            'proposal' => 'proposals/list_proposals/' . $id,
            'estimate' => 'estimates/list_estimates/' . $id,
            'invoice' => 'invoices/list_invoices/' . $id,
            'payment' => 'payments/payment/' . $id,
            'credit_note' => 'credit_notes/list_credit_notes/' . $id,
        ];
        return admin_url($map[$type] ?? 'sales_center/documents');
    }

    public function sales_document_public_url($type, $id, $hash = '')
    {
        $id = (int)$id;
        $hash = trim((string)$hash);
        if ($hash === '') { return ''; }
        if ($type === 'proposal') { return site_url('proposal/' . $id . '/' . $hash); }
        if ($type === 'estimate') { return site_url('estimate/' . $id . '/' . $hash); }
        if ($type === 'invoice') { return site_url('invoice/' . $id . '/' . $hash); }
        return '';
    }

    public function sales_document_pdf_url($type, $id, $download = false)
    {
        $id = (int)$id;
        $type = trim((string)$type);
        $output = $download ? 'D' : 'I';

        // Use native Perfex CRM PDF endpoints. These endpoints use the CRM PDF templates,
        // PDF library, permissions, and file output handling instead of duplicating logic.
        $map = [
            'proposal'    => 'proposals/pdf/' . $id . '?output_type=' . $output,
            'estimate'    => 'estimates/pdf/' . $id . '?output_type=' . $output,
            'invoice'     => 'invoices/pdf/' . $id . '?output_type=' . $output,
            'payment'     => 'payments/pdf/' . $id . '?output_type=' . $output,
            'credit_note' => 'credit_notes/pdf/' . $id . '?output_type=' . $output,
        ];

        return isset($map[$type]) ? admin_url($map[$type]) : admin_url('sales_center/documents');
    }

    public function sales_document_email_url($type, $id)
    {
        $id = (int)$id;
        $type = trim((string)$type);

        // Use the native Perfex "Send To Email" endpoints. Do not use reminder endpoints
        // because reminders do not open the normal send modal and may skip attachments/templates.
        $map = [
            'proposal'    => 'proposals/send_to_email/' . $id,
            'estimate'    => 'estimates/send_to_email/' . $id,
            'invoice'     => 'invoices/send_to_email/' . $id,
            'credit_note' => 'credit_notes/send_to_email/' . $id,
        ];

        return isset($map[$type]) ? admin_url($map[$type]) : '';
    }

    public function sales_document_email_js_function($type)
    {
        $type = trim((string)$type);
        $map = [
            'proposal'    => 'send_proposal_to_email',
            'estimate'    => 'send_estimate_to_email',
            'invoice'     => 'send_invoice_to_email',
            'credit_note' => 'send_credit_note_to_email',
        ];

        return $map[$type] ?? '';
    }

    public function save_sales_document_link($data)
    {
        if (!$this->db->table_exists(db_prefix() . 'sales_center_document_links')) {
            require_once module_dir_path('sales_center', 'install.php');
        }
        $doc = (string)($data['sales_document'] ?? '');
        $relType = (string)($data['rel_type'] ?? '');
        $relId = (int)($data['rel_id'] ?? 0);
        if ($doc !== '' && strpos($doc, ':') !== false) { [$relType, $relIdRaw] = explode(':', $doc, 2); $relId = (int)$relIdRaw; }
        $allowed = ['proposal','estimate','invoice','payment','credit_note'];
        if (!in_array($relType, $allowed, true) || $relId <= 0 || (int)($data['salesperson_id'] ?? 0) <= 0) { return false; }
        $documentTotal = (float)($data['document_total'] ?? $data['invoice_total'] ?? 0);
        if ($documentTotal <= 0) {
            foreach ($this->get_sales_documents(['rel_type' => $relType, 'limit' => 1000]) as $docRow) { if ((int)$docRow['rel_id'] === $relId) { $documentTotal = (float)$docRow['total']; break; } }
        }
        $amountCollected = (float)($data['amount_collected'] ?? 0);
        $rate = (float)($data['commission_rate'] ?? 0);
        $earned = round(max(0, $amountCollected) * ($rate / 100), 2);
        $paid = (float)($data['commission_paid'] ?? 0);
        $record = [
            'salesperson_id' => (int)$data['salesperson_id'],
            'manager_id' => (int)($data['manager_id'] ?? 0),
            'department_id' => (int)($data['department_id'] ?? 0),
            'rel_type' => $relType,
            'rel_id' => $relId,
            'document_total' => $documentTotal,
            'amount_collected' => $amountCollected,
            'commission_rate' => $rate,
            'commission_earned' => $earned,
            'commission_paid' => $paid,
            'commission_owed' => max(0, $earned - $paid),
            'status' => (string)($data['status'] ?? 'pending'),
            'issue_notes' => (string)($data['issue_notes'] ?? ''),
            'dateupdated' => date('Y-m-d H:i:s'),
        ];
        $existing = $this->db->where('rel_type', $relType)->where('rel_id', $relId)->where('salesperson_id', (int)$data['salesperson_id'])->get(db_prefix() . 'sales_center_document_links')->row();
        if ($existing) { $this->db->where('id', (int)$existing->id)->update(db_prefix() . 'sales_center_document_links', $record); return (int)$existing->id; }
        $record['created_by'] = function_exists('get_staff_user_id') ? get_staff_user_id() : 0;
        $record['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'sales_center_document_links', $record);
        return $this->db->insert_id();
    }

    public function get_sales_document_links($filters = [])
    {
        if (!$this->db->table_exists(db_prefix() . 'sales_center_document_links')) { return []; }
        $this->db->select('l.*, s.company as salesperson_company, m.company as manager_name, d.name as department_name');
        $this->db->from(db_prefix() . 'sales_center_document_links l');
        $this->db->join(db_prefix() . 'sales_center s', 's.id = l.salesperson_id', 'left');
        $this->db->join(db_prefix() . 'sales_center m', 'm.id = l.manager_id', 'left');
        if ($this->db->table_exists(db_prefix() . 'departments')) { $this->db->join(db_prefix() . 'departments d', 'd.departmentid = l.department_id', 'left'); }
        if (!empty($filters['salesperson_id'])) { $this->db->where('l.salesperson_id', (int)$filters['salesperson_id']); }
        if (!empty($filters['rel_type'])) { $this->db->where('l.rel_type', (string)$filters['rel_type']); }
        if (!empty($filters['status'])) { $this->db->where('l.status', (string)$filters['status']); }
        $this->db->order_by('l.datecreated', 'DESC');
        $links = $this->db->get()->result_array();
        $docs = [];
        foreach ($links as $link) {
            $doc = null;
            foreach ($this->get_sales_documents(['rel_type' => $link['rel_type'], 'limit' => 1000]) as $candidate) { if ((int)$candidate['rel_id'] === (int)$link['rel_id']) { $doc = $candidate; break; } }
            $link['document'] = $doc;
            $docs[] = $link;
        }
        return $docs;
    }

    public function delete_sales_document_link($id)
    {
        if (!$this->db->table_exists(db_prefix() . 'sales_center_document_links')) { return false; }
        $this->db->where('id', (int)$id)->delete(db_prefix() . 'sales_center_document_links');
        return $this->db->affected_rows() > 0;
    }

}
