<?php
/** CLI maintenance for the private Trash. Never accepts a web request. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$site=realpath($argv[1]??'');$command=$argv[2]??'health';
if(!$site||!is_file($site.'/application/config/app-config.php')){fwrite(STDERR,"Usage: php scripts/recovery/manage.php /absolute/crm/path health|install|baseline|enable|disable\n");exit(1);}
define('BASEPATH',$site.'/system/');define('APPPATH',$site.'/application/');define('FCPATH',$site.'/');
require APPPATH.'config/app-config.php';$code=dirname(__DIR__,2);require $code.'/application/helpers/recovery_helper.php';require $code.'/application/libraries/recovery/DatabaseArchive.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$db=new mysqli(APP_DB_HOSTNAME,APP_DB_USERNAME,APP_DB_PASSWORD,APP_DB_NAME);$db->set_charset('utf8mb4');$archive=new ScDatabaseArchive($db,defined('APP_DB_PREFIX')?APP_DB_PREFIX:'tbl');
$store=sc_recovery_store();
if($command==='install'){echo json_encode($archive->install()),"\n";}
elseif($command==='health'){echo json_encode(['enabled'=>sc_recovery_enabled(),'root'=>$store->root(),'disk_free'=>disk_free_space(dirname($store->root())),'database'=>$archive->health()],JSON_PRETTY_PRINT),"\n";}
elseif($command==='baseline'){
 $count=0;$bytes=0;$root=$store->root();$store->database();
 $it=new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(new RecursiveDirectoryIterator($site,FilesystemIterator::SKIP_DOTS),function($f){return !$f->isLink()&&!in_array($f->getFilename(),['.git','node_modules','vendor','cache','logs'],true);}));
 foreach($it as $file){if(!$file->isFile()||$store->protectedPath($file->getPathname())===null)continue;$store->capture($file->getPathname(),'baseline','baseline');$count++;$bytes+=$file->getSize();if($count%1000===0)echo json_encode(['baseline_files'=>$count,'bytes'=>$bytes]),"\n";}
 file_put_contents($root.'/baseline-complete.json',json_encode(['files'=>$count,'bytes'=>$bytes,'completed_at'=>gmdate('c')]));chmod($root.'/baseline-complete.json',0600);
 echo json_encode(['baseline_files'=>$count,'bytes'=>$bytes,'complete'=>true]),"\n";
}
elseif($command==='enable'){
 $health=$archive->health();if($health['missing_tables']||$health['changed_tables']||!is_file($store->root().'/baseline-complete.json'))throw new RuntimeException('Complete the verified baseline and database coverage before enabling recovery.');
 file_put_contents($store->root().'/enabled',gmdate('c'));chmod($store->root().'/enabled',0600);echo "Recovery enabled.\n";
}
elseif($command==='disable'){if(is_file($store->root().'/enabled'))unlink($store->root().'/enabled');echo "File protection disabled; archives and database protection retained.\n";}
else{fwrite(STDERR,"Unknown command\n");exit(1);}
