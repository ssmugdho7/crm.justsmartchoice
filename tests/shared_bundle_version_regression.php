<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', true);
$dir=sys_get_temp_dir().'/crm-assets-'.bin2hex(random_bytes(8));
mkdir($dir.'/assets/builds',0700,true);
define('FCPATH',$dir.'/');
register_shutdown_function(function()use($dir){unlink($dir.'/assets/builds/common.js');rmdir($dir.'/assets/builds');rmdir($dir.'/assets');rmdir($dir);});
file_put_contents($dir.'/assets/builds/common.js','fixture');
function base_url($path=''){return 'https://crm.example.test/'.$path;}
function get_app_version(){return '4.3.5';}
require dirname(__DIR__).'/application/libraries/assets/App_assets.php';
class AssetVersionFixture extends App_assets { public function url($p,$v=true){return $this->compileUrl($p,$v);} }
$assets=new AssetVersionFixture();
function check($ok,$label){if(!$ok)throw new RuntimeException($label);}
$first=$assets->url('assets/builds/common.js');
parse_str(parse_url($first,PHP_URL_QUERY),$query);
check(isset($query['mtime']) && $query['v']==='4.3.5','Shared bundle retains app version and adds file version');
touch($dir.'/assets/builds/common.js',time()+60);clearstatcache();
check($first!==$assets->url('assets/builds/common.js'),'Changed bundle invalidates browser cache');
check($assets->url('assets/js/other.js')==='https://crm.example.test/assets/js/other.js?v=4.3.5','Other asset URLs preserved');
check($assets->url('https://example.test/external.js')==='https://example.test/external.js','External assets preserved');
check($assets->url('assets/builds/common.js',false)==='https://crm.example.test/assets/builds/common.js','Explicit version opt-out preserved');
echo "5 shared-bundle cache checks passed.\n";
