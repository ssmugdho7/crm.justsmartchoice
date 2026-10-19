<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Apple client-secret JWT is generated locally; the .p8 key never enters CRM settings.
list($script,$keyFile,$teamId,$keyId,$servicesId,$output) = array_pad($argv,6,'');
if (!is_file($keyFile) || !preg_match('/^[A-Z0-9]{10}$/D',$teamId) || !preg_match('/^[A-Z0-9]{10}$/D',$keyId)
    || !preg_match('/^[a-zA-Z0-9.-]{3,255}$/D',$servicesId) || !$output || $output[0]!=='/') {
    fwrite(STDERR,"Usage: php tools/sc-apple-client-secret.php /private/AuthKey.p8 TEAMID KEYID SERVICES_ID /private/client-secret.txt\n");exit(1);
}
$root=realpath(dirname(__DIR__));$directory=realpath(dirname($output));
if (!$directory || strpos($directory.'/',$root.'/')===0 || strpos($directory.'/','/public_html/')!==false
    || file_exists($output)) { throw new RuntimeException('Use a new output file in a private directory outside the web root'); }
$key=openssl_pkey_get_private(file_get_contents($keyFile));$details=$key?openssl_pkey_get_details($key):[];
if (($details['type']??null)!==OPENSSL_KEYTYPE_EC || ($details['ec']['curve_name']??'')!=='prime256v1') { throw new RuntimeException('Apple ES256 private key required'); }
$b64=function($text){return rtrim(strtr(base64_encode($text),'+/','-_'),'=');};
$now=time();$body=$b64(json_encode(['alg'=>'ES256','kid'=>$keyId])).'.'.$b64(json_encode([
    'iss'=>$teamId,'iat'=>$now,'exp'=>$now+86400*180,'aud'=>'https://appleid.apple.com','sub'=>$servicesId]));
if (!openssl_sign($body,$der,$key,OPENSSL_ALGO_SHA256)) { throw new RuntimeException('Signing failed'); }
// OpenSSL returns ASN.1 DER ECDSA; JOSE requires two fixed-width unsigned integers (R || S).
$offset=2;$raw='';
if (ord($der[0])!==0x30 || ord($der[1])!==strlen($der)-2) { throw new RuntimeException('Invalid ES256 signature'); }
for($i=0;$i<2;$i++) {
    if (ord($der[$offset++])!==2) { throw new RuntimeException('Invalid ES256 integer'); }
    $length=ord($der[$offset++]);$integer=ltrim(substr($der,$offset,$length),"\0");$offset+=$length;
    if(strlen($integer)>32){throw new RuntimeException('Invalid ES256 size');}
    $raw.=str_pad($integer,32,"\0",STR_PAD_LEFT);
}
if($offset!==strlen($der)){throw new RuntimeException('Invalid ES256 length');}
$handle=fopen($output,'x');if(!$handle){throw new RuntimeException('Cannot create private output');}
chmod($output,0600);fwrite($handle,$body.'.'.$b64($raw));fclose($handle);
echo "Apple client secret saved privately. Paste it into Apple provider settings; renew before ".gmdate('Y-m-d',$now+86400*180)." UTC.\n";
