<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL);
set_error_handler(static function($number,$message){throw new RuntimeException($message);});
require dirname(__DIR__).'/modules/appointly/vendor/autoload.php';
foreach (['Google_Service','Google_Service_Resource','Google_Model','Google_Collection','Google\\Service','Google\\Service\\Resource','Google\\Model','Google\\Collection'] as $class) {
    if (!class_exists($class)) { throw new RuntimeException('Missing SDK class '.$class); }
}
$model=new Google_Model();$model['fixture']='value';
if($model['fixture']!=='value')throw new RuntimeException('ArrayAccess value');
unset($model['fixture']);if(isset($model['fixture']))throw new RuntimeException('ArrayAccess unset');
// Generated service collections declare their item property.
class DiagnosticGoogleCollection extends Google_Collection { public $items; }
$list=new DiagnosticGoogleCollection();$list['items']=[1,2];
if(count($list)!==2 || iterator_to_array($list)!==[1,2])throw new RuntimeException('Collection iteration');
$list[1]=3;if($list[1]!==3)throw new RuntimeException('Collection ArrayAccess');
unset($list[1]);if(isset($list[1]))throw new RuntimeException('Collection unset');
restore_error_handler();
echo "PASS Google aliases, ArrayAccess, iteration and count with strict PHP error handling\n";
