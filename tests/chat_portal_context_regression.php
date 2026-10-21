<?php
// Native controller/model fixtures; no live messages or persistent database writes.
require __DIR__.'/chat_client_endpoints_regression.php';
$ci->session = new class {
    public function userdata($key) { return $key === 'logged_in_as_client' ? ($GLOBALS['fixtureClientPreview'] ?? false) : 7; }
};
$fixtureStaffLoggedIn = true;
$fixtureClientLoggedIn = true;
$fixtureClientPreview = true;
$context = ['chat_context' => 'client'];
$controller->app_pusher = new class { public $events = []; function trigger($channels, $event, $payload) { $this->events[$event] = $payload; } };
$previewReply = request('initClientChat', $context + ['from'=>'client_33','to'=>'staff_7','typing'=>'false','client_message'=>'Preview client reply']);
check($previewReply->status === 200 && !empty($previewReply->data['id']), 'Native login-as-client preview can reply with its contact session');
$previewId = $previewReply->data['id'];
check($db->where('id', $previewId)->get('audit_chatclientmessages')->row('sender_id') === 'client_33', 'Preview writes contact identity, not the coexisting staff identity');
check($controller->app_pusher->events['send-event']['contact_full_name'] === 'Contact Fixture 33', 'Incoming contact events retain the contact name for the staff sidebar');
check(request('getStaffUnreadMessages', $context)->status === 200, 'Preview client can fetch its unread messages');
check(request('editClientMessage', $context + ['id'=>$previewId,'message'=>'Edited preview reply'])->data['success'] === true, 'Preview can edit its own contact reply');
check(request('editClientMessage', $context + ['id'=>$id,'message'=>'Forbidden edit'])->status === 403, 'Preview cannot edit staff-authored messages');
check($db->query('ALTER TABLE audit_chatclientmessages MODIFY reactions TEXT NULL'), 'Restore reaction fixture storage');
check(request('addReaction', $context + ['message_id'=>$id,'emoji'=>'🎉'])->data['success'] === true, 'Preview reaction succeeds');
$reacted = json_decode($db->where('id',$id)->get('audit_chatclientmessages')->row('reactions'), true);
check(in_array('client_33', $reacted['🎉'], true), 'Preview reaction belongs to contact identity');
check(request('initClientChat', $context + ['from'=>'client_34','to'=>'staff_7','typing'=>'false','client_message'=>'Spoof'])->status === 403, 'Preview cannot impersonate another contact');
check(request('getMutualMessages', $context + ['sender_id'=>'client_33','reciever_id'=>'staff_8'])->status === 403, 'Preview cannot read a hidden staff conversation');
check(request('uploadMethod', $context + ['send_to'=>'staff_8'])->status === 403, 'Preview cannot upload to hidden staff');
check(request('searchClients', $context + ['search'=>'Findable'])->status === 403, 'Client preview context cannot enumerate customers');
check(request('updateClientUnreadMessages', $context + ['id'=>'staff_7'])->status === 200, 'Preview receipts use contact identity');
check(request('updateClientUnreadMessages', $context + ['id'=>'7'])->status === 200, 'Customer widget numeric staff IDs mark messages read');
check(request('updateClientUnreadMessages', $context + ['id'=>'8'])->status === 403, 'Numeric receipt IDs cannot access hidden staff');
check(request('updateClientUnreadMessages', $context + ['id'=>['7']])->status === 403, 'Malformed receipt IDs are rejected');
check(request('initClientChat', ['from'=>'staff_7','to'=>'client_33','typing'=>'false','client_message'=>'Staff still works during preview'])->status === 200, 'Admin chat retains staff identity without portal context');
$fixtureClientPreview = false;
check(request('initClientChat', $context + ['from'=>'client_33','to'=>'staff_7','typing'=>'false','client_message'=>'Unapproved preview'])->status === 403, 'A context flag alone cannot switch a staff request to a contact');
$fixtureClientPreview = true;
$fixtureClientLoggedIn = false;
check(request('initClientChat', $context + ['from'=>'client_33','to'=>'staff_7','typing'=>'false','client_message'=>'No contact session'])->status === 403, 'Portal context requires an authenticated contact session');
echo "PASS native dual-session portal identity, sends, edits, reactions, receipts and cross-account denials\n";
