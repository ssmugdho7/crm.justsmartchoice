<?php
// Native CodeIgniter/MySQL verification. Every database mutation targets connection-local TEMPORARY tables.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__);define('BASEPATH',$root.'/system/');define('APPPATH',$root.'/application/');define('ENVIRONMENT','testing');
function log_message(...$args){}function is_php($version){return version_compare(PHP_VERSION,$version,'>=');}
function show_error($message){throw new RuntimeException($message);}function db_prefix(){return 'audit_jitsi_';}
function &get_instance(){return $GLOBALS['ci'];}
class CI_Model {function __get($key){return get_instance()->{$key};}}
class App_Model extends CI_Model {function __construct(){}}
$options=['google_meet_google_access_token'=>'private-fixture-token'];$staffId=42;
function get_option($key){return $GLOBALS['options'][$key]??'';}
function add_option($key,$value){if(!array_key_exists($key,$GLOBALS['options']))$GLOBALS['options'][$key]=$value;}
function update_option($key,$value){$GLOBALS['options'][$key]=$value;}
function is_staff_logged_in(){return true;}function get_staff_user_id(){return $GLOBALS['staffId'];}
function html_escape($text){return htmlspecialchars((string)$text,ENT_QUOTES,'UTF-8');}
function check($value,$label){if(!$value)throw new RuntimeException($label);$GLOBALS['checks']++;}
$checks=0;
require $root.'/system/database/DB.php';
$siteRoot=getenv('CRM_SITE_ROOT');if(!$siteRoot||!is_file($siteRoot.'/application/config/app-config.php'))throw new RuntimeException('Set CRM_SITE_ROOT to a private local site checkout');
require $siteRoot.'/application/config/app-config.php';
$params=['hostname'=>APP_DB_HOSTNAME,'username'=>APP_DB_USERNAME,'password'=>APP_DB_PASSWORD,'database'=>APP_DB_NAME,'dbdriver'=>'mysqli','dbprefix'=>db_prefix(),'pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>false];
DB($params,true)->close();
class JitsiFixtureDatabase extends CI_DB_mysqli_driver {
    public $failChildDelete=false;
    public function query($sql,$binds=false,$return_object=null){
        if(preg_match('/^CREATE TABLE `?(audit_jitsi_\w+)/i',$sql,$match)){
            $sql=preg_replace('/^CREATE TABLE /i','CREATE TEMPORARY TABLE ',$sql);
            $this->data_cache['table_names'][]=$match[1];
        }
        if($this->failChildDelete&&strpos($sql,'DELETE FROM `audit_jitsi_google_meet_comments`')===0){$sql='DELETE FROM audit_jitsi_nonexistent_fixture';}
        return parent::query($sql,$binds,$return_object);
    }
}
$db=new JitsiFixtureDatabase($params);$db->initialize();$db->data_cache['table_names']=[];$ci=(object)['db'=>$db];
require $root.'/modules/google_meet/install.php';
$db->insert(db_prefix().'google_meet_meetings',['title'=>'Legacy','subject'=>'Legacy','meet_link'=>'https://meet.google.com/abc-defg-hij','start_time'=>'2026-10-07 10:00:00','end_time'=>'2026-10-07 11:00:00']);
$legacyId=(int)$db->insert_id();
require $root.'/modules/google_meet/install.php';
check(get_option('google_meet_google_access_token')==='private-fixture-token','Upgrade preserves credentials');
check($db->where('id',$legacyId)->get(db_prefix().'google_meet_meetings')->row()->meet_link==='https://meet.google.com/abc-defg-hij','Repeated installer preserves legacy rooms');
check($db->field_exists('room_name',db_prefix().'google_meet_meetings')&&$db->field_exists('room_note_key',db_prefix().'google_meet_comments'),'Idempotent schema migration');
$db->query('CREATE TABLE audit_jitsi_modules (module_name VARCHAR(100) PRIMARY KEY, installed_version VARCHAR(30))');
$db->insert(db_prefix().'modules',['module_name'=>'google_meet','installed_version'=>'1.2.5']);
define('APP_MODULES_PATH',$root.'/modules/');
$ci->load=new class {function dbforge(){}};
$ci->lang=new class {function load($name){}function line($name){return $name.' %s';}};
$ci->app_modules=new class {function get($name){return ['headers'=>['version'=>'1.3.0']];}};
require $root.'/application/libraries/App_module_migration.php';
$migration=new App_module_migration('google_meet');
check((string)$migration->to_latest()==='130','Native Perfex upgrade from 1.2.5 has no sequence gaps');
check($db->where('module_name','google_meet')->get(db_prefix().'modules')->row()->installed_version==='1.3.0','Native module version records upgrade');
check($migration->to_latest()!==false,'Repeated native migration is idempotent');
require $root.'/modules/google_meet/models/Google_meet_model.php';$model=new Google_meet_model();
$options['jitsi_require_pin']='1';
$id=$model->create(['subject'=>'Scheduled fixture','start_time'=>'2026-10-07 10:00:00','duration_minutes'=>30]);
check($id>0,'Native create succeeds without OAuth');$saved=$model->get($id);
check($saved->provider==='jitsi'&&strpos($saved->meet_link,'https://meet.jit.si/SC-')===0&&strlen($saved->room_pin)===6,'Room identity and PIN persisted');
check($model->update($id,['subject'=>'Updated fixture']),'Native update');
$updated=$model->get($id);check($updated->meet_link===$saved->meet_link&&$updated->room_name===$saved->room_name&&$updated->room_pin===$saved->room_pin,'Update preserves shared identity');
check($model->record_participant_join($id,'guest'),'Customer join');$joined=$model->get($id);
check($joined->status==='live'&&!empty($joined->guest_joined_at)&&empty($joined->host_joined_at)&&!empty($joined->actual_start),'Guest start records actual attendance');
check($model->record_participant_join($id,'guest')&&$model->get($id)->actual_start===$joined->actual_start,'Duplicate join retains start');
check($model->record_participant_join($id,'host')&&!empty($model->get($id)->host_joined_at),'Host attendance separately recorded');
$db->where('id',$id)->update(db_prefix().'google_meet_meetings',['actual_start'=>date('Y-m-d H:i:s',time()-125)]);
check($model->record_meeting_finish($id),'Finish live meeting');$finished=$model->get($id);
check($finished->status==='completed'&&(int)$finished->duration_minutes===2&&!empty($finished->actual_end),'Actual duration calculated');
check($model->record_meeting_finish($id)&&$model->get($id)->actual_end===$finished->actual_end,'Duplicate finish retains end');
check(!$model->record_participant_join($id,'host')&&!$model->record_participant_join(404,'guest'),'Completed/missing joins rejected');
$key=bin2hex(random_bytes(16));$note=$model->save_room_note($id,'First note',$key);
check($note>0&&$model->save_room_note($id,'Revised note',$key)===$note,'Retries update one timeline note');
check($db->where('meeting_id',$id)->count_all_results(db_prefix().'google_meet_comments')===1,'No duplicate note rows');
$staffId=99;$otherNote=$model->save_room_note($id,'Other owner',$key);check($otherNote>0&&$otherNote!==$note,'Staff cannot overwrite another author');
check(!$model->save_room_note(404,'Orphan',$key)&&!$model->save_room_note($id,'', $key)&&!$model->save_room_note($id,'Bad key','wrong'),'Invalid/orphan notes rejected');$staffId=42;
$pending=$model->create(['subject'=>'Pending fixture','meeting_type'=>'instant']);$db->where('id',$pending)->update(db_prefix().'google_meet_meetings',['meet_link'=>'','room_name'=>null,'status'=>'link_required']);
check($model->ensure_shared_room($pending),'Generate missing legacy room');$room=$model->get($pending);
check($model->ensure_shared_room($pending)&&$model->get($pending)->meet_link===$room->meet_link&&$room->status==='scheduled','Repeated generation retains room and repairs pending status');
foreach(['attendees','notifications'] as $table){$db->insert(db_prefix().'google_meet_'.$table,['meeting_id'=>$id]);$db->insert(db_prefix().'google_meet_'.$table,['meeting_id'=>$pending]);}
check($model->delete_many([$id,$id,-1,404])===1,'Cascade deletes only existing requested meeting');
foreach(['attendees','comments','logs','notifications'] as $table){check($db->where('meeting_id',$id)->count_all_results(db_prefix().'google_meet_'.$table)===0,'Cascade child rows removed: '.$table);}
check($model->get($pending)!==null&&$db->where('meeting_id',$pending)->count_all_results(db_prefix().'google_meet_notifications')===1,'Unrelated meeting and notifications preserved');
check(!$model->save_room_note($id,'After deletion',$key),'No notes after deletion');
$db->failChildDelete=true;check($model->delete_many([$pending])===0,'Failure reports no deletion');
check($model->get($pending)!==null&&$db->where('meeting_id',$pending)->count_all_results(db_prefix().'google_meet_attendees')===1&&$db->where('meeting_id',$pending)->count_all_results(db_prefix().'google_meet_notifications')===1,'Cascade failure rolls back parent and children');
$db->close();echo "PASS: $checks native MySQL migration, room persistence, attendance, notes and cascade checks (temporary tables only)\n";
