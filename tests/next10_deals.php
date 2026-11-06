<?php
define('BASEPATH',__DIR__);
function staff_can($cap,$feature){return $GLOBALS['caps'][$feature.':'.$cap]??false;}function is_staff_member(){return true;}function get_staff_user_id(){return 7;}function is_customer_admin($id){return $id===8;}function db_prefix(){return 'tbl';}function _d($v){return $v;}function get_currency($name){return (object)['id'=>$name==='USD'?1:2];}function get_base_currency(){return (object)['id'=>1];}function hooks(){return new class{function add_action(...$x){}};}
require __DIR__.'/../modules/sc_admin_fixes/includes/deals_workflows.php';
class App_Model{public $db,$load,$projects_model;}
require __DIR__.'/../modules/sc_admin_fixes/models/Scaf_deals_model.php';
class DealDB{public $rows=[],$events=[],$linked;function trans_begin(){$this->events[]='begin';return true;}function query($q,$p){$this->events[]='lock';return $this;}function row(){return array_shift($this->rows);}function where(...$x){return $this;}function get(...$x){return $this;}function update($t,$d){$this->linked=$d['project_id'];return true;}function trans_status(){return true;}function trans_commit(){$this->events[]='commit';return true;}function trans_rollback(){$this->events[]='rollback';return true;}}
function deal_case($deal,$rows,$expect){$m=new Scaf_deals_model;$m->db=new DealDB;$m->db->rows=array_merge([$deal],$rows);$m->load=new class{function model($s){}};$m->projects_model=new class{public $calls=[];function add($d){$this->calls[]=$d;return 42;}};try{$result=$m->convert(3);if(!$expect)throw new Exception('unexpectedsuccess');return [$m,$result];}catch(RuntimeException $e){if($expect)throw $e;if(end($m->db->events)!=='rollback')throw new Exception('rollbackmissing');if($m->projects_model->calls)throw new Exception('unauthorizedprojectcreated');return [$m,null];}}
function dc($ok,$msg){if(!$ok)throw new Exception($msg);}
$caps=['projects:create'=>true,'deals:edit'=>true];$deal=(object)['default_deal_owner'=>7,'status'=>'won','project_id'=>null,'client_id'=>'[8]','stage_id'=>2,'currency'=>'USD','title'=>'Real project','notes'=>'Scope','deal_value'=>'125.10'];
[$m,$r]=deal_case($deal,[(object)['stage_name'=>'Closed Won'],(object)['default_currency'=>1]],true);dc($r['project_id']===42&&$m->db->linked===42,'persistnativeprojectlink');dc($m->projects_model->calls[0]['project_cost']==='125.10'&&$m->projects_model->calls[0]['clientid']===8,'actualvalueandcustomer');dc(!isset($m->projects_model->calls[0]['send_created_email']),'nomailsflag');
$retry=clone $deal;$retry->project_id=42;[$m,$r]=deal_case($retry,[(object)['id'=>42]],true);dc(!$r['created']&&!$m->projects_model->calls,'retryreusesproject');
$wrong=clone $deal;$wrong->default_deal_owner=99;deal_case($wrong,[],false);$multi=clone $deal;$multi->client_id='[8,9]';deal_case($multi,[(object)['stage_name'=>'Closed Won']],false);$currency=clone $deal;$currency->currency='EUR';deal_case($currency,[(object)['stage_name'=>'Closed Won'],(object)['default_currency'=>1]],false);$open=clone $deal;$open->status='open';deal_case($open,[(object)['stage_name'=>'Qualified']],false);$caps['projects:create']=false;deal_case($deal,[],false);
dc(strpos(file_get_contents(__DIR__.'/../modules/deals/install.php'),'DROP TABLE')===false,'activationmustnotdropcustompipeline');echo "PASS nativeprojectvalue/customer/currency/owner guards,transaction rollback, persistent retrylink and nondestructive install\n";

class AdminController{public $input,$app_modules;}
function show_404(){throw new RuntimeException('GET rejected');}function access_denied($m){throw new RuntimeException('Denied');}
require __DIR__.'/../modules/sc_admin_fixes/controllers/Scaf_deals.php';
class DealRouteFixture extends Scaf_deals {public function __construct(){}}
$route=new DealRouteFixture;$route->input=new class{function method(){return 'GET';}};try{$route->convert(3);throw new Exception('GET mutated');}catch(RuntimeException $e){dc($e->getMessage()==='GET rejected','GET refusedbeforeDB');}
$route->input=new class{function method(){return 'POST';}};$route->app_modules=new class{function is_active($name){return false;}};try{$route->convert(3);throw new Exception('Inactive module accepted');}catch(RuntimeException $e){dc($e->getMessage()==='Denied','Inactive module never bypassed');}
echo "PASS noGET mutation and inactive module authorization boundary
";
