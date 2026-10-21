<?php defined('BASEPATH') or exit('No direct script access allowed');

class Prchat_ClientsController extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->app_modules->is_active('prchat') || get_option('pusher_chat_enabled') != '1') {
            $this->output->set_status_header(503)->set_content_type('application/json')->set_output(json_encode(['error' => _l('chat_not_enabled')]))->_display();
            exit;
        }
        $this->load->model('prchat/prchat_model', 'chat_model');
        if (get_option('chat_client_enabled') != '1' || (!is_staff_logged_in() && !is_client_logged_in())) {
            $this->respond(['success' => false, 'error' => _l('chat_forbidden')], 403);
            $this->output->_display();
            exit;
        }
        // Reads and persistence remain available while realtime is unavailable.
        try { $this->load->library('App_pusher'); }
        catch (Throwable $e) { log_message('error', 'PRChat client realtime unavailable'); }
    }

    /** Portal preview shares a cookie with admin; the authenticated contact still owns portal actions. */
    private function isStaffActor(): bool
    {
        $preview = is_client_logged_in()
            && $this->input->get('chat_context') === 'client'
            && get_instance()->session->userdata('logged_in_as_client') === true;
        return is_staff_logged_in() && !$preview;
    }

    private function respond(array $data, int $status = 200): void
    {
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
    }

    /** Resolve identities from the session and authorize the peer before accessing any data. */
    private function conversation($first, $second): ?array
    {
        if (!is_string($first) || !is_string($second)) { return null; }
        $staff = null; $contact = null;
        foreach ([$first, $second] as $identity) {
            if (preg_match('/^staff_([1-9][0-9]*)$/D', $identity, $m)) { $staff = (int) $m[1]; }
            elseif (preg_match('/^client_([1-9][0-9]*)$/D', $identity, $m)) { $contact = (int) $m[1]; }
            else { return null; }
        }
        if (!$staff || !$contact) { return null; }
        if ($this->isStaffActor()) {
            if ($staff !== (int) get_staff_user_id() || !prchat_staff_can_contact($contact)) { return null; }
            return ['staff' => $staff, 'contact' => $contact, 'own' => 'staff_'.$staff, 'peer' => 'client_'.$contact];
        }
        if (!is_client_logged_in() || $contact !== (int) get_contact_user_id() || !prchat_staff_available_for_chat($staff)) { return null; }
        $visible = array_map('intval', array_column(get_customer_admins(), 'staffid'));
        if (!in_array($staff, $visible, true)) { return null; }
        return ['staff' => $staff, 'contact' => $contact, 'own' => 'client_'.$contact, 'peer' => 'staff_'.$staff];
    }

    private function deliver(string $event, array $payload): void
    {
        prchat_trigger_event($this->app_pusher ?? null, 'presence-clients', $event, $payload);
    }

    public function getMutualMessages()
    {
        $pair = $this->conversation($this->input->get('sender_id'), $this->input->get('reciever_id'));
        if (!$pair) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        $limit = max(1, min(100, (int) ($this->input->get('limit') ?: 10)));
        $offset = max(0, (int) $this->input->get('offset'));
        $this->respond($this->chat_model->getMutualMessages($pair['own'], $pair['peer'], $limit, $offset) ?: []);
    }

    public function initClientChat()
    {
        $pair = $this->conversation($this->input->post('from'), $this->input->post('to'));
        if (!$pair || $this->input->post('from') !== $pair['own']) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        $raw = $this->input->post('client_message', false);
        if ($this->input->post('typing') !== 'false') {
            $this->deliver('typing-event', ['from' => $pair['own'], 'to' => $pair['peer'], 'message' => $this->input->post('typing') === 'true' ? 'true' : 'null']);
            $this->respond(['success' => true]);
            return;
        }
        if (!is_string($raw) || trim($raw) === '') { $this->respond(['error' => _l('chat_invalid_message')], 422); return; }
        $stored = $this->chat_model->process_message_for_storage($raw);
        $sentAt = date('Y-m-d H:i:s');
        try { $id = $this->chat_model->createMessage(['sender_id' => $pair['own'], 'reciever_id' => $pair['peer'],
            'message' => htmlspecialchars($stored, ENT_QUOTES, 'UTF-8'), 'viewed' => 0, 'time_sent' => $sentAt], db_prefix().'chatclientmessages'); }
        catch (Throwable $e) { log_message('error', 'PRChat client message database save failed'); $id = false; }
        if (!$id) { $this->respond(['error' => _l('chat_save_failed')], 500); return; }
        $display = $this->chat_model->process_message_for_display($stored);
        $payload = ['from' => $pair['own'], 'to' => $pair['peer'], 'message' => clickable(pr_chat_convertLinkImageToString(htmlspecialchars($display, ENT_QUOTES, 'UTF-8'))),
            'last_insert_id' => $id, 'time_sent' => date(DATE_ATOM),
            'client_image_path' => contact_profile_image_url($pair['contact']),
            'from_name' => $this->isStaffActor() ? get_staff_full_name($pair['staff']) : get_contact_full_name($pair['contact']),
            'sender_image' => $this->isStaffActor() ? $this->chat_model->getUserImage($pair['staff']) : null,
            'is_call' => (bool) preg_match('/^\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]$/', $raw)];
        if ($this->isStaffActor()) {
            $staff = $this->db->select('staffid,firstname,lastname,profile_image,admin,role')->where('staffid', $pair['staff'])->get(db_prefix().'staff')->row_array();
            if ($staff) {
                $staff['role'] = !empty($staff['admin']) ? _l('chat_role_administrator') : (get_staff_userrole($staff['role']) ?: _l('chat_role_staff'));
                unset($staff['admin']);
                $payload['from_staff_data'] = $staff;
            }
        } else {
            $contact = getOwnClient($pair['contact']);
            $payload['contact_full_name'] = $payload['from_name'];
            $payload['client_id'] = $contact['client_id'] ?? null;
            $payload['company'] = $contact['company'] ?? '';
        }
        $this->respond(['success' => true, 'id' => $id, 'time_sent' => $payload['time_sent']]);
        $this->deliver('send-event', $payload);
        $this->deliver('notify-event', $payload);
    }

    public function updateClientUnreadMessages()
    {
        $peer = $this->input->post('id');
        if ($this->isStaffActor() && is_scalar($peer)) { $peer = 'client_'.str_replace('client_', '', (string) $peer); }
        if (!$this->isStaffActor() && is_scalar($peer) && ctype_digit((string) $peer)) { $peer = 'staff_'.$peer; }
        $own = $this->isStaffActor() ? 'staff_'.get_staff_user_id() : 'client_'.get_contact_user_id();
        $pair = $this->conversation($own, $peer);
        if (!$pair) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        $ok = $this->chat_model->updateClientUnreadMessages($this->isStaffActor() ? $pair['contact'] : 'staff_'.$pair['staff'],
            $this->app_pusher ?? null, $this->isStaffActor() ? null : true, $pair['contact']);
        $this->respond(['success' => (bool) $ok]);
    }

    public function getStaffUnreadMessages()
    {
        if (!is_client_logged_in() || $this->isStaffActor()) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        $this->respond($this->chat_model->getStaffUnreadMessages(null) ?: []);
    }

    public function getClientUnreadMessages()
    {
        if (!$this->isStaffActor() || !prchat_staff_can_chat() || !staffCanAccessClientsTab()) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        $this->respond($this->chat_model->getClientUnreadMessages() ?: ['result' => false]);
    }

    public function loadMoreClients()
    {
        if (!$this->isStaffActor() || !prchat_staff_can_chat() || !staffCanAccessClientsTab()) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        $this->respond(['customers' => get_staff_customers(30, max(0, (int) $this->input->get('offset')), true)]);
    }

    public function searchClients()
    {
        if (!$this->isStaffActor() || !prchat_staff_can_chat() || !staffCanAccessClientsTab()) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        $search = $this->input->post('search');
        if (!is_string($search)) { $this->respond([]); return; }
        $rows = $this->chat_model->searchClients(mb_substr(trim($search), 0, 200));
        $this->respond(array_values(array_filter($rows, static function($row) { return prchat_staff_can_contact($row['contact_id']); })));
    }

    public function uploadMethod()
    {
        $own = $this->isStaffActor() ? 'staff_'.get_staff_user_id() : 'client_'.get_contact_user_id();
        $peer = $this->input->post('send_to');
        if ($this->isStaffActor() && is_scalar($peer)) { $peer = 'client_'.str_replace('client_', '', (string) $peer); }
        if (!$this->isStaffActor() && is_scalar($peer) && ctype_digit((string) $peer)) { $peer = 'staff_'.$peer; }
        if (!$this->conversation($own, $peer)) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        $voice = (string) $this->input->post('prchat_voice_upload') === '1';
        if ($voice) { pr_chat_patch_upload_mimes_for_voice(); }
        else { pr_chat_patch_upload_mimes_for_crm_files(); }
        $path = $voice ? PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER.'/clients' : FCPATH.'modules/prchat/uploads/clients';
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) { $this->respond(['error' => _l('chat_storage_unavailable')], 500); return; }
        $this->load->library('upload', ['upload_path' => $path,
            'allowed_types' => $voice ? pr_chat_voice_upload_extensions_string() : pr_chat_client_crm_attachment_types_string(),
            'max_size' => $voice ? 10240 : pr_chat_max_upload_size_kb(), 'file_ext_tolower' => true, 'remove_spaces' => true]);
        if (!$this->upload->do_upload('userfile')) { $this->respond(['error' => strip_tags($this->upload->display_errors())], 422); return; }
        $data = $this->upload->data();
        if ($voice) { $data['subfolder'] = 'clients'; }
        unset($data['full_path'], $data['file_path']);
        $this->respond(['upload_data' => $data]);
    }

    private function authorizedMessage(int $id): ?object
    {
        $row = $this->db->where('id', $id)->get(db_prefix().'chatclientmessages')->row();
        return $row && $this->conversation($row->sender_id, $row->reciever_id) ? $row : null;
    }

    public function addReaction()
    {
        $id = (int) $this->input->post('message_id');
        $emoji = $this->input->post('emoji');
        $row = $this->authorizedMessage($id);
        if (!$row) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        if (!in_array($emoji, ['👍','❤️','😂','😮','😢','😡','🎉','🔥'], true)) { $this->respond(['error' => _l('chat_invalid_reaction')], 422); return; }
        $key = $this->isStaffActor() ? 'staff_'.get_staff_user_id() : 'client_'.get_contact_user_id();
        try { $reactions = $this->chat_model->toggleMessageReaction($id, $emoji, $key, 'client'); }
        catch (Throwable $e) {
            log_message('error', 'PRChat client reaction database save failed');
            $this->respond(['error' => _l('chat_reaction_save_failed')], 500); return;
        }
        $payload = ['message_id' => $id, 'message_type' => 'client', 'reactions' => $reactions, 'from' => $row->sender_id, 'to' => $row->reciever_id];
        $this->deliver('message-reaction', $payload);
        $this->respond(['success' => true, 'reactions' => $reactions]);
    }

    public function editClientMessage()
    {
        $id = (int) ($this->input->post('message_id') ?: $this->input->post('id'));
        $row = $this->authorizedMessage($id);
        $key = $this->isStaffActor() ? 'staff_'.get_staff_user_id() : 'client_'.get_contact_user_id();
        if (!$row || $row->sender_id !== $key || ($this->isStaffActor() && !staff_can('edit', 'prchat'))) { $this->respond(['error' => _l('chat_forbidden')], 403); return; }
        $raw = $this->input->post('message', false) ?? $this->input->post('new_message', false);
        if (!is_string($raw) || trim($raw) === '') { $this->respond(['error' => _l('chat_invalid_message')], 422); return; }
        $stored = $this->chat_model->process_message_for_storage($raw);
        $result = $this->chat_model->editMessage($id, htmlspecialchars($stored, ENT_QUOTES, 'UTF-8'), db_prefix().'chatclientmessages', 'sender_id', $key);
        $rendered = clickable(pr_chat_convertLinkImageToString(htmlspecialchars($this->chat_model->process_message_for_display($stored), ENT_QUOTES, 'UTF-8')));
        if ($result['success']) { $this->deliver('message-edited', ['message_id' => $id, 'rendered_message' => $rendered, 'from' => $row->sender_id, 'to' => $row->reciever_id]); }
        $this->respond($result + ['rendered_message' => $rendered]);
    }

    public function pusherCustomersAuth()
    {
        $channel_name = $this->input->post('channel_name') ?: $this->input->get('channel_name');
        $socket_id = $this->input->post('socket_id') ?: $this->input->get('socket_id');
        if (!$channel_name || !$socket_id) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['error' => _l('chat_auth_fields_required')]));
            return;
        }
        if (!is_client_logged_in()) {
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['error' => _l('chat_client_login_required')]));
            return;
        }
        $contact_id = function_exists('get_contact_user_id') ? (int) get_contact_user_id() : 0;
        if ($contact_id <= 0) { $this->output->set_status_header(403)->set_output('{}'); return; }
        if (get_option('chat_client_enabled') != '1' || !in_array($channel_name, ['presence-clients', 'private-prchat-clients-contact-' . $contact_id, 'private-prchat-receipts-contact-' . $contact_id, 'private-calls-client-' . $contact_id], true)) {
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['error' => _l('chat_forbidden')]));
            return;
        }
        $name = function_exists('get_contact_full_name') ? get_contact_full_name($contact_id) : 'Client';
        $presence = ['name' => $name, 'type' => 'client', 'contact_id' => $contact_id];
        try {
            if (strpos($channel_name, 'presence-') === 0) {
                $auth = $this->app_pusher->presence_auth($channel_name, $socket_id, 'client_' . $contact_id, $presence);
            } else {
                $auth = $this->app_pusher->socket_auth($channel_name, $socket_id);
            }
            $this->output->set_content_type('application/json')->set_output($auth);
        } catch (Throwable $e) {
            log_message('error', 'Prchat client pusher auth failed: ' . $e->getMessage());
            $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(['error' => _l('chat_realtime_auth_failed')]));
        }
    }
}
