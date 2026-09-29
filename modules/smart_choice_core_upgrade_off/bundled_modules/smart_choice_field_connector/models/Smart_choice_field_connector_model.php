<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_field_connector_model extends App_Model
{
    public function get_supported_modules()
    {
        return [
            'customer'     => _l('scfc_customer'),
            'contact'      => _l('scfc_contact'),
            'project'      => _l('scfc_project'),
            'estimate'     => _l('scfc_estimate'),
            'proposal'     => _l('scfc_proposal'),
            'invoice'      => _l('scfc_invoice'),
            'contract'     => _l('scfc_contract'),
            'lead'         => _l('scfc_lead'),
            'staff'        => _l('scfc_staff'),
            'company'      => _l('scfc_company'),
            'custom_field' => _l('scfc_custom_field'),
        ];
    }

    public function get_fields_for_module($module)
    {
        $fields = [
            'customer'     => ['company', 'vat', 'phonenumber', 'website', 'address', 'city', 'state', 'zip', 'country', 'billing_street', 'shipping_street'],
            'contact'      => ['firstname', 'lastname', 'email', 'phonenumber', 'title', 'last_login', 'is_primary'],
            'project'      => ['name', 'description', 'status', 'clientid', 'start_date', 'deadline', 'billing_type', 'project_cost'],
            'estimate'     => ['number', 'prefix', 'date', 'expirydate', 'subtotal', 'total', 'clientnote', 'adminnote', 'status'],
            'proposal'     => ['subject', 'content', 'date', 'open_till', 'subtotal', 'total', 'proposal_to', 'email', 'status'],
            'invoice'      => ['number', 'prefix', 'date', 'duedate', 'subtotal', 'total', 'clientnote', 'adminnote', 'status'],
            'contract'     => ['subject', 'description', 'contract_value', 'datestart', 'dateend', 'client', 'project_id'],
            'lead'         => ['name', 'title', 'email', 'phonenumber', 'company', 'address', 'city', 'state', 'zip', 'description', 'status', 'source'],
            'staff'        => ['firstname', 'lastname', 'email', 'phonenumber', 'role', 'department'],
            'company'      => ['companyname', 'main_domain', 'crm_url', 'admin_url', 'logo_url'],
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
            $fieldTo = isset($row['fieldto']) ? (string) $row['fieldto'] : '';
            $slug = isset($row['slug']) ? (string) $row['slug'] : '';
            $name = isset($row['name']) ? (string) $row['name'] : '';
            $out[] = $fieldTo . ':' . $slug . ' - ' . $name;
        }
        return $out;
    }

    public function get_groups()
    {
        $this->ensure_tables();
        return $this->db->order_by('group_name ASC')->get(db_prefix() . 'scfc_groups')->result_array();
    }

    public function get_mappings()
    {
        $this->ensure_tables();
        return $this->db
            ->select('m.*, g.group_name')
            ->from(db_prefix() . 'scfc_mappings m')
            ->join(db_prefix() . 'scfc_groups g', 'g.id = m.group_id', 'left')
            ->order_by('m.id DESC')
            ->get()
            ->result_array();
    }

    public function get_custom_tokens()
    {
        $this->ensure_tables();
        return $this->db
            ->select('t.*, g.group_name')
            ->from(db_prefix() . 'scfc_custom_tokens t')
            ->join(db_prefix() . 'scfc_groups g', 'g.id = t.group_id', 'left')
            ->order_by('t.id DESC')
            ->get()
            ->result_array();
    }

    public function get_combinations()
    {
        $this->ensure_tables();
        return $this->db
            ->select('c.*, g.group_name')
            ->from(db_prefix() . 'scfc_combinations c')
            ->join(db_prefix() . 'scfc_groups g', 'g.id = c.group_id', 'left')
            ->order_by('c.id DESC')
            ->get()
            ->result_array();
    }

    public function get_combination($id)
    {
        $this->ensure_tables();
        return $this->db->where('id', (int) $id)->get(db_prefix() . 'scfc_combinations')->row_array();
    }

    public function save_group($data, $id = 0)
    {
        $name = trim((string) ($data['group_name'] ?? ''));
        $key = trim((string) ($data['group_key'] ?? ''));
        if ($key === '') {
            $key = $name;
        }
        $key = $this->clean_key($key);

        $insert = [
            'group_name'  => $name,
            'group_key'   => $key,
            'description' => trim((string) ($data['description'] ?? '')),
            'is_active'   => isset($data['is_active']) ? 1 : 0,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        if ($insert['group_name'] === '' || $insert['group_key'] === '') {
            return false;
        }

        if ($id > 0) {
            return $this->db->where('id', $id)->update(db_prefix() . 'scfc_groups', $insert);
        }

        $insert['created_by'] = get_staff_user_id();
        $insert['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert(db_prefix() . 'scfc_groups', $insert);
    }

    public function save_mapping($data, $id = 0)
    {
        $insert = [
            'group_id'           => !empty($data['group_id']) ? (int) $data['group_id'] : null,
            'source_module'      => trim((string) ($data['source_module'] ?? '')),
            'source_field'       => trim((string) ($data['source_field'] ?? '')),
            'destination_module' => trim((string) ($data['destination_module'] ?? '')),
            'destination_field'  => trim((string) ($data['destination_field'] ?? '')),
            'merge_tag'          => $this->normalize_merge_tag(trim((string) ($data['merge_tag'] ?? ''))),
            'notes'              => trim((string) ($data['notes'] ?? '')),
            'is_active'          => isset($data['is_active']) ? 1 : 0,
            'updated_at'         => date('Y-m-d H:i:s'),
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
        $tokenKey = $this->normalize_merge_tag((string) ($data['token_key'] ?? ''));

        $insert = [
            'group_id'      => !empty($data['group_id']) ? (int) $data['group_id'] : null,
            'token_name'    => trim((string) ($data['token_name'] ?? '')),
            'token_key'     => strtolower($tokenKey),
            'source_module' => trim((string) ($data['source_module'] ?? '')),
            'source_field'  => trim((string) ($data['source_field'] ?? '')),
            'description'   => trim((string) ($data['description'] ?? '')),
            'is_active'     => isset($data['is_active']) ? 1 : 0,
            'updated_at'    => date('Y-m-d H:i:s'),
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

    public function save_combination($data, $id = 0)
    {
        $name = trim((string) ($data['combination_name'] ?? ''));
        $key = trim((string) ($data['combination_key'] ?? ''));
        if ($key === '') {
            $key = $name;
        }

        $insert = [
            'group_id'         => !empty($data['group_id']) ? (int) $data['group_id'] : null,
            'combination_name' => $name,
            'combination_key'  => $this->clean_key($key),
            'template'         => trim((string) ($data['template'] ?? '')),
            'description'      => trim((string) ($data['description'] ?? '')),
            'is_active'        => isset($data['is_active']) ? 1 : 0,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        if (!$insert['combination_name'] || !$insert['combination_key'] || !$insert['template']) {
            return false;
        }

        if ($id > 0) {
            return $this->db->where('id', $id)->update(db_prefix() . 'scfc_combinations', $insert);
        }

        $insert['created_by'] = get_staff_user_id();
        $insert['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert(db_prefix() . 'scfc_combinations', $insert);
    }

    public function delete_mapping($id)
    {
        return $this->db->where('id', $id)->delete(db_prefix() . 'scfc_mappings');
    }

    public function delete_custom_token($id)
    {
        return $this->db->where('id', $id)->delete(db_prefix() . 'scfc_custom_tokens');
    }

    public function delete_group($id)
    {
        $this->db->where('group_id', (int) $id)->update(db_prefix() . 'scfc_mappings', ['group_id' => null]);
        $this->db->where('group_id', (int) $id)->update(db_prefix() . 'scfc_custom_tokens', ['group_id' => null]);
        $this->db->where('group_id', (int) $id)->update(db_prefix() . 'scfc_combinations', ['group_id' => null]);
        return $this->db->where('id', (int) $id)->delete(db_prefix() . 'scfc_groups');
    }

    public function delete_combination($id)
    {
        return $this->db->where('id', (int) $id)->delete(db_prefix() . 'scfc_combinations');
    }

    public function token_impact($id)
    {
        $token = $this->db->where('id', (int) $id)->get(db_prefix() . 'scfc_custom_tokens')->row_array();
        if (!$token) {
            return ['total' => 0, 'items' => []];
        }
        return $this->search_token_usage($token['token_key']);
    }

    public function mapping_impact($id)
    {
        $mapping = $this->db->where('id', (int) $id)->get(db_prefix() . 'scfc_mappings')->row_array();
        if (!$mapping || empty($mapping['merge_tag'])) {
            return ['total' => 0, 'items' => []];
        }
        return $this->search_token_usage($mapping['merge_tag']);
    }

    public function combination_impact($id)
    {
        $combo = $this->get_combination((int) $id);
        if (!$combo || empty($combo['template'])) {
            return ['total' => 0, 'items' => []];
        }
        return $this->search_token_usage($combo['template']);
    }

    public function preview_template($template)
    {
        $template = (string) $template;
        $tokens = $this->available_preview_tokens();
        $output = $template;
        foreach ($tokens as $token => $sample) {
            $output = str_replace($token, $sample, $output);
        }

        preg_match_all('/\{[a-zA-Z0-9_:\-]+\}/', $template, $matches);
        $used = isset($matches[0]) ? array_values(array_unique($matches[0])) : [];
        $missing = [];
        foreach ($used as $token) {
            if (!array_key_exists($token, $tokens)) {
                $missing[] = $token;
            }
        }

        return [
            'success' => true,
            'input' => $template,
            'output' => $output,
            'used_tokens' => $used,
            'missing_tokens' => $missing,
        ];
    }

    public function database_report()
    {
        $checks = [];
        foreach ([db_prefix() . 'scfc_groups', db_prefix() . 'scfc_mappings', db_prefix() . 'scfc_custom_tokens', db_prefix() . 'scfc_combinations', db_prefix() . 'customfields'] as $table) {
            $checks[] = [
                'name'   => $this->human_table_name($table),
                'status' => $this->db->table_exists($table) ? 'OK' : 'Missing',
            ];
        }
        return $checks;
    }

    public function health_check()
    {
        $this->ensure_tables();
        return [
            ['label' => _l('scfc_groups_table'), 'status' => $this->db->table_exists(db_prefix() . 'scfc_groups') ? 'OK' : 'Missing'],
            ['label' => _l('scfc_mappings_table'), 'status' => $this->db->table_exists(db_prefix() . 'scfc_mappings') ? 'OK' : 'Missing'],
            ['label' => _l('scfc_tokens_table'), 'status' => $this->db->table_exists(db_prefix() . 'scfc_custom_tokens') ? 'OK' : 'Missing'],
            ['label' => _l('scfc_combinations_table'), 'status' => $this->db->table_exists(db_prefix() . 'scfc_combinations') ? 'OK' : 'Missing'],
            ['label' => _l('scfc_perfex_custom_fields_table'), 'status' => $this->db->table_exists(db_prefix() . 'customfields') ? 'OK' : 'Missing'],
            ['label' => _l('scfc_active_groups'), 'status' => (string) $this->safe_count(db_prefix() . 'scfc_groups', 'is_active', 1)],
            ['label' => _l('scfc_active_mappings'), 'status' => (string) $this->safe_count(db_prefix() . 'scfc_mappings', 'is_active', 1)],
            ['label' => _l('scfc_active_tokens'), 'status' => (string) $this->safe_count(db_prefix() . 'scfc_custom_tokens', 'is_active', 1)],
            ['label' => _l('scfc_active_combinations'), 'status' => (string) $this->safe_count(db_prefix() . 'scfc_combinations', 'is_active', 1)],
            ['label' => 'PHP', 'status' => PHP_VERSION],
        ];
    }

    public function available_preview_tokens()
    {
        $tokens = [
            '{customer_company}' => 'Smart Choice Sample Customer LLC',
            '{contact_firstname}' => 'Michael',
            '{contact_lastname}' => 'Owner',
            '{contact_email}' => 'customer@example.com',
            '{project_name}' => 'Tampa Bay Remodeling Project',
            '{estimate_total}' => '$12,500.00',
            '{proposal_subject}' => 'Residential Remodeling Proposal',
            '{contract_subject}' => 'Construction Agreement',
            '{invoice_total}' => '$8,750.00',
            '{companyname}' => get_option('companyname') ? get_option('companyname') : 'Smart Choice Contractors USA',
            '{crm_url}' => site_url(),
            '{admin_url}' => admin_url(),
        ];

        foreach ($this->get_custom_tokens() as $token) {
            if (!empty($token['token_key'])) {
                $tokens[$token['token_key']] = '[' . $token['token_name'] . ']';
            }
        }

        return $tokens;
    }

    private function search_token_usage($token)
    {
        $token = (string) $token;
        $tables = [
            'contracts' => ['subject', 'description'],
            'proposals' => ['subject', 'content'],
            'emailtemplates' => ['subject', 'message'],
            'estimates' => ['clientnote', 'adminnote'],
            'invoices' => ['clientnote', 'adminnote'],
            'scfc_combinations' => ['template', 'description'],
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
                    $items[] = ['table' => $this->human_table_name($full), 'column' => $this->human_label($col), 'count' => $count];
                }
            }
        }
        return ['total' => array_sum(array_column($items, 'count')), 'items' => $items, 'token' => $token];
    }

    private function normalize_merge_tag($value)
    {
        $value = trim((string) $value);
        $value = str_replace(['{', '}'], '', $value);
        $value = $this->clean_key($value);
        if ($value === '') {
            return '';
        }
        return '{' . strtolower($value) . '}';
    }

    private function clean_key($value)
    {
        $value = trim((string) $value);
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim($value, '_');
    }

    private function safe_count($table, $field, $value)
    {
        if (!$this->db->table_exists($table) || !$this->db->field_exists($field, $table)) {
            return 0;
        }
        return (int) $this->db->where($field, $value)->count_all_results($table);
    }

    private function ensure_tables()
    {
        if (!$this->db->table_exists(db_prefix() . 'scfc_groups') || !$this->db->table_exists(db_prefix() . 'scfc_combinations')) {
            require_once(module_dir_path('smart_choice_field_connector') . 'install.php');
        }
    }

    private function human_table_name($value)
    {
        $value = str_replace(db_prefix(), '', (string) $value);
        $value = str_replace(['scfc_', '_'], ['', ' '], $value);
        return ucwords(trim($value));
    }

    private function human_label($value)
    {
        return ucwords(str_replace(['_', '-'], ' ', (string) $value));
    }
}
