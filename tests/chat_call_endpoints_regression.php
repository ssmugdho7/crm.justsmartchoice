<?php
require __DIR__.'/chat_client_endpoints_regression.php';
define('CHAT_CALLS_STAFF_CHANNEL_PREFIX', 'private-calls-staff-');
define('CHAT_CALLS_CLIENT_CHANNEL_PREFIX', 'private-calls-client-');
class AdminController { public $input; public $output; public $app_pusher; public $db; public $chat_model; public $session; }
require $root.'/modules/prchat/controllers/Calls_Controller.php';
$calls=(new ReflectionClass(Calls_Controller::class))->newInstanceWithoutConstructor();
$calls->input=new ChatFixtureInput();
$calls->app_pusher=new class {
 public $events=[]; public $available=true;
 function trigger($channel,$event,$payload) { $this->events[]=[$channel,$event,$payload];return $this->available?(object)[]:false; }
};
foreach (['startCall'=>'call-offer','answerCall'=>'call-answer','iceCandidate'=>'call-ice','hangup'=>'call-hangup','decline'=>'call-declined','muteStatus'=>'call-mute-status'] as $method=>$event) {
 $calls->input->values=['from_id'=>999,'to_id'=>8,'recipient_type'=>'staff','sdp'=>'{"type":"offer","sdp":"fixture"}','candidate'=>'{}','is_muted'=>'1'];
 $calls->output=new ChatFixtureOutput();$calls->$method();
 [$channel,$actualEvent,$payload]=end($calls->app_pusher->events);
 check($calls->output->data['success']===true&&$channel==='private-calls-staff-8'&&$event===$actualEvent&&$payload['from_id']===7,'Call endpoint '.$method.' uses session sender and private recipient channel');
}
$calls->input->values=['to_id'=>33,'recipient_type'=>'client','is_video'=>'1','sdp'=>'{}'];$calls->output=new ChatFixtureOutput();$calls->startCall();
check(end($calls->app_pusher->events)[0]==='private-calls-client-33','Staff/client signal selects contact channel');
foreach ([['to_id'=>-1],['to_id'=>33,'recipient_type'=>'invalid'],['to_id'=>999]] as $bad) {
 $calls->input->values=$bad;$calls->output=new ChatFixtureOutput();
 try {$calls->startCall();check(false,'Invalid recipient must fail');} catch (RuntimeException $e) {check(in_array($calls->output->status,[403,422],true),'Invalid or unavailable call recipient denied');}
}
$calls->input->values=['to_id'=>8];$calls->output=new ChatFixtureOutput();$calls->app_pusher->available=false;
try {$calls->startCall();check(false,'Unavailable signaling must fail');} catch (RuntimeException $e) {check($calls->output->status===500&&$calls->output->data['success']===false,'Provider no-op cannot report successful call');}
echo "PASS six staff call endpoints, private recipient channels, sender spoof rejection, invalid recipients and provider failure\n";
