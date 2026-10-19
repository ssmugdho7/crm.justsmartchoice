<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smartchoice extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!staff_can('view', 'appointments')) {
            access_denied('appointments');
        }
        $this->load->helper('appointly/appointly');
    }

    public function dispatch()
    {
        $data['title'] = _l('appointly_dispatch_board');
        $data['appointments'] = $this->get_today_and_upcoming();
        $this->load->view('appointly/smart_choice/dispatch', $data);
    }

    public function health()
    {
        $data['title'] = _l('appointly_health_check');
        $checks = [];
        $checks[] = $this->check('PHP Version', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION);
        $checks[] = $this->check('Appointments Table', $this->db->table_exists(db_prefix() . 'appointly_appointments'), db_prefix() . 'appointly_appointments');
        $checks[] = $this->check('Services Table', $this->db->table_exists(db_prefix() . 'appointly_services'), db_prefix() . 'appointly_services');
        $checks[] = $this->check('Notification Log Table', $this->db->table_exists(db_prefix() . 'appointly_smartchoice_notifications'), db_prefix() . 'appointly_smartchoice_notifications');
        $checks[] = $this->check('Daily Popup', get_option('appointly_sc_daily_popup_enabled') === '1', get_option('appointly_sc_daily_popup_enabled'));
        $checks[] = $this->check('One Hour Reminder', get_option('appointly_sc_one_hour_reminder_enabled') === '1', get_option('appointly_sc_one_hour_reminder_enabled'));
        $telegramEnabled = get_option('appointly_sc_telegram_enabled') === '1';
        $telegramReady = $telegramEnabled && trim((string) get_option('appointly_sc_telegram_bot_token')) !== '' && trim((string) get_option('appointly_sc_telegram_default_chat_id')) !== '';
        $checks[] = $this->check('Telegram Integration', $telegramReady, $telegramReady ? 'Enabled; token and chat ID configured' : ($telegramEnabled ? 'Enabled but token or chat ID is missing' : 'Not configured (optional)'), $telegramEnabled ? null : 'optional');
        $facebookEnabled = get_option('appointly_sc_facebook_enabled') === '1';
        $facebookReady = $facebookEnabled && trim((string) get_option('appointly_sc_facebook_page_token')) !== '';
        $checks[] = $this->check('Facebook / Meta Integration', $facebookReady, $facebookReady ? 'Enabled; Page token configured' : ($facebookEnabled ? 'Enabled but Page token is missing' : 'Not configured (optional)'), $facebookEnabled ? null : 'optional');
        $checks[] = $this->check('Installer Tracking Bridge', $this->db->table_exists(db_prefix() . 'smart_installer_tracking') || $this->db->table_exists(db_prefix() . 'appointly_installer_tracking_bridge'), 'Optional integration');
        $data['checks'] = $checks;
        $this->load->view('appointly/smart_choice/health', $data);
    }

    public function help()
    {
        $data['title'] = _l('appointly_help_guide');
        $this->load->view('appointly/smart_choice/help', $data);
    }


    /**
     * Returns active appointment alerts for the logged-in staff member.
     * The popup uses this endpoint to disappear automatically after an appointment
     * is completed, cancelled, marked no-show, or otherwise moved out of an active status.
     */
    public function active_today_appointments()
    {
        if (!staff_can('view', 'appointments')) {
            ajax_access_denied();
        }

        $appointments = $this->get_active_today_appointments(get_staff_user_id());
        $items = [];

        foreach ($appointments as $appointment) {
            $items[] = [
                'id'      => (int) $appointment['id'],
                'subject' => html_escape($appointment['subject'] ?: 'Appointment'),
                'time'    => html_escape(trim(($appointment['start_hour'] ?? '') . ' - ' . ($appointment['end_hour'] ?? ''))),
                'status'  => html_escape($appointment['status'] ?? ''),
                'url'     => admin_url('appointly/appointments/view?appointment_id=' . (int) $appointment['id']),
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'items'   => $items,
                'count'   => count($items),
            ]));
    }

    private function get_active_today_appointments($staff_id = null)
    {
        if (!$this->db->table_exists(db_prefix() . 'appointly_appointments')) {
            return [];
        }

        $this->db->select('a.id, a.subject, a.date, a.start_hour, a.end_hour, a.status, a.provider_id');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        if ($this->db->table_exists(db_prefix().'appointly_installer_tracking_bridge')) {
            $this->db->select('dispatch.tracking_status AS dispatch_status');
            $this->db->join(db_prefix().'appointly_installer_tracking_bridge dispatch', 'dispatch.appointment_id = a.id', 'left');
        }
        $this->db->where('date', date('Y-m-d'));
        $this->db->where_in('status', ['pending', 'in-progress']);
        if ($staff_id) {
            $this->db->group_start();
            $this->db->where('provider_id', $staff_id);
            if ($this->db->field_exists('staff_id', db_prefix() . 'appointly_appointments')) {
                $this->db->or_where('staff_id', $staff_id);
            }
            $this->db->group_end();
        }
        $this->db->order_by('a.start_hour', 'ASC');
        $this->db->limit(50);

        return $this->db->get()->result_array();
    }

    public function test_notification()
    {
        if (!staff_can('edit', 'appointments')) { access_denied('appointments'); }
        $recipient=(int)($this->input->post('recipient_staff_id') ?: get_staff_user_id());
        $channel=(string)($this->input->post('channel') ?: 'crm');
        $message=trim((string)($this->input->post('message') ?: _l('appointly_test_notification_message')));
        $staff=$this->db->where('staffid',$recipient)->where('active',1)->get(db_prefix().'staff')->row();
        if(!$staff){set_alert('danger','Selected employee was not found.');redirect(admin_url('appointly/smartchoice/dispatch'));}
        $ok=false;$where=trim($staff->firstname.' '.$staff->lastname);
        if($channel==='crm'){
            add_notification(['description'=>$message,'touserid'=>$recipient,'fromcompany'=>true,'link'=>'appointly/smartchoice/dispatch']);
            if(function_exists('pusher_trigger_notification')){pusher_trigger_notification([$recipient]);}
            $ok=true;$where.=' in the CRM notification center';
        }elseif($channel==='email'&&!empty($staff->email)){
            $template = mail_template('appointly_dispatch_test_notification', 'appointly', $staff, $message);
            $ok = (bool) $template->send();
            $where.=' at '.$staff->email;
        }elseif($channel==='telegram'){$ok=$this->send_telegram_test($message);$where='Telegram chat '.get_option('appointly_sc_telegram_default_chat_id');}
        set_alert($ok?'success':'warning',($ok?'Notification sent by ':'Notification could not be sent by ').strtoupper($channel).' to '.$where.'.');
        redirect(admin_url('appointly/smartchoice/dispatch'));
    }
    private function send_telegram_test($message)
    {
        $token=trim((string)get_option('appointly_sc_telegram_bot_token'));$chat=trim((string)get_option('appointly_sc_telegram_default_chat_id'));
        if(get_option('appointly_sc_telegram_enabled')!=='1'||$token===''||$chat===''){return false;}
        $ch=curl_init('https://api.telegram.org/bot'.$token.'/sendMessage');
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['chat_id'=>$chat,'text'=>$message]),CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]);
        $res=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$json=json_decode((string)$res,true);
        return $code===200&&is_array($json)&&!empty($json['ok']);
    }


    public function update_dispatch_status()
    {
        if (!staff_can('edit', 'appointments')) { access_denied('appointments'); }
        if (strtoupper($this->input->method()) !== 'POST') { show_404(); return; }
        $id = (int) $this->input->post('appointment_id');
        $status = trim((string) $this->input->post('status'));
        $eta = filter_var($this->input->post('eta_minutes'), FILTER_VALIDATE_INT);
        $allowed = ['confirmed','on-the-way','15-minutes-away','arrived','in-progress','completed','follow-up','cancelled','no-show'];
        $appointment = $this->db->where('id', $id)->get(db_prefix().'appointly_appointments')->row();
        if (!$appointment || !in_array($status, $allowed, true)
            || ($status === 'on-the-way' && ($eta === false || $eta < 1 || $eta > 240))
            || (in_array($status, ['on-the-way','15-minutes-away'], true) && empty($appointment->provider_id))) {
            set_alert('danger', 'Select a valid appointment, assigned installer and ETA (1–240 minutes).');
            redirect(admin_url('appointly/smartchoice/dispatch'));
            return;
        }
        $bridge = db_prefix().'appointly_installer_tracking_bridge';
        if (!$this->db->table_exists($bridge)) {
            set_alert('danger', 'Please upgrade the Appointment Manager module before using dispatch.');
            redirect(admin_url('appointly/smartchoice/dispatch'));
            return;
        }
        // Dispatch progress must not replace the lifecycle statuses used by reminders.
        $lifecycle = ['confirmed'=>'approved', 'arrived'=>'in-progress', 'in-progress'=>'in-progress',
            'completed'=>'completed', 'cancelled'=>'cancelled', 'no-show'=>'no-show'];
        $this->db->trans_start();
        if (isset($lifecycle[$status])) {
            $this->db->where('id', $id)->update(db_prefix().'appointly_appointments', ['status'=>$lifecycle[$status]]);
        }
        $now = date('Y-m-d H:i:s');
        $existing = $this->db->where('appointment_id', $id)->get($bridge)->row();
        $state = ['staff_id'=>$appointment->provider_id ?: null, 'tracking_status'=>$status, 'updated_at'=>$now];
        if ($existing) { $this->db->where('appointment_id', $id)->update($bridge, $state); }
        else { $this->db->insert($bridge, array_merge($state, ['appointment_id'=>$id, 'created_at'=>$now])); }
        $this->db->trans_complete();
        if (!$this->db->trans_status()) {
            set_alert('danger', 'Dispatch status could not be saved. No email was sent.');
        } else {
            $this->load->model('appointly/appointly_dispatch_model', 'dispatch_notifications');
            try { $result = $this->dispatch_notifications->notify($appointment, $status, $eta === false ? null : $eta); }
            catch (Throwable $e) { $result = 'failed'; log_message('error', 'Dispatch notification failed for appointment #' . $id . '.'); }
            $messages = ['accepted'=>'Email accepted by the mail server.', 'duplicate'=>'This email was already sent; no duplicate was sent.',
                'disabled'=>'Customer dispatch emails are disabled in settings.', 'no-recipient'=>'No valid customer email address is configured.',
                'failed'=>'Email failed. Check Email Settings and the dispatch template, then retry.',
                'processing'=>'A previous send is unresolved. Check the mail log before retrying.',
                'busy'=>'Another notification is being processed. Try again shortly.',
                'unavailable'=>'Notification logging is unavailable. Upgrade the module before retrying.'];
            set_alert(in_array($result, ['accepted','duplicate'], true) ? 'success' : 'warning',
                'Dispatch status saved. '.($messages[$result] ?? 'Customer email could not be sent.'));
        }
        redirect(admin_url('appointly/smartchoice/dispatch'));
    }

    public function notification_log()
    {
        if (!staff_can('view', 'appointments')) { access_denied('appointments'); }
        $table = db_prefix().'appointly_smartchoice_notifications';
        $data['title'] = 'Appointment Notification Log';
        $data['logs'] = $this->db->table_exists($table)
            ? $this->db->order_by('id','DESC')->limit(500)->get($table)->result_array()
            : [];
        $this->load->view('appointly/smart_choice/notification_log', $data);
    }

    private function get_today_and_upcoming()
    {
        if (!$this->db->table_exists(db_prefix() . 'appointly_appointments')) {
            return [];
        }
        $this->db->select('a.id, a.subject, a.date, a.start_hour, a.end_hour, a.status, a.provider_id');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        if ($this->db->table_exists(db_prefix().'appointly_installer_tracking_bridge')) {
            $this->db->select('dispatch.tracking_status AS dispatch_status');
            $this->db->join(db_prefix().'appointly_installer_tracking_bridge dispatch', 'dispatch.appointment_id = a.id', 'left');
        }
        $this->db->where('a.date >=', date('Y-m-d'));
        $this->db->order_by('a.date', 'ASC');
        $this->db->order_by('a.start_hour', 'ASC');
        $this->db->limit(100);
        return $this->db->get()->result_array();
    }

    private function check($name, $passed, $details, $status = null)
    {
        return [
            'name' => $name,
            'passed' => (bool) $passed,
            'status' => $status ?: ($passed ? 'pass' : 'fail'),
            'details' => (string) $details,
        ];
    }
}
