<?php
// CLI only. Generates owner signing material or a customer trust configuration outside the web root.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$mode = $argv[1] ?? ''; $dir = $argv[2] ?? '';
if (!in_array($mode,['issuer','consumer'],true) || $dir==='' || $dir[0]!=='/') {
    fwrite(STDERR,"Usage: php tools/sc-license-config.php issuer /absolute/private/directory\n       php tools/sc-license-config.php consumer /absolute/private/directory https://license-server.example /path/to/owner-public.pem\n"); exit(1);
}
$webRoot=realpath(dirname(__DIR__));
if (!is_dir($dir) && !mkdir($dir,0700,true)) { throw new RuntimeException('Cannot create private directory'); }
$dir=realpath($dir);
if (!$dir || strpos($dir.'/',$webRoot.'/')===0 || strpos($dir.'/','/public_html/')!==false || file_exists($dir.'/config.php')) {
    throw new RuntimeException('Use a new private directory outside public_html and the checkout; existing configurations are never overwritten');
}
chmod($dir,0700);
if ($mode==='issuer') {
    $key=openssl_pkey_new(['private_key_type'=>OPENSSL_KEYTYPE_RSA,'private_key_bits'=>3072]);
    if (!$key || !openssl_pkey_export($key,$private)) { throw new RuntimeException('RSA key generation failed'); }
    $public=openssl_pkey_get_details($key)['key'];
    if (file_exists($dir.'/private.pem') || file_exists($dir.'/public.pem')) { throw new RuntimeException('Key material already exists'); }
    file_put_contents($dir.'/private.pem',$private,LOCK_EX); chmod($dir.'/private.pem',0600);
    file_put_contents($dir.'/public.pem',$public,LOCK_EX); chmod($dir.'/public.pem',0644);
    $config=['mode'=>'issuer','private_key_file'=>$dir.'/private.pem','public_key'=>$public];
} else {
    define('BASEPATH',true); require dirname(__DIR__).'/modules/sc_module_licensing/libraries/Sc_license_crypto.php';
    $server=Sc_license_crypto::site($argv[3] ?? '');
    $public=is_file($argv[4] ?? '')?file_get_contents($argv[4]):'';
    $key=openssl_pkey_get_public($public);
    $details=$key?openssl_pkey_get_details($key):[];
    if (($details['type']??null)!==OPENSSL_KEYTYPE_RSA || ($details['bits']??0)<2048) { throw new RuntimeException('A trusted RSA public key of at least 2048 bits is required'); }
    $config=['mode'=>'consumer','server'=>$server,'public_key'=>$public];
}
file_put_contents($dir.'/config.php',"<?php\nreturn ".var_export($config,true).";\n",LOCK_EX); chmod($dir.'/config.php',0600);
echo "Private configuration created: ".$dir."/config.php\nSet SC_LICENSING_CONFIG to that path in the web server environment. Never distribute private.pem.\n";
