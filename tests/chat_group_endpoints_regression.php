<?php
ob_start();
require __DIR__.'/chat_call_endpoints_regression.php';
define('CHAT_GROUP_PRESENCE_PREFIX','presence-');
function slugifyGroupName($name) {return trim(preg_replace('/[^A-Za-z0-9-]+/','-',trim($name)),'-');}
require $root.'/modules/prchat/controllers/Prchat_Controller.php';
$groupsController=(new ReflectionClass(Prchat_Controller::class))->newInstanceWithoutConstructor();
$groupsController->db=$db;$groupsController->chat_model=$model;$groupsController->session=$ci->session;
$groupsController->app_pusher=null;$groupsController->output=new ChatFixtureOutput();
$groupsController->input=new class extends ChatFixtureInput {function get($key=null,...$rest) {return null;}};
$groupsController->input->values=['q'=>'Fixture 38'];
ob_start();$groupsController->ajaxSearchStaff();$raw=ob_get_clean();$found=json_decode($raw,true);if($found===null)throw new RuntimeException($raw);
check(count($found)===1&&(int)$found[0]['id']===38,'POST member search respects the typed query');
$db->where('staffid',38)->update('audit_staff',['active'=>0]);
ob_start();$groupsController->ajaxSearchStaff();$found=json_decode(ob_get_clean(),true);
check($found===[],'Inactive staff excluded from searched members');
$db->where('staffid',38)->update('audit_staff',['active'=>1]);
$fixtureOptions['chat_members_can_create_groups']='1';
foreach (['   ','!!!',str_repeat('a',210)] as $name) {
 $groupsController->input->values=['group_name'=>$name,'members'=>[2]];
 $groupsController->output=new ChatFixtureOutput();$groupsController->addChatGroup();
 check($groupsController->output->status===422,'Invalid group name rejected before persistence');
}
$groupsController->input->values=['group_name'=>'New Fixture Group','members'=>[2,2]];
ob_start();$groupsController->addChatGroup();$created=json_decode(ob_get_clean(),true);
check(($created['data']['result']??'')==='success','Group creation returns saved state with realtime unavailable');
$groupId=$created['data']['group_id'];
check($db->where('group_id',$groupId)->count_all_results('audit_chatgroupmembers')===2,'Creator and selected member appear once');
$groupsController->output=new ChatFixtureOutput();$groupsController->addChatGroup();
check($groupsController->output->status===409&&$db->where('group_name','presence-New-Fixture-Group')->count_all_results('audit_chatgroups')===1,'Repeated group creation returns conflict instead of duplicate channel');
// A database failure must roll back the group as well as the member rows.
$db->query('ALTER TABLE audit_chatgroupmembers ADD CONSTRAINT fixture_positive_member CHECK (member_id > 0)');
$beforeGroups=$db->count_all_results('audit_chatgroups');$beforeMembers=$db->count_all_results('audit_chatgroupmembers');
ob_start();$model->addChatGroup(['created_by_id'=>7,'group_name'=>'presence-Failure'],['group_name'=>'presence-Failure','members'=>[7,-1]],null);$failed=json_decode(ob_get_clean(),true);
check(($failed['success']??true)===false&&$db->count_all_results('audit_chatgroups')===$beforeGroups&&$db->count_all_results('audit_chatgroupmembers')===$beforeMembers,'Failed membership insert rolls back the whole group');
echo "PASS POST member search, active member filter, group validation, offline creation, duplicate guard and atomic rollback\n";

ob_end_flush();
