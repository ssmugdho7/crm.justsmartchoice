<?php
defined('BASEPATH') or exit('No direct script access allowed');
function sccc_can($cap){ return is_admin() || staff_can($cap,'smart_choice_call_center'); }
function sccc_require($cap){ if(!sccc_can($cap)) access_denied('smart_choice_call_center'); }
function sccc_phone($v){
 $v=trim((string)$v);$d=preg_replace('/\D+/','',$v);
 if(strlen($d)===10 && strpos($v,'+')!==0)$d='1'.$d;
 if(!preg_match('/^[1-9][0-9]{7,14}$/',$d))return '';
 if(strpos($v,'+')!==0 && !(strlen($d)===11 && $d[0]==='1'))return '';
 return '+'.$d;
}
function sccc_phone_display($v){$d=preg_replace('/\D+/','',(string)$v);if(strlen($d)===11&&$d[0]==='1')$d=substr($d,1);return strlen($d)===10?'+1 ('.substr($d,0,3).') '.substr($d,3,3).'-'.substr($d,6):$v;}
function sccc_xml($s){return htmlspecialchars((string)$s,ENT_XML1|ENT_QUOTES,'UTF-8');}
function sccc_twilio_request($method,$path,$data=[]){
 $sid=get_option('sccc_twilio_account_sid');$token=get_option('sccc_twilio_auth_token'); if(!$sid||!$token)return ['ok'=>false,'error'=>_l('sccc_twilio_not_configured')];
 $url='https://api.twilio.com/2010-04-01/Accounts/'.rawurlencode($sid).'/'.ltrim($path,'/');
 $ch=curl_init($url);curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);curl_setopt($ch,CURLOPT_USERPWD,$sid.':'.$token);curl_setopt($ch,CURLOPT_TIMEOUT,25);curl_setopt($ch,CURLOPT_CUSTOMREQUEST,strtoupper($method));
 if($data){curl_setopt($ch,CURLOPT_POSTFIELDS,sccc_form_encode($data));}
 $body=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);$json=json_decode((string)$body,true);
 return ['ok'=>$code>=200&&$code<300,'status'=>$code,'data'=>$json,'error'=>$err?:($json['message']??null)];
}
function sccc_form_encode(array $data): string {
 $pairs=[];foreach($data as $key=>$values){foreach(is_array($values)?$values:[$values] as $value)$pairs[]=rawurlencode($key).'='.rawurlencode((string)$value);}return implode('&',$pairs);
}
function sccc_ai_agent_error(array $agent): ?string {
 $provider=$agent['provider']??'';
 $field=$provider==='generic_stream'?'websocket_url':($provider==='webhook'?'webhook_url':null);
 if(!$field)return _l('sccc_invalid_ai_provider');
 $url=trim((string)($agent[$field]??''));$parts=parse_url($url);
 if(!$parts || empty($parts['host']) || ($parts['scheme']??'')!==($provider==='generic_stream'?'wss':'https') || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment']) || preg_match('/[\s<>"\x00-\x1f]/',$url) || strlen($url)>500 || ($provider==='generic_stream' && isset($parts['query'])))return _l('sccc_invalid_ai_url');
 // Twilio permits fewer than 500 bytes for a parameter's name plus value.
 if($provider==='generic_stream' && (strlen((string)($agent['system_prompt']??''))+strlen('system_prompt')>=500 || strlen((string)($agent['voice_name']??''))+strlen('voice_name')>=500))return _l('sccc_ai_parameter_too_long');
 return null;
}
function sccc_valid_twilio_request(string $signature, array $params): bool {
 $token=(string)get_option('sccc_twilio_auth_token');
 if($token==='' || $signature==='')return false;
 $base=parse_url(site_url());$uri=$_SERVER['REQUEST_URI']??'';
 if(!$base || $uri==='' || $uri[0]!=='/')return false;
 // Use the configured public origin, never a caller-controlled Host/Forwarded header.
 $url=$base['scheme'].'://'.$base['host'].(isset($base['port'])?':'.$base['port']:'').$uri;
 if(isset($params['AccountSid']) && !hash_equals((string)get_option('sccc_twilio_account_sid'),(string)$params['AccountSid']))return false;
 return (new \Twilio\Security\RequestValidator($token))->validate($signature,$url,$params);
}
function sccc_base64url($d){return rtrim(strtr(base64_encode($d),'+/','-_'),'=');}
function sccc_twilio_access_token($identity,$allow_outgoing=true){
 $account=get_option('sccc_twilio_account_sid');$key=get_option('sccc_api_key_sid');$secret=get_option('sccc_api_key_secret');$app=get_option('sccc_twiml_app_sid'); if(!$account||!$key||!$secret||!$app)return null;
 $now=time();$header=['cty'=>'twilio-fpa;v=1','typ'=>'JWT','alg'=>'HS256'];$grants=['identity'=>(string)$identity,'voice'=>['outgoing'=>['application_sid'=>$app],'incoming'=>['allow'=>true]]];
 if(!$allow_outgoing)unset($grants['voice']['outgoing']);
 $payload=['jti'=>$key.'-'.$now,'iss'=>$key,'sub'=>$account,'exp'=>$now+3600,'grants'=>$grants];$h=sccc_base64url(json_encode($header));$p=sccc_base64url(json_encode($payload));$sig=sccc_base64url(hash_hmac('sha256',$h.'.'.$p,$secret,true));return $h.'.'.$p.'.'.$sig;
}
