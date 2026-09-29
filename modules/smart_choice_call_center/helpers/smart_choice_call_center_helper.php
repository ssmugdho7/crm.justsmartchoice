<?php
defined('BASEPATH') or exit('No direct script access allowed');
function sccc_can($cap){ return is_admin() || staff_can($cap,'smart_choice_call_center'); }
function sccc_require($cap){ if(!sccc_can($cap)) access_denied('smart_choice_call_center'); }
function sccc_phone($v){
 $d=preg_replace('/\D+/','',(string)$v); if(strlen($d)===10)$d='1'.$d; if(strlen($d)===11 && $d[0]==='1') return '+1'.substr($d,1); return strpos((string)$v,'+')===0?'+'.$d:$d;
}
function sccc_phone_display($v){$d=preg_replace('/\D+/','',(string)$v);if(strlen($d)===11&&$d[0]==='1')$d=substr($d,1);return strlen($d)===10?'+1 ('.substr($d,0,3).') '.substr($d,3,3).'-'.substr($d,6):$v;}
function sccc_xml($s){return htmlspecialchars((string)$s,ENT_XML1|ENT_QUOTES,'UTF-8');}
function sccc_twilio_request($method,$path,$data=[]){
 $sid=get_option('sccc_twilio_account_sid');$token=get_option('sccc_twilio_auth_token'); if(!$sid||!$token)return ['ok'=>false,'error'=>_l('sccc_twilio_not_configured')];
 $url='https://api.twilio.com/2010-04-01/Accounts/'.rawurlencode($sid).'/'.ltrim($path,'/');
 $ch=curl_init($url);curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);curl_setopt($ch,CURLOPT_USERPWD,$sid.':'.$token);curl_setopt($ch,CURLOPT_TIMEOUT,25);curl_setopt($ch,CURLOPT_CUSTOMREQUEST,strtoupper($method));
 if($data){curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data));}
 $body=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);$json=json_decode((string)$body,true);
 return ['ok'=>$code>=200&&$code<300,'status'=>$code,'data'=>$json,'error'=>$err?:($json['message']??null)];
}
function sccc_base64url($d){return rtrim(strtr(base64_encode($d),'+/','-_'),'=');}
function sccc_twilio_access_token($identity){
 $account=get_option('sccc_twilio_account_sid');$key=get_option('sccc_api_key_sid');$secret=get_option('sccc_api_key_secret');$app=get_option('sccc_twiml_app_sid'); if(!$account||!$key||!$secret||!$app)return null;
 $now=time();$header=['cty'=>'twilio-fpa;v=1','typ'=>'JWT','alg'=>'HS256'];$grants=['identity'=>(string)$identity,'voice'=>['outgoing'=>['application_sid'=>$app],'incoming'=>['allow'=>true]]];
 $payload=['jti'=>$key.'-'.$now,'iss'=>$key,'sub'=>$account,'exp'=>$now+3600,'grants'=>$grants];$h=sccc_base64url(json_encode($header));$p=sccc_base64url(json_encode($payload));$sig=sccc_base64url(hash_hmac('sha256',$h.'.'.$p,$secret,true));return $h.'.'.$p.'.'.$sig;
}
