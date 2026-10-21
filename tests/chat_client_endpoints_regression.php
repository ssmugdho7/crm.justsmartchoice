<?php
// Uses the native model and connection-local database fixtures; never sends live events.
require __DIR__.'/chat_database_regression.php';
$fixtureOptions=['pusher_chat_enabled'=>'1','chat_client_enabled'=>'1','chat_staff_can_access_clients'=>'1'];
function clickable($value) { return $value; }
function pr_chat_convertLinkImageToString($value) { return $value; }
function get_staff_full_name($id) { return 'Staff Fixture '.$id; }
function get_contact_full_name($id) { return 'Contact Fixture '.$id; }
function contact_profile_image_url($id) { return '/contact/'.$id; }
function get_customer_admins() { return [['staffid'=>7]]; }
function staffCanAccessClientsTab() { return true; }
function get_staff_customers($limit=30,$offset=0,$array=false) { return []; }
function getOwnClient($id) { return ['contact_id'=>$id,'firstname'=>'Fixture','lastname'=>'Contact']; }
foreach ([
 'contacts'=>'id INT PRIMARY KEY, userid INT, active INT, profile_image VARCHAR(100), firstname VARCHAR(60), lastname VARCHAR(60), title VARCHAR(60), last_login DATETIME',
 'clients'=>'userid INT PRIMARY KEY, active INT, company VARCHAR(100)',
 'customer_admins'=>'customer_id INT, staff_id INT',
 'chatclientmessages'=>'id INT AUTO_INCREMENT PRIMARY KEY, sender_id VARCHAR(60), reciever_id VARCHAR(60), message TEXT, time_sent DATETIME, viewed INT DEFAULT 0, viewed_at DATETIME, reactions TEXT, edited_at DATETIME',
] as $name=>$schema) { check($db->query('CREATE TEMPORARY TABLE audit_'.$name.' ('.$schema.')'), 'Client fixture '.$name); }
$db->insert('audit_contacts',['id'=>33,'userid'=>100,'active'=>1]);
$db->insert('audit_contacts',['id'=>34,'userid'=>101,'active'=>1]);
$db->insert('audit_contacts',['id'=>35,'userid'=>100,'active'=>0]);
$db->insert('audit_clients',['userid'=>100,'active'=>1]);$db->insert('audit_clients',['userid'=>101,'active'=>1]);
$db->insert('audit_customer_admins',['customer_id'=>100,'staff_id'=>7]);
class ClientsController { public $db; public $chat_model; public $input; public $output; public $app_pusher; }
class ChatFixtureOutput {
 public $status=200; public $data=[];
 function set_status_header($status) {$this->status=$status;return $this;}
 function set_content_type(...$args) {return $this;}
 function set_output($data) {$this->data=json_decode($data,true);return $this;}
 function _display() { throw new RuntimeException('HTTP '.$this->status); }
}
class ChatFixtureInput {
 public $values=[];
 function get($key=null,...$rest) {return $key===null?$this->values:($this->values[$key]??null);}
 function post($key=null,...$rest) {return $key===null?$this->values:($this->values[$key]??null);}
 function get_post($key) {return $this->post($key)??$this->get($key);}
 function is_ajax_request() {return true;}
}
require $root.'/modules/prchat/controllers/Prchat_ClientsController.php';
$controller=(new ReflectionClass(Prchat_ClientsController::class))->newInstanceWithoutConstructor();
$controller->db=$db;$controller->chat_model=$model;$controller->input=new ChatFixtureInput();$controller->output=new ChatFixtureOutput();
function request($method,array $values) {
 $c=$GLOBALS['controller'];$c->input->values=$values;$c->output=new ChatFixtureOutput();$c->$method();return $c->output;
}
$stored=request('initClientChat',['from'=>'staff_7','to'=>'client_33','typing'=>'false','client_message'=>'Hello 👋 <script>unsafe</script>']);
check($stored->status===200&&$stored->data['id']>0, 'Staff can persist a client message without realtime');
$id=$stored->data['id'];
$read=request('getMutualMessages',['sender_id'=>'client_33','reciever_id'=>'staff_7','limit'=>20]);
check(count($read->data)===1&&$read->data[0]['sender_id']==='staff_7'&&strpos($read->data[0]['message'],'<script>')===false, 'History returns saved content with safe markup');
check(request('getMutualMessages',['sender_id'=>'client_33','reciever_id'=>'staff_8'])->status===403, 'Staff cannot fetch another staff conversation');
check(request('initClientChat',['from'=>'client_33','to'=>'staff_7','typing'=>'false','client_message'=>'Spoof'])->status===403, 'Staff cannot impersonate a contact');
check(request('getMutualMessages',['sender_id'=>'client_35','reciever_id'=>'staff_7'])->status===403, 'Inactive contacts denied');
$fixtureCapabilities=['customers'=>['view'=>false]];
check(request('getMutualMessages',['sender_id'=>'client_34','reciever_id'=>'staff_7'])->status===403, 'Unassigned customer denied with own customer access');
$fixtureCapabilities=[];
$count=$db->count_all_results('audit_chatclientmessages');
request('initClientChat',['from'=>'staff_7','to'=>'client_33','typing'=>'true','client_message'=>'Draft']);
check($db->count_all_results('audit_chatclientmessages')===$count, 'Typing never persists a message');
$fixtureStaffLoggedIn=false;$fixtureClientLoggedIn=true;
check(request('getMutualMessages',['sender_id'=>'client_34','reciever_id'=>'staff_7'])->status===403, 'Contact cannot read another contact conversation');
check(request('getMutualMessages',['sender_id'=>'client_33','reciever_id'=>'staff_8'])->status===403, 'Contact cannot reach hidden staff');
check(request('loadMoreClients',[])->status===403&&request('searchClients',['search'=>'Fixture'])->status===403, 'Client cannot enumerate customers');
check(request('initClientChat',['from'=>'staff_7','to'=>'client_33','typing'=>'false','client_message'=>'Spoof'])->status===403, 'Contact cannot impersonate staff');
$reply=request('initClientChat',['from'=>'client_33','to'=>'staff_7','typing'=>'false','client_message'=>'Reply 👍']);
check($reply->data['id']>$id,'Contact reply persists with session identity');
$seen=request('updateClientUnreadMessages',['id'=>'staff_7','contact_id'=>34]);
check($seen->data['success']===true&&$db->where('id',$id)->get('audit_chatclientmessages')->row('viewed')==1, 'Contact read receipt uses session contact, ignoring spoofed ID');
check($db->where('id',$reply->data['id'])->get('audit_chatclientmessages')->row('viewed')==0,'Contact receipt cannot mark its outgoing reply as seen');
check(request('addReaction',['message_id'=>$id,'emoji'=>'👍'])->data['success']===true,'Contact reacts to its own conversation');
check(request('addReaction',['message_id'=>$id,'emoji'=>'invalid'])->status===422,'Unsupported reaction rejected');
$foreign=$model->createMessage(['sender_id'=>'staff_8','reciever_id'=>'client_34','message'=>'Foreign'], 'audit_chatclientmessages');
check(request('addReaction',['message_id'=>$foreign,'emoji'=>'👍'])->status===403,'Foreign message reactions denied');
check(request('editClientMessage',['id'=>$id,'message'=>'Change staff message'])->status===403,'Contact cannot edit the staff message');
check(request('editClientMessage',['id'=>$reply->data['id'],'message'=>'Edited reply'])->data['success']===true,'Contact can edit its own reply');
check(request('uploadMethod',['send_to'=>'staff_8'])->status===403,'Unauthorized upload denied before filesystem access');
$fixtureStaffLoggedIn=true;$fixtureClientLoggedIn=false;
check(request('updateClientUnreadMessages',['id'=>33])->data['success']===true,'Staff can acknowledge the incoming client reply');
check(request('getClientUnreadMessages',[])->data===['result'=>false],'Client unread endpoint retains UI payload shape');
// Database dates must retain the offset in effect on the actual message date (DST included).
date_default_timezone_set('America/New_York');
$timestamp=new ReflectionMethod(Prchat_model::class,'chatTimestamp');$timestamp->setAccessible(true);
check($timestamp->invoke($model,'2026-07-01 12:00:00')==='2026-07-01T12:00:00-04:00','Summer date offset retained');
check($timestamp->invoke($model,'2026-01-01 12:00:00')==='2026-01-01T12:00:00-05:00','Winter date offset retained');
check($timestamp->invoke($model,'0000-00-00 00:00:00')===null,'Missing timestamp never renders an invalid date');
echo "PASS native client send/history/read/edit/reaction paths; cross-user/customer denials and DST timestamps\n";

$fixtureStaffLoggedIn=false;$fixtureClientLoggedIn=true;
$controller->app_pusher=new class {
 public $identity;
 function presence_auth($channel,$socket,$identity,$data) { $this->identity=$identity;return '{}'; }
};
request('pusherCustomersAuth',['channel_name'=>'presence-clients','socket_id'=>'fixture']);
check($controller->app_pusher->identity==='client_33','Client presence identity matches staff UI contact IDs');
$fixtureStaffLoggedIn=true;$fixtureClientLoggedIn=false;

$count=$db->count_all_results('audit_chatclientmessages');
check(request('initClientChat',['from'=>'staff_7','to'=>'client_33','typing'=>'true'])->status===200,'Typing payload does not need message text');
check(request('initClientChat',['from'=>'staff_7','to'=>'client_33','typing'=>'null'])->status===200,'Stop-typing payload does not need message text');
check(request('initClientChat',['from'=>'staff_7','to'=>'client_33','typing'=>'false','client_message'=>' '])->status===422,'Empty sends never report persisted success');
check($db->count_all_results('audit_chatclientmessages')===$count,'Typing and invalid sends do not insert rows');
$db->where('id',33)->update('audit_contacts',['firstname'=>'Findable','lastname'=>'Contact']);
$search=request('searchClients',['search'=>'Findable']);
check(count($search->data)===1&&(int)$search->data[0]['contact_id']===33,'Native client search returns the authorized matching contact');
check(request('loadMoreClients',['offset'=>30])->data===['customers'=>[]],'Pagination endpoint retains the expected customers shape');
$messages=[];
for($i=0;$i<25;$i++) $messages[]=$model->createMessage(['sender_id'=>'client_33','reciever_id'=>'staff_7','message'=>'Page '.$i], 'audit_chatclientmessages');
$page1=request('getMutualMessages',['sender_id'=>'client_33','reciever_id'=>'staff_7','limit'=>20]);
$page2=request('getMutualMessages',['sender_id'=>'client_33','reciever_id'=>'staff_7','limit'=>10,'offset'=>20]);
check(count($page1->data)===20&&count($page2->data)>0&&!array_intersect(array_column($page1->data,'id'),array_column($page2->data,'id')),'History pagination returns older rows without duplicates');
$db->query("ALTER TABLE audit_chatclientmessages ADD CONSTRAINT fixture_reject_message CHECK (message != 'RejectFixture')");
$failure=request('initClientChat',['from'=>'staff_7','to'=>'client_33','typing'=>'false','client_message'=>'RejectFixture']);
check($failure->status===500&&!isset($failure->data['id']),'Native DB exception cannot fabricate send success');
echo "PASS typing, empty-send validation, client search/pagination and failed-save checks\n";

$fixtureStaffLoggedIn=false;$fixtureClientLoggedIn=true;
$clearReaction=request('addReaction',['message_id'=>$id,'emoji'=>'👍']);
check($clearReaction->status===200&&$clearReaction->data['success']===true&&$clearReaction->data['reactions']===null,'Clicking an existing reaction clears it successfully');
$fixtureStaffLoggedIn=true;$fixtureClientLoggedIn=false;

$db->query('ALTER TABLE audit_chatclientmessages ADD CONSTRAINT fixture_reject_reaction CHECK (reactions IS NULL)');
$reactionFailure=request('addReaction',['message_id'=>$id,'emoji'=>'🔥']);
check($reactionFailure->status===500&&!isset($reactionFailure->data['success']),'Reaction database failures do not report a saved reaction');
echo "PASS reaction removal and rejected-save response checks\n";
