<?php

use Carbon\Carbon;

defined('BASEPATH') or exit('No direct script access allowed');

class Flexiblewa extends AdminController
{
    public function index()
    {
        $this->load->model('flexiblewa/flexibleworkflow_model');
        $data['title'] = _flexiblewa_lang('tasks_automation');
        $data['rules'] = $this->flexibleworkflow_model->all();
        $data['statuses'] = flexiblewa_get_task_statuses();

        foreach ($data['rules'] as &$rule) {
            $rule['display_value'] = flexiblewa_get_display_value($rule['rule_id'], $rule['rule_value']);
        }

        $this->app_css->add('flexiblewa-tree-css', module_dir_url('flexiblewa', 'assets/css/flexiblewa.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexiblewa-js', module_dir_url('flexiblewa', 'assets/js/flexiblewa.js'), 'admin', ['app-js']);
        $this->load->view('index', $data);
    }

    public function lead_automation()
    {
        $this->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
        $data['title'] = _flexiblewa_lang('lead_automation');
        $data['rules'] = $this->flexibleworkflow_generic_rules_model->all(['rule_type' => FLEXIBLEWA_LEAD_RULE_TYPE]);

        foreach ($data['rules'] as &$rule) {
            $rule['display_value'] = flexiblewa_get_display_value_for_generic_rule($rule['rule_action'], $rule['rule_value']);
        }
        $statuses = flexiblewa_get_lead_statuses();
        $data['statuses'] = $statuses;

        $this->app_css->add('flexiblewa-tree-css', module_dir_url('flexiblewa', 'assets/css/flexiblewa.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexiblewa-js', module_dir_url('flexiblewa', 'assets/js/flexiblewa.js'), 'admin', ['app-js']);
        $this->load->view('lead_automation', $data);
    }

    public function project_automation()
    {
        $this->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
        $data['title'] = _flexiblewa_lang('project_automation');
        $data['rules'] = $this->flexibleworkflow_generic_rules_model->all(['rule_type' => FLEXIBLEWA_PROJECT_RULE_TYPE]);
        foreach ($data['rules'] as &$rule) {
            $rule['display_value'] = flexiblewa_get_display_value_for_generic_rule($rule['rule_action'], $rule['rule_value']);
        }
        $data['statuses'] = flexiblewa_get_project_statuses();
        //load csss and js
        $this->app_css->add('flexiblewa-tree-css', module_dir_url('flexiblewa', 'assets/css/flexiblewa.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexiblewa-js', module_dir_url('flexiblewa', 'assets/js/flexiblewa.js'), 'admin', ['app-js']);
        $this->load->view('project_automation', $data);
    }

    public function rule()
    {
        $post = $this->input->post();
        //print_r($post);die();
        if ($post) {
            $this->load->model('flexiblewa/flexibleworkflow_model');

            if (array_key_exists('rule_type', $post)) {
                $rule_type = $post['rule_type'];
                unset($post['rule_type']);
                if ($rule_type == FLEXIBLEWA_LEAD_RULE_TYPE) {
                    $post = array_merge($post, [
                        'rule_name' => flexiblewa_get_rule_name($post['rule_id']),
                        'user_id' => get_staff_user_id(),
                        'date_created' => to_sql_date(Carbon::now()->toDateTimeString(), true)
                    ]);
                }
            } else {
                $post = array_merge($post, [
                    'section_name' => flexiblewa_get_task_status_name($post['section_id']),
                    'rule_name' => flexiblewa_get_rule_name($post['rule_id']),
                    'user_id' => get_staff_user_id(),
                    'date_created' => to_sql_date(Carbon::now()->toDateTimeString(), true)
                ]);
            }
            //print_r($post['rule_id']);
            switch ($post['rule_id']) {
                case FLEXIBLEWA_SET_ASSIGNED_TO_ACTION:
                    $post = array_merge($post, [
                        'rule_value' => implode(',', $post['assignees'])
                    ]);
                    unset($post['assignees']);

                    break;
                case FLEXIBLEWA_SET_DUE_DATE_TO_ACTION:
                    $post = array_merge($post, [
                        'rule_value' => '+ ' . $post['time_count'] . ' ' . $post['period']
                    ]);
                    unset($post['time_count']);
                    unset($post['period']);

                    break;
                case FLEXIBLEWA_SET_PRIORITY_TO_ACTION:
                    $post = array_merge($post, [
                        'rule_value' => $post['priority']
                    ]);
                    unset($post['priority']);

                    break;
                case FLEXIBLEWA_ADD_NEW_CHECKLIST_ITEM_ACTION:
                    $post = array_merge($post, [
                        'rule_value' => $post['checklist']
                    ]);
                    unset($post['checklist']);

                    break;
                case FLEXIBLEWA_ADD_NEW_REMINDER_ACTION:
                    $reminder = '+ ' . $post['time_count'] . ' ' . $post['period'];
                    $reminder_data = [
                        $reminder,
                        $post['reminder_user_id']
                    ];

                    $post = array_merge($post, [
                        'rule_value' => implode(',', $reminder_data)
                    ]);
                    unset($post['time_count']);
                    unset($post['period']);
                    unset($post['reminder_user_id']);

                    break;
                case FLEXIBLEWA_ADD_NEW_COMMENT_ACTION:
                    $post = array_merge($post, [
                        'rule_value' => $post['comment']
                    ]);
                    unset($post['comment']);

                    break;
                case FLEXIBLEWA_ADD_NEW_FOLLOWER_ACTION:
                    $post = array_merge($post, [
                        'rule_value' => implode(',', $post['followers'])
                    ]);
                    unset($post['followers']);

                    break;

                case FLEXIBLEWA_ADD_NEW_FILE_ACTION:
                    $uploaded_file_path = flexiblewa_upload_file('file');

                    if (!$uploaded_file_path) {
                        set_alert('danger', _l('flexiblewa_adding_rule_failed'));
                        redirect(admin_url('flexiblewa'));
                    }

                    $post = array_merge($post, [
                        'rule_value' => $uploaded_file_path
                    ]);
                    unset($post['file']);

                    break;

                case FLEXIBLEWA_MOVE_TO_ANOTHER_RELATION_ACTION:

                    $post = array_merge($post, [
                        'rule_value' => implode(',', [
                            $post['rel_type'],
                            $post['relation_id']
                        ])
                    ]);
                    unset($post['rel_type']);
                    unset($post['relation_id']);
                    break;

                case FLEXIBLEWA_MOVE_TO_SECTION_ACTION:
                    if ($post['section_id'] == $post['move_to_section']) {
                        set_alert('danger', _l('flexiblewa_move_to_different_section'));
                        redirect(admin_url('flexiblewa'));
                    }

                    $post = array_merge($post, [
                        'rule_value' => $post['move_to_section']
                    ]);
                    unset($post['move_to_section']);

                    break;

                case FLEXIBLEWA_MARK_AS_COMPLETE_ACTION:
                    $this->load->model('Tasks_model');
                    $post = array_merge($post, [
                        'rule_value' => $this->tasks_model::STATUS_COMPLETE
                    ]);
                    break;

                default:
                    # code...
                    break;
            }


            $redirect_url = flexiblewa_admin_url();
            $conditions = [
                'section_id' => $post['section_id'],
                'rule_id' => $post['rule_id'],
                'user_id' => $post['user_id']
            ];

            // If the section-action combo exists for a user
            $workflow = $this->flexibleworkflow_model->get($conditions);
            if ($workflow) {
                // Update the record
                $saved = $this->flexibleworkflow_model->update($workflow['id'], $post);
            } else {
                $saved = $this->flexibleworkflow_model->add($post);
            }



            if ($saved) {
                set_alert('success', _l('flexiblewa_rule_added_successfully'));
                redirect($redirect_url);
            }
        }
    }

    public function delete_rule($rule_id = '')
    {
        if (!$rule_id) {
            set_alert('danger', _l('flexiblewa_rule_not_found'));
        } else {
            $this->load->model('flexiblewa/flexibleworkflow_model');
            $deleted = $this->flexibleworkflow_model->delete([
                'id' => $rule_id
            ]);

            if ($deleted) {
                set_alert('success', _l('flexiblewa_rule_deleted_successfully'));
            }
        }

        redirect(admin_url('flexiblewa'));
    }

    public function delete_generic_rule($rule_id = '', $rule_type = '')
    {
        if (!$rule_id) {
            set_alert('danger', _l('flexiblewa_rule_not_found'));
        }
        $this->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
        $deleted = $this->flexibleworkflow_generic_rules_model->delete([
            'id' => $rule_id
        ]);
        if ($deleted) {
            set_alert('success', _l('flexiblewa_rule_deleted_successfully'));
        }
        //redirect back
        redirect(flexiblewa_admin_url($rule_type . '_automation'));
    }

    public function add_generic_rule()
    {
        if ($post = $this->input->post()) {
            //load the model Flexibleworkflow_generic_rules_model
            $this->load->model('flexiblewa/flexibleworkflow_generic_rules_model');

            $rule_action = $post['rule_action']; //this corresponts to the constants in flexiblewa_constants.php
            if(!$rule_action) {
                set_alert('danger', _l('flexiblewa_rule_not_found'));
                return redirect(flexiblewa_admin_url());
            }
            $rule_type = $post['rule_type'];
            $when_event = $post['when'];
            switch ($rule_action) {
                case FLEXIBLEWA_ADD_NEW_TASK_ACTION:
                    $start_date = $post['start_time_count'] . '::' . $post['start_period'];
                    $due_date = $post['due_time_count'] . '::' . $post['due_period'];
                    $data = [
                        'subject' => $post['name'],
                        'description' => $post['description'],
                        'hourly_rate' => $post['hourly_rate'],
                        'startdate' => $start_date,
                        'duedate' => $due_date,
                        'priority' => $post['priority'],
                        'billable' => isset($post['billable']) ? 1 : 0,
                        'is_public' => isset($post['is_public']) ? 1 : 0,
                        'assigned_to' => implode(',', $post['assignees']),
                        'followers' => implode(',', $post['followers']),
                        'repeat_every' => $post['repeat_every'],
                        'tags' => $post['tags'],
                        'date_created' => date('Y-m-d H:i:s')
                    ];

                    //add the generic rule now
                    $generic_rule_data = [
                        'rule_name' => $post['title'],
                        'when_event' => $when_event,
                        'rule_type' => $rule_type,
                        'rule_action' => $rule_action,
                        'rule_value' => flexiblewa_serialize_data($data),
                        'user_id' => get_staff_user_id(),
                        'date_created' => date('Y-m-d H:i:s')
                    ];
                    $this->flexibleworkflow_generic_rules_model->add($generic_rule_data);
                    set_alert('success', _l('flexiblewa_rule_added_successfully'));
                    return redirect(flexiblewa_admin_url($rule_type . '_automation'));
                    break;

                case FLEXIBLEWA_ADD_NEW_NOTE_ACTION:
                    $description  = $post['note_description'];
                    $contact_indicator = "";

                    $data = [
                        'description' => $description,
                        'contacted_indicator' => $contact_indicator,
                    ];
                    $generic_rule_data = [
                        'rule_name' => $post['title'],
                        'when_event' => $when_event,
                        'rule_type' => $rule_type,
                        'rule_action' => $rule_action,
                        'rule_value' => flexiblewa_serialize_data($data),
                        'user_id' => get_staff_user_id(),
                        'date_created' => date('Y-m-d H:i:s')
                    ];
                    $this->flexibleworkflow_generic_rules_model->add($generic_rule_data);
                    set_alert('success', _l('flexiblewa_rule_added_successfully'));
                    return redirect(flexiblewa_admin_url($rule_type . '_automation'));

                    break;
                case FLEXIBLEWA_ADD_NEW_REMINDER_ACTION:
                    $reminder_data = [
                        'reminder_user_id' => $post['reminder_user_id'],
                        'reminder_message' => $post['reminder_message'],
                        'reminder_date' => $post['time_count'] . '::' . $post['period']
                    ];
                    $generic_rule_data = [
                        'rule_name' => $post['title'],
                        'when_event' => $when_event,
                        'rule_type' => $rule_type,
                        'rule_action' => $rule_action,
                        'rule_value' => flexiblewa_serialize_data($reminder_data),
                        'user_id' => get_staff_user_id(),
                        'date_created' => date('Y-m-d H:i:s')
                    ];
                    $this->flexibleworkflow_generic_rules_model->add($generic_rule_data);
                    set_alert('success', _l('flexiblewa_rule_added_successfully'));
                    return redirect(flexiblewa_admin_url($rule_type . '_automation'));

                    break;
                case FLEXIBLEWA_SEND_EMAIL_ACTION:
                    $email_data = [
                        'to' => implode(',', $post['to']),
                        'subject' => $post['subject'],
                        'body' => $post['body'],
                        'from_name' => $post['from_name']
                    ];
                    $generic_rule_data = [
                        'rule_name' => $post['title'],
                        'when_event' => $when_event,
                        'rule_type' => $rule_type,
                        'rule_action' => $rule_action,
                        'rule_value' => flexiblewa_serialize_data($email_data),
                        'user_id' => get_staff_user_id(),
                        'date_created' => date('Y-m-d H:i:s')
                    ];
                    $this->flexibleworkflow_generic_rules_model->add($generic_rule_data);
                    set_alert('success', _l('flexiblewa_rule_added_successfully'));
                    return redirect(flexiblewa_admin_url($rule_type . '_automation'));
                    break;

                case FLEXIBLEWA_ADD_NEW_DISCUSSION_ACTION:
                    $discussion_data = [
                        'subject' => $post['subject'],
                        'description' => $post['description'],
                        'show_to_customer' => isset($post['show_to_customer']) ? 1 : 0
                    ];
                    $generic_rule_data = [
                        'rule_name' => $post['title'],
                        'when_event' => $when_event,
                        'rule_type' => $rule_type,
                        'rule_action' => $rule_action,
                        'rule_value' => flexiblewa_serialize_data($discussion_data),
                        'user_id' => get_staff_user_id(),
                        'date_created' => date('Y-m-d H:i:s')
                    ];
                    $this->flexibleworkflow_generic_rules_model->add($generic_rule_data);
                    set_alert('success', _l('flexiblewa_rule_added_successfully'));
                    return redirect(flexiblewa_admin_url($rule_type . '_automation'));
                    break;

                case FLEXIBLEWA_ADD_NEW_MILESTONE_ACTION:
                    $milestone_data = [
                        'name' => $post['name'],
                        'description' => $post['description'],
                        'start_date' => $post['start_time_count'] . '::' . $post['start_period'],
                        'due_date' => $post['due_time_count'] . '::' . $post['due_period'],
                        'description_visible_to_customer' => isset($post['description_visible_to_customer']) ? 1 : 0,
                        'hide_from_customer' => isset($post['hide_from_customer']) ? 1 : 0,
                    ];
                    $generic_rule_data = [
                        'rule_name' => $post['title'],
                        'when_event' => $when_event,
                        'rule_type' => $rule_type,
                        'rule_action' => $rule_action,
                        'rule_value' => flexiblewa_serialize_data($milestone_data),
                        'user_id' => get_staff_user_id(),
                        'date_created' => date('Y-m-d H:i:s')
                    ];
                    $this->flexibleworkflow_generic_rules_model->add($generic_rule_data);
                    set_alert('success', _l('flexiblewa_rule_added_successfully'));
                    return redirect(flexiblewa_admin_url($rule_type . '_automation'));
                    break;

                default:
                    set_alert('danger', _l('flexiblewa_rule_not_found'));
                    return redirect(flexiblewa_admin_url());
                    break;
            }
        }
        return redirect(flexiblewa_admin_url());
    }

    public function ajax()
    {
        $action = $this->input->get('action') ? $this->input->get('action') : $this->input->post('action');

        $result = [
            'success' => false,
            'data' => []
        ];
        switch ($action) {
            case 'get_list_of_actions_for_section':
                $section_id = $this->input->get('id');
                $action_type = $this->input->get('action_type') ?? 'task';
                if($action_type == 'task') {
                    $conditions = [
                        'section_id' => $section_id
                    ];
                    $this->load->model('flexiblewa/flexibleworkflow_model');
                    $actions = $this->flexibleworkflow_model->all($conditions);
                    $result['html'] = $this->load->view('partials/list-of-actions', ['actions' => $actions, 'action_type' => $action_type], true);
                }else{
                    $conditions = [
                        'when_event' => $section_id
                    ];
                    $this->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
                    $actions = $this->flexibleworkflow_generic_rules_model->all($conditions);
                    $result['html'] = $this->load->view('partials/list-of-actions', ['actions' => $actions, 'action_type' => $action_type], true);
                }
                $result['success'] = true;
                break;

            case 'relations':
                $rel_type = $this->input->get('rel_type');
                $result['data']['relations'] = flexiblewa_get_relations($rel_type);
                $result['success'] = true;
                break;
            case 'assign_to_staff':
            case 'add_new_follower':
                $result['data']['members'] = flexiblewa_get_staff_members();
                $result['success'] = true;
                break;

            case 'move_to_section':
                $status_id = $this->input->get('status_id');
                $result['data']['statuses'] = flexiblewa_get_task_statuses($status_id);
                $result['success'] = true;
                break;

            case 'update_actions_order':
                $this->load->model('flexiblewa/flexibleworkflow_model');
                $actions = $this->input->post("actions");
                $action_type = $this->input->post("action_type") ?? 'task';
                if($action_type == 'task') {
                    $result['success'] = $this->flexibleworkflow_tasks_model->update_actions_order($actions);
                } else {
                    $this->load->model('flexiblewa/flexibleworkflow_generic_rules_model');
                    $result['success'] = $this->flexibleworkflow_generic_rules_model->update_actions_order($actions);
                }
                break;

            case 'get_mentions':
                $members = flexiblewa_get_staff_members();
                $members = array_map(function ($member) {
                    $_member['id'] = $member['staffid'];
                    $_member['name'] = $member['firstname'] . ' ' . $member['lastname'];
                    return $_member;
                }, $members);
                $result = $members;
            case 'get_form_for_action':
                $id = $this->input->get('id');
                $action_type = $this->input->get('action_type') ?? 'task';
                $html = '';
                switch ($id) {
                    case FLEXIBLEWA_ADD_NEW_REMINDER_ACTION:
                        $html = $this->load->view('partials/forms/reminder', ['action_type' => $action_type], true);
                        break;
                    case FLEXIBLEWA_ADD_NEW_TASK_ACTION:
                        $html = $this->load->view('partials/forms/task', ['members' => flexiblewa_get_staff_members(), 'action_type' => $action_type], true);
                        break;
                    case FLEXIBLEWA_ADD_NEW_NOTE_ACTION:
                        $html = $this->load->view('partials/forms/note', ['action_type' => $action_type], true);
                        break;
                    case FLEXIBLEWA_SEND_EMAIL_ACTION:
                        $html = $this->load->view('partials/forms/email', ['members' => flexiblewa_get_staff_members(), 'action_type' => $action_type], true);
                        break;
                    case FLEXIBLEWA_ADD_NEW_MILESTONE_ACTION:
                        $html = $this->load->view('partials/forms/milestone', ['action_type' => $action_type], true);
                        break;
                    case FLEXIBLEWA_ADD_NEW_DISCUSSION_ACTION:
                        $html = $this->load->view('partials/forms/discussion', ['action_type' => $action_type], true);
                        break;
                }
                $result['data']['html'] = $html;
                $result['success'] = true;
                break;
        }
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
