<?php
define('BASEPATH',__DIR__); class App_Model{}
require dirname(__DIR__).'/modules/google_analytics/models/Google_analytics_model.php';
class AnalyticsProbe extends Google_analytics_model{
 public $requests=[]; public function __construct(){} public function get_cached($key){return null;}
 public function set_cached($id,$key,$type,array $payload){}
 public function run_report($propertyId,array $body,$realtime=false){$this->requests[]=$body;return ['rows'=>[]];}
}
$p=new AnalyticsProbe();$site=(object)['id'=>1,'property_id'=>'508336863','website_url'=>'https://justsmartchoice.com'];
$p->dashboard_report($site);
if(count($p->requests)!==4)throw new Exception('Incomplete dashboard reports');
foreach($p->requests as $request){
 $filter=$request['dimensionFilter']['filter'];
 if($filter['fieldName']!=='hostName'||$filter['inListFilter']['values']!==['justsmartchoice.com','www.justsmartchoice.com'])throw new Exception('CRM traffic not isolated');
}
try{$site->website_url='';$p->dashboard_report($site);throw new Exception('Unbounded report allowed');}catch(InvalidArgumentException $expected){}
echo "PASS website-only hostname isolation for summary/pages/sources/events and rejection of unbounded host\n";
