<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Perfex CRM Powerful Chat
Description: Chat Module for Perfex CRM
Author: iDev
Author URI: https://idevalex.com
*/

/**
 * @property Prchat_model $chat_model
 */
class Prchat_Controller extends AdminController
{

  /**
   * Class constructor
   */
  public function __construct()
  {
    parent::__construct();

    if ($this->router->fetch_method() === 'get_call_token') {
      if (!prchat_staff_can_chat()) {
        $this->output
          ->set_status_header(403)
          ->set_content_type('application/json', 'utf-8')
          ->set_output(json_encode(['success' => false, 'error' => 'Forbidden']));
        $this->output->_display();
        exit;
      }

      if (!$this->app_modules->is_active('prchat') || get_option('pusher_chat_enabled') != '1') {
        $this->output
          ->set_status_header(503)
          ->set_content_type('application/json', 'utf-8')
          ->set_output(json_encode(['success' => false, 'error' => 'Calls are not enabled']));
        $this->output->_display();
        exit;
      }

      $this->load->helper(PR_CHAT_MODULE_NAME . '/prchat');
      $this->load->helper(PR_CHAT_MODULE_NAME . '/prchat_turn');
      return;
    }


    if (!prchat_staff_can_chat()) {
      redirect('admin');
    }

    if (!$this->app_modules->is_active('prchat')) {
      redirect('admin');
    }

    if (!prchat_authorize_staff_request($this->router->fetch_method(), $this->input)) {
      $this->jsonError('Forbidden', 403);
    }
    $this->load->model('prchat_model', 'chat_model');

    // Database-only bootstrap endpoints and the full chat shell must remain
    // available even when Pusher is unavailable. Real-time delivery is an
    // enhancement; it must never block the staff list or page rendering.
    $method = $this->router->fetch_method();
    $pusherOptionalMethods = [
      'users',
      'chat_full_view',
      'getMyGroups',
      'getGroupPreviews',
      'getMessages',
      'getGroupMessages',
      'getGroupMessagesHistory',
      'getUnread',
      'getUnreadCounts',
      'getClientContactPreviews',
      'ajaxSearchStaff',
      'ajaxSearchClients',
      'loadMoreStaff',
    ];

    $pusherConfigured = get_option('pusher_chat_enabled') == '1'
      && get_option('pusher_app_key') !== ''
      && get_option('pusher_app_secret') !== ''
      && get_option('pusher_app_id') !== '';

    if (!in_array($method, $pusherOptionalMethods, true) && $pusherConfigured) {
      try {
        $this->load->library('App_pusher');
      } catch (\Throwable $e) {
        // Database chat must continue even if Pusher cannot initialize.
        log_message('error', '[PRChat] Pusher initialization failed: ' . $e->getMessage());
      }
    }
  }

  private function jsonResponse(array $payload, int $status = 200): void
  {
    $this->output
      ->set_status_header($status)
      ->set_content_type('application/json', 'utf-8')
      ->set_output(json_encode($payload));
  }

  /**
   * Send the database result to the browser before optional real-time work.
   * This prevents a slow Pusher request from delaying the visible send action.
   */
  private function flushJsonResponse(array $payload, int $status = 200): void
  {
    $this->output
      ->set_status_header($status)
      ->set_content_type('application/json', 'utf-8')
      ->set_output(json_encode($payload));
    $this->output->_display();

    if (function_exists('fastcgi_finish_request')) {
      @fastcgi_finish_request();
    } else {
      @ob_flush();
      @flush();
    }
    ignore_user_abort(true);
  }

  private function jsonError(string $message, int $status = 500, array $payload = []): void
  {
    log_message('error', 'prchat legacy chat: ' . $message);
    $this->output
      ->set_status_header($status)
      ->set_content_type('application/json', 'utf-8')
      ->set_output(json_encode(array_merge([
        'success' => false,
        'error' => $message,
      ], $payload)));
    $this->output->_display();
    exit;
  }

  private function triggerPusher(string $channel, string $event, array $payload): bool
  {
    if (!isset($this->app_pusher) || !is_object($this->app_pusher)) {
      return false;
    }
    try {
      return prchat_trigger_event($this->app_pusher, $channel, $event, $payload);
    } catch (\Throwable $e) {
      // A real-time delivery failure must never roll back or hide a message
      // that was already stored successfully in MySQL.
      log_message('error', 'PRChat Pusher ' . $event . ' failed on ' . $channel . ': ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Messaging events
   *
   * @return void
   */
  public function initiateChat()
  {
    if ($this->input->post()) {
      $from = (int) get_staff_user_id();
      $receiver = (int) str_replace('#', '', (string) ($this->input->post('to') ?? ''));

      if ($this->input->post('typing') == 'false') {
        if ($from <= 0 || $receiver <= 0 || $receiver === $from) {
          $this->jsonError('Invalid staff sender or recipient.', 422);
          return;
        }
        $recipientExists = $this->db->where('staffid', $receiver)->where('active', 1)->count_all_results(db_prefix() . 'staff') > 0;
        if (!$recipientExists) {
          $this->jsonError('The selected employee is unavailable.', 404);
          return;
        }
        $msg = trim($this->input->post('msg') ?? '');

        if ($msg === '') {
          $this->triggerPusher(
            'presence-mychanel',
            'typing-event',
            [
              'message' => 'null',
              'from' => $from,
              'to' => $receiver,
            ]
          );
          $this->jsonResponse(['success' => true]);
          return;
        }

        $imageData['sender_image'] = $this->chat_model->getUserImage($from);
        $imageData['receiver_image'] = $this->chat_model->getUserImage($receiver);

        if ($msg !== '') {
          $raw_message = $this->input->post('msg', false);

          $stored_message = $this->chat_model->process_message_for_storage($raw_message);

          $final_message = htmlentities($stored_message);

          $is_call_message = preg_match('/^\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]$/', $raw_message);
          $is_missed_call = preg_match('/^\[CALL:missed_(voice|video):\d+:\d+:\d+\]$/', $raw_message);

          // Server-side dedup for call messages
          if ($is_call_message) {
            $this->db->where('sender_id', $from);
            $this->db->where('reciever_id', $receiver);
            $this->db->where('message', $final_message);
            $this->db->where('time_sent >', date("Y-m-d H:i:s", strtotime('-10 seconds')));
            $dup = $this->db->get(db_prefix() . 'chatmessages')->num_rows();
            if ($dup > 0) {
              $this->jsonResponse(['id' => 0, 'duplicate' => true]);
              return;
            }
          }

          $message_data = [
            'sender_id' => $from,
            'reciever_id' => $receiver,
            'message' => $final_message,
            'viewed' => ($is_call_message && !$is_missed_call) ? 1 : 0,
            'time_sent' => date("Y-m-d H:i:s"),
          ];

          $last_id = $this->chat_model->createMessage($message_data, db_prefix() . 'chatmessages');

          $display_message = $this->chat_model->process_message_for_display($stored_message);
          $display_message = htmlspecialchars($display_message, ENT_QUOTES, 'UTF-8');
          $safe_display = clickable(pr_chat_convertLinkImageToString($display_message));

          $sendPayload = [
            'message' => $safe_display,
            'from' => $from,
            'to' => $receiver,
            'from_name' => get_staff_full_name($from),
            'last_insert_id' => $last_id,
            'sender_image' => $imageData['sender_image'],
            'receiver_image' => $imageData['receiver_image'],
            'is_call' => $is_call_message ? true : false,
          ];
          $notifyPayload = [
            'from' => $from,
            'to' => $receiver,
            'from_name' => get_staff_full_name($from),
            'sender_image' => $imageData['sender_image'],
            'message' => $safe_display,
          ];

          // The message is already committed to MySQL. Return success now;
          // Pusher delivery is best-effort and must not delay the composer.
          $this->flushJsonResponse(['id' => $last_id, 'success' => true, 'stored' => true]);
          $this->triggerPusher('presence-mychanel', 'send-event', $sendPayload);
          if (!$is_call_message || $is_missed_call) {
            $this->triggerPusher('presence-mychanel', 'notify-event', $notifyPayload);
          }
          exit;
        }
      } else if ($this->input->post('typing') == 'true') {
        $this->triggerPusher(
          'presence-mychanel',
          'typing-event',
          [
            'message' => 'true',
            'from' => $from,
            'to' => $receiver,
          ]
        );
        $this->jsonResponse(['success' => true]);
      } else {
        $this->triggerPusher(
          'presence-mychanel',
          'typing-event',
          [
            'message' => 'null',
            'from' => $from,
            'to' => $receiver,
          ]
        );
        $this->jsonResponse(['success' => true]);
      }
    }
  }


  /**
   * Main function that handles, sending messages, notify events, typing events and inserts message data in database.
   *
   * @return websocket event
   */
  public function initiateGroupChat()
  {
    if ($this->input->post()) {
      $from = get_staff_user_id();
      $group_id = $this->input->post('group_id');
      $group_name = $this->db->get_where(TABLE_CHATGROUPS, ['id' => $group_id])->row('group_name');

      if ($this->input->post('typing') == 'false') {
        $imageData['sender_image'] = $this->chat_model->getUserImage($from);

        $stored_message = $this->chat_model->process_message_for_storage($this->input->post('g_message', false));

        $message_data = [
          'sender_id' => get_staff_user_id(),
          'group_id' => $this->input->post('group_id'),
          'message' => htmlspecialchars($stored_message),
          'time_sent' => date("Y-m-d H:i:s")
        ];

        $last_id = $this->chat_model->createGroupMessage($message_data);

        $group_display_message = $this->chat_model->process_message_for_display($stored_message);

        $hasMention = strpos($group_display_message, 'user_mentioned') !== false;
        $hasEmoji = strpos($group_display_message, '"emoji"') !== false;
        $hasQuickMention = strpos($group_display_message, 'quickMentionLink') !== false;

        if (!$hasMention && !$hasEmoji && !$hasQuickMention) {
          $group_display_message = pr_chat_convertLinkImageToString($group_display_message);
        }
        $group_display_message = clickable($group_display_message);

        $this->triggerPusher($group_name, 'group-send-event', [
          'message' => $group_display_message,
          'from' => $from,
          'to_group' => $group_id,
          'from_name' => get_staff_full_name(get_staff_user_id()),
          'group_name' => $group_name,
          'last_insert_id' => $last_id,
          'sender_image' => $imageData['sender_image'],
        ]);

        $this->triggerPusher($group_name, 'group-notify-event', [
          'from' => get_staff_user_id(),
          'from_name' => get_staff_full_name(get_staff_user_id()),
          'to_group' => $group_id,
          'group_name' => $group_name,
          'sender_image' => $imageData['sender_image'],
          'message' => $group_display_message,
        ]);

        $this->jsonResponse(['id' => $last_id, 'success' => true]);
        return;
      } else if ($this->input->post('typing') == 'true') {
        $this->triggerPusher(
          $group_name,
          'group-typing-event',
          [
            'message' => 'true',
            'from' => get_staff_user_id(),
            'from_name' => get_staff_full_name(get_staff_user_id()),
            'to_group' => $group_id,
            'group_name' => $group_name,
          ]
        );
        $this->jsonResponse(['success' => true]);
      } else {
        $this->triggerPusher(
          $group_name,
          'group-typing-event',
          [
            'message' => 'null',
            'from' => get_staff_user_id(),
            'from_name' => get_staff_full_name(get_staff_user_id()),
            'to_group' => $group_id,
            'group_name' => $group_name,
          ]
        );
        $this->jsonResponse(['success' => true]);
      }
    }
  }

  /**
   * Get staff members for chat.
   *
   * @return void
   */
  public function users()
  {
    try {
      $users = $this->chat_model->getUsers();
      $payload = is_array($users) ? array_values($users) : [];
      $this->emitCleanJson($payload, 200);
    } catch (\Throwable $e) {
      log_message('error', '[PRChat] Enhanced staff query failed; using core staff fallback: ' . $e->getMessage());
      try {
        $rows = $this->db
          ->select('staffid, firstname, lastname, email, phonenumber, profile_image, last_login, last_activity, facebook, linkedin, skype, admin, role')
          ->where('active', 1)
          ->order_by('firstname', 'ASC')
          ->order_by('lastname', 'ASC')
          ->get(db_prefix() . 'staff')
          ->result_array();
        foreach ($rows as &$row) {
          $row['message'] = '';
          $row['time_sent'] = null;
          $row['time_sent_formatted'] = '';
          $row['status'] = 'offline';
          $row['is_pinned'] = 0;
          $row['profile_image_url'] = staff_profile_image_url((int) $row['staffid'], 'small');
          $roleId = isset($row['role']) ? (int) $row['role'] : 0;
          $row['role'] = !empty($row['admin']) ? _l('chat_role_administrator') : ($roleId > 0 ? (string) get_staff_userrole($roleId) : _l('chat_role_staff'));
        }
        unset($row);
        $this->emitCleanJson(array_values($rows), 200);
      } catch (\Throwable $fallbackError) {
        log_message('error', '[PRChat] Core staff fallback failed: ' . $fallbackError->getMessage());
        $this->emitCleanJson(['success' => false, 'error' => _l('chat_error_table')], 500);
      }
    }
  }

  /**
   * Emit an uncontaminated JSON response for chat bootstrap endpoints.
   * Other installed modules may emit PHP notices during admin_init; any such
   * HTML would make jQuery reject the users response and leave ChatZoomView
   * permanently covered by the loader.
   */
  private function emitCleanJson(array $payload, int $status): void
  {
    while (ob_get_level() > 0) {
      @ob_end_clean();
    }

    if (!headers_sent()) {
      http_response_code($status);
      header('Content-Type: application/json; charset=utf-8');
      header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
      header('Pragma: no-cache');
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
  }


  /**
   * Get chat group members formatted for JSON response
   *
   * @return void
   */
  public function getChatGroupMembersAsJson()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $group_id = $this->input->get('group_id');

    if ($group_id) {
      $jsonFormattedUsers = $this->chat_model->getChatGroupMembersAsJson($group_id);
      header('Content-Type: application/json');

      if ($jsonFormattedUsers) {
        echo json_encode($jsonFormattedUsers, true);
      } else {
        echo json_encode(['error' => true, 'message' => _l('chat_error_table')]);
      }
    }
  }

  /**
   * Get pusher key
   *
   * @return mixed
   */
  public function getKey()
  {
    $app_key = get_option('pusher_app_key');
    header('Content-Type: application/json');
    if (!empty($app_key)) {
      echo json_encode(['key' => $app_key]);
    } else {
      echo json_encode(['error' => true, 'message' => _l('chat_app_key_not_found')]);
    }
  }

  /**
   * Get staff that will be used for the chat window.
   *
   * @return json|false
   */
  public function getStaffInfo()
  {
    if ($this->input->post('id')) {
      $id = $this->input->post('id');
      $response = $this->chat_model->getStaffInfo($id);

      if ($response) {
        echo json_encode($response);
      }
    }

    return false;
  }


  /**
   * Get logged in user messages sent to other user
   *
   * @return void
   */
  public function getMessages()
  {
    $limit = abs((int) $this->input->get('limit'));
    $from = (int) $this->input->get('from');
    $to = (int) $this->input->get('to');

    $limit = $limit > 0 ? min($limit, 100) : 10; // Default 10, max 100

    $offset = 0;
    if ($this->input->get('offset')) {
      $offset = abs((int) $this->input->get('offset'));
    }

    $currentUserId = (int) get_staff_user_id();
    if ($from !== $currentUserId && $to !== $currentUserId) {
      $from = $currentUserId;
    }

    $response = $this->chat_model->getMessages($from, $to, $limit, $offset);

    if ($response) {
      echo json_encode($response);
    } else {
      $message = _l('chat_no_more_messages_in_database');
      echo json_encode($message);
    }
  }


  /**
   *  Get group messages.
   *
   * @return void
   */
  /**
   * Get last message previews for multiple groups in a single batch request.
   */
  public function getGroupPreviews()
  {
    $group_ids = $this->input->get('group_ids');
    if (!$group_ids) {
      header('Content-Type: application/json');
      echo json_encode([]);
      return;
    }

    $group_ids = array_filter(array_map('intval', explode(',', $group_ids)));
    $previews = $this->chat_model->getGroupPreviews($group_ids);

    header('Content-Type: application/json');
    echo json_encode($previews);
  }

  public function getGroupMessages()
  {
    $limit = abs((int) $this->input->get('limit'));
    $group_id = (int) $this->input->get('group_id');

    $limit = $limit > 0 ? min($limit, 100) : 10; // Default 10, max 100

    $offset = 0;
    if ($this->input->get('offset')) {
      $offset = abs((int) $this->input->get('offset'));
    }

    $response = $this->chat_model->getGroupMessages($group_id, $limit, $offset);

    if ($response) {
      echo json_encode($response);
    } else {
      $message = _l('chat_no_more_messages_in_database');
      echo json_encode($message);
    }
  }


  /**
   * Get group messages history.
   *
   * @return void
   */
  public function getGroupMessagesHistory()
  {
    $limit = abs((int) $this->input->get('limit'));
    $group_id = (int) $this->input->get('group_id');

    $limit = $limit > 0 ? min($limit, 100) : 10;

    $offset = 0;
    $message = '';

    if ($this->input->get('offset')) {
      $offset = abs((int) $this->input->get('offset'));
    }

    $response = $this->chat_model->getGroupMessagesHistory($group_id, $limit, $offset);

    if ($response) {
      echo json_encode($response);
    } else {
      $message = _l('chat_no_more_messages_in_database');
      echo json_encode($message);
    }
  }

  /**
   * Get unread messages, used when somebody sent a message while the user is offline.
   *
   * @param bool
   *
   * @return mixed
   */
  public function getUnread($return = false)
  {
    $result = $this->chat_model->getUnread();

    if ($result) {
      echo json_encode($result);
    } else {
      echo json_encode(['success' => false]);
    }

    return false;
  }

  /**
   * Get all unread counts (staff + client) for floating notifications.
   *
   * @return void
   */
  public function getUnreadCounts()
  {
    $data = ['staff' => [], 'clients' => []];

    // Staff unread
    $staffUnread = $this->chat_model->getUnread();
    if ($staffUnread && is_array($staffUnread)) {
      foreach ($staffUnread as $entry) {
        $sid = (int) $entry['sender_id'];
        if ($sid <= 0)
          continue;
        $name = get_staff_full_name($sid);
        if (empty(trim($name)))
          continue;
        $data['staff'][] = [
          'id' => $sid,
          'name' => $name,
          'count' => (int) $entry['count_messages'],
          'avatar' => staff_profile_image_url($sid, 'small'),
        ];
      }
    }

    // Client unread
    if (isClientsEnabled()) {
      $clientUnread = $this->chat_model->getClientUnreadMessages();
      if ($clientUnread && is_array($clientUnread) && !isset($clientUnread['result'])) {
        foreach ($clientUnread as $entry) {
          $contactId = str_replace('client_', '', $entry['sender_id']);
          $clientData = isset($entry['client_data']) ? $entry['client_data'] : null;
          $name = ($clientData && !empty($clientData['firstname']))
            ? trim($clientData['firstname'] . ' ' . ($clientData['lastname'] ?? ''))
            : _l('chatbot_client_prefix', [$contactId]);
          $data['clients'][] = [
            'id' => $contactId,
            'name' => $name,
            'count' => (int) $entry['count_messages'],
            'avatar' => contact_profile_image_url($contactId),
          ];
        }
      }
    }

    echo json_encode($data);
  }


  /**
   * Updated unread messages to read.
   *
   * @return void
   */
  public function updateUnread()
  {
    if ($this->input->post('id')) {
      $id = $this->input->post('id');
      $result = $this->chat_model->updateUnread($this->app_pusher, $id);

      echo json_encode($result);
    }
  }

  /**
   * Mark messages as read from floating notifications
   *
   * @return void
   */
  public function mark_messages_as_read()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $type = $this->input->post('type');

    if ($type === 'staff') {
      $staff_id = $this->input->post('staff_id');
      if ($staff_id) {
        $result = $this->chat_model->updateUnread($this->app_pusher, $staff_id);
        echo json_encode(['success' => $result]);
      }
    } elseif ($type === 'client') {
      $contact_id = $this->input->post('contact_id');
      if ($contact_id) {
        // For client messages, we need to mark them as read from staff perspective
        $result = $this->chat_model->updateClientUnreadMessages($contact_id, $this->app_pusher);
        echo json_encode(['success' => $result]);
      }
    } else {
      echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_type')]);
    }
  }


  /**
   * Pusher authentication.
   *
   * @return mixed
   * @throws \Pusher\PusherException
   */
  public function pusher_auth()
  {
    if ($this->input->get() || $this->input->post()) {
      $name = get_staff_full_name();
      $user_id = get_staff_user_id();
      $channel_name = $this->input->post('channel_name') ?: $this->input->get('channel_name');
      $socket_id = $this->input->post('socket_id') ?: $this->input->get('socket_id');

      if (!prchat_staff_can_subscribe($channel_name)) { $this->jsonError('Forbidden channel', 403); }
      if (!$channel_name) {
        exit('channel_name must be supplied');
      }

      if (!$socket_id) {
        exit('socket_id must be supplied');
      }

      if (
        !empty(get_option('pusher_app_key'))
        && !empty(get_option('pusher_app_secret'))
        && !empty(get_option('pusher_app_id'))
      ) {
        if (
strpos($channel_name, 'private-') === 0
        ) {
          $auth = $this->app_pusher->socket_auth($channel_name, $socket_id);
        } else {
          $justLoggedIn = ($channel_name === 'presence-mychanel' && $this->session->has_userdata('prchat_user_before_login'));

          if ($justLoggedIn) {
            $this->session->unset_userdata('prchat_user_before_login');
          }

          $presence_data = [
            'name' => $name,
            'justLoggedIn' => $justLoggedIn,
            'status' => '' . $this->chat_model->getChatStatus() . ''
          ];

          $auth = $this->app_pusher->presence_auth($channel_name, $socket_id, $user_id, $presence_data);
        }

        $callback = $this->input->get('callback');
        if (!empty($callback)) {
          $callback = preg_replace('/[^a-zA-Z0-9_.$\[\]\'"\\\\]/', '', $callback);
          header('Content-Type: application/javascript');
          echo $callback . '(' . $auth . ');';
        } else {
          header('Content-Type: application/json');
          echo $auth;
        }
      } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Appkey, secret or appid is missing']);
      }
    }
  }

  /**
   * ICE / WebRTC bootstrap JSON for staff clients.
   */
  public function get_call_token()
  {
    $this->output
      ->set_status_header(200)
      ->set_content_type('application/json', 'utf-8')
      ->set_output(json_encode([
        'success'    => true,
        'iceServers' => prchat_build_ice_servers(),
        'staff_id'   => (int) get_staff_user_id(),
      ]));
  }

  /**
   * Upload method for files
   *
   * @return json
   */
  public function uploadMethod()
  {

    $isVoiceUpload = $this->input->post('prchat_voice_upload') === '1'
      || $this->input->post('prchat_voice_upload') === 1;

    if ($isVoiceUpload) {
      pr_chat_patch_upload_mimes_for_voice();
      $allowedFiles = pr_chat_voice_upload_extensions_string();
    } else {
      pr_chat_patch_upload_mimes_for_crm_files();
      $allowedFiles = pr_chat_client_crm_attachment_types_string();
    }

    if ($isVoiceUpload) {
      $uploadPath = PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/staff';
    } else {
      $uploadPath = PR_CHAT_MODULE_UPLOAD_FOLDER;
    }

    if (!is_dir($uploadPath)) {
      mkdir($uploadPath, 0755, true);
      file_put_contents($uploadPath . '/index.html', '');
    }

    $config = [
      'upload_path' => $uploadPath,
      'allowed_types' => $allowedFiles,
      'max_size' => $isVoiceUpload ? '10240' : pr_chat_max_upload_size_kb(),
      'file_ext_tolower' => true,
      'remove_spaces' => true,
    ];

    $this->load->library('upload', $config);

    if ($this->upload->do_upload('userfile')) {
      $data = $this->upload->data();
      if ($isVoiceUpload) {
        $data['subfolder'] = 'staff';
      }
      echo json_encode(['upload_data' => $data]);
    } else {
      echo json_encode(['error' => strip_tags($this->upload->display_errors())]);
    }
  }


  /**
   * Uploads method for chat group files
   *
   * @return json
   */
  public function groupUploadMethod()
  {

    $isVoiceUpload = $this->input->post('prchat_voice_upload') === '1'
      || $this->input->post('prchat_voice_upload') === 1;

    if ($isVoiceUpload) {
      pr_chat_patch_upload_mimes_for_voice();
      $allowedFiles = pr_chat_voice_upload_extensions_string();
    } else {
      pr_chat_patch_upload_mimes_for_crm_files();
      $allowedFiles = pr_chat_client_crm_attachment_types_string();
    }

    if ($isVoiceUpload) {
      $uploadPath = PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/groups';
    } else {
      $uploadPath = PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER;
    }

    if (!is_dir($uploadPath)) {
      mkdir($uploadPath, 0755, true);
      file_put_contents($uploadPath . '/index.html', '');
    }

    $config = [
      'upload_path' => $uploadPath,
      'allowed_types' => $allowedFiles,
      'max_size' => $isVoiceUpload ? '10240' : pr_chat_max_upload_size_kb(),
      'file_ext_tolower' => true,
      'remove_spaces' => true,
    ];

    $this->load->library('upload', $config);
    if ($this->upload->do_upload('userfile')) {
      $from = get_staff_user_id();
      $to_group = $this->input->post()['to_group'];

      $this->db->insert(
        db_prefix() . 'chatgroupsharedfiles',
        [
          'sender_id' => $from,
          'group_id' => $to_group,
          'file_name' => $this->upload->data('file_name'),
        ]
      );

      $data = $this->upload->data();
      if (!$isVoiceUpload && get_option('prchat_copy_group_uploads_to_project_media') == '1') {
        $copied = pr_chat_copy_group_upload_to_project_media($data['full_path'], $data['file_name'], (int) $to_group);
        $data['project_media_copied'] = $copied ? 1 : 0;
        $data['project_media_folder'] = pr_chat_projects_media_path_display() . '/chat_groups/group_' . (int) $to_group;
      }
      if ($isVoiceUpload) {
        $data['subfolder'] = 'groups';
      }
      echo json_encode(['upload_data' => $data, 'message' => 'File uploaded successfully']);
    } else {
      echo json_encode(['error' => strip_tags($this->upload->display_errors())]);
    }
  }





  /**
   * Change chat color for current user
   *
   * @return json
   */
  public function changeChatColor()
  {
    $id = get_staff_user_id();
    $color = trim($this->input->post('color') ?? '');

    if ($this->input->post('get_chat_color')) {
      echo json_encode(pr_get_chat_color($id));
    }

    if ($this->input->post('color')) {
      echo json_encode($this->chat_model->setChatColor($color));
    }
  }


  /**
   * Delete chat message
   *
   * @return json
   */
  public function deleteMessage()
  {
    // Must be AJAX and user must have delete capability
    if (!$this->input->is_ajax_request()) {
      access_denied();
    }

    if (!staff_can('delete', PR_CHAT_MODULE_NAME)) {
      access_denied();
    }

    $id = (int) $this->input->post('id');
    $contact_id = (string) $this->input->post('contact_id');
    $contact_id = ltrim($contact_id, '#');
    $contact_id = preg_replace('/[^0-9]/', '', $contact_id);
    $currentUserId = (int) get_staff_user_id();

    if ($this->input->post('group_id')) {
      $group_id = (int) $this->input->post('group_id');

      // Ownership check: only allow deleting own group messages
      $row = $this->db->select('sender_id')
        ->where('id', $id)
        ->where('group_id', $group_id)
        ->get(db_prefix() . 'chatgroupmessages')
        ->row();

      if (!$row || (int) $row->sender_id !== $currentUserId) {
        access_denied();
      }

      $deleted = $this->chat_model->deleteMessage($id, 'group_id' . $group_id);

      if ($deleted && $group_id > 0 && isset($this->app_pusher)) {
        $group_name = $this->db->select('group_name')
          ->where('id', $group_id)
          ->get(db_prefix() . 'chatgroups')
          ->row('group_name');

        if (!empty($group_name)) {
          $this->triggerPusher($group_name, 'group-message-deleted', [
            'message_id' => $id,
            'group_id' => $group_id,
            'sender_id' => $currentUserId,
          ]);
        }
      }

      echo json_encode($deleted);
    } else {
      // Ownership check: only allow deleting own direct messages
      $row = $this->db->select('sender_id')
        ->where('id', $id)
        ->get(db_prefix() . 'chatmessages')
        ->row();

      if (!$row || (int) $row->sender_id !== $currentUserId) {
        access_denied();
      }

      $deleted = $this->chat_model->deleteMessage($id, $contact_id);

      if ($deleted && !empty($contact_id) && isset($this->app_pusher)) {
        $this->triggerPusher('presence-mychanel', 'message-deleted', [
          'message_id' => $id,
          'from' => $currentUserId,
          'to' => (int) $contact_id,
        ]);
      }

      echo json_encode($deleted);
    }
  }


  /**
   * Scoped message deletion.
   *
   * scope=me hides only for the logged-in staff member.
   * scope=recipient hides only for the other direct participant.
   * scope=member hides only for a selected group member.
   * scope=everyone permanently removes the shared message using the legacy delete path.
   */
  public function deleteMessageScoped()
  {
    if (!$this->input->is_ajax_request() || $this->input->method(true) !== 'POST') {
      return $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'POST required.']));
    }
    if (!is_admin() && !staff_can('delete', PR_CHAT_MODULE_NAME)) {
      access_denied(PR_CHAT_MODULE_NAME);
    }

    $messageId = (int) $this->input->post('message_id');
    $messageType = (string) $this->input->post('message_type');
    $scope = (string) $this->input->post('scope');
    $targetId = (int) $this->input->post('target_id');
    $staffId = (int) get_staff_user_id();
    $allowedTypes = ['staff', 'client', 'group'];
    $allowedScopes = ['me', 'recipient', 'member', 'everyone'];

    if ($messageId <= 0 || !in_array($messageType, $allowedTypes, true) || !in_array($scope, $allowedScopes, true)) {
      return $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => _l('chat_delete_invalid_request')]));
    }

    $table = db_prefix() . 'chatmessages';
    if ($messageType === 'client') {
      $table = db_prefix() . 'chatclientmessages';
    } elseif ($messageType === 'group') {
      $table = db_prefix() . 'chatgroupmessages';
    }
    $row = $this->db->where('id', $messageId)->get($table)->row();
    if (!$row) {
      return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => _l('chat_message_not_found')]));
    }

    // Ensure the staff member is a participant/member of the conversation they are acting on.
    if ($messageType === 'staff') {
      if ((int) $row->sender_id !== $staffId && (int) $row->reciever_id !== $staffId) {
        access_denied(PR_CHAT_MODULE_NAME);
      }
    } elseif ($messageType === 'client') {
      $staffKey = 'staff_' . $staffId;
      if ((string) $row->sender_id !== $staffKey && (string) $row->reciever_id !== $staffKey) {
        access_denied(PR_CHAT_MODULE_NAME);
      }
    } else {
      $groupId = (int) $row->group_id;
      $isMember = $this->db->where('group_id', $groupId)->where('member_id', $staffId)->count_all_results(TABLE_CHATGROUPMEMBERS) > 0;
      $creatorId = (int) $this->db->where('id', $groupId)->get(TABLE_CHATGROUPS)->row('created_by_id');
      if (!$isMember && $creatorId !== $staffId && !is_admin()) {
        access_denied(PR_CHAT_MODULE_NAME);
      }
    }

    $success = false;
    $targetLabel = '';

    if ($scope === 'me') {
      $success = $this->chat_model->hideMessageForViewer($messageType, $messageId, 'staff', $staffId, $staffId);
      $targetLabel = 'me';
    } elseif ($scope === 'recipient') {
      if ($messageType === 'staff') {
        $other = ((int) $row->sender_id === $staffId) ? (int) $row->reciever_id : (int) $row->sender_id;
        $success = $this->chat_model->hideMessageForViewer('staff', $messageId, 'staff', $other, $staffId);
        $targetId = $other;
      } elseif ($messageType === 'client') {
        $staffKey = 'staff_' . $staffId;
        $otherKey = ((string) $row->sender_id === $staffKey) ? (string) $row->reciever_id : (string) $row->sender_id;
        if (strpos($otherKey, 'client_') === 0) {
          $targetId = (int) str_replace('client_', '', $otherKey);
          $success = $this->chat_model->hideMessageForViewer('client', $messageId, 'client', $targetId, $staffId);
        } elseif (strpos($otherKey, 'staff_') === 0) {
          $targetId = (int) str_replace('staff_', '', $otherKey);
          $success = $this->chat_model->hideMessageForViewer('client', $messageId, 'staff', $targetId, $staffId);
        }
      }
      $targetLabel = 'recipient';
    } elseif ($scope === 'member') {
      if ($messageType !== 'group' || $targetId <= 0) {
        return $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => _l('chat_select_group_member')]));
      }
      $groupId = (int) $row->group_id;
      $validMember = $this->db->where('group_id', $groupId)->where('member_id', $targetId)->count_all_results(TABLE_CHATGROUPMEMBERS) > 0;
      if (!$validMember) {
        return $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => _l('chat_select_group_member')]));
      }
      $success = $this->chat_model->hideMessageForViewer('group', $messageId, 'staff', $targetId, $staffId);
      $targetLabel = 'member';
    } else {
      if ($messageType === 'group') {
        $success = $this->chat_model->deleteMessage($messageId, 'group_id' . (int) $row->group_id);
      } elseif ($messageType === 'client') {
        $success = $this->chat_model->deleteClientMessage($messageId);
      } else {
        $other = ((int) $row->sender_id === $staffId) ? (int) $row->reciever_id : (int) $row->sender_id;
        $success = $this->chat_model->deleteMessage($messageId, (string) $other);
      }
      $targetLabel = 'everyone';
    }

    if ($success) {
      // Real-time synchronization: permanent deletes use the existing events;
      // scoped hides use targeted events so only the intended viewer removes it.
      if (isset($this->app_pusher)) {
        if ($scope === 'everyone') {
          if ($messageType === 'group') {
            $groupName = $this->db->select('group_name')->where('id', (int) $row->group_id)->get(TABLE_CHATGROUPS)->row('group_name');
            if ($groupName) {
              $this->triggerPusher($groupName, 'group-message-deleted', ['message_id' => $messageId, 'group_id' => (int) $row->group_id, 'sender_id' => $staffId]);
            }
          } elseif ($messageType === 'client') {
            $this->triggerPusher('presence-clients', 'message-deleted', ['message_id' => $messageId, 'from' => (string) $row->sender_id, 'to' => (string) $row->reciever_id]);
          } else {
            $this->triggerPusher('presence-mychanel', 'message-deleted', ['message_id' => $messageId, 'from' => (int) $row->sender_id, 'to' => (int) $row->reciever_id]);
          }
        } elseif ($scope === 'recipient' || $scope === 'member') {
          if ($messageType === 'group') {
            $groupName = $this->db->select('group_name')->where('id', (int) $row->group_id)->get(TABLE_CHATGROUPS)->row('group_name');
            if ($groupName) {
              $this->triggerPusher($groupName, 'group-message-hidden', ['message_id' => $messageId, 'group_id' => (int) $row->group_id, 'viewer_type' => 'staff', 'viewer_id' => $targetId]);
            }
          } elseif ($messageType === 'client') {
            $viewerType = 'client';
            $otherKey = ((string) $row->sender_id === 'staff_' . $staffId) ? (string) $row->reciever_id : (string) $row->sender_id;
            if (strpos($otherKey, 'staff_') === 0) { $viewerType = 'staff'; }
            $this->triggerPusher('presence-clients', 'message-hidden', ['message_id' => $messageId, 'viewer_type' => $viewerType, 'viewer_id' => $targetId]);
          } else {
            $this->triggerPusher('presence-mychanel', 'message-hidden', ['message_id' => $messageId, 'viewer_type' => 'staff', 'viewer_id' => $targetId]);
          }
        }
      }
      log_activity('PRChat message #' . $messageId . ' (' . $messageType . ') delete scope ' . $targetLabel . ' by staff #' . $staffId . ($targetId > 0 ? ' target #' . $targetId : ''));
    }

    return $this->output->set_content_type('application/json')->set_output(json_encode([
      'success' => (bool) $success,
      'scope' => $scope,
      'message_id' => $messageId,
      'message_type' => $messageType,
      'target_id' => $targetId,
      'message' => $success ? _l('chat_message_delete_scope_success') : _l('chat_message_delete_scope_failed'),
    ]));
  }

  /** Return members eligible for a one-member group hide action. */
  public function groupDeleteMembers()
  {
    if (!$this->input->is_ajax_request() || !is_staff_logged_in()) {
      access_denied(PR_CHAT_MODULE_NAME);
    }
    if (!is_admin() && !staff_can('delete', PR_CHAT_MODULE_NAME)) {
      access_denied(PR_CHAT_MODULE_NAME);
    }
    $groupId = (int) $this->input->get('group_id');
    if ($groupId <= 0) {
      return $this->output->set_content_type('application/json')->set_output(json_encode([]));
    }
    $this->db->select('s.staffid, s.firstname, s.lastname');
    $this->db->from(TABLE_CHATGROUPMEMBERS . ' gm');
    $this->db->join(TABLE_STAFF . ' s', 's.staffid = gm.member_id', 'inner');
    $this->db->where('gm.group_id', $groupId);
    $this->db->where('s.active', 1);
    $this->db->order_by('s.firstname', 'ASC');
    $rows = $this->db->get()->result_array();
    return $this->output->set_content_type('application/json')->set_output(json_encode($rows ?: []));
  }

  /**
   * Edit a staff or group message.
   */
  public function editMessage()
  {
    if (!$this->input->is_ajax_request()) {
      access_denied();
    }

    $id = (int) $this->input->post('id');
    $new_text = $this->input->post('message', false);
    $group_id = $this->input->post('group_id');
    $staff_id = get_staff_user_id();

    if (!$id || !$new_text) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'changed' => false, 'error' => 'Missing parameters']);
      return;
    }

    $stored = $this->chat_model->process_message_for_storage($new_text);
    $final = htmlentities($stored);

    if ($group_id) {
      $table = db_prefix() . 'chatgroupmessages';
      $editResult = $this->chat_model->editMessage($id, $final, $table, 'sender_id', $staff_id, ['sender_type' => 'staff']);
    } else {
      $table = db_prefix() . 'chatmessages';
      $editResult = $this->chat_model->editMessage($id, $final, $table, 'sender_id', $staff_id);
    }

    $success = !empty($editResult['success']);
    $changed = !empty($editResult['changed']);

    $display = $this->chat_model->process_message_for_display($stored);
    $display = htmlspecialchars($display, ENT_QUOTES, 'UTF-8');
    $rendered_safe = clickable(pr_chat_convertLinkImageToString($display));

    if ($success && $changed) {
      if ($group_id) {
        $group_name = $this->db->select('group_name')
          ->where('id', (int) $group_id)
          ->get(TABLE_CHATGROUPS)
          ->row('group_name');
        if ($group_name) {
          $gSender = $this->db->select('sender_id')
            ->where('id', $id)
            ->get(db_prefix() . 'chatgroupmessages')
            ->row();
          $this->triggerPusher($group_name, 'group-message-edited', [
            'message_id' => $id,
            'group_id' => (int) $group_id,
            'sender_id' => $gSender ? (int) $gSender->sender_id : (int) $staff_id,
            'rendered_message' => $rendered_safe,
            'edited_at' => date('Y-m-d H:i:s'),
          ]);
        }
      } else {
        $row = $this->db->where('id', $id)->get(db_prefix() . 'chatmessages')->row();
        if ($row) {
          $this->triggerPusher('presence-mychanel', 'message-edited', [
            'message_id' => $id,
            'from' => $row->sender_id,
            'to' => $row->reciever_id,
            'rendered_message' => $rendered_safe,
            'edited_at' => date('Y-m-d H:i:s'),
          ]);
        }
      }
    }

    header('Content-Type: application/json');
    echo json_encode([
      'success' => $success,
      'changed' => $changed,
      'rendered_message' => $rendered_safe,
    ]);
  }

  /**
   * Edit a client message.
   */
  public function editClientMessage()
  {
    if (!$this->input->is_ajax_request()) {
      access_denied();
    }

    $id = (int) $this->input->post('id');
    $new_text = $this->input->post('message', false);
    $staff_id = get_staff_user_id();

    if (!$id || !$new_text) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'changed' => false, 'error' => 'Missing parameters']);
      return;
    }

    $stored = $this->chat_model->process_message_for_storage($new_text);
    $final = htmlentities($stored);
    $table = db_prefix() . 'chatclientmessages';
    $editResult = $this->chat_model->editMessage($id, $final, $table, 'sender_id', 'staff_' . $staff_id);

    $success = !empty($editResult['success']);
    $changed = !empty($editResult['changed']);

    $display = $this->chat_model->process_message_for_display($stored);
    $display = htmlspecialchars($display, ENT_QUOTES, 'UTF-8');
    $rendered_safe = clickable(pr_chat_convertLinkImageToString($display));

    if ($success && $changed) {
      $row = $this->db->where('id', $id)->get($table)->row();
      if ($row) {
        $this->triggerPusher('presence-clients', 'message-edited', [
          'message_id' => $id,
          'from' => $row->sender_id,
          'to' => $row->reciever_id,
          'rendered_message' => $rendered_safe,
          'edited_at' => date('Y-m-d H:i:s'),
        ]);
      }
    }

    header('Content-Type: application/json');
    echo json_encode([
      'success' => $success,
      'changed' => $changed,
      'rendered_message' => $rendered_safe,
    ]);
  }

  /**
   * Delete chat client message
   *
   * @return mixed
   */
  public function deleteClientMessage()
  {
    // Must be AJAX and user must have delete capability
    if (!$this->input->is_ajax_request()) {
      access_denied();
    }

    if (!staff_can('delete', PR_CHAT_MODULE_NAME)) {
      access_denied();
    }

    $message_id = (int) $this->input->post('message_id');

    if ($message_id) {
      // Ownership check: only allow deleting own staff messages in client conversation
      $expectedSender = 'staff_' . get_staff_user_id();
      $row = $this->db->select('sender_id')
        ->where('id', $message_id)
        ->get(db_prefix() . 'chatclientmessages')
        ->row();

      if (!$row || (string) $row->sender_id !== $expectedSender) {
        access_denied();
      }

      echo json_encode($this->chat_model->deleteClientMessage($message_id));
    }
  }


  /**
   * Delete chat conversation
   *
   * @return mixed
   */
  public function deleteChatConversation()
  {
    if (!chatStaffCanDelete())
      access_denied();

    if ($this->input->post('id')) {
      $id = $this->input->post('id');
      $table = $this->input->post('table');
      $allowedTables = ['chatmessages', 'chatclientmessages'];
      if (!in_array($table, $allowedTables)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid table']);
        return;
      }
      header('Content-Type: application/json');
      echo json_encode($this->chat_model->deleteMutualConversation($id, $table));
    }
  }


  /**
   * Switch user theme
   * Light or Dark.
   *
   * @return json
   */
  public function switchTheme()
  {
    $id = get_staff_user_id();
    $theme_name = $this->input->post('theme_name');

    echo json_encode($this->chat_model->updateChatTheme($id, $theme_name));
  }


  /**
   * Loads user full chat browser view.
   *
   * @return view
   */
  public function chat_full_view()
  {
    $result = $this->chat_model->getUnread();
    $this->load->view('prchat/chat_full_view', ['unreadMessages' => $result]);
  }

  /**
   * Handles shared files between two users.
   *
   * @return json
   */
  public function getSharedFiles()
  {
    if ($this->input->post()) {
      $own_id = $this->input->post('own_id');
      $contact_id = $this->input->post('contact_id');

      $html = $this->chat_model->get_shared_files_and_create_template($own_id, $contact_id);
      if ($html) {
        echo json_encode($html);
      }
    }
  }


  /**
   * Handles shared files between users in group.
   *
   * @return json
   */
  public function getGroupSharedFiles()
  {
    if ($this->input->post()) {
      $group_id = $this->input->post('group_id');

      $html = $this->chat_model->get_group_shared_files_and_create_template($group_id);

      if ($html) {
        echo json_encode($html);
      }
    }
  }


  /**
   *  Handles staff announcement modal view.
   *
   * @return view modal
   */
  public function staff_announcement()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_announcement_modal_text');

    $this->load->view('prchat/includes/modal', $data);
  }


  /**
   *  Handles clients mass message modal view.
   *
   * @return view modal
   */
  public function clients_announcement_message()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_client_announcement_title');

    $this->load->view('prchat/includes/client_announcment_modal', $data);
  }


  /**
   * Handles data inserting for global message to selected clients.
   *
   * @return json
   */
  public function clients_announcement()
  {
    if ($this->input->post()) {
      $members = $this->input->post('clients');
      $message = $this->input->post('message');

      echo json_encode($this->chat_model->announcementToClients($members, $message, $this->app_pusher));
    }
  }


  /**
   *  Handles staff announcement modal view.
   *
   * @return view modal
   */
  public function quick_mentions($id = '')
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if (!staff_can('edit', 'tasks') && !staff_can('create', 'tasks')) {
      ajax_access_denied();
    }

    $data = [];

    $data['milestones'] = [];
    $data['checklistTemplates'] = [];
    $data['project_end_date_attrs'] = [];


    $this->load->view('prchat/includes/quick_mentions_modal', $data);
  }


  /**
   * Handles data inserting for global message to selected members.
   *
   * @return json
   */
  public function staff_get_selected_members()
  {
    if ($this->input->post()) {
      $members = $this->input->post('members');
      $message = $this->input->post('message');

      echo json_encode($this->chat_model->globalMessage($members, $message, $this->app_pusher));
    }
  }


  /**
   * Fetch chat groups
   *
   * @return view
   */
  public function chatGroups()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_group_modal_title');
    $data['association_only'] = false;
    $data['group_id'] = 0;
    $data['related_type'] = '';
    $data['related_id'] = 0;
    $data['related_name'] = '';

    if ($this->input->get('association_only') === '1') {
      $gid = (int) $this->input->get('group_id');
      if ($gid <= 0) {
        echo '<div class="alert alert-danger tw-m-3">' . _l('access_denied') . '</div>';
        return;
      }
      $row = $this->db->select('id, created_by_id, related_type, related_id')
        ->where('id', $gid)
        ->get(db_prefix() . 'chatgroups')
        ->row();
      $staffId = (int) get_staff_user_id();
      if (!$row || ((int) $row->created_by_id !== $staffId && !is_admin())) {
        echo '<div class="alert alert-danger tw-m-3">' . _l('access_denied') . '</div>';
        return;
      }
      $data['association_only'] = true;
      $data['group_id'] = $gid;
      $data['title'] = _l('chat_associate_with');
      $data['related_type'] = $row->related_type ?: '';
      $data['related_id'] = !empty($row->related_id) ? (int) $row->related_id : 0;
      if ($data['related_type'] && $data['related_id']) {
        $resolved = $this->chat_model->resolveRelatedItemPublic($data['related_type'], $data['related_id']);
        $data['related_name'] = $resolved['name'] ?? '';
      }
    }

    $this->load->view('prchat/includes/groups_modal', $data);
  }


  /**
   * Loads new modal for creating new chat group.
   *
   * @return view
   */
  public function addNewChatGroupMembersModal()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_group_modal_add_title');
    $group_id = (int) $this->input->get('group_id');
    $data['group_id'] = $group_id;

    $this->load->view('prchat/includes/add_modal', $data);
  }


  /**
   * Adds new chat members to specific group.
   *
   * @return json
   */
  public function addChatGroupMembers()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if (!empty($this->input->post('group_name'))) {
      $group_name = $this->input->post('group_name');
      $members = $this->input->post('members');
      $group_id = $this->input->post('group_id');

      return $this->chat_model->addChatGroupMembers($group_name, $group_id, $members, $this->app_pusher);
    }
  }


  /**
   * Create new chat group
   *
   * @return mixed
   */
  public function addChatGroup()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    // Check if staff can create groups (admins bypass)
    if (get_option('chat_members_can_create_groups') != 1 && !is_admin()) {
      echo json_encode(['success' => false, 'message' => _l('access_denied')]);
      return;
    }

    if ($this->input->post('group_name')) {
      $data = [];

      $data['group_name'] = CHAT_GROUP_PRESENCE_PREFIX . slugifyGroupName($this->input->post('group_name'));

      $data['members'] = $this->input->post('members');

      $own_id = $this->session->userdata('staff_user_id');

      if (empty($data['members'])) {
        return false;
      }

      if (!in_array($own_id, $data['members'])) {
        $data['members'][] = $own_id;
      }

      $insertData = [
        'created_by_id' => $own_id,
        'group_name' => $data['group_name'],
      ];

      // Add association if provided
      $related_type = $this->input->post('related_type');
      $related_id = $this->input->post('related_id');
      if (!empty($related_type) && !empty($related_id)) {
        $insertData['related_type'] = $related_type;
        $insertData['related_id'] = (int) $related_id;
        // Include in Pusher payload so sidebar renders association immediately
        $data['related_type'] = $related_type;
        $data['related_id'] = (int) $related_id;
        $resolved = $this->chat_model->resolveRelatedItemPublic($related_type, (int) $related_id);
        $data['related_name'] = $resolved['name'];
        $data['related_url'] = $resolved['url'];
      }

      return $this->chat_model->addChatGroup($insertData, $data, $this->app_pusher);
    }
  }

  /**
   * Task search for group association (Perfex core get_relation_data does not search tasks by name).
   * Same JSON shape as admin/misc/get_relation_data for ajax bootstrap-select.
   */
  public function prchatRelationSearch()
  {
    if (!$this->input->is_ajax_request()) {
      echo json_encode([]);
      return;
    }

    $type = $this->input->post('type');
    $q = trim((string) $this->input->post('q'));
    if ($type !== 'task' || $q === '') {
      echo json_encode([]);
      return;
    }

    if (staff_cant('view', 'tasks')) {
      echo json_encode([]);
      return;
    }

    $this->load->helper('relation');
    $this->db->select('id, name');
    $this->db->from(db_prefix() . 'tasks');
    $this->db->group_start();
    $this->db->like('name', $q);
    $this->db->group_end();
    $this->db->limit(50);
    $rows = $this->db->get()->result_array();

    $out = [];
    foreach ($rows as $r) {
      $out[] = get_relation_values($r, 'task');
    }

    echo json_encode($out);
  }

  /**
   * Legacy: full list loader (unused by group modal; association uses ajax search like core CRM).
   */
  public function getRelatedItems()
  {
    if (!$this->input->is_ajax_request()) {
      echo json_encode([]);
      return;
    }

    echo json_encode([]);
  }

  public function renameChatGroup()
  {
    if (!is_admin() && !staff_can('edit', PR_CHAT_MODULE_NAME)) { access_denied(PR_CHAT_MODULE_NAME); }
    $groupId = $this->input->post('groupId');
    $newName = $this->input->post('groupName');

    try {
      if ($groupId) {
        $this->db->where('id', $groupId)->update(db_prefix() . 'chatgroups', ['group_name' => CHAT_GROUP_PRESENCE_PREFIX . slugifyGroupName($newName)]);
        $this->db->where('group_id', $groupId)->update(db_prefix() . 'chatgroupmembers', ['group_name' => CHAT_GROUP_PRESENCE_PREFIX . slugifyGroupName($newName)]);
      }

      $this->triggerPusher(
        'group-chat',
        'group-renamed',
        [
          'group_id' => $groupId,
          'newName' => $newName,
        ]
      );
    } catch (Exception $e) {
      echo json_encode(['error' => $e->getMessage()]);
      die;
    }

    echo json_encode(['success' => true]);
  }

  /**
   * Link an existing group (no association yet) to a CRM record. Group creator or admin only.
   */
  public function updateChatGroupAssociation()
  {
    if ($this->input->method(true) !== 'POST') {
      return $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'POST required.']));
    }

    header('Content-Type: application/json; charset=utf-8');

    $groupId = (int) $this->input->post('group_id');
    $relatedType = $this->input->post('related_type');
    $relatedId = (int) $this->input->post('related_id');

    if ($groupId <= 0 || $relatedId <= 0 || !is_string($relatedType) || $relatedType === '') {
      echo json_encode(['success' => false, 'message' => _l('chat_select_association')]);
      return;
    }

    $allowed = ['project', 'invoice', 'estimate', 'contract', 'ticket', 'lead', 'task'];
    if (!in_array($relatedType, $allowed, true)) {
      echo json_encode(['success' => false, 'message' => _l('access_denied')]);
      return;
    }

    $group = $this->db->where('id', $groupId)->get(db_prefix() . 'chatgroups')->row();
    if (!$group) {
      echo json_encode(['success' => false, 'message' => _l('chat_error_float')]);
      return;
    }

    $staffId = (int) get_staff_user_id();
    if ((int) $group->created_by_id !== $staffId && !is_admin() && !staff_can('edit', PR_CHAT_MODULE_NAME)) {
      echo json_encode(['success' => false, 'message' => _l('access_denied')]);
      return;
    }

    $this->db->where('id', $groupId)->update(db_prefix() . 'chatgroups', [
      'related_type' => $relatedType,
      'related_id' => $relatedId,
    ]);

    $resolved = $this->chat_model->resolveRelatedItemPublic($relatedType, $relatedId);

    echo json_encode([
      'success' => true,
      'related_type' => $relatedType,
      'related_id' => $relatedId,
      'related_name' => $resolved['name'],
      'related_url' => $resolved['url'],
    ]);
  }


  /**
   * Fetches all groups linked to current logged in user
   *
   * @return json
   */
  public function getMyGroups()
  {
    if (!$this->input->is_ajax_request()) {
      $this->emitCleanJson(['success' => false, 'error' => 'AJAX request required'], 400);
    }

    try {
      if (!$this->db->table_exists(db_prefix() . 'chatgroups') || !$this->db->table_exists(db_prefix() . 'chatgroupmembers')) {
        $this->emitCleanJson(['noChannels' => true, 'groups' => []], 200);
      }

      $staffId = (int) get_staff_user_id();
      $groups = $this->db
        ->select('g.*')
        ->from(db_prefix() . 'chatgroups g')
        ->join(db_prefix() . 'chatgroupmembers m', 'm.group_id = g.id', 'inner')
        ->where('m.member_id', $staffId)
        ->group_by('g.id')
        ->order_by('g.id', 'ASC')
        ->get()
        ->result_array();

      foreach ($groups as &$group) {
        $group['members'] = $this->db
          ->select('m.member_id, s.firstname, s.lastname, m.group_id')
          ->from(db_prefix() . 'chatgroupmembers m')
          ->join(db_prefix() . 'staff s', 's.staffid = m.member_id', 'left')
          ->where('m.group_id', (int) $group['id'])
          ->get()
          ->result_array();
        if (!empty($group['related_type']) && !empty($group['related_id'])) {
          $resolved = $this->chat_model->resolveRelatedItemPublic($group['related_type'], (int) $group['related_id']);
          $group['related_name'] = $resolved['name'];
          $group['related_url'] = $resolved['url'];
        }
      }
      unset($group);

      $this->emitCleanJson(empty($groups) ? ['noChannels' => true, 'groups' => []] : ['groups' => array_values($groups)], 200);
    } catch (\Throwable $e) {
      log_message('error', '[PRChat] Unable to load groups: ' . $e->getMessage());
      $this->emitCleanJson(['success' => false, 'error' => _l('chat_error_table'), 'groups' => []], 500);
    }
  }


  /**
   * Delete chat group
   *
   * @return json
   */
  public function deleteGroup()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if (!chatStaffCanDeleteGroups()) {
      access_denied();
    }

    if ($this->input->post('group_id')) {
      $group_id = $this->input->post('group_id');
      $group_name = $this->input->post('group_name');

      return $this->chat_model->deleteGroup($group_id, $group_name, $this->app_pusher);
    }
  }


  /**
   * Get all group members
   *
   * @return json
   */
  public function getGroupUsers()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if ($this->input->post('group_id') !== '') {
      $group_id = $this->input->post('group_id');

      return $this->chat_model->getGroupUsers($group_id);
    }
  }


  /**
   * Backup function that fetches all group members.
   *
   * @return mixed
   */
  public function getCurrentGroupUsers()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if ($this->input->post('group_id') !== '') {
      $group_id = $this->input->post('group_id');
      $users = $this->chat_model->getCurrentGroupUsers($group_id);
      if (is_array($users) && !empty($users)) {
        return $users;
      } else {
        return false;
      }
    }
  }


  /**
   * Remove user from group
   *
   * @return mixed
   */
  public function removeChatGroupUser()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if (!staff_can('delete', PR_CHAT_MODULE_NAME)) {
      access_denied();
    }

    $own_id = get_staff_user_id();

    if ($this->input->post('id')) {
      $group_name = $this->input->post('group_name');
      $user_id = $this->input->post('id');
      $group_id = $this->input->post('group_id');

      return $this->chat_model->removeChatGroupUser($group_name, $group_id, $user_id, $own_id, $this->app_pusher);
    } else {
      return false;
    }
  }


  /**
   * Chat members leaves group event
   *
   * @return mixed
   */
  public function chatMemberLeaveGroup()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if ($this->input->post('group_id')) {
      $group_id = $this->input->post('group_id');
      $member_id = get_staff_user_id();

      return $this->chat_model->chatMemberLeaveGroup($group_id, $member_id, $this->app_pusher);
    }
  }


  /**
   * Downloads CSV file of exported messages from database between two users staff or clients
   *
   * @return void
   */
  public function exportCSV()
  {
    if (!is_admin()) {
      access_denied();
    }

    $to = $this->input->get('user');

    $this->chat_model->initiateExportToCSV($to);
  }


  /**
   * Conver to ticket load model view
   *
   * @return view
   */
  public function convertToTicket()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    // Check if ticket conversion is enabled (admins bypass)
    if (get_option('chat_allow_staff_to_create_tickets') != 1 && !is_admin()) {
      access_denied();
    }

    $id = $this->input->post('id');
    $table = 'chatclientmessages';

    $name = (strpos($id, 'client') !== false)
      ? get_contact_full_name(str_replace('client_', '', $id))
      : get_staff_full_name(get_staff_user_id());

    $data = [
      'id' => $id,
      'user_full_name' => $name,
      'messages' => $this->chat_model->getMessagesForTicketConversion($id, $table),
    ];

    $this->load->view('prchat/includes/convert_to_ticket_modal', $data);
  }


  /**
   * Create new support ticket
   *
   * @return string
   */
  public function createNewSupportTicket()
  {
    // Check if ticket conversion is enabled (admins bypass)
    if (get_option('chat_allow_staff_to_create_tickets') != 1 && !is_admin()) {
      access_denied();
    }

    $data = [];

    $data = $this->input->post('content');
    $assigned = $this->input->post('assigned');
    $subject = $this->input->post('subject');
    $department = $this->input->post('department');

    return $this->chat_model->chatHandleSupportTicketCreation($data, $subject, $department, $assigned);
  }


  /**
   * Chat status update
   *
   * @return mixed
   */
  public function handleChatStatus()
  {
    $status = $this->input->post('status');

    if (!$status || !$this->input->is_ajax_request()) {
      show_404();
    }

    $response = $this->chat_model->handleChatStatus($status);

    if (!empty($response)) {
      $this->triggerPusher(
        'user_changed_chat_status',
        'status-changed-event',
        [
          'user_id' => $response['user_id'],
          'status' => $response['status'],
        ]
      );
      header('Content-Type: application/json');
      echo json_encode($response);
    }
  }


  /**
   * Toggle pin state for a conversation
   *
   * @return void
   */
  public function togglePin()
  {
    if (!$this->input->is_ajax_request() || !$this->input->post()) {
      show_404();
    }

    $type = $this->input->post('type'); // staff, group, client
    $target_id = $this->input->post('target_id');

    if (!$type || !$target_id) {
      show_404();
    }

    $settingName = 'pinned_' . $type;
    $allowedTypes = ['staff', 'groups', 'clients'];
    if (!in_array($type, $allowedTypes)) {
      show_404();
    }

    $result = $this->chat_model->togglePinChat($settingName, $target_id);

    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'pinned' => $result['added'],
      'ids' => $result['ids']
    ]);
  }

  /**
   * Toggle mute state for a conversation
   *
   * @return void
   */
  public function toggleMute()
  {
    if (!$this->input->is_ajax_request() || !$this->input->post()) {
      show_404();
    }

    $type = $this->input->post('type'); // staff, groups, clients
    $target_id = $this->input->post('target_id');

    if (!$type || !$target_id) {
      show_404();
    }

    $settingName = 'muted_' . $type;
    $allowedTypes = ['staff', 'groups', 'clients'];
    if (!in_array($type, $allowedTypes)) {
      show_404();
    }

    $result = $this->chat_model->toggleMuteChat($settingName, $target_id);

    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'muted' => $result['muted'],
      'ids' => $result['ids']
    ]);
  }

  /**
   * Get all pin/mute settings for the current user
   *
   * @return void
   */
  public function getPinMuteSettings()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $settings = $this->chat_model->getAllPinMuteSettings();

    header('Content-Type: application/json');
    echo json_encode($settings);
  }

  /**
   * Mentions
   *
   * @return void
   */
  public function pusherMentionEvent()
  {
    $data = $this->input->post();

    if (!$data || !$this->input->is_ajax_request()) {
      show_404();
    }
    if ($data) {
      $data['from'] = get_staff_user_id();
      $group = $this->db->where('id', (int) $data['group_id'])->get(TABLE_CHATGROUPS)->row();
      $data['channel'] = substr($group->group_name, strlen(CHAT_GROUP_PRESENCE_PREFIX));
      $data['users'] = array_values(array_filter((array) ($data['users'] ?? []), static function ($user) use ($data) {
        return is_array($user) && isset($user['user_id']) && prchat_staff_can_group($data['group_id'], $user['user_id']);
      }));
      $this->chat_model->handleMentionEvent($data, $this->app_pusher);
    }
  }


  /**
   * Show modal view with staff users and clients for message forwarding
   *
   * @return void
   */
  public function showForwardUsersModal()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_forward_message_title');
    $data['groups'] = $this->chat_model->getChatGroups();

    $this->load->view('prchat/includes/forward_to_modal', $data);
  }


  /**
   * Live Search staff.
   *
   * @return void
   */
  public function searchStaffForForward()
  {
    $search = $this->input->get('search');
    $staff = $this->chat_model->searchStaff($search);
    echo json_encode($staff);
  }


  /**
   * Load more staff members for pagination (AJAX)
   *
   * @return void
   */
  public function loadMoreStaffMembers()
  {
    $offset = abs((int) $this->input->get('offset'));
    echo json_encode($this->chat_model->loadMoreStaffMembers($offset));
  }


  /**
   * AJAX search endpoint for staff members.
   * Returns JSON [{id, name, subtext}] compatible with ajaxSelectPicker.
   */
  public function ajaxSearchStaff()
  {
    header('Content-Type: application/json');

    $q = $this->input->get('q') ?: '';
    $excludeIds = [];

    $excludeGroup = (int) $this->input->get('exclude_group');
    if ($excludeGroup > 0) {
      $members = $this->getCurrentGroupUsers($excludeGroup);
      foreach ($members as $m) {
        $excludeIds[] = (int) $m['member_id'];
      }
    }

    echo json_encode($this->chat_model->searchStaffAjax($q, $excludeIds));
  }

  /**
   * AJAX search endpoint for client contacts.
   * Returns JSON [{id, name, subtext}] compatible with ajaxSelectPicker.
   */
  public function ajaxSearchClients()
  {
    header('Content-Type: application/json');

    $q = $this->input->get('q') ?: '';
    echo json_encode($this->chat_model->searchClientsAjax($q));
  }

  /**
   * Live ajax search for chat messages for staff to staff and staff to client.
   *
   * @return void
   */
  public function searchMessages()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $id = $this->input->post('id');
    $table = $this->input->post('table');

    $name = (strpos($id, 'client') !== false)
      ? get_contact_full_name(str_replace('client_', '', $id))
      : get_staff_full_name($id);

    $data = [
      'id' => $id,
      'user_full_name' => $name,
      'messages' => json_encode($this->chat_model->getMessagesHistoryBetween($id, $table), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
    ];

    $this->load->view('prchat/includes/search_messages_modal', $data);
  }


  /**
   * Deletes conversation history from staff, clients or groups with all uploads
   */
  public function purgeConversations()
  {
    if (!chatStaffCanDelete()) {
      access_denied();
    }

    $type = $this->input->post('type');

    if ($type) {
      header('Content-Type: application/json');
      echo json_encode($this->chat_model->purgeConversations($type));
    }
  }

  /**
   * Get internal client notes for a contact (staff with access only).
   */
  public function get_client_notes()
  {
    $contact_id = (int) $this->input->get_post('contact_id');
    if ($contact_id <= 0 || !staff_can_access_contact_for_chat($contact_id)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'notes' => []]);
      return;
    }
    $notes = $this->chat_model->get_client_notes($contact_id);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'notes' => $notes]);
  }

  /**
   * Save a new client note (staff with access only).
   */
  public function save_client_note()
  {
    $contact_id = (int) $this->input->post('contact_id');
    $content = $this->input->post('note_content');
    if ($contact_id <= 0 || !staff_can_access_contact_for_chat($contact_id)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $id = $this->chat_model->add_client_note($contact_id, $content);
    header('Content-Type: application/json');
    echo json_encode(['success' => (bool) $id, 'id' => $id]);
  }

  /**
   * Update a client note (author only; staff must have access to contact).
   */
  public function update_client_note()
  {
    $note_id = (int) $this->input->post('note_id');
    $content = $this->input->post('note_content');
    if ($note_id <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $contact_id = $this->chat_model->get_contact_id_by_note_id($note_id);
    if ($contact_id === null || !staff_can_access_contact_for_chat($contact_id)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $ok = $this->chat_model->update_client_note($note_id, $content);
    header('Content-Type: application/json');
    echo json_encode(['success' => $ok]);
  }

  /**
   * Toggle emoji reaction for a chat message (staff/admin context).
   *
   * POST: message_id, emoji, message_type (staff|client|group)
   */
  public function addReaction()
  {
    $messageId = (int) $this->input->post('message_id');
    $emoji = (string) $this->input->post('emoji');
    $messageType = (string) $this->input->post('message_type');

    $allowedEmojis = ['👍', '❤️', '😂', '😮', '😢', '😡', '🎉', '🔥'];
    if ($messageId <= 0 || empty($emoji) || !in_array($emoji, $allowedEmojis, true)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }

    if (!in_array($messageType, ['staff', 'client', 'group'], true)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }

    $staffId = get_staff_user_id();
    if ($staffId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }

    // Reaction identity key must match frontend expectations
    $userKey = $messageType === 'client' ? 'staff_' . $staffId : (string) $staffId;

    $updatedReactions = $this->chat_model->toggleMessageReaction(
      $messageId,
      $emoji,
      $userKey,
      $messageType
    );

    // Broadcast reaction update to participants (via Pusher).
    if ($messageType === 'staff' || $messageType === 'client') {
      $payload = [
        'message_id' => $messageId,
        'message_type' => $messageType,
        'emoji' => $emoji,
        'reactions' => $updatedReactions,
        'reactor_key' => $userKey,
      ];

      // Staff clients (full chat and toggled chat) listen on presence-mychanel.
      if ($messageType === 'staff') { $this->triggerPusher('presence-mychanel', 'message-reaction', $payload); }

      // Client portal listens on presence-clients for staff<->client messages.
      if ($messageType === 'client') {
        $this->triggerPusher('presence-clients', 'message-reaction', $payload);
      }
    } elseif ($messageType === 'group') {
      // Resolve group channel name for group message reactions.
      $groupIdRow = $this->db->select('group_id')->from(db_prefix() . 'chatgroupmessages')->where('id', $messageId)->limit(1)->get()->row();
      $groupId = $groupIdRow && isset($groupIdRow->group_id) ? (int) $groupIdRow->group_id : 0;
      $groupName = $groupId > 0 ? $this->db->get_where(TABLE_CHATGROUPS, ['id' => $groupId])->row('group_name') : null;

      if ($groupName) {
        $payload = [
          'message_id' => $messageId,
          'message_type' => $messageType,
          'emoji' => $emoji,
          'reactions' => $updatedReactions,
          'reactor_key' => $userKey,
        ];
        $this->triggerPusher($groupName, 'message-reaction', $payload);
      }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'reactions' => $updatedReactions]);
  }

  /**
   * Delete a client note (author only; staff must have access to contact).
   */
  public function delete_client_note()
  {
    $note_id = (int) $this->input->post('note_id');
    if ($note_id <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $contact_id = $this->chat_model->get_contact_id_by_note_id($note_id);
    if ($contact_id === null || !staff_can_access_contact_for_chat($contact_id)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $ok = $this->chat_model->delete_client_note($note_id);
    header('Content-Type: application/json');
    echo json_encode(['success' => $ok]);
  }


  /**
   * Smart Choice Health Check for Chat module.
   */
  public function health_check()
  {
    if (!prchat_staff_can_chat()) {
      access_denied(PR_CHAT_MODULE_NAME);
    }
    $this->load->helper('prchat/prchat');
    $data = [];
    $data['title'] = 'Chat Health Check';
    $data['checks'] = [
      'Module Uploads Folder' => is_dir(PR_CHAT_MODULE_UPLOAD_FOLDER) && is_writable(PR_CHAT_MODULE_UPLOAD_FOLDER),
      'Group Uploads Folder' => is_dir(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER) && is_writable(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER),
      'Audio Uploads Folder' => is_dir(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER) && is_writable(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER),
      'CRM Project Media Folder' => pr_chat_ensure_directory(PR_CHAT_MEDIA_PROJECTS_FOLDER),
      'Allowed File Types Configured' => pr_chat_client_crm_attachment_types_string() !== '',
      'Pusher Enabled' => get_option('pusher_chat_enabled') == '1',
      'Staff Calls Enabled' => get_option('chat_staff_calls_enabled') == '1',
      'Video Calls Enabled' => get_option('chat_calls_video_enabled') == '1',
      'Group Media Copy Enabled' => get_option('prchat_copy_group_uploads_to_project_media') == '1',
      'Smart Choice Message Sound Enabled' => get_option('prchat_smart_message_sound_enabled') == '1',
      'Smart Choice Center Toast Enabled' => get_option('prchat_smart_center_toast_enabled') == '1',
      'Twilio Setting Present' => get_option('prchat_twilio_enabled') !== false,
      'Twilio Account SID Configured' => get_option('prchat_twilio_enabled') != '1' || get_option('prchat_twilio_account_sid') != '',
      'Twilio Phone Number Configured' => get_option('prchat_twilio_enabled') != '1' || get_option('prchat_twilio_phone_number') != '',
    ];
    $data['media_path'] = pr_chat_projects_media_path_display();
    $this->load->view('prchat/health_check', $data);
  }

  /**
   * Project media storage page.
   */
  public function project_media()
  {
    if (!prchat_staff_can_chat()) {
      access_denied(PR_CHAT_MODULE_NAME);
    }
    $this->load->helper('prchat/prchat');
    pr_chat_ensure_directory(PR_CHAT_MEDIA_PROJECTS_FOLDER);
    $data = [];
    $data['title'] = 'Project Media';
    $data['media_path'] = pr_chat_projects_media_path_display();
    $data['folders'] = is_dir(PR_CHAT_MEDIA_PROJECTS_FOLDER) ? array_values(array_filter(scandir(PR_CHAT_MEDIA_PROJECTS_FOLDER), static function ($item) {
      return $item !== '.' && $item !== '..' && is_dir(PR_CHAT_MEDIA_PROJECTS_FOLDER . '/' . $item);
    })) : [];
    $this->load->view('prchat/project_media', $data);
  }


  /**
   * Smart Choice: create a CRM task from a chat message or group comment.
   */
  public function createTaskFromChat()
  {
    if ($this->input->method(true) !== 'POST') {
      return $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'POST required.']));
    }
    if (!staff_can('create', 'tasks') && !staff_can('edit', 'tasks')) {
      access_denied('tasks');
    }

    $messageId = (int) $this->input->post('message_id');
    $messageType = (string) $this->input->post('message_type');
    $projectId = (int) $this->input->post('project_id');
    $title = trim((string) $this->input->post('title'));

    $messageText = $this->smartChoiceResolveMessageText($messageId, $messageType);
    if ($title === '') {
      $title = mb_substr(trim(strip_tags(html_entity_decode($messageText))), 0, 90);
      if ($title === '') {
        $title = 'Task from chat message';
      }
    }

    $description = "Created from Smart Choice Chat.\n\n" . strip_tags(html_entity_decode($messageText));
    $data = [
      'name' => $title,
      'description' => $description,
      'priority' => 2,
      'startdate' => date('Y-m-d'),
    ];
    if ($projectId > 0) {
      $data['rel_type'] = 'project';
      $data['rel_id'] = $projectId;
    }

    $this->load->model('tasks_model');
    $taskId = false;
    if (method_exists($this->tasks_model, 'add')) {
      $taskId = $this->tasks_model->add($data);
    }

    header('Content-Type: application/json');
    echo json_encode([
      'success' => (bool) $taskId,
      'task_id' => $taskId,
      'message' => $taskId ? 'Task created successfully.' : 'Task could not be created.',
    ]);
  }

  /**
   * Smart Choice: attach a chat comment or uploaded group photo/file to a CRM project folder.
   */
  public function attachChatItemToProject()
  {
    if ($this->input->method(true) !== 'POST') {
      return $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'POST required.']));
    }
    if (!prchat_staff_can_chat()) {
      access_denied(PR_CHAT_MODULE_NAME);
    }

    $projectId = (int) $this->input->post('project_id');
    $messageId = (int) $this->input->post('message_id');
    $messageType = (string) $this->input->post('message_type');
    $groupId = (int) $this->input->post('group_id');
    $fileName = basename((string) $this->input->post('file_name'));

    if ($projectId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Please select a project.']);
      return;
    }

    $projectDir = FCPATH . 'uploads/projects/' . $projectId;
    if (!is_dir($projectDir)) {
      @mkdir($projectDir, 0755, true);
      @file_put_contents($projectDir . '/index.html', '');
    }

    $copiedFile = '';
    if ($fileName !== '') {
      $possible = [
        PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER . '/' . $fileName,
        PR_CHAT_MODULE_UPLOAD_FOLDER . '/' . $fileName,
        PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/groups/' . $fileName,
      ];
      foreach ($possible as $src) {
        if (is_file($src)) {
          $destName = time() . '_chat_' . $fileName;
          if (@copy($src, $projectDir . '/' . $destName)) {
            $copiedFile = $destName;
          }
          break;
        }
      }
    }

    $messageText = $messageId > 0 ? $this->smartChoiceResolveMessageText($messageId, $messageType) : '';
    if ($messageText !== '') {
      $noteFile = $projectDir . '/chat_note_' . date('Ymd_His') . '.txt';
      @file_put_contents($noteFile, "Smart Choice Chat Note\nProject ID: {$projectId}\nGroup ID: {$groupId}\nMessage ID: {$messageId}\n\n" . strip_tags(html_entity_decode($messageText)));
    }

    log_activity('Smart Choice Chat item attached to project #' . $projectId . ' by staff #' . get_staff_user_id());

    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'file' => $copiedFile,
      'message' => 'Chat item attached to project successfully.',
    ]);
  }

  /**
   * Smart Choice: upload/replace group avatar image.
   */
  public function uploadGroupAvatar()
  {
    if ($this->input->method(true) !== 'POST') {
      return $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'POST required.']));
    }
    if (!is_admin() && !staff_can('edit', PR_CHAT_MODULE_NAME)) {
      access_denied(PR_CHAT_MODULE_NAME);
    }

    $groupId = (int) $this->input->post('group_id');
    if ($groupId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Missing group.']);
      return;
    }

    $group = $this->db->where('id', $groupId)->get(TABLE_CHATGROUPS)->row();
    if (!$group) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Group not found.']);
      return;
    }

    pr_chat_patch_upload_mimes_for_crm_files();
    $path = PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER . '/avatars';
    if (!is_dir($path)) {
      @mkdir($path, 0755, true);
      @file_put_contents($path . '/index.html', '');
    }

    $config = [
      'upload_path' => $path,
      'allowed_types' => 'jpg|jpeg|png|gif|webp|heic|bmp|svg',
      'max_size' => pr_chat_max_upload_size_kb(),
      'file_ext_tolower' => true,
      'remove_spaces' => true,
    ];
    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('group_avatar')) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => strip_tags($this->upload->display_errors())]);
      return;
    }

    $data = $this->upload->data();
    $relative = 'avatars/' . $data['file_name'];
    if (!$this->db->field_exists('group_image', TABLE_CHATGROUPS)) {
      $this->db->query("ALTER TABLE `" . TABLE_CHATGROUPS . "` ADD `group_image` VARCHAR(255) NULL DEFAULT NULL AFTER `group_name`");
    }
    $this->db->where('id', $groupId)->update(TABLE_CHATGROUPS, ['group_image' => $relative]);

    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'image' => module_dir_url('prchat', 'uploads/groups/' . $relative),
      'message' => 'Group photo updated successfully.',
    ]);
  }

  /**
   * Remove a group avatar without changing the chat background.
   */
  public function removeGroupAvatar()
  {
    if (!$this->input->is_ajax_request()) { show_404(); return; }
    if (!is_admin() && !staff_can('edit', PR_CHAT_MODULE_NAME)) { access_denied(PR_CHAT_MODULE_NAME); }
    $groupId = (int) $this->input->post('group_id');
    $group = $this->db->where('id', $groupId)->get(TABLE_CHATGROUPS)->row();
    if (!$group) { echo json_encode(['success' => false, 'message' => _l('chat_group_not_found')]); return; }
    if ($this->db->field_exists('group_image', TABLE_CHATGROUPS) && !empty($group->group_image)) {
      $path = PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER . '/' . ltrim($group->group_image, '/');
      if (is_file($path)) { @unlink($path); }
      $this->db->where('id', $groupId)->update(TABLE_CHATGROUPS, ['group_image' => null]);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => _l('chat_group_photo_removed')]);
  }

  /**
   * Smart Choice project picker for chat actions.
   */
  public function smartChoiceProjectPicker()
  {
    if ($this->input->method(true) !== 'POST') {
      return $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'POST required.']));
    }
    $q = trim((string) $this->input->get('q'));
    $this->db->select('id, name');
    $this->db->from(db_prefix() . 'projects');
    if ($q !== '') {
      $this->db->like('name', $q);
    }
    $this->db->order_by('id', 'DESC');
    $this->db->limit(50);
    $rows = $this->db->get()->result_array();
    $out = [];
    foreach ($rows as $row) {
      $out[] = ['id' => (int) $row['id'], 'text' => '#' . $row['id'] . ' - ' . $row['name']];
    }
    header('Content-Type: application/json');
    echo json_encode($out);
  }

  private function smartChoiceResolveMessageText($messageId, $messageType)
  {
    if ($messageId <= 0) {
      return '';
    }
    $table = db_prefix() . 'chatmessages';
    if ($messageType === 'group') {
      $table = db_prefix() . 'chatgroupmessages';
    } elseif ($messageType === 'client') {
      $table = db_prefix() . 'chatclientmessages';
    }
    if (!$this->db->table_exists($table)) {
      return '';
    }
    $row = $this->db->select('message')->where('id', $messageId)->get($table)->row();
    return $row ? (string) $row->message : '';
  }

  /**
   * Improve a draft chat message using the CRM chatbot/OpenAI configuration.
   * This endpoint is permission controlled and never sends the message automatically.
   */
  /**
   * Return last-message previews for CRM client contacts.
   * Staff-only endpoint used by the full chat sidebar.
   */
  public function getClientContactPreviews()
  {
    if (!is_staff_logged_in()) {
      return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode([]));
    }
    $raw = (string) $this->input->get('contact_ids');
    $ids = array_values(array_filter(array_map('intval', explode(',', $raw)), static function ($id) { return $id > 0; }));
    $ids = array_values(array_filter(array_slice(array_unique($ids), 0, 500), 'prchat_staff_can_contact'));
    $staffKey = 'staff_' . get_staff_user_id();
    $result = $this->chat_model->getClientContactPreviews($staffKey, $ids);
    return $this->output->set_content_type('application/json')->set_output(json_encode($result ?: []));
  }

  public function improve_message()
  {
    @ini_set('display_errors', '0');
    $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
    if ($this->input->method(true) !== 'POST') {
      return $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'POST required.']));
    }
    if (!staff_can('ai_assist', PR_CHAT_MODULE_NAME) && !is_admin()) {
      return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode([
        'success' => false,
        'message' => _l('chat_ai_assist_not_allowed'),
      ]));
    }

    $text = trim((string) $this->input->post('text'));
    $context = trim((string) $this->input->post('context'));
    $language = strtolower(trim((string) $this->input->post('language')));
    if ($text === '') {
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode([
        'success' => false,
        'message' => _l('chat_ai_assist_empty'),
      ]));
    }
    if (mb_strlen($text) > 5000) {
      $text = mb_substr($text, 0, 5000);
    }
    if (mb_strlen($context) > 6000) {
      $context = mb_substr($context, -6000);
    }

    if (!function_exists('chatbot_resolve_openai_key')) {
      $this->load->helper('prchat/prchat_chatbot_language');
    }
    $apiKey = trim((string) get_option('prchat_ai_api_key'));
    if ($apiKey === '') { $apiKey = trim((string) get_option('sc_ai_api_key')); }
    if ($apiKey === '') { $apiKey = trim((string) get_option('openai_api_key')); }
    if ($apiKey === '') { $apiKey = function_exists('chatbot_resolve_openai_key') ? trim((string) chatbot_resolve_openai_key()) : ''; }
    if ($apiKey === '') {
      return $this->output->set_status_header(503)->set_content_type('application/json')->set_output(json_encode([
        'success' => false,
        'message' => _l('chat_ai_assist_no_key'),
      ]));
    }

    $target = in_array($language, ['es', 'spanish'], true) ? 'Spanish' : (in_array($language, ['en', 'english'], true) ? 'English' : 'the same language as the draft');
    $system = 'You are the Smart Choice CRM writing assistant. Improve the staff draft for spelling, grammar, clarity, professionalism, and natural tone. Preserve the intended meaning and factual details. Use ' . $target . '. Use the conversation context only to resolve ambiguity. Return only the improved message without quotation marks, labels, or commentary.';
    $user = "Conversation context:\n" . ($context !== '' ? $context : '[No context supplied]') . "\n\nDraft message:\n" . $text;

    $payload = json_encode([
      'model' => get_option('prchat_ai_model') ?: (get_option('sc_ai_model') ?: (get_option('chatbot_openai_chat_model') ?: 'gpt-4o-mini')),
      'messages' => [
        ['role' => 'system', 'content' => $system],
        ['role' => 'user', 'content' => $user],
      ],
      'temperature' => 0.25,
      'max_tokens' => 900,
    ]);

    $coreBase = rtrim((string) get_option('sc_ai_api_base_url'), '/');
    $endpoint = $coreBase !== '' ? $coreBase . '/chat/completions' : 'https://api.openai.com/v1/chat/completions';
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 15,
      CURLOPT_TIMEOUT => 45,
      CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
      ],
      CURLOPT_POSTFIELDS => $payload,
    ]);
    $raw = curl_exec($ch);
    if ($raw === false) {
      $curlMessage = curl_error($ch);
      log_message('error', 'PRChat AI improve cURL error: ' . $curlMessage);
      if (PHP_VERSION_ID < 80500) { curl_close($ch); }
      return $this->output->set_status_header(502)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'AI connection failed: ' . $curlMessage]));
    }
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    if (PHP_VERSION_ID < 80500) {
      curl_close($ch);
    }
    $data = json_decode((string) $raw, true);
    $improved = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
    if ($status < 200 || $status >= 300 || $improved === '') {
      $apiMessage = isset($data['error']['message']) ? (string) $data['error']['message'] : '';
      log_message('error', 'PRChat AI improve failed. HTTP ' . $status . ' ' . $error . ' ' . $apiMessage);
      return $this->output->set_status_header(502)->set_content_type('application/json')->set_output(json_encode([
        'success' => false,
        'message' => $apiMessage !== '' ? $apiMessage : _l('chat_ai_assist_failed'),
      ]));
    }

    return $this->output->set_content_type('application/json')->set_output(json_encode([
      'success' => true,
      'text' => $improved,
    ], JSON_UNESCAPED_UNICODE));
  }



  /** Send an actual SMS to an employee and record every attempt. */
  public function send_staff_sms()
  {
    if ($this->input->method(true) !== 'POST') {
      return $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'POST required.']));
    }
    if (!is_staff_logged_in() || (!staff_can('create', PR_CHAT_MODULE_NAME) && !is_admin())) {
      return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'Permission denied.']));
    }
    if (get_option('prchat_sms_enabled') != '1' || get_option('prchat_twilio_enabled') != '1') {
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'SMS is disabled. Enable PRChat SMS and Twilio in Chat Settings.']));
    }
    $recipientId = (int) $this->input->post('staff_id');
    $message = trim((string) $this->input->post('message'));
    if ($recipientId <= 0 || $message === '') {
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'Employee and message are required.']));
    }
    $staff = $this->db->select('staffid,firstname,lastname,phonenumber')->where('staffid',$recipientId)->where('active',1)->get(db_prefix().'staff')->row();
    if (!$staff || trim((string)$staff->phonenumber) === '') {
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'The employee does not have a phone number in the CRM profile.']));
    }
    $sid=trim((string)get_option('prchat_twilio_account_sid'));
    $token=trim((string)get_option('prchat_twilio_auth_token'));
    $from=trim((string)get_option('prchat_twilio_phone_number'));
    $to=preg_replace('/[^0-9+]/','',(string)$staff->phonenumber);
    $table=db_prefix().'prchat_sms_log';
    $log=[
      'staff_id'=>(int)get_staff_user_id(),'recipient_staff_id'=>$recipientId,'to_number'=>$to,
      'from_number'=>$from,'message'=>$message,'provider'=>'twilio','status'=>'sending','created_at'=>date('Y-m-d H:i:s')
    ];
    $this->db->insert($table,$log); $logId=(int)$this->db->insert_id();
    if ($sid==='' || $token==='' || $from==='') {
      $this->db->where('id',$logId)->update($table,['status'=>'failed','error_message'=>'Twilio credentials or sending number are missing.','updated_at'=>date('Y-m-d H:i:s')]);
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'Twilio credentials or sending number are missing.','log_id'=>$logId]));
    }
    $url='https://api.twilio.com/2010-04-01/Accounts/'.rawurlencode($sid).'/Messages.json';
    $fields=['To'=>$to,'From'=>$from,'Body'=>$message];
    $callback=trim((string)get_option('prchat_sms_status_callback'));
    if ($callback!=='') { $fields['StatusCallback']=$callback; }
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_USERPWD=>$sid.':'.$token,CURLOPT_POSTFIELDS=>http_build_query($fields),CURLOPT_CONNECTTIMEOUT=>15,CURLOPT_TIMEOUT=>45]);
    $raw=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $curlError=curl_error($ch);
    if (PHP_VERSION_ID < 80500) { curl_close($ch); }
    $data=json_decode((string)$raw,true);
    $ok=$http>=200 && $http<300 && !empty($data['sid']);
    $status=$ok ? (string)($data['status'] ?? 'queued') : 'failed';
    $error=$ok ? null : (string)($data['message'] ?? $curlError ?: 'Twilio rejected the SMS request.');
    $this->db->where('id',$logId)->update($table,['provider_message_id'=>$data['sid'] ?? null,'status'=>$status,'error_message'=>$error,'updated_at'=>date('Y-m-d H:i:s')]);
    if (!$ok) {
      return $this->output->set_status_header($http ?: 502)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>$error,'log_id'=>$logId]));
    }
    log_activity('PRChat SMS sent to staff #'.$recipientId.' by staff #'.get_staff_user_id());
    return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true,'message'=>'SMS queued successfully.','log_id'=>$logId,'provider_message_id'=>$data['sid'],'status'=>$status]));
  }

  public function sms_log()
  {
    if (!is_staff_logged_in() || (!prchat_staff_can_chat() && !is_admin())) { access_denied(PR_CHAT_MODULE_NAME); }
    $table=db_prefix().'prchat_sms_log';
    $data['title']='Employee SMS Log';
    if (prchat_staff_own_scope()) { $this->db->group_start()->where('l.staff_id', get_staff_user_id())->or_where('l.recipient_staff_id', get_staff_user_id())->group_end(); }
    $data['sms_rows']=$this->db->select('l.*, CONCAT(s.firstname," ",s.lastname) AS recipient_name, CONCAT(a.firstname," ",a.lastname) AS sender_name')
      ->from($table.' l')->join(db_prefix().'staff s','s.staffid=l.recipient_staff_id','left')->join(db_prefix().'staff a','a.staffid=l.staff_id','left')
      ->order_by('l.id','DESC')->limit(500)->get()->result_array();
    $this->load->view('sms_log',$data);
  }

  /**
   * Place a real PSTN call to a staff member through Twilio and bridge it to
   * the logged-in staff member's CRM phone number.
   */
  public function start_twilio_staff_call()
  {
    if (!is_staff_logged_in()) {
      return $this->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'Authentication required.']));
    }
    if (get_option('prchat_twilio_enabled') != '1') {
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'Twilio Voice is not enabled in PRChat settings.']));
    }
    $toStaffId = (int) $this->input->post('staff_id');
    if ($toStaffId < 1 || $toStaffId === (int)get_staff_user_id()) {
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'Select another employee.']));
    }
    $this->db->select('staffid,firstname,lastname,phonenumber,active')->where('staffid',$toStaffId);
    $recipient = $this->db->get(db_prefix().'staff')->row();
    $this->db->select('staffid,firstname,lastname,phonenumber,active')->where('staffid',(int)get_staff_user_id());
    $caller = $this->db->get(db_prefix().'staff')->row();
    if (!$recipient || !$recipient->active || trim((string)$recipient->phonenumber)==='') {
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'The selected employee does not have an active CRM phone number.']));
    }
    if (!$caller || trim((string)$caller->phonenumber)==='') {
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'Your own CRM staff profile must contain a phone number so Twilio can bridge the call.']));
    }
    $sid=trim((string)get_option('prchat_twilio_account_sid'));
    $token=trim((string)get_option('prchat_twilio_auth_token'));
    $from=trim((string)get_option('prchat_twilio_phone_number'));
    if ($sid==='' || $token==='' || $from==='') {
      return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'Twilio Account SID, Auth Token, and voice-capable phone number are required.']));
    }
    $normalize=function($n){ return preg_replace('/[^0-9+]/','',(string)$n); };
    $to=$normalize($recipient->phonenumber); $bridge=$normalize($caller->phonenumber); $from=$normalize($from);
    $callerName=trim($caller->firstname.' '.$caller->lastname);
    $twiml='<Response><Say voice="alice">Incoming Smart Choice call from '.htmlspecialchars($callerName,ENT_XML1,'UTF-8').'. Please hold while we connect you.</Say><Dial callerId="'.htmlspecialchars($from,ENT_XML1,'UTF-8').'" timeout="30"><Number>'.htmlspecialchars($bridge,ENT_XML1,'UTF-8').'</Number></Dial></Response>';
    $url='https://api.twilio.com/2010-04-01/Accounts/'.rawurlencode($sid).'/Calls.json';
    $post=http_build_query(['To'=>$to,'From'=>$from,'Twiml'=>$twiml,'StatusCallback'=>admin_url('prchat/Prchat_Controller/twilio_voice_status'),'StatusCallbackEvent'=>'initiated ringing answered completed']);
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$post,CURLOPT_RETURNTRANSFER=>true,CURLOPT_USERPWD=>$sid.':'.$token,CURLOPT_HTTPAUTH=>CURLAUTH_BASIC,CURLOPT_TIMEOUT=>30,CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded']]);
    $raw=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
    $data=json_decode((string)$raw,true);
    if ($err || $http<200 || $http>=300) {
      $msg=$err ?: (isset($data['message'])?$data['message']:'Twilio rejected the call request.');
      log_message('error','PRChat Twilio Voice: '.$msg.' response='.$raw);
      return $this->output->set_status_header(502)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>$msg]));
    }
    return $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true,'message'=>'The real phone call was submitted to Twilio.','call_sid'=>$data['sid']??'']));
  }

  public function twilio_voice_status()
  {
    log_message('info','PRChat Twilio Voice status: '.json_encode($this->input->post(NULL,false)));
    return $this->output->set_status_header(204);
  }

}