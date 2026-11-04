<?php
if(PHP_SAPI!=='cli')exit(1);
define('BASEPATH',__DIR__);
require dirname(__DIR__).'/modules/appointly/helpers/appointly_helper.php';
function log_message($level,$message){}
class RefreshTestLoad{function model($name){}}
class RefreshTestClient{
 public $refreshes=0,$token;
 function setAccessToken($t){$this->token=$t;}
 function getAccessToken(){return $this->token;}
 function fetchAccessTokenWithRefreshToken($t){$this->refreshes++;return ['access_token'=>'synthetic-renewed','expires_in'=>3600];}
}
class RefreshTestModel{
 public $account,$saved=[];
 function getAccountDetails(){return [$this->account];}
 function saveNewTokenValues($t){$this->saved[]=$t;}
}
$client=new RefreshTestClient;$model=new RefreshTestModel;
$ci=(object)['load'=>new RefreshTestLoad,'googlecalendar'=>$model,'googleplus'=>(object)['client'=>$client]];
function &get_instance(){global $ci;return $ci;}
$model->account=(object)['access_token'=>'synthetic-valid','refresh_token'=>'synthetic-refresh','expires_in'=>time()+3600];
if(!appointlyGoogleAuth()||$client->refreshes!==0||!is_array($client->token))throw new RuntimeException('Unexpired token was refreshed or malformed');
$model->account->expires_in=time()-1;
if(!appointlyGoogleAuth()||$client->refreshes!==1||count($model->saved)!==1)throw new RuntimeException('Expired token not renewed/persisted');
$model->account->refresh_token='';
if(appointlyGoogleAuth()!==false||$client->refreshes!==1)throw new RuntimeException('Missing refresh grant accepted');
echo "PASS unexpired tokens avoid refresh, expired tokens renew and persist, missing refresh grant fails closed\n";
