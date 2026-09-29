<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_field_connector_model extends App_Model
{
    public function get_supported_modules()
    {
        return [
            'customer' => 'Customer',
            'contact' => 'Contact',
            'project' => 'Project',
            'estimate' => 'Estimate',
            'proposal' => 'Proposal',
            'invoice' => 'Invoice',
            'contract' => 'Contract',
            'lead' => 'Lead',
            'custom_field' => 'Custom Field',
        ];
    }

    public function get_fields_for_module($module)
    {
        $fields = [
            'customer' => ['company','vat','phonenumber','website','address','city','state','zip','country','billing_street','shipping_street'],
            'contact' => ['firstname','lastname','email','phonenumber','title','last_login','is_primary'],
            'project' => ['name','description','status','clientid','start_date','deadline','billing_type','project_cost'],
            'estimate' => ['number','prefix','date','expirydate','subtotal','total','clientnote','adminnote','status'],
            'proposal' => ['subject','content','date','open_till','subtotal','total','proposal_to','email','status'],
            'invoice' => ['number','prefix','date','duedate','subtotal','total','clientnote','adminnote','status'],
            'contract' => ['subject','description','contract_value','datestart','dateend','client','project_id'],
            'lead' => ['name','title','email','phonenumber','company','address','city','state','zip','description','status','source'],
            'custom_field' => $this->get_custom_field_slugs(),
        ];
        return isset($fields[$module]) ? $fields[$module] : [];
    }

    public function get_custom_field_slugs()
    {
        if (!$this->db->table_exists(db_prefix() . 'customfields')) {
            return [];
        }
        $rows = $this->db->select('slug,name,fieldto')->from(db_prefix() . 'customfields')->order_by('fieldto ASC, name ASC')->get()->result_array();
        $out = [];
        foreach ($rows as $row) {
            $out[] = $row['fieldto'] . ':' . $row['slug'] . ' - ' . $row['name'];
        }
        return $out;
    }

    public function get_mappings()
    {
        return $this->db->order_by('id DESC')->get(db_prefix() . 'scfc_mappings')->result_array();
    }

    public function get_custom_tokens()
    {
        return $this->db->order_by('id DESC')->get(db_prefix() . 'scfc_custom_tokens')->result_array();
    }

    public function save_mapping($data, $id = 0)
    {
        $insert = [
            'source_module' => trim((string)($data['source_module'] ?? '')),
            'source_field' => trim((string)($data['source_field'] ?? '')),
            'destination_module' => trim((string)($data['destination_module'] ?? '')),
            'destination_field' => trim((string)($data['destination_field'] ?? '')),
            'merge_tag' => trim((string)($data['merge_tag'] ?? '')),
            'notes' => trim((string)($data['notes'] ?? '')),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (!$insert['source_module'] || !$insert['source_field'] || !$insert['destination_module'] || !$insert['destination_field']) {
            return false;
        }
        if ($id > 0) {
            return $this->db->where('id', $id)->update(db_prefix() . 'scfc_mappings', $insert);
        }
        $insert['created_by'] = get_staff_user_id();
        $insert['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert(db_prefix() . 'scfc_mappings', $insert);
    }

    public function save_custom_token($data, $id = 0)
    {
        $tokenKey = trim((string)($data['token_key'] ?? ''));
        $tokenKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $tokenKey);
        $tokenKey = trim($tokenKey, '_');
        if ($tokenKey && strpos($tokenKey, '{') === false) {
            $tokenKey = '{' . strtolower($tokenKey) . '}';
        }
        $insert = [
            'token_name' => trim((string)($data['token_name'] ?? '')),
            'token_key' => strtolower($tokenKey),
            'source_module' => trim((string)($data['source_module'] ?? '')),
            'source_field' => trim((string)($data['source_field'] ?? '')),
            'description' => trim((string)($data['description'] ?? '')),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (!$insert['token_name'] || !$insert['token_key'] || !$insert['source_module'] || !$insert['source_field']) {
            return false;
        }
        if ($id > 0) {
            return $this->db->where('id', $id)->update(db_prefix() . 'scfc_custom_tokens', $insert);
        }
        $insert['created_by'] = get_staff_user_id();
        $insert['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert(db_prefix() . 'scfc_custom_tokens', $insert);
    }

    public function delete_mapping($id)
    {
        return $this->db->where('id', $id)->delete(db_prefix() . 'scfc_mappings');
    }

    public function delete_custom_token($id)
    {
        return $this->db->where('id', $id)->delete(db_prefix() . 'scfc_custom_tokens');
    }

    public function token_impact($id)
    {
        $token = $this->db->where('id', $id)->get(db_prefix() . 'scfc_custom_tokens')->row_array();
        if (!$token) {
            return ['total' => 0, 'items' => []];
        }
        return $this->search_token_usage($token['token_key']);
    }

    public function mapping_impact($id)
    {
        $mapping = $this->db->where('id', $id)->get(db_prefix() . 'scfc_mappings')->row_array();
        if (!$mapping || empty($mapping['merge_tag'])) {
            return ['total' => 0, 'items' => []];
        }
        return $this->search_token_usage($mapping['merge_tag']);
    }

    private function search_token_usage($token)
    {
        $tables = [
            'contracts' => ['subject','description'],
            'proposals' => ['subject','content'],
            'emailtemplates' => ['subject','message'],
            'estimates' => ['clientnote','adminnote'],
            'invoices' => ['clientnote','adminnote'],
        ];
        $items = [];
        foreach ($tables as $table => $columns) {
            $full = db_prefix() . $table;
            if (!$this->db->table_exists($full)) {
                continue;
            }
            foreach ($columns as $col) {
                if (!$this->db->field_exists($col, $full)) {
                    continue;
                }
                $count = $this->db->like($col, $token)->count_all_results($full);
                if ($count > 0) {
                    $items[] = ['table' => $full, 'column' => $col, 'count' => $count];
                }
            }
        }
        return ['total' => array_sum(array_column($items, 'count')), 'items' => $items, 'token' => $token];
    }

    public function database_report()
    {
        $checks = [];
        foreach ([db_prefix().'scfc_mappings', db_prefix().'scfc_custom_tokens', db_prefix().'customfields'] as $table) {
            $checks[] = [
                'name' => $table,
                'status' => $this->db->table_exists($table) ? 'OK' : 'Missing',
            ];
        }
        return $checks;
    }

    public function health_check()
    {
        $report = [];
        $report[] = ['label' => 'Mappings table', 'status' => $this->db->table_exists(db_prefix().'scfc_mappings') ? 'OK' : 'Missing'];
        $report[] = ['label' => 'Custom tokens table', 'status' => $this->db->table_exists(db_prefix().'scfc_custom_tokens') ? 'OK' : 'Missing'];
        $report[] = ['label' => 'Perfex custom fields table', 'status' => $this->db->table_exists(db_prefix().'customfields') ? 'OK' : 'Missing'];
        $report[] = ['label' => 'Active mappings', 'status' => (string)$this->db->where('is_active',1)->count_all_results(db_prefix().'scfc_mappings')];
        $report[] = ['label' => 'Active custom tokens', 'status' => (string)$this->db->where('is_active',1)->count_all_results(db_prefix().'scfc_custom_tokens')];
        return $report;
    }
}
