<?php

defined('BASEPATH') or exit('No direct script access allowed');

function superman_seed_insert_mapping($CI, $row)
{
    $table = db_prefix() . 'superman_mappings';
    $exists = $CI->db->where('source_module', $row[0])
        ->where('source_field', $row[1])
        ->where('destination_module', $row[2])
        ->where('destination_field', $row[3])
        ->count_all_results($table);

    if ($exists > 0) {
        return;
    }

    $CI->db->insert($table, [
        'source_module' => $row[0],
        'source_field' => $row[1],
        'destination_module' => $row[2],
        'destination_field' => $row[3],
        'merge_tag' => $row[4],
        'map_group' => $row[5],
        'priority' => isset($row[6]) ? (int)$row[6] : 10,
        'overwrite_existing' => isset($row[7]) ? (int)$row[7] : 0,
        'is_locked_default' => 1,
        'is_active' => 1,
        'notes' => isset($row[8]) ? $row[8] : 'Default Superman automation map created by Smart Choice.',
        'created_by' => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

function superman_seed_insert_token($CI, $name, $key, $module, $field, $description)
{
    $table = db_prefix() . 'superman_tokens';
    if (!$CI->db->table_exists($table)) {
        return;
    }

    $key = strtolower(trim($key));
    $exists = $CI->db->where('token_key', $key)->count_all_results($table);
    if ($exists > 0) {
        return;
    }

    $CI->db->insert($table, [
        'token_name' => $name,
        'token_key' => $key,
        'source_module' => $module,
        'source_field' => $field,
        'description' => $description,
        'is_active' => 1,
        'created_by' => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

function superman_custom_field_slug($fieldTo, $name)
{
    $base = strtolower(trim($name));
    $base = preg_replace('/[^a-z0-9]+/', '_', $base);
    $base = trim($base, '_');
    return $fieldTo . '_' . $base;
}

function superman_seed_custom_field($CI, $fieldTo, $name, $type = 'input', $options = '')
{
    $table = db_prefix() . 'customfields';
    if (!$CI->db->table_exists($table)) {
        return;
    }

    $slug = superman_custom_field_slug($fieldTo, $name);
    $exists = $CI->db->where('fieldto', $fieldTo)->group_start()->where('slug', $slug)->or_where('name', $name)->group_end()->count_all_results($table);
    if ($exists > 0) {
        return;
    }

    $data = [];
    $columns = $CI->db->list_fields($table);
    $defaults = [
        'fieldto' => $fieldTo,
        'name' => $name,
        'slug' => $slug,
        'required' => 0,
        'type' => $type,
        'options' => $options,
        'display_inline' => 0,
        'field_order' => 0,
        'active' => 1,
        'show_on_pdf' => 1,
        'show_on_ticket_form' => 0,
        'only_admin' => 0,
        'show_on_table' => 1,
        'show_on_client_portal' => 0,
        'disalow_client_to_edit' => 0,
        'bs_column' => 6,
        'default_value' => '',
    ];

    foreach ($defaults as $key => $value) {
        if (in_array($key, $columns, true)) {
            $data[$key] = $value;
        }
    }

    $CI->db->insert($table, $data);
}

function superman_seed_smart_choice_custom_fields($CI)
{
    $universal = [
        ['Property Address', 'input', ''],
        ['Property City', 'input', ''],
        ['Property State', 'input', ''],
        ['Property Zip', 'input', ''],
        ['Property Owner Name', 'input', ''],
        ['Property APN Or Folio Number', 'input', ''],
        ['Permit Number', 'input', ''],
        ['Scope Of Work', 'textarea', ''],
        ['Warranty', 'textarea', ''],
        ['Project Start Time', 'input', ''],
        ['Preferred Communication', 'select', "Text Message,Phone Call,In Person,Email"],
        ['Mobile Phone', 'input', ''],
        ['Sales Agent', 'input', ''],
        ['Contractor Name', 'input', ''],
        ['Subcontractor Name', 'input', ''],
    ];

    $fieldTos = ['leads','customers','projects','estimate','proposal','invoice','credit_note','contracts','tasks'];
    foreach ($fieldTos as $fieldTo) {
        foreach ($universal as $field) {
            superman_seed_custom_field($CI, $fieldTo, $field[0], $field[1], $field[2]);
        }
    }
}

function superman_seed_default_mappings($CI)
{
    $table = db_prefix() . 'superman_mappings';
    if (!$CI->db->table_exists($table)) {
        return;
    }

    superman_seed_smart_choice_custom_fields($CI);

    $defaults = [
        // Lead and customer identity flow. Contacts are intentionally not used as default source.
        ['lead','name','customer','company','{lead_name}','Customer Identity Flow',10,0,'Lead name becomes the main customer name.'],
        ['lead','email','customer','email','{lead_email}','Customer Identity Flow',11,0,'Lead email follows the main customer file, not a random contact.'],
        ['lead','phonenumber','customer','phonenumber','{lead_phone}','Customer Identity Flow',12,0,'Lead phone follows the main customer file.'],
        ['lead','address','customer','address','{lead_address}','Customer Address Flow',13,0,'Lead address becomes the main customer address.'],
        ['lead','city','customer','city','{lead_city}','Customer Address Flow',14,0,'Lead city becomes customer city.'],
        ['lead','state','customer','state','{lead_state}','Customer Address Flow',15,0,'Lead state becomes customer state.'],
        ['lead','zip','customer','zip','{lead_zip}','Customer Address Flow',16,0,'Lead zip becomes customer zip.'],

        // Customer to sales documents.
        ['customer','company','estimate','clientid','{client_company}','Sales Document Flow',20,0],
        ['customer','company','proposal','proposal_to','{client_company}','Sales Document Flow',21,0],
        ['customer','company','invoice','clientid','{client_company}','Sales Document Flow',22,0],
        ['customer','company','credit_note','clientid','{client_company}','Sales Document Flow',23,0],
        ['customer','company','contract','client','{client_company}','Sales Document Flow',24,0],
        ['customer','company','project','clientid','{client_company}','Project Production Flow',25,0],
        ['customer','company','payment','customer','{client_company}','Payment Flow',26,0],

        // Phones and emails across non-contact modules.
        ['customer','email','estimate','client_email','{client_email}','Customer Communication Flow',30,0],
        ['customer','email','proposal','email','{client_email}','Customer Communication Flow',31,0],
        ['customer','email','invoice','client_email','{client_email}','Customer Communication Flow',32,0],
        ['customer','email','contract','client_email','{client_email}','Customer Communication Flow',33,0],
        ['customer','email','project','client_email','{client_email}','Customer Communication Flow',34,0],
        ['customer','phonenumber','estimate','client_phone','{client_phone}','Customer Communication Flow',35,0],
        ['customer','phonenumber','proposal','phone','{client_phone}','Customer Communication Flow',36,0],
        ['customer','phonenumber','invoice','client_phone','{client_phone}','Customer Communication Flow',37,0],
        ['customer','phonenumber','contract','client_phone','{client_phone}','Customer Communication Flow',38,0],
        ['customer','phonenumber','project','client_phone','{client_phone}','Customer Communication Flow',39,0],

        // Address to every customer-related record.
        ['customer','address','estimate','billing_street','{client_address}','Customer Address Flow',40,0],
        ['customer','city','estimate','billing_city','{client_city}','Customer Address Flow',41,0],
        ['customer','state','estimate','billing_state','{client_state}','Customer Address Flow',42,0],
        ['customer','zip','estimate','billing_zip','{client_zip}','Customer Address Flow',43,0],
        ['customer','address','proposal','address','{client_address}','Customer Address Flow',44,0],
        ['customer','city','proposal','city','{client_city}','Customer Address Flow',45,0],
        ['customer','state','proposal','state','{client_state}','Customer Address Flow',46,0],
        ['customer','zip','proposal','zip','{client_zip}','Customer Address Flow',47,0],
        ['customer','address','invoice','billing_street','{client_address}','Customer Address Flow',48,0],
        ['customer','city','invoice','billing_city','{client_city}','Customer Address Flow',49,0],
        ['customer','state','invoice','billing_state','{client_state}','Customer Address Flow',50,0],
        ['customer','zip','invoice','billing_zip','{client_zip}','Customer Address Flow',51,0],
        ['customer','address','contract','project_address','{client_address}','Customer Address Flow',52,0],
        ['customer','address','project','project_address','{client_address}','Project Production Flow',53,0],

        // Sales progression.
        ['estimate','number','proposal','estimate_number','{estimate_number}','Sales Progression Flow',60,0],
        ['estimate','total','proposal','estimate_total','{estimate_total}','Sales Progression Flow',61,0],
        ['proposal','subject','contract','subject','{proposal_subject}','Sales Progression Flow',62,0],
        ['proposal','content','contract','description','{proposal_content}','Sales Progression Flow',63,0],
        ['contract','subject','project','name','{contract_subject}','Sales Progression Flow',64,0],
        ['estimate','number','invoice','estimate_reference','{estimate_number}','Invoice Flow',65,0],
        ['invoice','number','payment','invoice_number','{invoice_number}','Payment Flow',66,0],
        ['invoice','total','payment','invoice_total','{invoice_total}','Payment Flow',67,0],
        ['invoice','number','credit_note','invoice_reference','{invoice_number}','Credit Note Flow',68,0],

        // Smart Choice construction custom fields. These are seeded as custom fields and mapped from lead/customer/project flow.
        ['lead','property_address','customer','property_address','{lead_property_address}','Smart Choice Construction Flow',80,0],
        ['customer','property_address','estimate','property_address','{customer_property_address}','Smart Choice Construction Flow',81,0],
        ['customer','property_address','proposal','property_address','{customer_property_address}','Smart Choice Construction Flow',82,0],
        ['customer','property_address','invoice','property_address','{customer_property_address}','Smart Choice Construction Flow',83,0],
        ['customer','property_address','contract','property_address','{customer_property_address}','Smart Choice Construction Flow',84,0],
        ['customer','property_address','project','property_address','{customer_property_address}','Smart Choice Construction Flow',85,0],
        ['customer','property_owner_name','project','property_owner_name','{customer_property_owner_name}','Smart Choice Construction Flow',86,0],
        ['customer','property_apn_or_folio_number','project','property_apn_or_folio_number','{customer_property_apn_or_folio_number}','Smart Choice Construction Flow',87,0],
        ['customer','permit_number','project','permit_number','{customer_permit_number}','Smart Choice Construction Flow',88,0],
        ['customer','scope_of_work','estimate','scope_of_work','{customer_scope_of_work}','Smart Choice Scope Flow',89,0],
        ['customer','scope_of_work','proposal','scope_of_work','{customer_scope_of_work}','Smart Choice Scope Flow',90,0],
        ['customer','scope_of_work','contract','scope_of_work','{customer_scope_of_work}','Smart Choice Scope Flow',91,0],
        ['customer','scope_of_work','project','scope_of_work','{customer_scope_of_work}','Smart Choice Scope Flow',92,0],
        ['customer','warranty','contract','warranty','{customer_warranty}','Smart Choice Warranty Flow',93,0],
        ['customer','warranty','project','warranty','{customer_warranty}','Smart Choice Warranty Flow',94,0],
        ['customer','project_start_time','project','project_start_time','{customer_project_start_time}','Smart Choice Schedule Flow',95,0],
        ['customer','preferred_communication','project','preferred_communication','{customer_preferred_communication}','Smart Choice Communication Flow',96,0],
        ['customer','sales_agent','project','sales_agent','{customer_sales_agent}','Smart Choice Team Flow',97,0],
        ['customer','contractor_name','project','contractor_name','{customer_contractor_name}','Smart Choice Team Flow',98,0],
        ['customer','subcontractor_name','project','subcontractor_name','{customer_subcontractor_name}','Smart Choice Team Flow',99,0],
    ];

    foreach ($defaults as $row) {
        superman_seed_insert_mapping($CI, $row);
    }

    $tokens = [
        ['Customer Full Name','{customer_full_name}','customer','company','Main customer or client name.'],
        ['Customer Email','{customer_email}','customer','email','Main customer email.'],
        ['Customer Phone','{customer_phone}','customer','phonenumber','Main customer phone.'],
        ['Customer Mobile Phone','{customer_mobile_phone}','customer','mobile_phone','Main customer mobile phone custom field.'],
        ['Customer Address','{customer_address}','customer','address','Main customer address.'],
        ['Property Address','{property_address}','customer','property_address','Smart Choice property address.'],
        ['Property Owner Name','{property_owner_name}','customer','property_owner_name','Owner name on public record or deed.'],
        ['Property APN Or Folio Number','{property_apn_or_folio_number}','customer','property_apn_or_folio_number','Parcel, APN, or folio number.'],
        ['Permit Number','{permit_number}','project','permit_number','Permit number attached to project.'],
        ['Scope Of Work','{scope_of_work}','project','scope_of_work','Construction scope of work.'],
        ['Warranty','{warranty}','contract','warranty','Warranty terms.'],
        ['Project Start Time','{project_start_time}','project','project_start_time','Project start time.'],
        ['Preferred Communication','{preferred_communication}','customer','preferred_communication','Customer communication preference.'],
        ['Sales Agent','{sales_agent}','customer','sales_agent','Assigned sales agent.'],
        ['Contractor Name','{contractor_name}','project','contractor_name','Assigned contractor.'],
        ['Subcontractor Name','{subcontractor_name}','project','subcontractor_name','Assigned subcontractor.'],
    ];

    foreach ($tokens as $token) {
        superman_seed_insert_token($CI, $token[0], $token[1], $token[2], $token[3], $token[4]);
    }
}
