<?php defined('BASEPATH') or exit('No direct script access allowed');

class Prchat_ClientsController extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->app_modules->is_active('prchat') || get_option('pusher_chat_enabled') != '1') {
            $this->output->set_status_header(503)->set_content_type('application/json')->set_output(json_encode(['error' => 'Chat is not enabled']))->_display();
            exit;
        }
        $this->load->model('prchat/prchat_model', 'chat_model');
        $this->load->library('App_pusher');
    }

    public function pusherCustomersAuth()
    {
        $channel_name = $this->input->post('channel_name') ?: $this->input->get('channel_name');
        $socket_id = $this->input->post('socket_id') ?: $this->input->get('socket_id');
        if (!$channel_name || !$socket_id) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['error' => 'channel_name and socket_id are required']));
            return;
        }
        if (!is_client_logged_in()) {
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['error' => 'Client login required']));
            return;
        }
        $contact_id = function_exists('get_contact_user_id') ? (int) get_contact_user_id() : 0;
        if ($contact_id <= 0) { $this->output->set_status_header(403)->set_output('{}'); return; }
        if (get_option('chat_client_enabled') != '1' || !in_array($channel_name, ['presence-clients', 'private-prchat-clients-contact-' . $contact_id, 'private-prchat-receipts-contact-' . $contact_id, 'private-calls-client-' . $contact_id], true)) {
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['error' => 'Forbidden channel']));
            return;
        }
        $name = function_exists('get_contact_full_name') ? get_contact_full_name($contact_id) : 'Client';
        $presence = ['name' => $name, 'type' => 'client', 'contact_id' => $contact_id];
        try {
            if (strpos($channel_name, 'presence-') === 0) {
                $auth = $this->app_pusher->presence_auth($channel_name, $socket_id, 'client-' . $contact_id, $presence);
            } else {
                $auth = $this->app_pusher->socket_auth($channel_name, $socket_id);
            }
            $this->output->set_content_type('application/json')->set_output($auth);
        } catch (Throwable $e) {
            log_message('error', 'Prchat client pusher auth failed: ' . $e->getMessage());
            $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(['error' => 'Pusher authentication failed']));
        }
    }
}
