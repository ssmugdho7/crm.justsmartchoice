<?php
/** Nightly private database backup and media reconciliation. CLI only. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$code=realpath(dirname(__DIR__,2));$site=$code;
foreach($argv as $argument){if(strpos($argument,'--site=')===0)$site=realpath(substr($argument,7));}
if(!$site||!is_file($site.'/application/config/app-config.php')){fwrite(STDERR,"A valid CRM installation is required.\n");exit(1);}
define('BASEPATH',$site.'/system/');define('APPPATH',$site.'/application/');define('FCPATH',$site.'/');
require APPPATH.'config/app-config.php';require $code.'/application/helpers/recovery_helper.php';require $code.'/application/libraries/recovery/DatabaseArchive.php';
$initial=in_array('--initial',$argv,true);
if (!sc_recovery_enabled()&&!$initial) exit;
sc_recovery_store()->database();
$root=sc_recovery_root();$lock=fopen($root.'/maintenance.lock','c');
if(!$lock||!flock($lock,LOCK_EX|LOCK_NB))exit;
$daily=in_array('--daily',$argv,true);$now=new DateTimeImmutable('now',new DateTimeZone('Asia/Dhaka'));
if($daily&&$now->format('H')!=='03')exit;
$statusPath=$root.'/maintenance.json';$previous=is_file($statusPath)?json_decode(file_get_contents($statusPath),true):[];
if($daily&&($previous['date']??'')===$now->format('Y-m-d')&&($previous['ok']??false))exit;
umask(0077);$temporary=[];$exitCode=0;
try{
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$db=new mysqli(APP_DB_HOSTNAME,APP_DB_USERNAME,APP_DB_PASSWORD,APP_DB_NAME);$db->set_charset('utf8mb4');
 $archive=new ScDatabaseArchive($db,defined('APP_DB_PREFIX')?APP_DB_PREFIX:'tbl');$coverage=$initial?['initial_backup'=>true]:$archive->install();
 $backups=$root.'/database';if(!is_dir($backups)&&!mkdir($backups,0700,true))throw new RuntimeException('Cannot create private backup directory.');
 $stamp=gmdate('Ymd-His');$auth=tempnam($root,'.mysql-');$temporary[]=$auth;
 $quote=static function($s){return '"'.str_replace(["\\","\"","\n","\r"],["\\\\","\\\"","\\n","\\r"],(string)$s).'"';};
 file_put_contents($auth,"[client]\nhost=".$quote(APP_DB_HOSTNAME)."\nuser=".$quote(APP_DB_USERNAME)."\npassword=".$quote(APP_DB_PASSWORD)."\n");chmod($auth,0600);
 $error=tempnam($root,'.dump-error-');$temporary[]=$error;$target=$backups.'/'.$stamp.'.sql.gz';$part=$target.'.part';$temporary[]=$part;
 $binary=is_file('/usr/bin/mysqldump')?'/usr/bin/mysqldump':'mysqldump';
 $process=proc_open([$binary,'--defaults-extra-file='.$auth,'--single-transaction','--quick','--routines','--triggers','--hex-blob','--no-tablespaces','--default-character-set=utf8mb4',APP_DB_NAME],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['file',$error,'w']],$pipes);
 if(!is_resource($process))throw new RuntimeException('Cannot start a database backup.');
 $gz=gzopen($part,'wb6');if(!$gz)throw new RuntimeException('Cannot write the database backup.');$bytes=0;
 while(!feof($pipes[1])){$chunk=fread($pipes[1],1048576);if($chunk===false)throw new RuntimeException('Reading the database backup failed.');if($chunk!==''){$written=gzwrite($gz,$chunk);if($written!==strlen($chunk))throw new RuntimeException('The database backup is incomplete.');$bytes+=$written;}}
 fclose($pipes[1]);gzclose($gz);$exit=proc_close($process);
 if($exit!==0||$bytes<1000)throw new RuntimeException('The database backup did not complete. See the private maintenance status.');
 // Validate the entire gzip stream before making the snapshot available.
 $verify=gzopen($part,'rb');$verified=0;while(!gzeof($verify)){$data=gzread($verify,1048576);if($data===false)throw new RuntimeException('Database backup verification failed.');$verified+=strlen($data);}gzclose($verify);
 if($verified!==$bytes||!rename($part,$target))throw new RuntimeException('Database backup verification failed.');chmod($target,0600);
 file_put_contents($target.'.sha256',hash_file('sha256',$target)."\n");
 $files=0;$store=sc_recovery_store();$it=new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(new RecursiveDirectoryIterator($site,FilesystemIterator::SKIP_DOTS),static function($f){return !$f->isLink()&&!in_array($f->getFilename(),['.git','node_modules','vendor','cache','logs'],true);}));
 foreach($it as $file){if(!$file->isFile()||$store->protectedPath($file->getPathname())===null)continue;$store->capture($file->getPathname(),'baseline','daily-'.$now->format('Y-m-d'));$files++;}
 if($initial){file_put_contents($root.'/baseline-complete.json',json_encode(['files'=>$files,'completed_at'=>gmdate('c')]));chmod($root.'/baseline-complete.json',0600);}
 $status=['ok'=>true,'date'=>$now->format('Y-m-d'),'completed_at'=>gmdate('c'),'database_backup'=>basename($target),'database_bytes'=>$bytes,'verified_bytes'=>$verified,'media_files'=>$files,'coverage'=>$coverage];
 file_put_contents($statusPath,json_encode($status,JSON_PRETTY_PRINT));chmod($statusPath,0600);echo json_encode($status),"\n";
}catch(Throwable $e){$status=['ok'=>false,'date'=>$now->format('Y-m-d'),'failed_at'=>gmdate('c'),'message'=>$e->getMessage()];file_put_contents($statusPath,json_encode($status));chmod($statusPath,0600);fwrite(STDERR,"Recovery maintenance failed; see private maintenance.json.\n");$exitCode=1;}
finally{foreach($temporary as $path){if(is_file($path))unlink($path);}flock($lock,LOCK_UN);fclose($lock);}

exit($exitCode);
