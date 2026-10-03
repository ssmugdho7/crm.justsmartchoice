<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Read access is shared by navigation, staff lookup, endpoints and subscriptions. */
function prchat_staff_can_chat($staffId = '')
{
    $staffId = $staffId === '' ? (int) get_staff_user_id() : (int) $staffId;
    return $staffId > 0 && (staff_can('view', 'prchat', $staffId) || staff_can('view_own', 'prchat', $staffId));
}

function prchat_staff_available_for_chat($staffId)
{
    if (!prchat_staff_can_chat($staffId)) { return false; }
    $CI = &get_instance();
    return $CI->db->where('staffid', (int) $staffId)->where('active', 1)->count_all_results(db_prefix() . 'staff') > 0;
}

function prchat_staff_own_scope($staffId = '')
{
    return prchat_staff_can_chat($staffId) && !staff_can('view', 'prchat', $staffId);
}

function prchat_staff_can_group($groupId, $staffId = '')
{
    $staffId = $staffId === '' ? (int) get_staff_user_id() : (int) $staffId;
    if (!is_scalar($groupId) || (int) $groupId <= 0 || !prchat_staff_can_chat($staffId)) { return false; }
    $CI = &get_instance();
    $group = $CI->db->select('id,created_by_id')->where('id', (int) $groupId)->get(db_prefix() . 'chatgroups')->row();
    if (!$group) { return false; }
    if (is_admin($staffId) || (int) $group->created_by_id === $staffId) { return true; }
    return $CI->db->where('group_id', (int) $groupId)->where('member_id', $staffId)
        ->count_all_results(db_prefix() . 'chatgroupmembers') > 0;
}

function prchat_staff_can_contact($contactId, $staffId = '')
{
    $staffId = $staffId === '' ? (int) get_staff_user_id() : (int) $staffId;
    if (!is_scalar($contactId) || (int) $contactId <= 0 || !prchat_staff_can_chat($staffId)
        || get_option('chat_client_enabled') != '1'
        || (!is_admin($staffId) && get_option('chat_staff_can_access_clients') != '1')) { return false; }
    $CI = &get_instance();
    $contact = $CI->db->select('c.userid')->from(db_prefix() . 'contacts c')
        ->join(db_prefix() . 'clients a', 'a.userid=c.userid')->where('c.id', (int) $contactId)
        ->where('c.active', 1)->where('a.active', 1)->get()->row();
    if (!$contact) { return false; }
    if (staff_can('view', 'customers', $staffId) && !prchat_staff_own_scope($staffId)) { return true; }
    return $CI->db->where('customer_id', $contact->userid)->where('staff_id', $staffId)
        ->count_all_results(db_prefix() . 'customer_admins') > 0;
}

function prchat_staff_can_message($type, $messageId)
{
    $tables = ['staff' => 'chatmessages', 'client' => 'chatclientmessages', 'group' => 'chatgroupmessages'];
    if (!isset($tables[$type]) || !is_scalar($messageId) || (int) $messageId <= 0) { return false; }
    $CI = &get_instance();
    $row = $CI->db->where('id', (int) $messageId)->get(db_prefix() . $tables[$type])->row();
    if (!$row) { return false; }
    if ($type === 'group') { return prchat_staff_can_group($row->group_id); }
    $key = $type === 'client' ? 'staff_' . get_staff_user_id() : (string) get_staff_user_id();
    $participant = (string) $row->sender_id === $key || (string) $row->reciever_id === $key;
    if ($type === 'client') {
        $peer = (string) $row->sender_id === $key ? (string) $row->reciever_id : (string) $row->sender_id;
        return $participant && strpos($peer, 'client_') === 0 && prchat_staff_can_contact(substr($peer, 7));
    }
    return $participant;
}

/** Return false before dispatching a method, including manually constructed requests. */
function prchat_authorize_staff_request($method, $input)
{
    if (!prchat_staff_can_chat() || get_option('pusher_chat_enabled') != '1') { return false; }
    $method = strtolower($method);
    $capabilities = [
        'create' => ['addchatgroup', 'staff_announcement', 'staff_get_selected_members', 'clients_announcement', 'clients_announcement_message', 'send_staff_sms'],
        'edit' => ['editmessage', 'editclientmessage', 'renamechatgroup', 'updatechatgroupassociation', 'uploadgroupavatar', 'removegroupavatar', 'addchatgroupmembers', 'addnewchatgroupmembersmodal'],
        'delete' => ['deletemessage', 'deleteclientmessage', 'deletemessagescoped', 'deletechatconversation', 'removechatgroupuser', 'groupdeletemembers', 'purgeconversations'],
        'delete_groups' => ['deletegroup'],
        'ai_assist' => ['improve_message'],
    ];
    foreach ($capabilities as $capability => $methods) {
        if (in_array($method, $methods, true) && !staff_can($capability, 'prchat')) { return false; }
    }
    if (in_array($method, ['health_check', 'project_media'], true) && !staff_can('view', 'settings')) { return false; }
    if (prchat_staff_own_scope() && in_array($method, ['purgeconversations', 'exportcsv', 'staff_announcement', 'staff_get_selected_members', 'clients_announcement', 'clients_announcement_message'], true)) { return false; }
    foreach (['group_id', 'groupId', 'to_group'] as $key) {
        $groupId = $input->get_post($key);
        if ($groupId !== null && $groupId !== '' && !prchat_staff_can_group($groupId)) { return false; }
    }
    $requiredGroups = ['getgroupmessages','getgroupmessageshistory','getgroupsharedfiles','getchatgroupmembersasjson','getgroupusers','getcurrentgroupusers','addnewchatgroupmembersmodal','groupdeletemembers','updatechatgroupassociation','initiategroupchat','deletegroup','removechatgroupuser','chatmemberleavegroup','addchatgroupmembers','uploadgroupavatar','removegroupavatar'];
    if (in_array($method, $requiredGroups, true) && !prchat_staff_can_group($input->get_post('group_id'))) { return false; }
    $groupId = $input->get_post('group_id') ?: $input->get_post('groupId');
    $groupName = $input->get_post('group_name');
    if ($groupId && $groupName) {
        $CI = &get_instance();
        $row = $CI->db->select('group_name')->where('id', (int) $groupId)->get(db_prefix() . 'chatgroups')->row();
        if (!$row || $row->group_name !== $groupName) { return false; }
    }
    if (in_array($method, ['searchmessages', 'deletechatconversation', 'converttoticket'], true)) {
        $peer = $input->post('id');
        if (is_string($peer) && strpos($peer, 'client_') === 0 && !prchat_staff_can_contact(substr($peer, 7))) { return false; }
        $table = $input->post('table');
        if ($table !== null && !in_array($table, ['chatmessages', 'chatclientmessages', 'chatgroupmessages'], true)) { return false; }
        if ($table === 'chatgroupmessages' && !prchat_staff_can_group($peer)) { return false; }
    }
    if ($method === 'groupuploadmethod' && !prchat_staff_can_group($input->post('to_group'))) { return false; }
    if ($method === 'renamechatgroup' && !prchat_staff_can_group($input->post('groupId'))) { return false; }
    if (in_array($method, ['deletemessage', 'editmessage', 'editclientmessage', 'deleteclientmessage', 'addreaction', 'deletemessagescoped'], true)) {
        $type = in_array($method, ['addreaction', 'deletemessagescoped'], true) ? $input->post('message_type')
            : (strpos($method, 'client') !== false ? 'client' : ($input->post('group_id') ? 'group' : 'staff'));
        $id = $input->post('message_id') ?: $input->post('id');
        if (!prchat_staff_can_message($type, $id)) { return false; }
    }
    if ($method === 'mark_messages_as_read' && $input->post('type') === 'client') {
        if (!prchat_staff_can_contact(str_replace('client_', '', (string) $input->post('contact_id')))) { return false; }
    }
    if (in_array($method, ['addchatgroup', 'addchatgroupmembers'], true)) {
        $members = $input->post('members');
        if (!is_array($members) || !$members) { return false; }
        foreach ($members as $member) { if (!is_scalar($member) || !ctype_digit((string) $member) || !prchat_staff_available_for_chat($member)) { return false; } }
    }
    if ($method === 'pushermentionevent' && !prchat_staff_can_group($input->post('group_id'))) { return false; }
    if ($method === 'getsharedfiles') {
        $own = (string) $input->post('own_id');
        if (!in_array($own, [(string) get_staff_user_id(), 'staff_' . get_staff_user_id()], true)) { return false; }
        $peer = (string) $input->post('contact_id');
        if (strpos($peer, 'client_') === 0 && !prchat_staff_can_contact(substr($peer, 7))) { return false; }
    }
    if ($method === 'getmessages') {
        if ((int) $input->get('from') !== (int) get_staff_user_id() && (int) $input->get('to') !== (int) get_staff_user_id()) { return false; }
    }
    return true;
}

/** Never authorize an arbitrary private/presence channel supplied by a browser. */
function prchat_staff_can_subscribe($channel)
{
    if (!is_string($channel) || !prchat_staff_can_chat()) { return false; }
    $id = (int) get_staff_user_id();
    if (in_array($channel, ['presence-mychanel', 'private-prchat-staff-' . $id, 'private-prchat-groups-' . $id, 'private-prchat-receipts-' . $id, 'private-calls-staff-' . $id], true)) { return true; }
    if (in_array($channel, ['presence-clients', 'private-prchat-clients-staff-' . $id], true)) {
        return get_option('chat_client_enabled') == '1' && (is_admin() || get_option('chat_staff_can_access_clients') == '1');
    }
    $CI = &get_instance();
    $group = $CI->db->select('id')->where('group_name', $channel)->get(db_prefix() . 'chatgroups')->row();
    return $group && prchat_staff_can_group($group->id);
}

/** Route message contents only to authorized participants; presence carries online status only. */
function prchat_event_channels($channel, $event, array $payload)
{
    $CI = &get_instance();
    $recipients = [];
    if ($channel === 'group-chat') {
        $groupId = (int) ($payload['group_id'] ?? 0);
        $members = $CI->db->select('member_id')->where('group_id', $groupId)->get(db_prefix() . 'chatgroupmembers')->result_array();
        $ids = array_merge(array_column($members, 'member_id'), (array) ($payload['user_ids'] ?? []), (array) ($payload['members'] ?? []), [(int) ($payload['user_id'] ?? 0), (int) ($payload['member_id'] ?? 0)]);
        foreach ($ids as $id) { if (prchat_staff_available_for_chat($id)) { $recipients[] = 'private-prchat-groups-' . (int) $id; } }
    } elseif ($channel === 'user_messages') {
        foreach ($payload as $message) {
            $message = (array) $message;
            foreach (['sender_id', 'reciever_id'] as $key) {
                $identity = (string) ($message[$key] ?? '');
                if (strpos($identity, 'client_') === 0) { $recipients[] = 'private-prchat-receipts-contact-' . (int) substr($identity, 7); continue; }
                $id = (int) str_replace('staff_', '', $identity);
                if (prchat_staff_available_for_chat($id)) { $recipients[] = 'private-prchat-receipts-' . $id; }
            }
        }
    } elseif (in_array($channel, ['presence-mychanel', 'presence-clients'], true)) {
        $client = $channel === 'presence-clients';
        $from = $payload['from'] ?? null;
        $to = $payload['to'] ?? null;
        if (!$from && !$to && !empty($payload['message_id'])) {
            $row = $CI->db->select('sender_id,reciever_id')->where('id', (int) $payload['message_id'])
                ->get(db_prefix() . ($client ? 'chatclientmessages' : 'chatmessages'))->row();
            if ($row) { $from = $row->sender_id; $to = $row->reciever_id; }
        }
        if ($event === 'message-hidden') {
            $from = ($client ? (($payload['viewer_type'] ?? 'staff') === 'client' ? 'client_' : 'staff_') : '') . (int) ($payload['viewer_id'] ?? 0);
            $to = null;
        }
        foreach ([$from, $to] as $identity) {
            if ($client && is_string($identity) && strpos($identity, 'client_') === 0) {
                $recipients[] = 'private-prchat-clients-contact-' . (int) substr($identity, 7);
            } else {
                $id = (int) str_replace('staff_', '', (string) $identity);
                if ($id > 0 && prchat_staff_available_for_chat($id)) {
                    $recipients[] = ($client ? 'private-prchat-clients-staff-' : 'private-prchat-staff-') . $id;
                }
            }
        }
    } else {
        // Group channels are authenticated against current membership.
        return [$channel];
    }
    return array_values(array_unique($recipients));
}

function prchat_trigger_event($pusher, $channel, $event, array $payload)
{
    if (!is_object($pusher)) { return false; }
    $channels = prchat_event_channels($channel, $event, $payload);
    if (!$channels) { return false; }
    $success = true;
    foreach (array_chunk($channels, 100) as $batch) {
        try { $pusher->trigger($batch, $event, $payload); }
        catch (Throwable $e) { log_message('error', 'PRChat event delivery failed: ' . $e->getMessage()); $success = false; }
    }
    return $success;
}
