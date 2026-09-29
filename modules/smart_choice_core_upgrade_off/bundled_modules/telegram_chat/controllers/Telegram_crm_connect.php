<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Telegram_crm_connect extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('telegram_smartchoice');
    }

    public function index() { redirect(admin_url('telegram_crm_connect/settings')); }

    public function settings()
    {
        if ($this->input->post()) {
            $fields = ['telegram_bot_token','telegram_owner_chat_id','telegram_crm_alerts_group_id','telegram_project_alerts_group_id','telegram_payments_group_id','telegram_support_group_id','telegram_default_chat_id'];
            $toggles = ['telegram_notify_new_leads','telegram_notify_lead_assignment','telegram_notify_lead_status_changes','telegram_notify_new_customers','telegram_notify_new_projects','telegram_notify_project_status_changes','telegram_notify_task_assignment','telegram_notify_completed_tasks','telegram_notify_overdue_tasks','telegram_notify_estimates_created','telegram_notify_estimates_accepted','telegram_notify_invoices_created','telegram_notify_invoices_paid','telegram_notify_payments_received','telegram_notify_support_tickets','telegram_notify_ticket_replies','telegram_notify_proposals_accepted','telegram_notify_contracts_signed','telegram_notify_permits','telegram_notify_inspections','telegram_notify_document_uploads'];
            foreach (array_merge($fields,$toggles) as $field) { update_option($field, $this->input->post($field, true) ?: ''); }
            $templates = $this->input->post('templates');
            if (is_array($templates)) { foreach ($templates as $key => $value) { update_option('telegram_template_'.$key, $value); } }
            set_alert('success','Telegram settings saved.');
            redirect(admin_url('telegram_crm_connect/settings'));
        }
        $data['title'] = 'Telegram Settings';
        $this->load->view('telegram_smartchoice/settings', $data);
    }

    public function test_chat()
    {
        echo json_encode(['success'=>true,'response'=>telegram_sc_send_message($this->input->post('chat_id', true), '✅ Test message from Smart Choice Contractors USA Perfex CRM.')]);
    }

    public function recent_chats()
    {
        $updates = telegram_sc_get_updates();
        $chats = [];
        if (!empty($updates['result'])) {
            foreach ($updates['result'] as $update) {
                $message = $update['message'] ?? $update['channel_post'] ?? null;
                if (!$message || empty($message['chat']['id'])) { continue; }
                $chat = $message['chat'];
                $chats[(string)$chat['id']] = ['id'=>$chat['id'],'type'=>$chat['type'] ?? '','title'=>$chat['title'] ?? trim(($chat['first_name'] ?? '').' '.($chat['last_name'] ?? '')),'username'=>$chat['username'] ?? ''];
            }
        }
        echo json_encode(['success'=>true,'chats'=>array_values($chats)]);
    }

    public function webhook()
    {
        $update = json_decode(file_get_contents('php://input'), true);
        $message = $update['message'] ?? null;
        if (!$message || empty($message['text'])) { echo 'OK'; return; }
        $fromId = $message['from']['id'] ?? '';
        $chatId = $message['chat']['id'] ?? '';
        $parts = explode(' ', trim($message['text']));
        $command = strtolower(ltrim($parts[0], '/'));
        if (!telegram_sc_allowed_user($fromId, $command)) {
            telegram_sc_send_message($chatId, 'Access denied. Your Telegram User ID is not approved for this CRM command.');
            echo 'OK'; return;
        }
        telegram_sc_send_message($chatId, $this->handle_command($command, array_slice($parts,1)));
        echo 'OK';
    }

    private function handle_command($command, $args)
    {
        switch ($command) {
            case 'openleads': return 'Open leads: '.$this->db->count_all(db_prefix().'leads');
            case 'openprojects': return 'Open projects: '.$this->db->count_all(db_prefix().'projects');
            case 'overduetasks':
                return 'Overdue tasks: '.$this->db->where('duedate <', date('Y-m-d'))->where('status !=',5)->count_all_results(db_prefix().'tasks');
            case 'openinvoices':
                return 'Open invoices: '.$this->db->where_not_in('status',[2,5])->count_all_results(db_prefix().'invoices');
            case 'setprojectgroup':
                if (count($args) < 2) { return 'Usage: /setprojectgroup PROJECT_ID TELEGRAM_GROUP_ID'; }
                $this->save_project_group((int)$args[0], $args[1]);
                return 'Project Telegram group saved.';
            default:
                return 'Command /'.$command.' received. CRM command workflow is active.';
        }
    }

    private function save_project_group($projectId, $groupId)
    {
        if (!$this->db->table_exists(db_prefix().'telegram_project_groups')) { return false; }
        $row = $this->db->where('project_id',$projectId)->get(db_prefix().'telegram_project_groups')->row();
        $data = ['project_id'=>$projectId,'telegram_group_id'=>$groupId,'updated_at'=>date('Y-m-d H:i:s')];
        if ($row) { $this->db->where('id',$row->id)->update(db_prefix().'telegram_project_groups',$data); }
        else { $data['created_at'] = date('Y-m-d H:i:s'); $this->db->insert(db_prefix().'telegram_project_groups',$data); }
        return true;
    }

    public function health()
    {
        echo '<h3>Telegram CRM Connect Health Check</h3><p>Module loaded.</p><p>Bot Token: '.(get_option('telegram_bot_token') ? 'Configured' : 'Missing').'</p>';
    }
}
