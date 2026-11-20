<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('BASEPATH',__DIR__);define('APPPATH',dirname(__DIR__).'/application/');
$GLOBALS['recovery_admin']=false;
function is_admin(){return $GLOBALS['recovery_admin'];}
function access_denied($module){throw new RuntimeException('DENIED');}
class RecoveryTestLoader{public function helper($name){}}
class AdminController{public $load;public function __construct(){$this->load=new RecoveryTestLoader();}}
require APPPATH.'hooks/ScRecoveryHook.php';require APPPATH.'controllers/admin/Recovery.php';
try{new Recovery();throw new RuntimeException('Non-admin could access recovery');}catch(RuntimeException $e){if($e->getMessage()!=='DENIED')throw $e;}
$GLOBALS['recovery_admin']=true;new Recovery();
echo "PASS every recovery action inherits administrator authorization; hook and controller coexist.\n";
