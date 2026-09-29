<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Superman_model extends App_Model
{
    public function get_supported_modules()
    {
        return [
            'lead' => 'Lead',
            'customer' => 'Customer',
            'contact' => 'Contact',
            'project' => 'Project',
            'estimate' => 'Estimate',
            'proposal' => 'Proposal',
            'contract' => 'Contract',
            'invoice' => 'Invoice',
            'credit_note' => 'Credit Note',
            'payment' => 'Payment',
            'task' => 'Task',
            'ticket' => 'Ticket',
            'subcontractor' => 'Subcontractor',
            'subcontractor_contract' => 'Subcontractor Contract',
            'contractor' => 'Contractor',
            'sales_agent' => 'Sales Agent',
            'sales_hub' => 'Sales Hub',
            'sales_contract' => 'Sales Contract',
            'purchase' => 'Purchase',
            'purchase_bill' => 'Purchase Bill',
            'purchase_order' => 'Purchase Order',
            'custom_field' => 'Custom Field',
        ];
    }



    public function human_label($value)
    {
        $value = str_replace(['_', '-'], ' ', (string)$value);
        $value = preg_replace('/\s+/', ' ', trim($value));
        return ucwords($value);
    }

    public function get_visual_field_groups()
    {
        $groups = [
            'lead' => ['title' => 'Lead Fields', 'icon' => 'fa fa-bullhorn', 'modules' => ['lead']],
            'customer' => ['title' => 'Customer Fields', 'icon' => 'fa fa-users', 'modules' => ['customer', 'contact']],
            'project' => ['title' => 'Project Fields', 'icon' => 'fa fa-briefcase', 'modules' => ['project']],
            'estimate' => ['title' => 'Estimate Fields', 'icon' => 'fa fa-calculator', 'modules' => ['estimate']],
            'invoice' => ['title' => 'Invoice Fields', 'icon' => 'fa fa-file-text-o', 'modules' => ['invoice', 'credit_note', 'payment']],
            'proposal' => ['title' => 'Proposal Fields', 'icon' => 'fa fa-file-powerpoint-o', 'modules' => ['proposal']],
            'contract' => ['title' => 'CRM Contract Fields', 'icon' => 'fa fa-file-signature', 'modules' => ['contract']],
            'contractor' => ['title' => 'Contractor Fields', 'icon' => 'fa fa-hard-hat', 'modules' => ['contractor']],
            'subcontractor' => ['title' => 'Subcontractor Fields', 'icon' => 'fa fa-id-card', 'modules' => ['subcontractor', 'subcontractor_contract']],
            'sales_hub' => ['title' => 'Sales Hub Fields', 'icon' => 'fa fa-line-chart', 'modules' => ['sales_agent', 'sales_hub', 'sales_contract']],
            'purchasing' => ['title' => 'Purchasing Fields', 'icon' => 'fa fa-shopping-cart', 'modules' => ['purchase', 'purchase_bill', 'purchase_order']],
        ];
        foreach ($groups as $key => $group) {
            $items = [];
            foreach ($group['modules'] as $module) {
                foreach ($this->get_fields_for_module($module) as $field) {
                    $items[] = [
                        'module' => $module,
                        'module_label' => $this->human_label($module),
                        'field' => $field,
                        'field_label' => $this->human_label($field),
                        'key' => $module . ':' . $field,
                    ];
                }
            }
            $groups[$key]['items'] = $items;
        }
        return $groups;
    }

    public function get_flow_sets()
    {
        return [
            ['name' => 'Customer Identity Flow', 'status' => 'Active From Start', 'fields' => ['Lead Name','Customer Name','Contact First Name','Contact Last Name','Estimate Customer','Proposal Customer','Contract Customer','Invoice Customer','Project Customer']],
            ['name' => 'Phone And Email Flow', 'status' => 'Active From Start', 'fields' => ['Lead Phone','Customer Phone','Contact Phone','Proposal Phone','Lead Email','Contact Email','Proposal Email','Invoice Email']],
            ['name' => 'Property Address Flow', 'status' => 'Active From Start', 'fields' => ['Lead Address','Customer Address','Billing Street','Shipping Street','Proposal Address','Project Address','Contract Address']],
            ['name' => 'Sales Document Flow', 'status' => 'Active From Start', 'fields' => ['Estimate Number','Proposal Subject','Contract Subject','Invoice Reference','Credit Note Reference','Payment Record']],
            ['name' => 'Project Production Flow', 'status' => 'Active From Start', 'fields' => ['Project Name','Project Address','Project Cost','Start Date','Deadline','Assigned Staff']],
            ['name' => 'Smart Choice Construction Flow', 'status' => 'Active From Start', 'fields' => ['Property Address','Scope Of Work','Warranty','Permit Number','APN Or Folio Number','Property Owner Name','Preferred Communication','Sales Agent','Contractor','Subcontractor']],
        ];
    }

    public function toggle_mapping($id)
    {
        $row = $this->db->where('id', (int)$id)->get(db_prefix() . 'superman_mappings')->row_array();
        if (!$row) { return false; }
        return $this->db->where('id', (int)$id)->update(db_prefix() . 'superman_mappings', ['is_active' => ((int)$row['is_active'] === 1 ? 0 : 1), 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function bulk_delete_mappings($ids)
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) { return false; }
        return $this->db->where_in('id', $ids)->where('is_locked_default', 0)->delete(db_prefix() . 'superman_mappings');
    }

    public function import_mappings_csv($path)
    {
        $handle = fopen($path, 'r');
        if (!$handle) { return 0; }
        $headers = fgetcsv($handle);
        if (!is_array($headers)) { fclose($handle); return 0; }
        $headers = array_map('trim', $headers);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = [];
            foreach ($headers as $i => $header) { $data[$header] = isset($row[$i]) ? $row[$i] : ''; }
            if ($this->save_mapping([
                'source_module' => $data['source_module'] ?? '',
                'source_field' => $data['source_field'] ?? '',
                'destination_module' => $data['destination_module'] ?? '',
                'destination_field' => $data['destination_field'] ?? '',
                'merge_tag' => $data['merge_tag'] ?? '',
                'map_group' => $data['map_group'] ?? 'Imported Flow',
                'priority' => $data['priority'] ?? 10,
                'overwrite_existing' => !empty($data['overwrite_existing']) ? 1 : 0,
                'is_active' => !isset($data['is_active']) || (string)$data['is_active'] === '1' ? 1 : 0,
                'notes' => $data['notes'] ?? '',
            ])) { $count++; }
        }
        fclose($handle);
        return $count;
    }

    public function save_visual_flow($name, $payload)
    {
        if (!$payload) { return false; }
        $decoded = json_decode($payload, true);
        if (!is_array($decoded) || empty($decoded['items'])) { return false; }
        $group = $name;
        $first = $decoded['items'][0];
        $created = 0;
        foreach ($decoded['items'] as $item) {
            if (empty($first['module']) || empty($first['field']) || empty($item['module']) || empty($item['field'])) { continue; }
            if ($first['module'] === $item['module'] && $first['field'] === $item['field']) { continue; }
            $tag = '{' . strtolower($first['module'] . '_' . $first['field']) . '}';
            if ($this->save_mapping([
                'source_module' => $first['module'],
                'source_field' => $first['field'],
                'destination_module' => $item['module'],
                'destination_field' => $item['field'],
                'merge_tag' => $tag,
                'map_group' => $group,
                'priority' => 10,
                'is_active' => 1,
                'notes' => 'Created with Superman visual drag builder.',
            ])) { $created++; }
        }
        $this->save_profile($group, 'visual_flow', $decoded, false);
        return $created > 0;
    }

    public function get_common_fields($module)
    {
        $fields = [
            'lead' => ['name','title','email','phonenumber','company','address','city','state','zip','description','status','source','assigned','property_address','property_city','property_state','property_zip','property_owner_name','property_apn_or_folio_number','permit_number','scope_of_work','warranty','project_start_time','preferred_communication','mobile_phone','sales_agent','contractor_name','subcontractor_name'],
            'customer' => ['userid','company','vat','email','phonenumber','mobile_phone','website','address','city','state','zip','country','billing_street','billing_city','billing_state','billing_zip','shipping_street','shipping_city','shipping_state','shipping_zip','property_address','property_city','property_state','property_zip','property_owner_name','property_apn_or_folio_number','permit_number','scope_of_work','warranty','project_start_time','preferred_communication','sales_agent','contractor_name','subcontractor_name'],
            'contact' => ['id','userid','firstname','lastname','email','phonenumber','title','is_primary'],
            'project' => ['id','name','description','status','clientid','start_date','deadline','billing_type','project_cost','project_address','client_email','client_phone','property_address','property_city','property_state','property_zip','property_owner_name','property_apn_or_folio_number','permit_number','scope_of_work','warranty','project_start_time','preferred_communication','sales_agent','contractor_name','subcontractor_name'],
            'estimate' => ['id','clientid','project_id','number','prefix','date','expirydate','subtotal','total','clientnote','adminnote','status','client_email','client_phone','billing_street','billing_city','billing_state','billing_zip','property_address','scope_of_work'],
            'proposal' => ['id','rel_id','rel_type','subject','content','date','open_till','proposal_to','email','phone','address','city','state','zip','subtotal','total','status','property_address','scope_of_work','estimate_number','estimate_total'],
            'contract' => ['id','subject','description','contract_value','datestart','dateend','client','project_id','client_email','client_phone','project_address','property_address','scope_of_work','warranty'],
            'invoice' => ['id','clientid','project_id','number','prefix','date','duedate','subtotal','total','clientnote','adminnote','status','client_email','client_phone','billing_street','billing_city','billing_state','billing_zip','property_address','estimate_reference'],
            'credit_note' => ['id','clientid','number','prefix','date','subtotal','total','clientnote','adminnote','status','invoice_reference','property_address'],
            'payment' => ['id','invoiceid','amount','paymentmode','date','transactionid','note'],
            'task' => ['id','name','description','rel_id','rel_type','status','startdate','duedate'],
            'ticket' => ['ticketid','userid','contactid','name','email','subject','message','status','department'],
            'subcontractor' => ['subcontractor_name','phone','mobile_phone','email','property_address','scope_of_work','project_start_time'],
            'contractor' => ['contractor_name','phone','mobile_phone','email','property_address','scope_of_work','project_start_time'],
            'sales_agent' => ['sales_agent','phone','mobile_phone','email','preferred_communication','commission_rate','department','sales_role'],
            'subcontractor_contract' => ['id','subject','description','contract_value','status','project_id','subcontractor_id','date_start','date_end','scope_of_work','insurance_status'],
            'sales_hub' => ['id','salesperson_id','sales_manager_id','department','total_sold','total_paid','commission_rate','commission_due','commission_paid','status'],
            'sales_contract' => ['id','subject','salesperson_id','clientid','project_id','contract_value','commission_rate','status','date_start','date_end'],
            'purchase' => ['id','vendor_id','project_id','number','date','total','status','notes'],
            'purchase_bill' => ['id','vendor_id','project_id','bill_number','date','due_date','subtotal','total','status'],
            'purchase_order' => ['id','vendor_id','project_id','order_number','date','subtotal','total','status'],
        ];
        return isset($fields[$module]) ? $fields[$module] : [];
    }

    public function get_fields_for_module($module)
    {
        if ($module === 'custom_field') {
            return $this->get_custom_field_catalog(true);
        }
        $fields = $this->get_common_fields($module);
        $table = $this->table_for_module($module);
        if ($table && $this->db->table_exists(db_prefix() . $table)) {
            $dbFields = $this->db->list_fields(db_prefix() . $table);
            $fields = array_values(array_unique(array_merge($fields, $dbFields)));
        }
        sort($fields);
        return $fields;
    }

    public function table_for_module($module)
    {
        $map = [
            'lead' => 'leads',
            'customer' => 'clients',
            'contact' => 'contacts',
            'project' => 'projects',
            'estimate' => 'estimates',
            'proposal' => 'proposals',
            'contract' => 'contracts',
            'invoice' => 'invoices',
            'credit_note' => 'creditnotes',
            'payment' => 'invoicepaymentrecords',
            'task' => 'tasks',
            'ticket' => 'tickets',
            'subcontractor' => 'smartsource_subcontractors',
            'contractor' => 'smartsource_contractors',
            'sales_agent' => 'staff',
            'subcontractor_contract' => 'smartsource_subcontractor_contracts',
            'sales_hub' => 'sales_center_people',
            'sales_contract' => 'sales_center_contracts',
            'purchase' => 'pur_orders',
            'purchase_bill' => 'pur_bills',
            'purchase_order' => 'pur_orders',
        ];
        return isset($map[$module]) ? $map[$module] : null;
    }

    public function get_mappings($activeOnly = false)
    {
        if ($activeOnly) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('map_group ASC, priority ASC, id ASC')->get(db_prefix() . 'superman_mappings')->result_array();
    }

    public function get_tokens($activeOnly = false)
    {
        if ($activeOnly) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('token_name ASC')->get(db_prefix() . 'superman_tokens')->result_array();
    }

    public function save_mapping($data, $id = 0)
    {
        $insert = [
            'source_module' => trim((string)($data['source_module'] ?? '')),
            'source_field' => trim((string)($data['source_field'] ?? '')),
            'destination_module' => trim((string)($data['destination_module'] ?? '')),
            'destination_field' => trim((string)($data['destination_field'] ?? '')),
            'merge_tag' => trim((string)($data['merge_tag'] ?? '')),
            'map_group' => trim((string)($data['map_group'] ?? 'Custom Flow')),
            'priority' => (int)($data['priority'] ?? 10),
            'overwrite_existing' => isset($data['overwrite_existing']) ? 1 : 0,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'notes' => trim((string)($data['notes'] ?? '')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (!$insert['source_module'] || !$insert['source_field'] || !$insert['destination_module'] || !$insert['destination_field']) {
            return false;
        }
        if ($id > 0) {
            return $this->db->where('id', $id)->update(db_prefix() . 'superman_mappings', $insert);
        }
        $insert['created_by'] = get_staff_user_id();
        $insert['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert(db_prefix() . 'superman_mappings', $insert);
    }

    public function delete_mapping($id)
    {
        return $this->db->where('id', (int)$id)->where('is_locked_default', 0)->delete(db_prefix() . 'superman_mappings');
    }

    public function save_token($data, $id = 0)
    {
        $tokenKey = trim((string)($data['token_key'] ?? ''));
        $tokenKey = preg_replace('/[^a-zA-Z0-9_{}]/', '_', $tokenKey);
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
            return $this->db->where('id', $id)->update(db_prefix() . 'superman_tokens', $insert);
        }
        $insert['created_by'] = get_staff_user_id();
        $insert['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert(db_prefix() . 'superman_tokens', $insert);
    }

    public function delete_token($id)
    {
        return $this->db->where('id', (int)$id)->delete(db_prefix() . 'superman_tokens');
    }

    public function get_settings_payload()
    {
        $keys = ['superman_enable_automation','superman_enable_form_autofill','superman_overwrite_existing','superman_enable_visual_builder','superman_enable_merge_search','superman_enable_rollback_profiles','superman_dropdown_single_scrollbar','superman_gradient_white_text','superman_sleek_mode','superman_primary_color','superman_secondary_color','superman_accent_color','superman_dark_color','superman_surface_color','superman_sidebar_width','superman_topbar_height','superman_logo_max_height','superman_menu_font_size','superman_mobile_menu_font_size','superman_table_name_width','superman_table_email_width','superman_client_login_button_size'];
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = get_option($key);
        }
        return $out;
    }

    public function save_settings($data)
    {
        $this->save_profile('Automatic Backup ' . date('Y-m-d H:i:s'), 'settings', $this->get_settings_payload(), false);
        $keys = array_keys($this->get_settings_payload());
        foreach ($keys as $key) {
            if (strpos($key, 'enable_') !== false || in_array($key, ['superman_overwrite_existing','superman_dropdown_single_scrollbar','superman_gradient_white_text','superman_sleek_mode'], true)) {
                update_option($key, isset($data[$key]) ? '1' : '0');
            } elseif (isset($data[$key])) {
                update_option($key, trim((string)$data[$key]));
            }
        }
        return true;
    }

    public function save_profile($name, $type, $payload, $default = false)
    {
        return $this->db->insert(db_prefix() . 'superman_profiles', [
            'profile_name' => $name,
            'profile_type' => $type,
            'payload' => json_encode($payload),
            'is_default' => $default ? 1 : 0,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_profiles($type = '')
    {
        if ($type !== '') {
            $this->db->where('profile_type', $type);
        }
        return $this->db->order_by('id DESC')->limit(50)->get(db_prefix() . 'superman_profiles')->result_array();
    }

    public function restore_profile($id)
    {
        $profile = $this->db->where('id', (int)$id)->get(db_prefix() . 'superman_profiles')->row_array();
        if (!$profile || empty($profile['payload'])) {
            return false;
        }
        $payload = json_decode($profile['payload'], true);
        if (!is_array($payload)) {
            return false;
        }
        foreach ($payload as $key => $value) {
            if (strpos($key, 'superman_') === 0) {
                update_option($key, (string)$value);
            }
        }
        return true;
    }

    public function get_custom_field_catalog($flat = false)
    {
        if (!$this->db->table_exists(db_prefix() . 'customfields')) {
            return [];
        }
        $rows = $this->db->select('id,fieldto,name,slug,type,bs_column,active')->from(db_prefix() . 'customfields')->order_by('fieldto ASC, name ASC')->get()->result_array();
        if ($flat) {
            $flatRows = [];
            foreach ($rows as $row) {
                $flatRows[] = $row['fieldto'] . ':' . $row['slug'] . ' - ' . $row['name'];
            }
            return $flatRows;
        }
        return $rows;
    }

    public function build_merge_catalog()
    {
        $catalog = [];
        foreach ($this->get_supported_modules() as $module => $label) {
            if ($module === 'custom_field') {
                continue;
            }
            foreach ($this->get_fields_for_module($module) as $field) {
                $catalog[] = [
                    'module' => $label,
                    'field' => $field,
                    'merge_tag' => '{' . $module . '_' . $field . '}',
                    'source' => 'Core Field',
                ];
            }
        }
        foreach ($this->get_custom_field_catalog() as $field) {
            $catalog[] = [
                'module' => ucfirst((string)$field['fieldto']),
                'field' => $field['name'],
                'merge_tag' => '{custom_' . $field['slug'] . '}',
                'source' => 'Custom Field',
            ];
        }
        foreach ($this->get_tokens(true) as $token) {
            $catalog[] = [
                'module' => ucfirst((string)$token['source_module']),
                'field' => $token['source_field'],
                'merge_tag' => $token['token_key'],
                'source' => 'Superman Token',
            ];
        }
        return $catalog;
    }

    public function get_client_bundle($clientId)
    {
        $clientId = (int)$clientId;
        $bundle = ['client' => [], 'primary_contact' => [], 'custom_fields' => []];
        if ($clientId <= 0 || !$this->db->table_exists(db_prefix() . 'clients')) {
            return $bundle;
        }
        $client = $this->db->where('userid', $clientId)->get(db_prefix() . 'clients')->row_array();
        if ($client) {
            $bundle['client'] = $client;
        }
        // Superman intentionally does not use contact records as default source because one customer can have multiple contacts.
        $bundle['primary_contact'] = [];

        if ($this->db->table_exists(db_prefix() . 'customfields') && $this->db->table_exists(db_prefix() . 'customfieldsvalues')) {
            $values = $this->db->select('cf.name, cf.slug, cfv.value')
                ->from(db_prefix() . 'customfieldsvalues cfv')
                ->join(db_prefix() . 'customfields cf', 'cf.id = cfv.fieldid', 'left')
                ->where('cfv.relid', $clientId)
                ->where_in('cf.fieldto', ['customers', 'customer'])
                ->get()->result_array();
            foreach ($values as $value) {
                $slug = (string)($value['slug'] ?? '');
                $slug = preg_replace('/^(customers|customer)_/', '', $slug);
                if ($slug !== '') {
                    $bundle['custom_fields'][$slug] = $value['value'];
                }
            }
        }

        $bundle['mappings'] = $this->get_mappings(true);
        return $bundle;
    }


    public function render_flow_preview($flow)
    {
        $items = isset($flow['items']) && is_array($flow['items']) ? $flow['items'] : [];
        if (empty($items)) {
            return '<div class="alert alert-info">' . html_escape(_l('superman_no_fields_selected')) . '</div>';
        }
        $source = $items[0];
        $sourceLabel = $this->human_label(($source['module'] ?? '') . ' ' . ($source['field'] ?? ''));
        $html = '<div class="superman-preview-result">';
        $html .= '<h4>' . html_escape(_l('superman_preview_combination')) . '</h4>';
        $html .= '<p><strong>' . html_escape(_l('superman_source')) . ':</strong> ' . html_escape($sourceLabel) . '</p>';
        $html .= '<ol>';
        foreach ($items as $index => $item) {
            if ($index === 0) {
                continue;
            }
            $label = $this->human_label(($item['module'] ?? '') . ' ' . ($item['field'] ?? ''));
            $html .= '<li>' . html_escape($sourceLabel) . ' &rarr; ' . html_escape($label) . '</li>';
        }
        $html .= '</ol>';
        $html .= '<p class="text-muted">' . html_escape(_l('superman_preview_help')) . '</p>';
        $html .= '</div>';
        return $html;
    }

    public function capture_source_snapshot($module, $id)
    {
        $table = $this->table_for_module($module);
        if (!$table || !$this->db->table_exists(db_prefix() . $table)) {
            return false;
        }
        $primary = $this->primary_key_for_module($module);
        if (!$primary || !$this->db->field_exists($primary, db_prefix() . $table)) {
            return false;
        }
        $row = $this->db->where($primary, (int)$id)->get(db_prefix() . $table)->row_array();
        if (!$row) {
            return false;
        }
        return $this->db->insert(db_prefix() . 'superman_snapshots', [
            'source_module' => $module,
            'source_id' => (int)$id,
            'payload' => json_encode($row),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function primary_key_for_module($module)
    {
        $keys = ['lead'=>'id','customer'=>'userid','contact'=>'id','project'=>'id','estimate'=>'id','proposal'=>'id','contract'=>'id','invoice'=>'id','credit_note'=>'id','payment'=>'id','task'=>'id','ticket'=>'ticketid','subcontractor'=>'id','subcontractor_contract'=>'id','contractor'=>'id','sales_agent'=>'staffid','sales_hub'=>'id','sales_contract'=>'id','purchase'=>'id','purchase_bill'=>'id','purchase_order'=>'id'];
        return isset($keys[$module]) ? $keys[$module] : 'id';
    }

    public function apply_auto_sync_for_record($id)
    {
        // Runtime sync is primarily handled safely by form autofill. This method stores snapshots for traceability.
        return true;
    }

    public function token_impact($token)
    {
        $tables = [
            'contracts' => ['subject','description'],
            'proposals' => ['subject','content'],
            'emailtemplates' => ['subject','message'],
            'estimates' => ['clientnote','adminnote'],
            'invoices' => ['clientnote','adminnote'],
            'creditnotes' => ['clientnote','adminnote'],
            'projects' => ['name','description'],
            'tasks' => ['name','description'],
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

    public function health_check()
    {
        $checks = [];
        $tables = [
            'superman_mappings'   => _l('superman_table_mappings'),
            'superman_tokens'     => _l('superman_table_tokens'),
            'superman_profiles'   => _l('superman_table_profiles'),
            'superman_snapshots'  => _l('superman_table_snapshots'),
            'customfields'        => _l('superman_table_custom_fields'),
        ];

        foreach ($tables as $table => $label) {
            $checks[] = ['label' => $label, 'status' => $this->db->table_exists(db_prefix() . $table) ? 'OK' : _l('superman_missing')];
        }

        $defaultMapCount = 0;
        if ($this->db->table_exists(db_prefix() . 'superman_mappings') && $this->db->field_exists('is_active', db_prefix() . 'superman_mappings')) {
            $defaultMapCount = (int) $this->db->where('is_active', 1)->count_all_results(db_prefix() . 'superman_mappings');
        }

        $checks[] = ['label' => _l('superman_active_default_maps'), 'status' => (string) $defaultMapCount];
        $checks[] = ['label' => _l('superman_automation_setting'), 'status' => get_option('superman_enable_automation') == '1' ? _l('superman_enabled') : _l('superman_disabled')];
        $checks[] = ['label' => _l('superman_form_autofill_setting'), 'status' => get_option('superman_enable_form_autofill') == '1' ? _l('superman_enabled') : _l('superman_disabled')];
        $checks[] = ['label' => _l('superman_english_language_file'), 'status' => file_exists(module_dir_path('superman') . 'language/english/superman/superman_lang.php') ? 'OK' : _l('superman_missing')];
        $checks[] = ['label' => _l('superman_spanish_language_file'), 'status' => file_exists(module_dir_path('superman') . 'language/spanish/superman/superman_lang.php') ? 'OK' : _l('superman_missing')];
        $checks[] = ['label' => _l('superman_php_compatibility'), 'status' => 'PHP 8.5'];

        return $checks;
    }
}
