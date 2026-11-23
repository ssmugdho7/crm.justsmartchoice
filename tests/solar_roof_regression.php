<?php
if (PHP_SAPI !== 'cli') exit;
define('BASEPATH', __DIR__);
function check($ok, $why) { if (!$ok) throw new RuntimeException($why); }
function &get_instance() { return $GLOBALS['ci']; }
function is_admin() { return $GLOBALS['isAdmin']; }
function staff_can($cap, $module) { return in_array($cap, $GLOBALS['caps']); }
function get_staff_user_id() { return 7; }
function access_denied($module) { throw new RuntimeException('denied'); }
function show_404() { throw new RuntimeException('missing'); }
class AdminController {}
class App_Controller {}
require __DIR__.'/../modules/solar_pro/libraries/Solar_pro_layers.php';
require __DIR__.'/../modules/solar_pro/controllers/Solar_pro.php';
require __DIR__.'/../modules/solar_pro/controllers/Solar_portal.php';
foreach (['https://evil.test/v1/geoTiff:get?id=x', 'http://solar.googleapis.com/v1/geoTiff:get',
 'https://solar.googleapis.com.evil.test/v1/geoTiff:get', 'https://a@solar.googleapis.com/v1/geoTiff:get',
 'https://solar.googleapis.com:443/v1/geoTiff:get', 'https://solar.googleapis.com/v1/../secret'] as $url) {
 check(!Solar_pro_layers::validRasterUrl($url), 'SSRF URL accepted');
}
check(Solar_pro_layers::validRasterUrl('https://solar.googleapis.com/v1/geoTiff:get?id=test'), 'Google raster rejected');
$GLOBALS['isAdmin']=false; $GLOBALS['caps']=['view_own'];
$model = new class { public $record=['id'=>3,'created_by'=>8]; public function getAnalysis($id){ return $this->record; } public function getByToken($token){return $token==='valid'?$this->record:null;} };
$served = new class {public $count=0;public function serve($a,$l){$this->count++;}};
$loader = new class {public function library($name){}};
$admin = (new ReflectionClass('Solar_pro'))->newInstanceWithoutConstructor();
$admin->solar_pro_model=$model; $admin->solar_pro_layers=$served; $admin->load=$loader;
try {$admin->roof_layers(3); throw new LogicException('Other owner allowed');} catch (RuntimeException $e){check($e->getMessage()==='denied','Wrong denial');}
check($served->count===0,'Upstream called before authorization');
$model->record['created_by']=7; $admin->roof_layers(3); check($served->count===1,'Owner denied');
$GLOBALS['caps']=[];
try {$admin->roof_layers(3); throw new LogicException('Permissionless access');} catch (RuntimeException $e){check($e->getMessage()==='denied','Wrong denial');}
$public=(new ReflectionClass('Solar_portal'))->newInstanceWithoutConstructor();
$public->solar_pro_model=$model;$public->solar_pro_layers=$served;$public->load=$loader;
try {$public->roof_layers('wrong');throw new LogicException('Invalid token accepted');}catch(RuntimeException $e){check($e->getMessage()==='missing','Wrong token denial');}
$public->roof_layers('valid');check($served->count===2,'Valid token denied');
echo "PASS raster URL boundary, owner permissions, and public token authorization\n";
$GLOBALS['ci']=(object)[
 'session'=>new class {public $values=[];public function userdata($k){return $this->values[$k]??null;}public function set_userdata($k,$v){$this->values[$k]=$v;}},
 'output'=>new class {public $body,$status=200;public function set_status_header($v){$this->status=$v;return $this;}public function set_content_type($v){return $this;}public function set_header($v){return $this;}public function set_output($v){$this->body=$v;return $this;}},
 'solar_pro_google'=>new class {public $calls=0;public function dataLayers($lat,$lng){$this->calls++;return ['success'=>true,'data'=>['rgbUrl'=>'https://solar.googleapis.com/v1/geoTiff:get?id=rgb','maskUrl'=>'https://solar.googleapis.com/v1/geoTiff:get?id=mask','annualFluxUrl'=>'https://solar.googleapis.com/v1/geoTiff:get?id=annual','hourlyShadeUrls'=>['https://solar.googleapis.com/v1/geoTiff:get?id=shade'],'imageryQuality'=>'HIGH']];}}
];
$proxy=new Solar_pro_layers();$record=['id'=>7,'latitude'=>37.4,'longitude'=>-122.1];
$proxy->serve($record,'manifest');$body=json_decode($GLOBALS['ci']->output->body,true);
check($body['shadeMonths']===[1]&&$body['quality']==='HIGH','Manifest metadata invalid');
check(strpos($GLOBALS['ci']->output->body,'geoTiff')===false,'Provider URL leaked');
$proxy->serve($record,'manifest');check($GLOBALS['ci']->solar_pro_google->calls===1,'Repeated paid request');
$proxy->serve(['id'=>8,'latitude'=>91,'longitude'=>0],'manifest');check($GLOBALS['ci']->output->status===422,'Invalid coordinates accepted');
$proxy->serve($record,'shade-13');check($GLOBALS['ci']->output->status===400,'Invalid shade month accepted');
$GLOBALS['ci']->session->values['solar_layers_7']['expires']=time()-1;
$proxy->serve($record,'rgb');check($GLOBALS['ci']->output->status===410,'Expired raster accepted');
$proxy->serve(['id'=>8,'latitude'=>37,'longitude'=>-122],'manifest');check($GLOBALS['ci']->output->status===429,'Provider throttle missing');
echo "PASS private manifest, lazy reuse, coordinate validation, expiry and request throttle\n";
