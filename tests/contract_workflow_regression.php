<?php
define('BASEPATH',true);
class AdminController {}
class ContractHookFixture {function add_action($name,$callback){}}
function hooks(){return new ContractHookFixture();}
require dirname(__DIR__).'/modules/sc_admin_fixes/includes/contract_workflows.php';
require dirname(__DIR__).'/modules/sc_admin_fixes/controllers/Sc_admin_fixes.php';
$options=[];$admin=true;$edit=true;$view=true;
function get_option($key){return $GLOBALS['options'][$key]??'';}
function update_option($key,$value,$autoload=null){$GLOBALS['options'][$key]=$value;return true;}
function is_admin(){return $GLOBALS['admin'];}
function staff_can($cap,$module){return $cap==='edit'?$GLOBALS['edit']:$GLOBALS['view'];}
function is_staff_logged_in(){return true;}
function get_staff_user_id(){return 7;}
function admin_url($p){return 'https://crm.example.test/admin/'.$p;}
function db_prefix(){return 'tbl';}
function redirect($url){$GLOBALS['redirected']=$url;}
function log_activity($message){}
function _l($key){return $key;}
function set_alert($type,$text){$GLOBALS['alert']=[$type,$text];}
function show_error($text,$code){throw new RuntimeException((string)$code);}
function show_404(){throw new RuntimeException('404');}
function access_denied($module){throw new RuntimeException('403');}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
class ContractInputFixture {public $verb='POST',$state='draft';function method($upper=false){return $this->verb;}function post($name){return $this->state;}}
class ContractLoadFixture {function model($name){}}
class ContractModelFixture {public $item;function get($id){return $this->item;}}
class ContractDbFixture {public $key;function where($k,$v){$this->key=$v;return $this;}function get($t){return $this;}function row(){return isset($GLOBALS['options'][$this->key])?(object)['value'=>$GLOBALS['options'][$this->key]]:null;}}
$controller=(new ReflectionClass(Sc_admin_fixes::class))->newInstanceWithoutConstructor();
$controller->input=new ContractInputFixture();$controller->load=new ContractLoadFixture();$controller->contracts_model=new ContractModelFixture();$controller->db=new ContractDbFixture();
$contract=(object)['id'=>62,'signed'=>0,'marked_as_signed'=>0,'addedfrom'=>7,'dateend'=>'2027-04-02','not_visible_to_client'=>0,'last_sent_at'=>null,'signature'=>null,'hash'=>'fixture'];
$controller->contracts_model->item=$contract;$snapshot=clone $contract;
foreach(scaf_contract_workflow_states() as $state){$controller->input->state=$state;$controller->contract_workflow('62');check(scaf_contract_workflow($contract)===$state,'Authorized '.$state.' workflow persists');check($contract==$snapshot,'Workflow '.$state.' preserves dates, signature, sent-email history and visibility');}
$controller->input->verb='GET';try{$controller->contract_workflow('62');throw new LogicException('Expected405');}catch(RuntimeException $e){check($e->getMessage()==='405','GET cannot change contract workflow');}$controller->input->verb='POST';
$admin=false;$edit=false;try{$controller->contract_workflow('62');throw new LogicException('Expected403');}catch(RuntimeException $e){check($e->getMessage()==='403','Editing permission enforced');}$edit=true;$view=false;$contract->addedfrom=9;
try{$controller->contract_workflow('62');throw new LogicException('Expected404');}catch(RuntimeException $e){check($e->getMessage()==='404','Other staff contracts are not exposed without global view');}
$view=true;$contract->signed=1;$controller->input->state='draft';$before=$options;$controller->contract_workflow('62');check($options===$before&&scaf_contract_workflow($contract)==='signed','Signed contracts cannot be relabeled draft');
$contract->signed=0;$contract->marked_as_signed=1;$controller->contract_workflow('62');check($options===$before,'Manually signed contracts are also protected');
$controller->input->state='arbitrary';try{$controller->contract_workflow('62');throw new LogicException('Expected422');}catch(RuntimeException $e){check($e->getMessage()==='422','Unknown status rejected');}
function html_escape($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}
function form_open($url,$attrs=[]){return '<form method="post" action="'.html_escape($url).'"><input name="csrf_token_name" value="fixture">';}
function form_close(){return '</form>';}
$admin=true;$contract->signed=0;$contract->marked_as_signed=0;
ob_start();require dirname(__DIR__).'/modules/sc_admin_fixes/views/contracts/workflow_actions.php';$menu=ob_get_clean();
check(substr_count($menu,'<form method="post"')===4,'All four actions use CSRF-bearing native POST forms');
check(strpos($menu,'scaf_contract_attach_file')!==false,'Attachments shortcut appears in the contract menu');
$contract->signed=1;ob_start();require dirname(__DIR__).'/modules/sc_admin_fixes/views/contracts/workflow_actions.php';$menu=ob_get_clean();
check(strpos($menu,'<form')===false&&strpos($menu,'scaf_contract_state_signed')!==false,'Signed menu hides unsigned actions and displays signed state');
$contract->signed=0;$admin=false;$edit=false;ob_start();require dirname(__DIR__).'/modules/sc_admin_fixes/views/contracts/workflow_actions.php';$menu=ob_get_clean();
check(strpos($menu,'<form')===false&&strpos($menu,'scaf_contract_attach_file')===false,'Read-only staff receive no workflow or upload controls');
