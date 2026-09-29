<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexagile extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            access_denied('Flexible Agile');
        }
        $this->load->model('flexagile/Flexagilesprint_model');
        $this->load->model('flexagile/Flexagiletasks_model');
    }

    /* Database back up functions */
    public function index()
    {
        $data['title'] = flexagile_lang('sprint-planning');
        $this->load->model('projects_model');

        $data['projects'] = $this->projects_model->get();
        $data['sprints'] = $this->Flexagilesprint_model->all();
        //add project name to each sprint
        foreach($data['sprints'] as &$sprint){
            $sprint['project_name'] = get_project_name_by_id($sprint['project_id']);
            //count tasks connected to this sprint
            $sprint['tasks_count'] = count($this->Flexagiletasks_model->all(['sprint_id' => $sprint['id']]));
        }
        $this->load->view('index', $data);
    }

    public function create_edit_sprint()
    {
        if ($this->input->post()) {
            //check if dates are valid
            $start_date = to_sql_date($this->input->post('start_date'),true);
            $end_date = to_sql_date($this->input->post('end_date'),true);
            if($start_date > $end_date){
                set_alert('warning',flexagile_lang('start-date-must-be-before-end-date'));
                redirect(admin_url('flexagile'));
            }
            $fields['name'] = $this->input->post('name');
            $fields['start_date'] = $start_date;
            $fields['end_date'] = $end_date;
            $fields['description'] = $this->input->post('description');
            $id = $this->input->post('id'); //we are editing
            if($id){
                $this->Flexagilesprint_model->update($fields,$id);
                set_alert('success',flexagile_lang('sprint-updated-successfully'));
            }else{
                //check project id
                $fields['status'] = 0;
                $project_id = $this->input->post('project_id');
                if(!$project_id){
                    set_alert('warning',flexagile_lang('project-is-required'));
                    redirect(admin_url('flexagile'));
                }
                $fields['project_id'] = $project_id;
                $this->Flexagilesprint_model->add($fields);
                set_alert('success',flexagile_lang('sprint-added-successfully'));
            }
            redirect(admin_url('flexagile'));
        }
    }

    public function view($sprint_id){
        $this->load->model('tasks_model');
        $sprint = $this->Flexagilesprint_model->get(['id' => $sprint_id]);
        if(!$sprint){
            set_alert('warning',flexagile_lang('sprint-not-found'));
            redirect(admin_url('flexagile'));
        }
        $data['sprint'] = $sprint;
        $data['project_name'] = get_project_name_by_id($sprint['project_id']);
        $data['project_link'] = admin_url('projects/view/' . $sprint['project_id']);
        $sprint_tasks = $this->Flexagiletasks_model->all(['sprint_id' => $sprint_id]);
        $enriched_tasks = [];
        foreach($sprint_tasks as $sprint_task){
             $sprint_task['taskdata'] = (array)$this->tasks_model->get($sprint_task['task_id']);
             //get the task assignees
                $task_assignees = $this->tasks_model->get_task_assignees($sprint_task['task_id']);
                $task_assignees_names = array();
                $task_assigneesIds = array();
                foreach ($task_assignees as $assignee) {
                    $task_assigneesIds[] = $assignee['assigneeid'];
                    $task_assignees_names[] = $assignee['firstname'] . ' ' . $assignee['lastname'];
                }
                $sprint_task['taskdata']['assignees'] = implode(', ', $task_assignees_names);
                $sprint_task['taskdata']['assignees_ids'] = implode(', ',$task_assigneesIds);
                $enriched_tasks[] = $sprint_task;
        }
        $data['tasks'] = $enriched_tasks;
        //get all active and planned sprints for this project
        $data['sprints'] = $this->Flexagilesprint_model->all(['project_id' => $sprint['project_id'],'status !=' => FLEXAGILE_STATUS_COMPLETE]);
        $data['title'] = flexagile_lang('sprint-management');
        return $this->load->view('view', $data);
    }

    public function start($sprint_id){
        $sprint = $this->Flexagilesprint_model->get(['id' => $sprint_id]);
        if(!$sprint){
            set_alert('warning',flexagile_lang('sprint-not-found'));
            redirect(admin_url('flexagile'));
        }
        $this->Flexagilesprint_model->update(['status' => FLEXAGILE_STATUS_ONGOING], $sprint_id);
        set_alert('success',flexagile_lang('sprint-started-successfully'));
        redirect(admin_url('flexagile'));
    }

    public function complete($sprint_id){
        $sprint = $this->Flexagilesprint_model->get(['id' => $sprint_id]);
        if(!$sprint){
            set_alert('warning',flexagile_lang('sprint-not-found'));
            redirect(admin_url('flexagile'));
        }
        //check if there are any incomplete tasks
        $tasks = $this->Flexagiletasks_model->all(['sprint_id' => $sprint_id]);
        foreach($tasks as $task){
            if($task['status'] != $this->tasks_model::STATUS_COMPLETE){
                set_alert('warning',flexagile_lang('sprint-has-incomplete-tasks'));
                redirect(admin_url('flexagile/view/' . $sprint_id));
            }
        }
        $this->Flexagilesprint_model->update(['status' => FLEXAGILE_STATUS_COMPLETE],$sprint_id);
        set_alert('success',flexagile_lang('sprint-completed-successfully'));
        redirect(admin_url('flexagile/view/' . $sprint_id));
    }

    public function delete($sprint_id){
        $sprint = $this->Flexagilesprint_model->get(['id' => $sprint_id]);
        if(!$sprint){
            set_alert('warning',flexagile_lang('sprint-not-found'));
            redirect(admin_url('flexagile'));
        }
        //delete tasks connected to this sprint
        $this->Flexagiletasks_model->delete($sprint_id,false,true); //delete all tasks connected to this sprint
        $this->Flexagilesprint_model->delete($sprint_id);
        set_alert('success',flexagile_lang('sprint-deleted-successfully'));
        redirect(admin_url('flexagile'));
    }

    public function settings(){
        $data['title'] = flexagile_lang('settings');
        if($this->input->post()){
            $_post = $this->input->post();
            update_option('flexagile_auto_add_new_task_to_active_sprint', $_post['settings']['flexagile_auto_add_new_task_to_active_sprint'] ?? 0);
            update_option('flexagile_add_billable_tasks_only_to_sprint_invoice', $_post['settings']['flexagile_add_billable_tasks_only_to_sprint_invoice'] ?? 0);
            set_alert('success',flexagile_lang('settings-updated-successfully'));
            redirect(admin_url('flexagile/settings'));
        }
        $this->load->view('settings', $data);
    }

    public function create_invoice($sprint_id){
        //get sprint
        $sprint = $this->Flexagilesprint_model->get(['id' => $sprint_id]);
        if(!$sprint){
            set_alert('warning',flexagile_lang('sprint-not-found'));
            redirect(admin_url('flexagile'));
        }
        //get tasks
        $sprint_tasks = $this->Flexagiletasks_model->all(['sprint_id' => $sprint_id]);
        if(!$sprint_tasks){
            set_alert('warning',flexagile_lang('sprint-has-no-tasks'));
            redirect(admin_url('flexagile/view/' . $sprint_id));
        }

        //sprint must be complete
        if($sprint['status'] != FLEXAGILE_STATUS_COMPLETE){
            set_alert('warning',flexagile_lang('sprint-must-be-complete'));
            redirect(admin_url('flexagile/view/' . $sprint_id));
        }
        $project = $this->projects_model->get($sprint['project_id']);

        $this->load->model('payment_modes_model');
        //loop throuh tasks and add them to invoice
        $invoice_items = [];
        $total_amount = 0;
        $order = 1;
        foreach ($sprint_tasks as $sprint_task) {
            $task = $this->tasks_model->get_billable_task_data($sprint_task['task_id']);
            if(!$task){
                continue;
            }
            if(get_option('flexagile_add_billable_tasks_only_to_sprint_invoice') && !$task['billable']){
                continue;
            }
            $invoice_items[] = [
                'description' => $task->name,
                'long_description' => $task->description,
                'qty' => $task->total_hours,
                'rate' => $task->hourly_rate,
                'order' => $order,
                'unit' => ''
            ];
            $total_amount += $task->total_hours * $task->hourly_rate;
            $order++;
        }
        if(!$invoice_items){
            set_alert('warning',flexagile_lang('sprint-has-no-billable-tasks'));
            redirect(admin_url('flexagile/view/' . $sprint_id));
        }
        $this->load->model('payment_modes_model');
        $this->load->model('clients_model');
        $this->load->model('invoices_model');
        $customer_reference_id = $project->clientid;
        $due_after = get_option('invoice_due_after') > 0 ? get_option('invoice_due_after') : 30;
        $currency = flexagile_get_base_currency();
        $payment_modes = $this->payment_modes_model->get();
        $client_note = flexagile_lang('sprint-invoice-for-project') . ' ' . $project->name . ' ' . flexagile_lang('sprint') . ' ' . $sprint['name'];
        $invoice_data = [
            'allowed_payment_modes' => array_pluck($payment_modes, 'id'),
            'currency' => $currency->id,
            'clientid' => $customer_reference_id,
            'number' => get_option('next_invoice_number'),
            'number_format' => get_option('invoice_number_format'),
            'newitems' => $invoice_items,
            'subtotal' => $total_amount,
            'total' => $total_amount,
            'duedate' => date('Y-m-d', strtotime('+' . $due_after . ' DAY')),
            'billing_street' => '',
            'clientnote' => $client_note,
            'status' => 6,
            'project_id' => $project->id,
        ];
        $invoice_id = $this->invoices_model->add($invoice_data);
        //connect invoice to project
        $this->load->model('projects_model');

        if(!$invoice_id){
            set_alert('warning',flexagile_lang('sprint-invoice-could-not-be-generated'));
            redirect(admin_url('flexagile/view/' . $sprint_id));
        }
        set_alert('success',flexagile_lang('sprint-invoice-generated-successfully'));
        redirect(admin_url('invoices/invoice/' . $invoice_id));
    }

    public function ajax(){
        $action = $this->input->get('action') ? $this->input->get('action') : $this->input->post('action');
        $result = [
            'success' => false,
            'data' => []
        ];
        if ($action == 'update_sprint_data') {
            $sprint_id = $this->input->post('sprint_id');
            $task_id = $this->input->post('task_id');
            $task_status = $this->input->post('task_status');
            //delete the last sprint data for this task if there is any to
            //prevent a task from being in multiple sprints
            $this->Flexagiletasks_model->delete($task_id, true);
            $this->Flexagiletasks_model->add([
                'sprint_id' => $sprint_id,
                'task_id' => $task_id,
                'status' => $task_status,
                'date_added' => date('Y-m-d H:i:s'),
                'date_updated' => date('Y-m-d H:i:s')
            ]);
            $result['success'] = true;
        }
        echo json_encode($result);
    }
}

