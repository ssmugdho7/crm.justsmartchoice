<?php
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__).'/application/');
$CFG=new class {function item($name){return [dirname(__DIR__).'/modules/'=>'../../modules/'];}};
function log_message($level,$message){}
function show_error($message){throw new RuntimeException($message);}
require APPPATH.'third_party/MX/Modules.php';
foreach(['incoming','ivr_route','outgoing_twiml','browser_outgoing','browser_complete','ai_turn','status','recording'] as $action){
 $route=Modules::parse_routes('smart_choice_call_center','smart_choice_call_center/webhook/'.$action);
 if($route!==['smart_choice_call_center','webhook',$action])throw new RuntimeException('Public webhook routed to '.implode('/',$route??[]));
 echo 'PASS framework routes public webhook to Webhook::'.$action.PHP_EOL;
}
