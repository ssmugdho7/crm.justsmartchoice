<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$base=sys_get_temp_dir().'/crm-recovery-test-'.bin2hex(random_bytes(6));mkdir($base.'/site/uploads/development/39',0755,true);mkdir($base.'/site/cache',0755,true);
define('BASEPATH',__DIR__);define('APPPATH',dirname(__DIR__).'/application/');define('FCPATH',$base.'/site/');define('SC_RECOVERY_ROOT',$base.'/private-trash');define('SC_RECOVERY_ENABLED',true);
require APPPATH.'helpers/recovery_helper.php';require dirname(__DIR__).'/scripts/recovery/protect_calls.php';
$n=0;function check($ok,$message){global $n;if(!$ok)throw new RuntimeException($message);$n++;echo "PASS $message\n";}
function throws($callback){try{$callback();return false;}catch(RuntimeException $e){return true;}}
$path=FCPATH.'uploads/development/39/picture.png';file_put_contents($path,"original-image\0bytes");
check(sc_recovery_unlink($path)===true&&!is_file($path),'original deletion behavior preserved');
$rows=sc_recovery_store()->entries();check(count($rows)===1&&$rows[0]['event']==='delete','deleted image appears in private Trash');
$entry=$rows[0];$blob=sc_recovery_store()->object($entry);check(file_get_contents($blob)==="original-image\0bytes",'recovery bytes identical');check((fileperms($blob)&0777)===0600,'archived objects are private');check((fileperms(SC_RECOVERY_ROOT)&0777)===0700,'recovery directory private');
check(sc_recovery_store()->restore((int)$entry['id'])==='uploads/development/39/picture.png','restore uses original path');check(file_get_contents($path)==="original-image\0bytes",'restored bytes identical');check((fileperms($path)&0777)===0644,'restored static media readable');
file_put_contents($path,'newer-file');check(throws(fn()=>sc_recovery_store()->restore((int)$entry['id']))&&file_get_contents($path)==='newer-file','restore never overwrites current content');check(file_get_contents($blob)==="original-image\0bytes",'editing restored file cannot change archived bytes');
sc_recovery_file_put_contents($path,'replacement');$rows=sc_recovery_store()->entries(1,true);check((bool)array_filter($rows,fn($r)=>$r['event']==='replace'&&file_get_contents(sc_recovery_store()->object($r))==='newer-file'),'replacement preserves previous version');
$h=sc_recovery_fopen($path,'wb');fwrite($h,'streamed-content');sc_recovery_fclose($h);check((bool)array_filter(sc_recovery_store()->entries(1,true),fn($r)=>$r['event']==='write'&&file_get_contents(sc_recovery_store()->object($r))==='streamed-content'),'stream writes saved on close');
$to=dirname($path).'/renamed.png';file_put_contents($to,'previous-destination');check(sc_recovery_rename($path,$to)&&!is_file($path),'rename behavior preserved');check((bool)array_filter(sc_recovery_store()->entries(1,true),fn($r)=>$r['event']==='replace'&&file_get_contents(sc_recovery_store()->object($r))==='previous-destination'),'rename preserves overwritten destination');
$cache=FCPATH.'cache/temp';file_put_contents($cache,'cache');$before=count(sc_recovery_store()->entries(1,true));check(sc_recovery_unlink($cache),'cache cleanup works');check(count(sc_recovery_store()->entries(1,true))===$before,'disposable cache excluded from file archive');
$outside=$base.'/outside.txt';file_put_contents($outside,'outside');symlink($outside,dirname($path).'/linked.png');check(throws(fn()=>sc_recovery_unlink(dirname($path).'/linked.png'))&&is_file($outside),'symlink deletion fails safely');
check(throws(fn()=>new ScTrashStore(FCPATH,FCPATH.'uploads/trash')),'public recovery store rejected');
check(sc_recovery_guard_sql(' /* comment */ TRUNCATE TABLE tblinvoices','tbl')==='DELETE FROM tblinvoices','clear-all workflows use recoverable row deletions');
check(sc_recovery_guard_sql('DROP TEMPORARY TABLE temp_report','tbl')==='DROP TEMPORARY TABLE temp_report','temporary report cleanup remains available');check(throws(fn()=>sc_recovery_guard_sql('DROP TABLE tblclients','tbl')),'DROP cannot bypass Trash');check(throws(fn()=>sc_recovery_guard_sql('DELETE FROM tblsc_recovery_rows','tbl')),'archive purge blocked');sc_recovery_guard_sql('SELECT * FROM tblinvoices','tbl');sc_recovery_guard_sql('DELETE FROM tblinvoices WHERE id=1','tbl');check(true,'normal database reads and trigger-backed deletes permitted');
$code='<?php // unlink($comment);'."\n".'$x="unlink($text)"; $o->unlink($file); C::copy($a,$b); unlink($file); \\copy($a,$b); array_map("unlink",$files);';
[$rewritten,$count]=sc_recovery_rewrite_source($code);check($count===3,'only native function calls and callbacks protected');check(strpos($rewritten,'$o->unlink($file)')!==false&&strpos($rewritten,'C::copy($a,$b)')!==false,'object and static methods unchanged');check(strpos($rewritten,'// unlink($comment);')!==false&&strpos($rewritten,'"unlink($text)"')!==false,'comments and business strings unchanged');check(sc_recovery_rewrite_source($rewritten)[1]===0,'source migration idempotent');
// A corrupt archive cannot authorize loss of the only remaining original.
file_put_contents($blob,'corrupt');$original=FCPATH.$entry['path'];file_put_contents($original,"original-image\0bytes");check(throws(fn()=>sc_recovery_unlink($original))&&is_file($original),'corrupt backup blocks deletion');
// Cleanup the isolated fixture with native functions, never the production vault.
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $file){if($file->isDir()&&!$file->isLink())rmdir($file->getPathname());else unlink($file->getPathname());}rmdir($base);
echo "PASS $n recovery behavior checks.\n";
