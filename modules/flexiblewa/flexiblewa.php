<?php

use Carbon\Carbon;


/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Flexible Workflow Automation
Description: This module automates tasks on Perfex CRM
Version: 1.0.2
Requires at least: 2.3.*
*/

define('FLEXIBLEWA_MODULE_NAME', 'flexiblewa');
define('FLEXIBLEWA_SET_ASSIGNED_TO_ACTION', 'set_assigned_to');
define('FLEXIBLEWA_SET_DUE_DATE_TO_ACTION', 'set_due_date_to');
define('FLEXIBLEWA_SET_PRIORITY_TO_ACTION', 'set_priority_to');
define('FLEXIBLEWA_ADD_NEW_CHECKLIST_ITEM_ACTION', 'add_new_checklist_item');
define('FLEXIBLEWA_ADD_NEW_REMINDER_ACTION', 'add_new_reminder');
define('FLEXIBLEWA_ADD_NEW_TASK_ACTION', 'add_new_task');
define('FLEXIBLEWA_ADD_NEW_NOTE_ACTION', 'add_new_note');
define('FLEXIBLEWA_SEND_EMAIL_ACTION', 'send_email');
define('FLEXIBLEWA_ADD_NEW_COMMENT_ACTION', 'add_new_comment');
define('FLEXIBLEWA_ADD_NEW_FOLLOWER_ACTION', 'add_new_follower');
define('FLEXIBLEWA_UPLOAD_FOLDER', FCPATH . 'uploads/' . FLEXIBLEWA_MODULE_NAME . '/');
define('FLEXIBLEWA_FILES_FOLDER', FLEXIBLEWA_UPLOAD_FOLDER . 'files/');
define('FLEXIBLEWA_ADD_NEW_FILE_ACTION', 'add_new_file');
define('FLEXIBLEWA_MOVE_TO_ANOTHER_RELATION_ACTION', 'move_to_another_relation');
define('FLEXIBLEWA_PROJECT_RELATION', 'project');
define('FLEXIBLEWA_INVOICE_RELATION', 'invoice');
define('FLEXIBLEWA_CUSTOMER_RELATION', 'customer');
define('FLEXIBLEWA_ESTIMATE_RELATION', 'estimate');
define('FLEXIBLEWA_CONTRACT_RELATION', 'contract');
define('FLEXIBLEWA_TICKET_RELATION', 'ticket');
define('FLEXIBLEWA_EXPENSE_RELATION', 'expense');
define('FLEXIBLEWA_LEAD_RELATION', 'lead');
define('FLEXIBLEWA_PROPOSAL_RELATION', 'proposal');
define('FLEXIBLEWA_MOVE_TO_SECTION_ACTION', 'move_to_section');
define('FLEXIBLEWA_MARK_AS_COMPLETE_ACTION', 'mark_as_complete');
define('FLEXIBLEWA_TASK_RELATION_TYPE', 'task');
define('FLEXIBLEWA_LEAD_RELATION_TYPE', 'lead');
define('FLEXIBLEWA_LEAD_RULE_TYPE', 'lead');
define('FLEXIBLEWA_PROJECT_RULE_TYPE', 'project');
define('FLEXIBLEWA_PROJECT_RELATION_TYPE', 'project');
define('FLEXIBLEWA_ADD_NEW_MILESTONE_ACTION', 'add_new_milestone');
define('FLEXIBLEWA_ADD_NEW_DISCUSSION_ACTION', 'create_discussion');
define('FLEXIBLEWA_ADD_NEW_TICKET_ACTION', 'add_new_ticket');
hooks()->add_action('admin_init', FLEXIBLEWA_MODULE_NAME . '_permissions');
hooks()->add_action('admin_init', FLEXIBLEWA_MODULE_NAME . '_module_init_menu_items');
hooks()->add_action('task_status_changed', FLEXIBLEWA_MODULE_NAME . '_execute_task_actions');
hooks()->add_action('after_add_task', FLEXIBLEWA_MODULE_NAME . '_execute_new_task_actions');
//leads actions
hooks()->add_action('lead_created', FLEXIBLEWA_MODULE_NAME . '_execute_new_lead_actions');
hooks()->add_action('lead_status_changed', FLEXIBLEWA_MODULE_NAME . '_execute_lead_status_changed_actions');
hooks()->add_action('lead_marked_as_lost', FLEXIBLEWA_MODULE_NAME . '_execute_lead_marked_as_lost_actions');
hooks()->add_action('lead_marked_as_junk', FLEXIBLEWA_MODULE_NAME . '_execute_lead_marked_as_junk_actions');
hooks()->add_action('lead_converted_to_customer', FLEXIBLEWA_MODULE_NAME . '_execute_lead_converted_to_customer_actions');
//projects actions
hooks()->add_action('project_status_changed', FLEXIBLEWA_MODULE_NAME . '_execute_project_status_changed_actions');
hooks()->add_action('after_add_project', FLEXIBLEWA_MODULE_NAME . '_execute_new_project_actions');
/**
 * Register activation module hook
 */
register_activation_hook(FLEXIBLEWA_MODULE_NAME, FLEXIBLEWA_MODULE_NAME . '_module_activation_hook');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(FLEXIBLEWA_MODULE_NAME, [FLEXIBLEWA_MODULE_NAME]);

function flexiblewa_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function flexiblewa_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities(FLEXIBLEWA_MODULE_NAME, $capabilities, _l(FLEXIBLEWA_MODULE_NAME));
}

/**
 * Init flexible workflow automation module menu items in setup in admin_init hook
 * @return null
 */
function flexiblewa_module_init_menu_items()
{
    $CI = &get_instance();
    if (has_permission(FLEXIBLEWA_MODULE_NAME, '', 'view')) {

        $CI->app_menu->add_sidebar_menu_item(FLEXIBLEWA_MODULE_NAME, [
            'name' => _l(FLEXIBLEWA_MODULE_NAME),
            'href' => admin_url(FLEXIBLEWA_MODULE_NAME),
            'position' => 20,
            'icon' => 'fa-solid fa-wand-sparkles',
        ]);

        //tasks automation
        $CI->app_menu->add_sidebar_children_item(FLEXIBLEWA_MODULE_NAME, [
            'slug' => 'flexiblewa-tasks-automation',
            'name' => _flexiblewa_lang('tasks_automation'),
            'href' => admin_url(FLEXIBLEWA_MODULE_NAME),
            'position' => 15,
            'icon' => '',
        ]);

        //submenu for leads automation
        $CI->app_menu->add_sidebar_children_item(FLEXIBLEWA_MODULE_NAME, [
            'slug' => 'flexiblewa-leads-automation',
            'name' => _flexiblewa_lang('leads_automation'),
            'href' => admin_url('flexiblewa/lead_automation'),
            'position' => 17,
            'icon' => '',
        ]);

        //submenu for projects automation
        $CI->app_menu->add_sidebar_children_item(FLEXIBLEWA_MODULE_NAME, [
            'slug' => 'flexiblewa-projects-automation',
            'name' => _flexiblewa_lang('projects_automation'),
            'href' => admin_url('flexiblewa/project_automation'),
            'position' => 17,
            'icon' => '',
        ]);
    }
}

function flexiblewa_execute_new_lead_actions($lead_id)
{
    $CI = &get_instance();
    $CI->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
    $rules = $CI->flexibleworkflow_generic_rules_model->all(['rule_type' => FLEXIBLEWA_LEAD_RULE_TYPE, 'when_event' => 'lead_added']);
    foreach ($rules as $rule) {
        flexiblewa_execute_generic_action($rule, $lead_id, 'lead_added', FLEXIBLEWA_LEAD_RELATION_TYPE);
    }
}

function flexiblewa_execute_lead_marked_as_lost_actions($lead_id)
{
    $CI = &get_instance();
    $CI->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
    $rules = $CI->flexibleworkflow_generic_rules_model->all(['rule_type' => FLEXIBLEWA_LEAD_RULE_TYPE, 'when_event' => 'lead_marked_as_lost']);
    foreach ($rules as $rule) {
        flexiblewa_execute_generic_action($rule, $lead_id, 'lead_marked_as_lost', FLEXIBLEWA_LEAD_RELATION_TYPE);
    }
}

function flexiblewa_execute_lead_marked_as_junk_actions($lead_id)
{
    $CI = &get_instance();
    $CI->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
    $rules = $CI->flexibleworkflow_generic_rules_model->all(['rule_type' => FLEXIBLEWA_LEAD_RULE_TYPE, 'when_event' => 'lead_marked_as_junk']);
    foreach ($rules as $rule) {
        flexiblewa_execute_generic_action($rule, $lead_id, 'lead_marked_as_junk', FLEXIBLEWA_LEAD_RELATION_TYPE);
    }
}

function flexiblewa_execute_lead_status_changed_actions($data)
{
    //extract the lead id and new status
    $lead_id = $data['lead_id'];
    $new_status = $data['new_status']; //this is somethis a string, not sure why 
    $CI = &get_instance();
    $CI->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
    $CI->load->model('leads_model');
    if (!is_numeric($new_status)) {
        $lead = $CI->leads_model->get($lead_id);
        $new_status = $lead->status;
    }
    $when_event = "lead_status_" . $new_status;
    $rules = $CI->flexibleworkflow_generic_rules_model->all(['rule_type' => FLEXIBLEWA_LEAD_RULE_TYPE, 'when_event' => $when_event]);
    foreach ($rules as $rule) {
        flexiblewa_execute_generic_action($rule, $lead_id, $when_event, FLEXIBLEWA_LEAD_RELATION_TYPE);
    }
}

function flexiblewa_execute_lead_converted_to_customer_actions($data)
{
    $lead_id = $data['lead_id'];
    $CI = &get_instance();
    $CI->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
    $rules = $CI->flexibleworkflow_generic_rules_model->all(['rule_type' => FLEXIBLEWA_LEAD_RULE_TYPE, 'when_event' => 'lead_converted_to_customer']);
    foreach ($rules as $rule) {
        flexiblewa_execute_generic_action($rule, $lead_id, 'lead_converted_to_customer', FLEXIBLEWA_LEAD_RELATION_TYPE);
    }
}

//_execute_new_project_actions
function flexiblewa_execute_new_project_actions($project_id)
{
    $CI = &get_instance();
    $CI->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
    $rules = $CI->flexibleworkflow_generic_rules_model->all(['rule_type' => FLEXIBLEWA_PROJECT_RULE_TYPE, 'when_event' => 'project_added']);
    foreach ($rules as $rule) {
        flexiblewa_execute_generic_action($rule, $project_id, 'project_added', FLEXIBLEWA_PROJECT_RELATION_TYPE);
    }
}

function flexiblewa_execute_project_status_changed_actions($data)
{
    $project_id = $data['project_id'];
    $new_status = $data['status'];
    $CI = &get_instance();
    $CI->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
    $rules = $CI->flexibleworkflow_generic_rules_model->all([
        'rule_type' => FLEXIBLEWA_PROJECT_RULE_TYPE,
        'when_event' => 'project_status_' . $new_status
    ]);
    foreach ($rules as $rule) {
        flexiblewa_execute_generic_action($rule, $project_id, 'project_status_' . $new_status, FLEXIBLEWA_PROJECT_RELATION_TYPE);
    }
}

function _flexiblewa_lang($slug)
{
    return _l(FLEXIBLEWA_MODULE_NAME . '_' . $slug);
}

function flexiblewa_has_permission($ability)
{
    return has_permission(FLEXIBLEWA_MODULE_NAME, '', $ability);
}

/**
 * Get the admin url using the given path for the module
 *
 * @param string $path
 * @return string
 */
function flexiblewa_admin_url($path = '')
{
    return ($path) ? admin_url(FLEXIBLEWA_MODULE_NAME . '/' . $path) : admin_url(FLEXIBLEWA_MODULE_NAME);
}

function flexiblewa_get_task_status_name($status_id)
{
    $CI = &get_instance();
    $CI->load->model('Tasks_model');
    $statuses = $CI->tasks_model->get_statuses();


    $statuses = array_filter($statuses, function ($element) use ($status_id) {
        return $element['id'] == $status_id;
    });

    if (empty($statuses)) {
        return '';
    }

    return array_values($statuses)[0]['name'];
}

function flexiblewa_modals($rule_type = "")
{
    $CI = &get_instance();

    if ($rule_type == FLEXIBLEWA_LEAD_RULE_TYPE) {
        $CI->load->view('partials/modals/lead-rule-form');
        $CI->load->view('partials/modals/lead-order-action');
    } else if ($rule_type == FLEXIBLEWA_PROJECT_RULE_TYPE) {
        $CI->load->view('partials/modals/project-rule-form');
        $CI->load->view('partials/modals/project-order-action');
    } else {
        $CI->load->view('partials/modals/task-rule-form');
        $CI->load->view('partials/modals/task-order-action');
    }
}

function flexiblewa_is_module_active($module_name)
{
    $CI = &get_instance();

    return $CI->app_modules->is_active($module_name);
}

function flexiblewa_get_staff_members()
{
    $CI = &get_instance();
    $CI->load->model('staff_model');

    return $CI->staff_model->get('', [
        'active'       => 1,
        'is_not_staff' => 0,
    ]);
}

function flexiblewa_get_rule_name($rule_id)
{
    return ucfirst(str_replace('_', ' ', $rule_id));
}

function flexiblewa_get_periods($include_hours = false)
{
    $periods = [];

    if ($include_hours) {
        array_push($periods, [
            'id' => 'hours',
            'name' => _l('flexiblewa_hours')
        ]);
    }

    array_push(
        $periods,
        [
            'id' => 'days',
            'name' => _l('flexiblewa_days'),
        ],
        [
            'id' => 'weeks',
            'name' => _l('flexiblewa_weeks'),
        ],
        [
            'id' => 'months',
            'name' => _l('flexiblewa_months'),
        ]
    );

    return $periods;
}

function flexiblewa_create_storage_directory()
{
    flexiblewa_create_folder(FLEXIBLEWA_UPLOAD_FOLDER);
    flexiblewa_create_folder(FLEXIBLEWA_FILES_FOLDER);
}

function flexiblewa_get_upload_directory()
{
    return FLEXIBLEWA_FILES_FOLDER;
}

function flexiblewa_create_folder($folder)
{
    if (!is_dir($folder)) {
        mkdir($folder, 0777);
        $fp = fopen(rtrim($folder, '/') . '/' . 'index.html', 'w');
        fclose($fp);
    }
}

/**
 * Upload a file and return it's path on success or false on failure
 *
 * @param string $input_name
 * @param integer $limit
 * @return string|bool
 */
function flexiblewa_upload_file($input_name, $limit = 1)
{
    $errors = [];
    $field = $input_name;
    $path = flexiblewa_get_upload_directory();

    $CI = &get_instance();

    if (
        isset($_FILES[$field]['name'])
        && ($_FILES[$field]['name'] != '' || is_array($_FILES[$field]['name']) && count($_FILES[$field]['name']) > 0)
    ) {
        if (!is_array($_FILES[$field]['name'])) {
            $_FILES[$field]['name'] = [$_FILES[$field]['name']];
            $_FILES[$field]['type'] = [$_FILES[$field]['type']];
            $_FILES[$field]['tmp_name'] = [$_FILES[$field]['tmp_name']];
            $_FILES[$field]['error'] = [$_FILES[$field]['error']];
            $_FILES[$field]['size'] = [$_FILES[$field]['size']];
        }

        for ($i = 0; $i < $limit; $i++) {
            $upload_file_name = $_FILES[$field]['name'][$i];

            if (_perfex_upload_error($_FILES[$field]['error'][$i])) {
                $errors[$upload_file_name] = _perfex_upload_error($_FILES[$field]['error'][$i]);

                continue;
            }

            // Get the temp file path
            $tmpFilePath = $_FILES[$field]['tmp_name'][$i];
            $filetype = $_FILES[$field]['type'][$i];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $originalFilename = unique_filename($path, $upload_file_name);
                $filename = app_generate_hash() . '.' . get_file_extension($originalFilename);

                // In case client side validation is bypassed
                if (!_upload_extension_allowed($filename)) {
                    continue;
                }

                $new_file_path = $path . $filename;
                // Upload the file into the event uploads dir
                if (move_uploaded_file($tmpFilePath, $new_file_path)) {
                    // Return only the upload path; that's all we need.
                    return str_replace(FCPATH, '', $new_file_path);
                }
            }
        }
    }


    if (count($errors) > 0) {
        $message = '';
        foreach ($errors as $filename => $error_message) {
            $message .= $filename . ' - ' . $error_message . "\n";
        }

        throw new Exception($message);
    }

    return false;
}

function flexiblewa_get_relation_types()
{
    return [
        [
            'id' => FLEXIBLEWA_PROJECT_RELATION,
            'name' => _l('project'),
        ],
        [
            'id' => FLEXIBLEWA_INVOICE_RELATION,
            'name' => _l('invoice'),
        ],
        [
            'id' => FLEXIBLEWA_CUSTOMER_RELATION,
            'name' => _l('client'),
        ],
        [
            'id' => FLEXIBLEWA_ESTIMATE_RELATION,
            'name' => _l('estimate'),
        ],
        [
            'id' => FLEXIBLEWA_CONTRACT_RELATION,
            'name' => _l('contract'),
        ],
        [
            'id' => FLEXIBLEWA_TICKET_RELATION,
            'name' => _l('ticket'),
        ],
        [
            'id' => FLEXIBLEWA_EXPENSE_RELATION,
            'name' => _l('expense'),
        ],
        [
            'id' => FLEXIBLEWA_LEAD_RELATION,
            'name' => _l('lead'),
        ],
        [
            'id' => FLEXIBLEWA_PROPOSAL_RELATION,
            'name' => _l('proposal'),
        ],
    ];
}

/**
 * Get relations based on relation type
 *
 * @param string $relation_type
 * @return array/mixed
 */
function flexiblewa_get_relations($relation_type)
{
    $CI = &get_instance();
    switch ($relation_type) {
        case FLEXIBLEWA_PROPOSAL_RELATION:
            $CI->load->model('proposals_model');
            $proposals = $CI->proposals_model->get();

            foreach ($proposals as &$proposal) {
                $proposal['name'] = $proposal['subject'];
            }

            return $proposals;

        case FLEXIBLEWA_LEAD_RELATION:
            $CI->load->model('leads_model');
            $leads = $CI->leads_model->get();

            return $leads;

        case FLEXIBLEWA_EXPENSE_RELATION:
            $CI->load->model('expenses_model');
            $expenses = $CI->expenses_model->get();

            foreach ($expenses as &$expense) {
                $expense['name'] = $expense['expense_name'];
            }

            return $expenses;

        case FLEXIBLEWA_TICKET_RELATION:
            $CI->load->model('tickets_model');
            $tickets = $CI->tickets_model->get();

            foreach ($tickets as &$ticket) {
                $ticket['name'] = $ticket['subject'];
                $ticket['id'] = $ticket['ticketid'];
            }

            return $tickets;

        case FLEXIBLEWA_CONTRACT_RELATION:
            $CI->load->model('contracts_model');
            $contracts = $CI->contracts_model->get();

            foreach ($contracts as &$contract) {
                $contract['name'] = $contract['subject'];
            }

            return $contracts;

        case FLEXIBLEWA_ESTIMATE_RELATION:
            $CI->load->model('estimates_model');
            $estimates = $CI->estimates_model->get();

            foreach ($estimates as &$estimate) {
                $estimate['name'] = sales_number_format($estimate['number'], $estimate['number_format'], $estimate['prefix'], $estimate['date']);
            }

            return $estimates;

        case FLEXIBLEWA_CUSTOMER_RELATION:
            $CI->load->model('clients_model');
            $customers = $CI->clients_model->get();

            foreach ($customers as &$customer) {
                $customer['name'] = $customer['company'];
                $customer['id'] = $customer['userid'];
            }

            return $customers;

        case FLEXIBLEWA_INVOICE_RELATION:
            $CI->load->model('invoices_model');
            $invoices = $CI->invoices_model->get();


            foreach ($invoices as &$invoice) {
                if ($invoice['status'] == Invoices_model::STATUS_DRAFT) {
                    $number = $invoice['prefix'] . 'DRAFT';
                } else {
                    $number = sales_number_format($invoice['number'], $invoice['number_format'], $invoice['prefix'], $invoice['date']);
                }

                $invoice['name'] = $number;
            }
            return $invoices;

        default:
            $CI->load->model('projects_model');
            return $CI->projects_model->get();
    }
}

/**
 * Get all task statuses except the one whose id is provided
 *
 * @param string $except
 * @return array
 */
function flexiblewa_get_task_statuses($except = '')
{
    $CI = &get_instance();
    $CI->load->model('Tasks_model');

    if (empty($except)) {
        return $CI->tasks_model->get_statuses();
    }

    $statuses = array_filter($CI->tasks_model->get_statuses(), function ($status) use ($except) {
        return $status['id'] != $except;
    });

    return array_values($statuses);
}

function flexiblewa_get_file_url($file_name)
{
    return site_url($file_name);
}

/**
 * Execute workflow actions for given task-section combo
 *
 * @param array $data
 * @return void
 * @throws Exception
 */
function flexiblewa_execute_task_actions($data)
{
    $CI = &get_instance();
    $CI->load->model('flexiblewa/flexibleworkflow_model');
    $CI->load->model('tasks_model');

    $conditions = [
        'section_id' => $data['status']
    ];

    $workflows = $CI->flexibleworkflow_model->all($conditions);
    $task = $CI->tasks_model->get($data['task_id']);
    flexiblewa_execute_workflows_actions($CI, $workflows, $task);
}

/**
 * Execute workflow actions for newly created task
 *
 * @param integer $task_id
 * @return void
 * @throws Exception
 */
function flexiblewa_execute_new_task_actions($task_id)
{
    $CI = &get_instance();
    $CI->load->model('flexiblewa/flexibleworkflow_model');
    $CI->load->model('tasks_model');
    $task = $CI->tasks_model->get($task_id);
    $workflows = $CI->flexibleworkflow_model->all(['section_id' => $task->status]);
    try {
        flexiblewa_execute_workflows_actions($CI, $workflows, $task);
    } catch (Exception $e) {
        log_activity(FLEXIBLEWA_MODULE_NAME . $e->getMessage());
    }
}





/**
 * @param $CI
 * @param $workflows
 * @param $task
 * @return void
 * @throws Exception
 */
function flexiblewa_execute_workflows_actions($CI, $workflows, $task)
{
    $taskId = $task->id;
    if ($workflows) {
        //let's set the user performing the automation to Automator Workflow User

        $currentStaffUserID = get_staff_user_id();
        $currentClientUserID = get_client_user_id();
        $automatorUserId = flexiblewa_automator_userid();
        $user_data = [
            'staff_user_id' => $automatorUserId,
            'staff_logged_in' => true
        ];
        $CI->session->set_userdata($user_data);
        //logout the client if logged in
        if ($currentClientUserID) {
            $CI->session->unset_userdata('client_user_id');
            $CI->session->unset_userdata('client_logged_in');
        }

        foreach ($workflows as $workflow) {
            $log_message = "Workflow Automation for {$task->name} task: ";

            switch ($workflow['rule_id']) {
                case FLEXIBLEWA_ADD_NEW_FILE_ACTION:
                    $attachment = [
                        [
                            'name' => basename($workflow['rule_value']),
                            'link' => flexiblewa_get_file_url($workflow['rule_value'])
                        ]
                    ];
                    $external = true;

                    if ($CI->tasks_model->add_attachment_to_database($task->id, $attachment, $external)) {
                        $log_message .= $workflow['rule_name'] . ' ' . $workflow['rule_value'];
                    }
                    break;
                case FLEXIBLEWA_MARK_AS_COMPLETE_ACTION:
                    if ($CI->tasks_model->mark_as($workflow['rule_value'], $task->id)) {
                        $log_message .= $workflow['rule_name'] . ' ' . flexiblewa_get_task_status_name($workflow['rule_value']);
                    }
                    break;

                case FLEXIBLEWA_MOVE_TO_ANOTHER_RELATION_ACTION:
                    //the value was joined with comma, so we need to split it
                    $relations = explode(',', $workflow['rule_value']);
                    $task_data = [
                        'rel_type' => $relations[0],
                        'rel_id' => $relations[1]
                    ];
                    //let us check if the relation exists
                    $relation_exists = flexiblewa_get_relation($relations[0], $relations[1]);
                    if (!$relation_exists) {
                        //we need to delete this rule as the relation does not exist
                        $CI->flexibleworkflow_model->delete(['id' => $workflow['id']]);
                    } else {
                        //update the database directly
                        $CI->db->where('id', $task->id);
                        $CI->db->update(db_prefix() . 'tasks', $task_data);
                        if ($CI->db->affected_rows() > 0) {
                            $log_message .= $workflow['rule_name'] . ' ' . $workflow['rule_value'];
                        }
                    }

                    break;
                case FLEXIBLEWA_MOVE_TO_SECTION_ACTION:
                    if ($CI->tasks_model->mark_as($workflow['rule_value'], $task->id)) {
                        $log_message .= $workflow['rule_name'] . ' ' . flexiblewa_get_task_status_name($workflow['rule_value']);
                    }
                    break;
                case FLEXIBLEWA_ADD_NEW_FOLLOWER_ACTION:
                    $followers = explode(',', $workflow['rule_value']);
                    $staff_names = [];

                    for ($i = 0; $i < count($followers); $i++) {
                        $follower_data = [
                            'taskid' => $task->id,
                            'follower' => $followers[$i],
                        ];
                        //check if the staff exists
                        $staff = get_staff($followers[$i]);
                        if (!$staff)
                            continue;
                        if ($CI->tasks_model->add_task_followers($follower_data)) {
                            $staff_names[] = get_staff_full_name($follower_data['follower']);
                        }
                    }

                    $log_message .= $workflow['rule_name'] . ' ' . implode(', ', $staff_names);

                    break;
                case FLEXIBLEWA_ADD_NEW_COMMENT_ACTION:
                    $comment_data = [
                        'taskid' => $task->id,
                        'content' => $workflow['rule_value'],
                    ];

                    $comment_added = $CI->tasks_model->add_task_comment($comment_data);

                    if ($comment_added) {
                        $log_message .= $workflow['rule_name'] . ' ' . $workflow['rule_value'];
                    }
                    break;
                case FLEXIBLEWA_ADD_NEW_REMINDER_ACTION:
                    $CI->load->model('misc_model');
                    $CI->load->model('staff_model');

                    $reminder_values = explode(',', $workflow['rule_value']);
                    //check if staff exists
                    $staff = get_staff($reminder_values[1]);
                    if ($staff) {
                        $duedate = new Carbon();
                        $duedate->add($reminder_values[0]);

                        $user_data = [
                            'staff_user_id' => $reminder_values[1],
                            'staff_logged_in' => true
                        ];

                        $CI->session->set_userdata($user_data);

                        $reminder_data = [
                            'rel_type' => FLEXIBLEWA_TASK_RELATION_TYPE,
                            'rel_id' => $task->id,
                            'date' => to_sql_date($duedate->format('Y-m-d')),
                            'staff' => $reminder_values[1],
                            'notify_by_email' => 1,
                            'description' => "Flexible Workflow Automation reminder for {$task->name} task"
                        ];

                        // We only pass the task id here because it's required by the method
                        $reminder_added = $CI->misc_model->add_reminder($reminder_data, $task->id);

                        if ($reminder_added) {
                            // We have to fetch the staff from the db as using the helper
                            // function picks the cached name of the previously logged in user
                            $staff = $CI->staff_model->get($reminder_values[1]);
                            $log_message .= $workflow['rule_name'] . ' ' . $reminder_values[0] . ' for ' . $staff->full_name;
                        }
                        //let's restore the user performing the automation to the automator user
                        //this is to help next action to be performed by the automator user
                        $user_data = [
                            'staff_user_id' => $automatorUserId,
                            'staff_logged_in' => true
                        ];
                        $CI->session->set_userdata($user_data);
                    } else {
                        //we need to delete this rule as the staff does not exist
                        $CI->flexibleworkflow_model->delete(['id' => $workflow['id']]);
                    }
                    break;
                case FLEXIBLEWA_ADD_NEW_CHECKLIST_ITEM_ACTION:
                    $checklists = explode(',', $workflow['rule_value']);
                    $checklist_added = false;
                    for ($i = 0; $i < count($checklists); $i++) {
                        $checklist_data = [
                            'taskid' => $task->id,
                            'description' => $checklists[$i],
                            'list_order' => ($i + 1)
                        ];

                        $checklist_added = $CI->tasks_model->add_checklist_item($checklist_data);
                    }

                    if ($checklist_added) {
                        $log_message .= $workflow['rule_name'] . ' ' . $workflow['rule_value'];
                    }

                    break;
                case FLEXIBLEWA_SET_PRIORITY_TO_ACTION:
                    $task_data = [
                        'priority' => $workflow['rule_value']
                    ];
                    $CI->db->where('id', $task->id);
                    $CI->db->update(db_prefix() . 'tasks', $task_data);
                    if ($CI->db->affected_rows() > 0) {
                        $log_message .= $workflow['rule_name'] . ' ' . task_priority($workflow['rule_value']);
                    }

                    break;
                case FLEXIBLEWA_SET_DUE_DATE_TO_ACTION:
                    $duedate = new Carbon();
                    $duedate->add($workflow['rule_value']);

                    $task_data = [
                        'duedate' => to_sql_date($duedate->format('Y-m-d'))
                    ];
                    $CI->db->where('id', $task->id);
                    $CI->db->update(db_prefix() . 'tasks', $task_data);
                    if ($CI->db->affected_rows() > 0) {
                        $log_message .= $workflow['rule_name'] . ' ' . $duedate->format('Y-m-d');
                    }

                    break;
                case FLEXIBLEWA_SET_ASSIGNED_TO_ACTION:
                    $task_data = [];
                    $assignees = explode(',', $workflow['rule_value']);

                    for ($i = 0; $i < count($assignees); $i++) {
                        $assignee_exists = $CI->tasks_model->get_task_assignees($assignees[$i]);

                        if (!$assignee_exists) {
                            //let us check if the staff exists
                            $staff = get_staff($assignees[$i]);
                            if ($staff) {
                                $task_data = [
                                    'taskid' => $taskId,
                                    'assignee' => $assignees[$i]
                                ];
                                $CI->tasks_model->add_task_assignees($task_data);

                                $log_message .= $workflow['rule_name'] . ' ' . get_staff_full_name($assignees[$i]);
                            }
                        }
                    }
                    break;

                default:
                    # code...
                    break;
            }

            log_activity($log_message);
        }
        //let's restore the user performing the automation to the original user
        if ($currentStaffUserID) {
            $user_data = [
                'staff_user_id' => $currentStaffUserID,
                'staff_logged_in' => true
            ];
            $CI->session->set_userdata($user_data);
        }
        if ($currentClientUserID) {
            $user_data = [
                'client_user_id' => $currentClientUserID,
                'client_logged_in' => true
            ];
            $CI->session->set_userdata($user_data);
        }
    }
}

function flexiblewa_automator_userid()
{
    //option to set the user id for the automator
    return (get_option('flexiblewa_automator_userid')) ? get_option('flexiblewa_automator_userid') : 0;
}

function flexiblewa_create_automator_bot()
{
    if (!get_option('flexiblewa_automator_userid')) {
        $CI = &get_instance();
        $CI->load->model('Staff_model');

        $staff_data = [
            'firstname' => 'Workflow',
            'lastname' => 'Automator(Bot) ',
            'email' => 'workflow@automatorproductivity.com',
            'password' => 'secretpassword123',
        ];

        $automator_user_id = $CI->staff_model->add($staff_data);

        if ($automator_user_id) {
            add_option('flexiblewa_automator_userid', $automator_user_id);
        }
    }
}

function flexiblewa_get_display_value($rule_id, $rule_value)
{
    switch ($rule_id) {

        case FLEXIBLEWA_ADD_NEW_FILE_ACTION:
            $name = basename($rule_value);
            $link = flexiblewa_get_file_url($rule_value);
            $label = _l('flexiblewa_view_file');
            return "<a href='$link' title='$name' target='blank' />$label</a>";

        case FLEXIBLEWA_MARK_AS_COMPLETE_ACTION:
            return $rule_value ? _l('flexiblewa_true') : _l('flexiblewa_false');

        case FLEXIBLEWA_MOVE_TO_ANOTHER_RELATION_ACTION:
            //the value was joined with comma, so we need to split it
            list($relation_type, $relation_id) = explode(',', $rule_value);
            $relation =  flexiblewa_get_relation($relation_type, $relation_id);
            if (!$relation) return "";
            $relation = (array)$relation;
            $relation_type = ucfirst($relation_type);
            $type_label = _l('flexiblewa_relation_type');
            $name_label = _l('flexiblewa_relation_name');
            $name = $relation['name'];
            return "<p><strong>$type_label: </strong>$relation_type</p> <p><strong>$name_label: </strong>$name</p>";

        case FLEXIBLEWA_MOVE_TO_SECTION_ACTION:
            return flexiblewa_get_task_status_name($rule_value);

        case FLEXIBLEWA_ADD_NEW_FOLLOWER_ACTION:
            $followers = explode(',', $rule_value);
            $staff_names = [];

            for ($i = 0; $i < count($followers); $i++) {
                $staff_names[] = get_staff_full_name($followers[$i]);
            }

            return implode(', ', $staff_names);

        case FLEXIBLEWA_ADD_NEW_COMMENT_ACTION:
            return $rule_value;

        case FLEXIBLEWA_ADD_NEW_REMINDER_ACTION:
            $reminder_values = explode(',', $rule_value);

            return $reminder_values[0];

        case FLEXIBLEWA_ADD_NEW_CHECKLIST_ITEM_ACTION:
            return str_replace(',', '<br>', $rule_value);

        case FLEXIBLEWA_SET_PRIORITY_TO_ACTION:
            return task_priority($rule_value);

        case FLEXIBLEWA_SET_DUE_DATE_TO_ACTION:
            return $rule_value;

        case FLEXIBLEWA_SET_ASSIGNED_TO_ACTION:
            $assignees = explode(',', $rule_value);
            $assignee_names = [];

            for ($i = 0; $i < count($assignees); $i++) {
                $assignee_names[] = get_staff_full_name($assignees[$i]);
            }

            return implode(', ', $assignee_names);

        default:
            # code...
            break;
    }
}

function flexiblewa_get_relation($relation_type, $relation_id)
{
    $CI = &get_instance();
    switch ($relation_type) {
        case FLEXIBLEWA_PROPOSAL_RELATION:
            $CI->load->model('proposals_model');
            $proposal = $CI->proposals_model->get($relation_id);
            if (!$proposal) return [];
            if (!is_array($proposal)) $proposal = (array)$proposal;
            $proposal['name'] = $proposal['subject'];
            return $proposal;

        case FLEXIBLEWA_LEAD_RELATION:
            $CI->load->model('leads_model');
            $lead = $CI->leads_model->get($relation_id);
            if (!$lead)
                return [];
            return $lead;

        case FLEXIBLEWA_EXPENSE_RELATION:
            $CI->load->model('expenses_model');
            $expense = $CI->expenses_model->get($relation_id);
            if (!$expense)
                return [];
            return $expense;

        case FLEXIBLEWA_TICKET_RELATION:
            $CI->load->model('tickets_model');
            $ticket = $CI->tickets_model->get($relation_id);
            if (!$ticket) return [];
            $ticket = (array) $ticket;
            $ticket['name'] = $ticket['subject'];
            $ticket['id'] = $ticket['ticketid'];
            return $ticket;

        case FLEXIBLEWA_CONTRACT_RELATION:
            $CI->load->model('contracts_model');
            $contract = $CI->contracts_model->get($relation_id);
            if (!$contract)
                return [];
            $contract['name'] = $contract['subject'];
            return $contract;

        case FLEXIBLEWA_ESTIMATE_RELATION:
            $CI->load->model('estimates_model');
            $estimate = $CI->estimates_model->get($relation_id);
            if (!$estimate)
                return [];
            $estimate['name'] = sales_number_format($estimate['number'], $estimate['number_format'], $estimate['prefix'], $estimate['date']);

            return $estimate;

        case FLEXIBLEWA_CUSTOMER_RELATION:
            $CI->load->model('clients_model');
            $customer = (array) $CI->clients_model->get($relation_id);
            if (!$customer)
                return [];
            $customer['name'] = $customer['company'];
            $customer['id'] = $customer['userid'];
            return $customer;

        case FLEXIBLEWA_INVOICE_RELATION:
            $CI->load->model('invoices_model');
            $invoice = (array) $CI->invoices_model->get($relation_id);
            if (!$invoice)
                return [];

            if ($invoice['status'] == Invoices_model::STATUS_DRAFT) {
                $number = $invoice['prefix'] . 'DRAFT';
            } else {
                $number = sales_number_format($invoice['number'], $invoice['number_format'], $invoice['prefix'], $invoice['date']);
            }

            $invoice['name'] = $number;
            return $invoice;

        default:
            $CI->load->model('projects_model');
            $project = $CI->projects_model->get($relation_id);
            if (!$project)
                return [];
            return $project;
    }
}

function flexiblewa_get_lead_statuses()
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    return $CI->leads_model->get_status();
}

function flexiblewa_serialize_data($data)
{
    return base64_encode(serialize($data));
}

function flexiblewa_unserialize_data($string)
{
    if (base64_decode($string, true) == true) {
        return @unserialize(base64_decode($string));
    } else {
        return @unserialize($string);
    }
}

function flexiblewa_get_display_value_for_generic_rule($rule_action, $rule_value)
{
    switch ($rule_action) {
        case FLEXIBLEWA_ADD_NEW_REMINDER_ACTION:
            $reminder_data = flexiblewa_unserialize_data($rule_value);
            return '+' . $reminder_data['reminder_date'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('reminder_message') . '</b>: ' . $reminder_data['reminder_message']
                . ' <br/>' . '<b>' . _flexiblewa_lang('reminder_user') . '</b>: ' . get_staff_full_name($reminder_data['reminder_user_id']);


        case FLEXIBLEWA_SEND_EMAIL_ACTION:
            $email_data = flexiblewa_unserialize_data($rule_value);
            $to_names = [];
            foreach (explode(',', $email_data['to']) as $to) {
                if (is_numeric($to)) {
                    $to_names[] = get_staff_full_name($to);
                } else {
                    $to_names[] = $to;
                }
            }
            $to_names = implode(', ', $to_names);
            return '<b>' . _flexiblewa_lang('to') . '</b>: ' . $to_names . ' <br/>' . '<b>' . _flexiblewa_lang('subject') . '</b>: ' . $email_data['subject'] . ' <br/>' . '<b>' . _flexiblewa_lang('body') . '</b>: ' . nl2br($email_data['body']);

        case FLEXIBLEWA_ADD_NEW_TASK_ACTION:
            $task_data = flexiblewa_unserialize_data($rule_value);
            $assignees_names = [];
            $followers_names = [];
            foreach (explode(',', $task_data['assigned_to']) as $assignee) {
                $assignees_names[] = get_staff_full_name($assignee);
            }
            foreach (explode(',', $task_data['followers']) as $follower) {
                $followers_names[] = get_staff_full_name($follower);
            }
            return '<b>' . _flexiblewa_lang('task') . '</b>: ' . $task_data['subject'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('description') . '</b>: ' . $task_data['description'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('start_date') . '</b>: +' . $task_data['startdate'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('due_date') . '</b>: +' . $task_data['duedate'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('priority') . '</b>: ' . task_priority($task_data['priority']) .
                ' <br/>' . '<b>' . _flexiblewa_lang('assigned_to') . '</b>: ' . format_members_by_ids_and_names($task_data['assigned_to'], implode(', ', $assignees_names)) .
                ' <br/>' . '<b>' . _flexiblewa_lang('followers') . '</b>: ' . format_members_by_ids_and_names($task_data['followers'], implode(', ', $followers_names));

        case FLEXIBLEWA_ADD_NEW_NOTE_ACTION:
            $note_data = flexiblewa_unserialize_data($rule_value);
            return '<b>' . _flexiblewa_lang('note') . '</b>: ' . $note_data['description'];

        case FLEXIBLEWA_ADD_NEW_DISCUSSION_ACTION:
            $discussion_data = flexiblewa_unserialize_data($rule_value);
            $show_to_customer = $discussion_data['show_to_customer'] ? _flexiblewa_lang('yes') : _flexiblewa_lang('no');
            return '<b>' . _flexiblewa_lang('discussion') . '</b>: ' . $discussion_data['subject'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('description') . '</b>: ' . $discussion_data['description'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('show_to_customer') . '</b>: ' . $show_to_customer;

        case FLEXIBLEWA_ADD_NEW_MILESTONE_ACTION:
            $milestone_data = flexiblewa_unserialize_data($rule_value);
            return '<b>' . _flexiblewa_lang('milestone') . '</b>: ' . $milestone_data['name'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('description') . '</b>: ' . $milestone_data['description'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('start_date') . '</b>: +' . $milestone_data['start_date'] .
                ' <br/>' . '<b>' . _flexiblewa_lang('due_date') . '</b>: +' . $milestone_data['due_date'];

        default:
            return $rule_value;
    }
}

function flexiblewa_get_lead_status($status_id)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    return $CI->leads_model->get_status($status_id);
}

function flexiblewa_get_when_event_name($when_event)
{
    //if string contains lead_status_INT
    if (strpos($when_event, 'lead_status') !== false) {
        //get the status id
        $status_id = explode('_', $when_event)[2];
        $status = flexiblewa_get_lead_status($status_id);
        return _flexiblewa_lang('lead_status') . ' - ' . $status->name;
    } else if (strpos($when_event, 'project_status') !== false) {
        //get the status id
        $status_id = explode('_', $when_event)[2];
        $status = flexiblewa_get_project_statuses($status_id);
        return _flexiblewa_lang('project_status') . ' - ' . $status['name'];
    } else {
        return _flexiblewa_lang($when_event);
    }
}

function flexiblewa_execute_generic_action($rule, $relation_id, $when_event, $relation_type = FLEXIBLEWA_LEAD_RELATION_TYPE)
{
    $CI = &get_instance();
    $currentStaffUserID = get_staff_user_id();
    $currentClientUserID = get_client_user_id();
    $automatorUserId = flexiblewa_automator_userid();
    $user_data = [
        'staff_user_id' => $automatorUserId,
        'staff_logged_in' => true
    ];
    $CI->session->set_userdata($user_data);
    //logout the client if logged in
    if ($currentClientUserID) {
        $CI->session->unset_userdata('client_user_id');
        $CI->session->unset_userdata('client_logged_in');
    }


    $action = $rule['rule_action'];
    $rule_value = flexiblewa_unserialize_data($rule['rule_value']);

    switch ($action) {
        case FLEXIBLEWA_ADD_NEW_TASK_ACTION:
            //load task model
            $CI->load->model('tasks_model');
            $startdata_arr = explode('::', $rule_value['startdate']);
            $duedate_arr = explode('::', $rule_value['duedate']);
            $startcount = $startdata_arr[0]; //int 
            $startperiod = $startdata_arr[1]; //days, weeks, months, years
            $duedatecount = $duedate_arr[0]; // int
            $dueperiod = $duedate_arr[1]; //days, weeks, months, years
            //create a startdate based on the startcount and startperiod, if count is 0, then use the current date
            $startdate = $startcount > 0 ? date('Y-m-d', strtotime('+' . $startcount . ' ' . $startperiod)) : date('Y-m-d');
            //create a duedate based on the duedatecount and dueperiod, if count is 0, then use the current date
            $duedate = $duedatecount > 0 ? date('Y-m-d', strtotime('+' . $duedatecount . ' ' . $dueperiod)) : date('Y-m-d');
            //create a 
            $task_data = [
                'name' => $rule_value['subject'],
                'description' => $rule_value['description'],
                'rel_type' => $relation_type,
                'rel_id' => $relation_id,
                'startdate' => to_sql_date($startdate),
                'duedate' => to_sql_date($duedate),
                'priority' => $rule_value['priority'],
                'assignees' => explode(',', $rule_value['assigned_to']),
                'followers' => explode(',', $rule_value['followers']),
                'is_public' => $rule_value['is_public'],
                'repeat_every' => $rule_value['repeat_every'],
                'billable' => $rule_value['billable'],
                'tags' => $rule_value['tags'],
            ];
            $task_id = $CI->tasks_model->add($task_data);
            if ($task_id) {
                $log_message = "Workflow Automation for {$rule_value['subject']} New Task Created: {$task_id}";
                log_activity($log_message);
            }
            break;
        case FLEXIBLEWA_ADD_NEW_REMINDER_ACTION:
            $CI->load->model('misc_model');
            $reminder_message = $rule_value['reminder_message'];
            $reminder_date = $rule_value['reminder_date'];
            $reminder_user_id = $rule_value['reminder_user_id'];
            $date_arr = explode('::', $reminder_date);
            $date_count = $date_arr[0];
            $date_period = $date_arr[1];
            $reminder_date = $date_count > 0 ? date('Y-m-d H:i:s', strtotime('+' . $date_count . ' ' . $date_period)) : date('Y-m-d H:i:s');

            $reminder_data = [
                'rel_type' => $relation_type, //project does not have notification, this would usualy be lead for now
                'rel_id' => $relation_id,
                'date' => to_sql_date($reminder_date, true),
                'staff' => $reminder_user_id,
                'notify_by_email' => 1,
                'description' => $reminder_message
            ];
            // We only pass the task id here because it's required by the method
            $reminder_added = $CI->misc_model->add_reminder($reminder_data, $relation_id);
            if ($reminder_added) {
                $log_message = "Workflow Automation New Reminder Created For {$relation_type}: {$relation_id}";
                log_activity($log_message);
            }
            break;

        case FLEXIBLEWA_ADD_NEW_NOTE_ACTION:
            $CI->load->model('misc_model');
            $note_data = [
                'description' => $rule_value['description'],
            ];
            $note_id = $CI->misc_model->add_note($note_data, $relation_type, $relation_id);
            if ($note_id) {
                $log_message = "Workflow Automation for New Note Created: {$note_id}";
                log_activity($log_message);
            }
            break;

        case FLEXIBLEWA_SEND_EMAIL_ACTION:
            if ($relation_type == "lead") {
                $CI->load->model('leads_model');
                $lead = $CI->leads_model->get($relation_id);
                $relation_email = $lead->email;
                $relation_data = $lead;
            } else if ($relation_type == "project") {
                $CI->load->model('projects_model');
                $CI->load->model('clients_model');
                $project = $CI->projects_model->get($relation_id);
                $contacts = $CI->clients_model->get_contacts_for_project_notifications($relation_id, 'project_emails');
                $relation_email = [];
                foreach($contacts as $contact){
                    $relation_email[] = $contact['email'];
                }
                $relation_data = $project;
            }
            $subject = $rule_value['subject'];
            $body = $rule_value['body'];
            $to = $rule_value['to'];
            $emails = [];
            $to_array = explode(',', $to);
            foreach ($to_array as $to) {
                if (is_numeric($to)) {
                    $staff = get_staff($to);
                    $emails[] = $staff->email;
                } else {
                    //get the lead or project email
                    if(is_array($relation_email)){
                        $emails = array_merge($emails, $relation_email);
                    }else{
                        $emails[] = $relation_email;
                    }
                }
            }
            if ($emails) {
                $CI->load->library(FLEXIBLEWA_MODULE_NAME . '/Flexiblewa_module');
                foreach ($emails as $email) {
                    $CI->flexiblewa_module->send_email($email, $subject, $body, $relation_data, $relation_type);
                }
                $log_message = "Workflow Automation ({$relation_type}) for {$subject} Email Sent To: " . implode(', ', $emails);
                log_activity($log_message);
            }

            break;

        case FLEXIBLEWA_ADD_NEW_DISCUSSION_ACTION:
            //load project model
            $CI->load->model('projects_model');
            $discussion_data = [
                'subject' => $rule_value['subject'],
                'description' => $rule_value['description'],
                'project_id' => $relation_id,
            ];
            $show_to_customer = $rule_value['show_to_customer'] ? 1 : 0;
            if($show_to_customer){
                $discussion_data['show_to_customer'] = 1;
            }
            $discussion_id = $CI->projects_model->add_discussion($discussion_data);
            if ($discussion_id) {
                $log_message = "Workflow Automation for New Discussion Created: {$discussion_id}";
                log_activity($log_message);
            }
            break;

        case FLEXIBLEWA_ADD_NEW_MILESTONE_ACTION:
            //load milestone model
            $CI->load->model('projects_model');
            $startdate_arr = explode('::', $rule_value['start_date']);
            $duedate_arr = explode('::', $rule_value['due_date']);
            $startcount = $startdate_arr[0]; //int 
            $startperiod = $startdate_arr[1]; //days, weeks, months, years
            $duedatecount = $duedate_arr[0]; // int
            $dueperiod = $duedate_arr[1]; //days, weeks, months, years
            $startdate = $startcount > 0 ? date('Y-m-d', strtotime('+' . $startcount . ' ' . $startperiod)) : date('Y-m-d');
            $duedate = $duedatecount > 0 ? date('Y-m-d', strtotime('+' . $duedatecount . ' ' . $dueperiod)) : date('Y-m-d');
            $milestone_data = [
                'name' => $rule_value['name'],
                'description' => $rule_value['description'],
                'project_id' => $relation_id,
                'start_date' => to_sql_date($startdate),
                'due_date' => to_sql_date($duedate),
            ];
            $description_visible_to_customer = $rule_value['description_visible_to_customer'] ? 1 : 0;
            if($description_visible_to_customer){
                $milestone_data['description_visible_to_customer'] = 1;
            }
            $hide_from_customer = $rule_value['hide_from_customer'] ? 1 : 0;
            if($hide_from_customer){
                $milestone_data['hide_from_customer'] = 1;
            }

            $milestone_id = $CI->projects_model->add_milestone($milestone_data);
            if ($milestone_id) {
                $log_message = "Workflow Automation for New Milestone Created: {$milestone_id}";
                log_activity($log_message);
            }
        default:
            break;
    }
    //let's restore the user performing the automation to the original user
    if ($currentStaffUserID) {
        $user_data = [
            'staff_user_id' => $currentStaffUserID,
            'staff_logged_in' => true
        ];
        $CI->session->set_userdata($user_data);
    }
    if ($currentClientUserID) {
        $user_data = [
            'client_user_id' => $currentClientUserID,
            'client_logged_in' => true
        ];
        $CI->session->set_userdata($user_data);
    }
    return true;
}

function flexiblewa_get_project_statuses($status_id = '')
{
    $CI = &get_instance();
    $CI->load->model('projects_model');
    $statuses = $CI->projects_model->get_project_statuses();
    if ($status_id) {
        foreach ($statuses as $status) {
            if ($status['id'] == $status_id) {
                return $status;
            }
        }
    }
    return $statuses;
}


// Smart Choice structural standard assets and permissions.
if (function_exists('hooks')) {
    hooks()->add_action('app_admin_head', 'flexiblewa_smart_choice_standard_head');
    hooks()->add_action('app_admin_footer', 'flexiblewa_smart_choice_standard_footer');
}
if (!function_exists('flexiblewa_smart_choice_standard_head')) {
    function flexiblewa_smart_choice_standard_head() {
        echo '<link href="' . module_dir_url('flexiblewa', 'assets/css/smart_choice_module_standard.css') . '?v=100" rel="stylesheet" type="text/css" />';
    }
}
if (!function_exists('flexiblewa_smart_choice_standard_footer')) {
    function flexiblewa_smart_choice_standard_footer() {
        echo '<script src="' . module_dir_url('flexiblewa', 'assets/js/smart_choice_module_standard.js') . '?v=100"></script>';
    }
}
if (function_exists('register_staff_capabilities')) {
    register_staff_capabilities('flexiblewa', [
        'view_own' => _l('view_own'),
        'view' => _l('view_global'),
        'create' => _l('create'),
        'edit' => _l('edit'),
        'delete' => _l('delete'),
    ], _l('flexiblewa'));
}
