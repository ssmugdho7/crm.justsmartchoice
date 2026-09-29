<?php

$config['get_webhook_fields'] = "8dbdb9c49e951599966948bf0220914db66ecafc";
$config['get_webhook_name'] = "f303329833ceb983bab2c8d791f600723b59322c";
$config['get_webhook_methods'] = "aWYgKCRjYWNoZV9kYXRhICE9ICJmMzAzMzI5ODMzY2ViOTgzYmFiMmM4ZDc5MWY2MDA3MjNiNTkzMjJjOGRiZGI5YzQ5ZTk1MTU5OTk2Njk0OGJmMDIyMDkxNGRiNjZlY2FmY2JmNWI2MTBhODRiMTJiZTYzODA2Mjg2YzNjMjJmZDNjM2JlYmZjOTlkZWM0YWM1Y2YwYmUxYWJkNGFlNWQ5ZGY5MzhiZTM3YTc4NmIwNmY2YzRjM2VjNTA4NWE2Y2NjZmIyNjM5YTc4NTFkNDJhZTJhMTMxMTFjMzlmOWY3YzA0YmYwZThmYzVkOWY5OTAyMmVkMGQ3NTA1MThjYTNiNTU0ZDIwZTZkNTZlNzZkMTEyYWM1MDM5MDA0NGUzNGJiYTFmMWExNzEzNWYwODdkMmM0ZTA1ZGFiM2Q4ZDllZWYwZGJjZGQ5NGY1ZWYwMjRmMyIpIHsKICAgIGRpZTsKfQ==";
$config['get_allowed_files'] = 'WyJcL3ZlbmRvclwvY29tcG9zZXJcL2ZpbGVzX2F1dG9sb2FkLnBocCIsIlwvaW5zdGFsbC5waHAiLCJcL3dlYmhvb2tzLnBocCIsIlwvY29yZVwvQXBpaW5pdC5waHAiLCJcL2xpYnJhcmllc1wvV2ViaG9va3NfYWVpb3UucGhwIiwiXC9jb250cm9sbGVyc1wvRW52X3Zlci5waHAiLCJcL3ZpZXdzXC9hY3RpdmF0ZS5waHAiLCJcL2NvbmZpZ1wvY3NyZl9leGNsdWRlX3VyaXMucGhwIl0=';

$config['mapping_fields'] = [
    'leads' => [
        'assigned' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'status' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get_status"],
        'last_lead_status' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get_status"],
        'source' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get_source"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'client_id' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        'from_form_id' => ["fetch_method" => 'database', "table_name" => 'web_to_lead', "id_column" => "id"],
        "country" => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
    ],
    'client' => [
        'leadid' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get"],
        'country' => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        'shipping_country' => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        'billing_country' => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        'default_currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        // 'userid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
    ],
    'expenses' => [
        // 'category' => ["fetch_method" => 'database', "table_name" => 'expenses_categories', "id_column" => "id"],
        // 'currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        // 'tax' => ["fetch_method" => 'model', "model" => 'taxes_model', "method" => "get"],
        // 'tax2' => ["fetch_method" => 'model', "model" => 'taxes_model', "method" => "get"],
        // 'clientid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        // 'project_id' => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
        'invoiceid' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
        'paymentmode' => ["fetch_method" => 'database', "table_name" => 'payment_modes', "id_column" => "id"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'attachment_added_from' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
    ],
    'staff' => [
        'role' => ["fetch_method" => 'model', "model" => 'roles_model', "method" => "get"],
    ],
    'estimate' => [
        'status' => ["fetch_method" => "helper", "method" => "estimate_status_by_id"],
        'clientid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        'invoiceid' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
        'sale_agent' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        'addedfrom' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
    ],
    'invoice' => [
        // 'clientid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        'currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        'addedfrom' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'sale_agent' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'project_id' => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
        'subscription_id' => ["fetch_method" => 'model', "model" => 'subscriptions_model', "method" => "get_by_id"],
        'is_recurring_from' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
    ],
    'invoice_payments' => [
        'invoiceid' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
        'paymentmode' => ["fetch_method" => 'model', "model" => 'payment_modes_model', "method" => "get"],
    ],
    'tasks' => [
        'priority' => ["fetch_method" => "helper", "method" => "task_priority"],
        'addedfrom' => ["fetch_method" => "helper", "method" => "task_added_from", "data_id" => true],
        'status' => ["fetch_method" => "helper", "method" => "get_task_status_by_id"],
        'rel_id' => ["fetch_method" => "helper", "method" => "task_rel_id", "data_id" => true],
        'invoice_id' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
    ],
    'projects' => [
        // 'clientid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        'status' => ["fetch_method" => "helper", "method" => "get_project_status_by_id"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
    ],
    'proposals' => [
        "addedfrom" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        "rel_id" => ["fetch_method" => "helper", "method" => "proposal_rel_id", "data_id" => true],
        "assigned" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        // "project_id" => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
        "country" => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        "estimate_id" => ["fetch_method" => 'model', "model" => 'estimates_model', "method" => "get"],
        "invoice_id" => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
        "currencyid" => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
    ],
    'ticket' => [
        "userid" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        "contactid" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get_contact"],
        "merged_ticket_id" => ["fetch_method" => 'model', "model" => 'tickets_model', "method" => "get"],
        "service" => ["fetch_method" => 'model', "model" => 'tickets_model', "method" => "get_service"],
        "assigned" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        "staff_id_replying" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        "project_id" => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
    ],
    'contract' => [
        "project_id" => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
        "addedfrom" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        "client" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
    ]
];