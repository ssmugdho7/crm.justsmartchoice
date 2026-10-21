<?php
ob_start();
require __DIR__.'/chat_group_endpoints_regression.php';
define('PR_CHAT_MODULE_NAME','prchat');
function prchat_checkMessageIfFileExists($text) { return false; }
function staff_profile_image_url($id) { return '/staff/'.$id; }
function access_denied(...$args) { throw new RuntimeException('Forbidden'); }
$db->query('CREATE TEMPORARY TABLE audit_chatgroupmessages (id INT AUTO_INCREMENT PRIMARY KEY, sender_id INT, group_id INT, message TEXT, time_sent DATETIME, edited_at DATETIME, reactions TEXT)');
$db->query('ALTER TABLE audit_chatmessages ADD edited_at DATETIME, ADD reactions TEXT');
$groupsController->input=new class extends ChatFixtureInput { function method($upper=false) {return $upper?'POST':'post';} };
$id=$model->createGroupMessage(['sender_id'=>7,'group_id'=>$groupId,'message'=>'Before edit']);
$groupsController->input->values=['id'=>$id,'message'=>'After edit','group_id'=>$groupId];
ob_start();$groupsController->editMessage();$raw=ob_get_clean();$edited=json_decode($raw,true);if ($edited===null) throw new RuntimeException('Invalid edit response: '.$raw);
check($edited['success']===true&&$db->where('id',$id)->get('audit_chatgroupmessages')->row('message')==='After edit','Native group message edit uses actual group schema and own sender');
$groupsController->input->values=['id'=>$id,'message'=>'Wrong group','group_id'=>9999];
ob_start();$groupsController->editMessage();$denied=json_decode(ob_get_clean(),true);
check($denied['success']===false&&$db->where('id',$id)->get('audit_chatgroupmessages')->row('message')==='After edit','Group edit cannot cross group identity');
check($model->editMessage(1,'Changed fixture','audit_chatmessages','sender_id',7)['success']===false,'Staff cannot edit received message');
check($model->editMessage(2,'Own edit','audit_chatmessages','sender_id',7)['success']===true,'Staff can edit own message');
foreach (['pinned_staff','pinned_groups','pinned_clients'] as $name) {
 $first=$model->togglePinChat($name,'2');$second=$model->togglePinChat($name,'2');
 check($first['added']===true&&$second['added']===false,'Pin toggle persists and toggles off for '.$name);
}
foreach (['muted_staff','muted_groups','muted_clients'] as $name) {
 $first=$model->toggleMuteChat($name,'2');$settings=$model->getAllPinMuteSettings();$second=$model->toggleMuteChat($name,'2');
 check($first['muted']===true&&in_array('2',$settings[$name],true)&&$second['muted']===false,'Mute persists, reloads and toggles off for '.$name);
}
$history=$model->getMessagesHistoryBetween('client_33','chatclientmessages');
check(is_array($history)&&count($history)>0,'Native conversation search history can read permitted client messages');
check($model->deleteMessage($id,'group_id'.$groupId)===true&&$db->where('id',$id)->count_all_results('audit_chatgroupmessages')===0,'Native delete removes group text message');
check($model->deleteMessage(2,'2')===true&&$db->where('id',2)->count_all_results('audit_chatmessages')===0,'Native delete removes staff text message');
$clientRow=$db->where('sender_id','staff_7')->get('audit_chatclientmessages')->row();
check($model->deleteClientMessage($clientRow->id)===true&&$db->where('id',$clientRow->id)->count_all_results('audit_chatclientmessages')===0,'Native delete removes client text message');
echo "PASS native group/staff editing, pin/mute persistence, searchable history and text deletion fixtures\n";

$fixtureAdministrator=false;
$db->insert('audit_chatgroups',['created_by_id'=>8,'group_name'=>'presence-Foreign-Group']);$foreignGroup=$db->insert_id();
$groupsController->input->values=['group_id'=>$foreignGroup];
check(!prchat_authorize_staff_request('getGroupMessages',$groupsController->input),'Nonmember group history access denied');
check(!prchat_authorize_staff_request('uploadGroupAvatar',$groupsController->input),'Nonmember group photo changes denied');
check(prchat_staff_can_group($groupId),'Existing group membership still grants access');
$groupsController->input->values=['id'=>1,'message_id'=>1,'message_type'=>'staff'];
$fixtureCapabilities=['prchat'=>['edit'=>false,'delete'=>false]];
check(!prchat_authorize_staff_request('editMessage',$groupsController->input),'Edit capability enforced by backend dispatch');
check(!prchat_authorize_staff_request('deleteMessageScoped',$groupsController->input),'Delete capability enforced by backend dispatch');
$fixtureCapabilities=[];$fixtureAdministrator=true;
echo "PASS nonmember group and backend edit/delete permission denials\n";
ob_end_flush();
