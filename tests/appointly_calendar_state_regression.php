<?php
define('BASEPATH', __DIR__); define('APPOINTLY_MODULE_NAME','appointly');
class AdminController {}
function get_staff_user_id(){return 7;}
$options=[];
function get_option($key){global $options;return $options[$key]??'';}
function show_error($message,$status){if($status!==403)throw new Exception('Unexpected status');}
function admin_url(){return '/admin/';}
function redirect($url){}
require dirname(__DIR__).'/modules/appointly/controllers/google/Auth.php';
class Probe extends Auth { public $session,$input,$googlecalendar; public function __construct(){} }
function probe($tx,$state,$code='synthetic'){
 $p=new Probe;
 $p->session=new class($tx){public $tx;function __construct($tx){$this->tx=$tx;}function userdata($k){return $this->tx;}function unset_userdata($k){$this->tx=null;}};
 $p->input=new class($state,$code){public $state,$code;function __construct($s,$c){$this->state=$s;$this->code=$c;}function get($k,$filter){return $k==='state'?$this->state:$this->code;}};
 $p->googlecalendar=new class{public $calls=0;function login($c){$this->calls++;return true;}};
 return $p;
}
$tx=['state'=>str_repeat('a',64),'staff_id'=>7,'expires'=>time()+600];
foreach([['mismatch',$tx,'bad','synthetic'],['missing',null,$tx['state'],'synthetic'],['wrong_staff',array_replace($tx,['staff_id'=>8]),$tx['state'],'synthetic'],['expired',array_replace($tx,['expires'=>time()-1]),$tx['state'],'synthetic'],['array_state',$tx,[],'synthetic'],['missing_code',$tx,$tx['state'],null]] as [$label,$t,$s,$c]){ $p=probe($t,$s,$c);$p->oauth();if($p->googlecalendar->calls!==0||$p->session->tx!==null)throw new Exception($label);echo "$label: pass\n";}
$p=probe($tx,$tx['state']);$p->oauth();if($p->googlecalendar->calls!==1)throw new Exception('valid');$p->oauth();if($p->googlecalendar->calls!==1)throw new Exception('replay');echo "valid and replay: pass\n";

$options=['appointly_google_client_id'=>'synthetic-client','appointly_google_calendar_staff_id'=>8];
$p=probe($tx,$tx['state']);$p->oauth();if($p->googlecalendar->calls!==0)throw new Exception('Non-owner connected dedicated integration');
echo "dedicated calendar non-owner denied: pass\n";
