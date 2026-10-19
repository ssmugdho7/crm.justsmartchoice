<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH',true);
require dirname(__DIR__).'/modules/sc_module_licensing/libraries/Sc_license_crypto.php';
require dirname(__DIR__).'/modules/sc_social_login/libraries/Sc_oauth.php';
$checks=0;
function check($condition,$label) { if(!$condition)throw new RuntimeException($label); $GLOBALS['checks']++; }
function denied($callback,$label) { try{$callback();}catch(Throwable $e){check(true,$label);return;}throw new RuntimeException($label); }
$key=openssl_pkey_new(['private_key_bits'=>2048,'private_key_type'=>OPENSSL_KEYTYPE_RSA]);
openssl_pkey_export($key,$private); $details=openssl_pkey_get_details($key); $public=$details['key'];
$now=time(); $claims=['v'=>1,'id'=>1,'company'=>'Fixture company','module'=>'sc_social_login',
    'site'=>'https://crm.example.test/path','installation'=>str_repeat('a',64),'issued'=>$now,'expires'=>$now+3600,'license_expires'=>$now+86400];
$signed=Sc_license_crypto::sign($claims,$private);
check(Sc_license_crypto::verify($signed,$public,'sc_social_login',$claims['site'],$claims['installation'])['id']===1,'valid signed lease');
foreach (['expired'=>['expires'=>$now-1],'wrong module'=>['module'=>'other_module'],'wrong company site'=>['site'=>'https://other.example.test'],
    'wrong installation'=>['installation'=>str_repeat('b',64)],'unbounded lease'=>['expires'=>$now+3601],
    'license expired'=>['license_expires'=>$now-1],'future issue'=>['issued'=>$now+120]] as $label=>$change) {
    $bad=Sc_license_crypto::sign(array_replace($claims,$change),$private);
    denied(function()use($bad,$public,$claims){Sc_license_crypto::verify($bad,$public,'sc_social_login',$claims['site'],$claims['installation']);},$label);
}
denied(function()use($signed,$public,$claims){Sc_license_crypto::verify('a'.substr($signed,1),$public,'sc_social_login',$claims['site'],$claims['installation']);},'tampered lease');
foreach(['http://crm.example.test','https://user:pass@crm.example.test','https://crm.example.test/?key=secret','https://crm.example.test:8080'] as $url) {
    denied(function()use($url){Sc_license_crypto::site($url);},'noncanonical site');
}
check(Sc_license_crypto::site('https://CRM.example.test/path/')==='https://crm.example.test/path','canonical URL');
$jwk=['kty'=>'RSA','use'=>'sig','alg'=>'RS256','kid'=>'fixture','n'=>Sc_oauth::b64($details['rsa']['n']),'e'=>Sc_oauth::b64($details['rsa']['e'])];
$identity=['iss'=>'https://accounts.google.com','aud'=>'client-fixture','sub'=>'subject-fixture','nonce'=>'nonce-fixture','iat'=>$now,'exp'=>$now+600,'email'=>'new@example.test','email_verified'=>true];
function jwtFixture($claims,$private,$alg='RS256') {
    $body=Sc_oauth::b64(json_encode(['alg'=>$alg,'kid'=>'fixture'])).'.'.Sc_oauth::b64(json_encode($claims));
    openssl_sign($body,$signature,$private,OPENSSL_ALGO_SHA256);return $body.'.'.Sc_oauth::b64($signature);
}
$jwt=jwtFixture($identity,$private);
check(Sc_oauth::verifyIdToken($jwt,[$jwk],[$identity['iss']],$identity['aud'],$identity['nonce'])['sub']==='subject-fixture','verified OIDC JWT');
foreach(['wrong issuer'=>['iss'=>'https://attacker.example.test'],'wrong audience'=>['aud'=>'attacker-client'],'wrong nonce'=>['nonce'=>'attacker-nonce'],
    'expired token'=>['exp'=>$now-1],'future token'=>['iat'=>$now+120],'wrong authorized party'=>['azp'=>'other-client'],'missing subject'=>['sub'=>'']] as $label=>$change) {
    $bad=jwtFixture(array_replace($identity,$change),$private);
    denied(function()use($bad,$jwk,$identity){Sc_oauth::verifyIdToken($bad,[$jwk],[$identity['iss']],$identity['aud'],$identity['nonce']);},$label);
}
denied(function()use($identity,$private,$jwk){Sc_oauth::verifyIdToken(jwtFixture($identity,$private,'none'),[$jwk],[$identity['iss']],$identity['aud'],$identity['nonce']);},'algorithm confusion');
denied(function()use($jwt,$identity){Sc_oauth::verifyIdToken($jwt,[],[$identity['iss']],$identity['aud'],$identity['nonce']);},'unknown key');
$oauth=new Sc_oauth(['client_id'=>'client-fixture','client_secret'=>'fixture-secret'],function($url,$form)use($jwt,$jwk){
    if(strpos($url,'/token')!==false){check($form['code_verifier']==='verifier-fixture','PKCE exchange');return ['id_token'=>$jwt];}
    return ['keys'=>[$jwk]];
});
$tx=['state'=>'state-fixture','nonce'=>'nonce-fixture','verifier'=>'verifier-fixture'];
$profile=$oauth->exchange('google','code-fixture',$tx,'https://crm.example.test/callback');
check($profile['verified']===true && $profile['subject']==='subject-fixture','Google exchanges only verified identity');
parse_str(parse_url($oauth->authorization('google',$tx,'https://crm.example.test/callback'),PHP_URL_QUERY),$params);
check($params['code_challenge_method']==='S256' && $params['nonce']===$tx['nonce'] && $params['state']===$tx['state'],'Google request state nonce PKCE');
parse_str(parse_url($oauth->authorization('apple',$tx,'https://crm.example.test/callback'),PHP_URL_QUERY),$params);
check($params['response_mode']==='form_post','Apple form-post callback');
$fb=new Sc_oauth(['client_id'=>'fixture-app','client_secret'=>'fixture-secret'],function($url,$form){
    if(strpos($url,'/oauth/access_token')!==false)return ['access_token'=>'fixture-token'];
    if(strpos($url,'/debug_token')!==false)return ['data'=>['is_valid'=>true,'app_id'=>'fixture-app','user_id'=>'fixture-user','expires_at'=>time()+600]];
    return ['id'=>'fixture-user','email'=>'new@example.test'];
});
check($fb->exchange('facebook','fixture-code',$tx,'https://crm.example.test/callback')['subject']==='fixture-user','Facebook token app/user validation');
$fb=new Sc_oauth(['client_id'=>'fixture-app','client_secret'=>'fixture-secret'],function($url,$form){
    if(strpos($url,'/oauth/access_token')!==false)return ['access_token'=>'fixture-token'];
    return ['data'=>['is_valid'=>true,'app_id'=>'attacker-app','user_id'=>'fixture-user','expires_at'=>time()+600]];
});
denied(function()use($fb,$tx){$fb->exchange('facebook','fixture-code',$tx,'https://crm.example.test/callback');},'Facebook wrong app rejected');
foreach(['sc_module_licensing','sc_social_login'] as $module){
    $lang=[];include dirname(__DIR__).'/modules/'.$module.'/language/english/'.$module.'_lang.php';$en=$lang;
    $lang=[];include dirname(__DIR__).'/modules/'.$module.'/language/spanish/'.$module.'_lang.php';$es=$lang;
    check(array_keys($en)===array_keys($es),$module.' bilingual key parity');
    foreach($es as $value)check(trim($value)!=='','no empty Spanish translation');
}
echo "$checks license/OAuth/language checks passed. No external mail or provider login.\n";
