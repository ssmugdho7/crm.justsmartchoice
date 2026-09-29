<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_crm_backup_service{
 private $CI;
 private $crmRoot;
 private $backupRoot;
 private $archiveExt=['zip','tar','gz','tgz','rar','7z','bz2'];

 public function __construct(){
  $this->CI=&get_instance();
  $this->crmRoot=rtrim(realpath(FCPATH)?:FCPATH,DIRECTORY_SEPARATOR);
  $this->backupRoot=dirname($this->crmRoot).DIRECTORY_SEPARATOR.'CRM MASTER BACKUP';
  if(!is_dir($this->backupRoot)){@mkdir($this->backupRoot,0755,true);}
 }

 public function backupRoot(){return $this->backupRoot;}

 public function createJob(array $in){
  $id=date('Y-m-d_H-i-s').'_'.bin2hex(random_bytes(3));
  $dir=$this->backupRoot.DIRECTORY_SEPARATOR.$id;
  if(!@mkdir($dir,0755,true) && !is_dir($dir)){throw new RuntimeException('Unable to create backup folder.');}
  $mb=max(50,min(480,(int)($in['pack_size_mb']??480)));
  $s=[
   'job_id'=>$id,'created_at'=>date('c'),'updated_at'=>date('c'),'status'=>'scanning',
   'crm_root'=>$this->crmRoot,'job_dir'=>$dir,'pack_size_mb'=>$mb,'pack_size_bytes'=>$mb*1048576,
   'include_database'=>!empty($in['include_database']),'exclude_archives'=>!empty($in['exclude_archives']),
   'include_uploads'=>!empty($in['include_uploads']),'packs'=>[],'excluded'=>[],'archives'=>[],
   'current_pack'=>0,'current_file'=>0,'processed_files'=>0,'total_files'=>0,'total_bytes'=>0,
   'errors'=>[],'database_exported'=>false,'output_files'=>[]
  ];
  $this->save($s); return $s;
 }

 public function process(array $s){
  if($s['status']==='scanning') return $this->scan($s);
  if($s['status']==='packing') return $this->pack($s);
  if($s['status']==='database') return $this->database($s);
  if($s['status']==='finalizing') return $this->finalize($s);
  return $s;
 }

 private function scan(array $s){
  $all=[];$excluded=[];$archives=[];
  $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->crmRoot,FilesystemIterator::SKIP_DOTS));
  foreach($it as $f){
   if(!$f->isFile()||$f->isLink()) continue;
   $abs=$f->getPathname();
   $rel=str_replace('\\','/',ltrim(str_replace($this->crmRoot,'',$abs),DIRECTORY_SEPARATOR));
   $ext=strtolower(pathinfo($abs,PATHINFO_EXTENSION));
   if($this->excludedPath($rel,$s)){ $excluded[]=$rel; continue; }
   if(in_array($ext,$this->archiveExt,true)){
    $archives[]=['path'=>$rel,'size'=>$f->getSize()];
    if($s['exclude_archives']){ $excluded[]=$rel; continue; }
   }
   $size=(int)$f->getSize();
   if($size>$s['pack_size_bytes']){ $excluded[]=$rel.' [Exceeds pack size]'; continue; }
   $all[]=['absolute'=>$abs,'relative'=>$rel,'size'=>$size];
  }
  usort($all,fn($a,$b)=>strcmp($a['relative'],$b['relative']));
  $packs=[];$p=[];$n=0;
  foreach($all as $f){
   if($p && $n+$f['size']>$s['pack_size_bytes']){$packs[]=$p;$p=[];$n=0;}
   $p[]=$f;$n+=$f['size'];
  }
  if($p)$packs[]=$p;
  $s['packs']=$packs;$s['excluded']=$excluded;$s['archives']=$archives;
  $s['total_files']=count($all);$s['total_bytes']=array_sum(array_column($all,'size'));
  $s['status']='packing';$s['updated_at']=date('c');
  $this->reports($s);$this->save($s); return $s;
 }

 private function pack(array $s){
  @set_time_limit(25);$start=microtime(true);$done=0;
  while($s['current_pack']<count($s['packs'])){
   $pi=(int)$s['current_pack'];$items=$s['packs'][$pi];
   $name='CRM Pack '.($pi+1).'.zip';$path=$s['job_dir'].'/'.$name;
   $z=new ZipArchive();
   if($z->open($path,file_exists($path)?0:ZipArchive::CREATE)!==true){
    $s['status']='failed';$s['errors'][]='Unable to open '.$name;$this->save($s);return $s;
   }
   while($s['current_file']<count($items)){
    $f=$items[$s['current_file']];
    if(is_readable($f['absolute'])){$z->addFile($f['absolute'],$f['relative']);}
    else{$s['errors'][]='Unreadable: '.$f['relative'];}
    $s['current_file']++;$s['processed_files']++;$done++;
    if($done>=200 || microtime(true)-$start>=16) break;
   }
   $z->close();
   if($s['current_file']>=count($items)){$s['current_pack']++;$s['current_file']=0;}
   if($done>=200 || microtime(true)-$start>=16) break;
  }
  if($s['current_pack']>=count($s['packs'])){$s['status']=$s['include_database']?'database':'finalizing';}
  $s['updated_at']=date('c');$this->save($s);return $s;
 }

 private function database(array $s){
  @set_time_limit(0);
  $sql=$s['job_dir'].'/CRM Database '.$s['job_id'].'.sql';
  $h=@fopen($sql,'wb');
  if(!$h){$s['errors'][]='Unable to create SQL file.';$s['status']='finalizing';$this->save($s);return $s;}
  fwrite($h,"-- Smart Choice CRM Database Backup\\nSET FOREIGN_KEY_CHECKS=0;\\n\\n");
  foreach($this->CI->db->list_tables() as $t){
   $q=$this->CI->db->query('SHOW CREATE TABLE `'.str_replace('`','``',$t).'`')->row_array();
   $create=$q['Create Table']??(array_values($q)[1]??'');
   fwrite($h,'DROP TABLE IF EXISTS `'.str_replace('`','``',$t)."`;\\n".$create.";\\n");
   for($offset=0;;$offset+=250){
    $rows=$this->CI->db->limit(250,$offset)->get($t)->result_array();
    foreach($rows as $r){
     $cols=[];$vals=[];
     foreach($r as $c=>$v){$cols[]='`'.str_replace('`','``',$c).'`';$vals[]=$v===null?'NULL':$this->CI->db->escape($v);}
     fwrite($h,'INSERT INTO `'.str_replace('`','``',$t).'` ('.implode(',',$cols).') VALUES ('.implode(',',$vals).");\\n");
    }
    if(count($rows)<250) break;
   }
   fwrite($h,"\\n");
  }
  fwrite($h,"SET FOREIGN_KEY_CHECKS=1;\\n");fclose($h);
  $z=new ZipArchive();$zp=$s['job_dir'].'/CRM Database.zip';
  if($z->open($zp,ZipArchive::CREATE|ZipArchive::OVERWRITE)===true){
   $z->addFile($sql,basename($sql));$z->close();@unlink($sql);$s['database_exported']=true;
  } else {$s['errors'][]='Unable to compress database export.';}
  $s['status']='finalizing';$s['updated_at']=date('c');$this->save($s);return $s;
 }

 private function finalize(array $s){
  $out=[];
  foreach(glob($s['job_dir'].'/*.zip')?:[] as $f){
   $out[]=['name'=>basename($f),'size'=>filesize($f),'sha256'=>hash_file('sha256',$f)];
  }
  $manifest=['backup_id'=>$s['job_id'],'created_at'=>$s['created_at'],'completed_at'=>date('c'),
   'total_source_files'=>$s['total_files'],'total_source_bytes'=>$s['total_bytes'],
   'database_exported'=>$s['database_exported'],'packs'=>$out,'errors'=>$s['errors']];
  file_put_contents($s['job_dir'].'/BACKUP MANIFEST.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  file_put_contents($s['job_dir'].'/RESTORE INSTRUCTIONS.txt',
   "SMART CHOICE CRM MASTER RESTORE\\n\\n1. Put CRM in maintenance mode.\\n2. Extract CRM Pack files in numerical order into:\\n".
   $s['crm_root']."\\n3. Allow overwrite.\\n4. Import CRM Database.zip only when a database rollback is required.\\n".
   "5. Clear application/cache and test login, modules, email, PDF, cron, uploads, and client portal.\\n\\n".
   "Full restore must be completed through cPanel File Manager or SSH, not inside the running CRM.\\n");
  $s['status']='completed';$s['output_files']=$out;$s['updated_at']=date('c');$this->save($s);return $s;
 }

 public function jobs(){
  $r=[];
  foreach(glob($this->backupRoot.'/*',GLOB_ONLYDIR)?:[] as $d){
   $f=$d.'/state.json'; if(is_file($f)){ $s=json_decode(file_get_contents($f),true); if(is_array($s))$r[]=$s; }
  }
  usort($r,fn($a,$b)=>strcmp($b['created_at']??'',$a['created_at']??'')); return $r;
 }

 public function load($id){
  $id=$this->safeId($id);$f=$this->backupRoot.'/'.$id.'/state.json';
  if(!is_file($f))throw new RuntimeException('Backup job not found.');
  $s=json_decode(file_get_contents($f),true);
  if(!is_array($s))throw new RuntimeException('Invalid backup state.');
  return $s;
 }

 public function save(array $s){file_put_contents($s['job_dir'].'/state.json',json_encode($s,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));}

 public function file($id,$name){
  $id=$this->safeId($id);$name=basename($name);$base=realpath($this->backupRoot.'/'.$id);$f=realpath($base.'/'.$name);
  if(!$base||!$f||strpos($f,$base.DIRECTORY_SEPARATOR)!==0||!is_file($f))throw new RuntimeException('File not found.');
  return $f;
 }

 public function delete($id){
  $id=$this->safeId($id);$dir=$this->backupRoot.'/'.$id;if(!is_dir($dir))return true;
  $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
  foreach($it as $x){$x->isDir()?@rmdir($x->getPathname()):@unlink($x->getPathname());}
  return @rmdir($dir);
 }

 public function archives(){
  $r=[];$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->crmRoot,FilesystemIterator::SKIP_DOTS));
  foreach($it as $f){
   if($f->isFile() && in_array(strtolower(pathinfo($f->getFilename(),PATHINFO_EXTENSION)),$this->archiveExt,true)){
    $r[]=['path'=>str_replace('\\','/',ltrim(str_replace($this->crmRoot,'',$f->getPathname()),DIRECTORY_SEPARATOR)),
      'size'=>$f->getSize(),'modified'=>date('Y-m-d H:i:s',$f->getMTime())];
   }
  }
  usort($r,fn($a,$b)=>$b['size']<=>$a['size']);return $r;
 }

 private function excludedPath($p,array $s){
  $p=trim(str_replace('\\','/',$p),'/');
  foreach(['.git/','.svn/','node_modules/','application/cache/','application/logs/'] as $x){
   if(strpos($p,$x)===0||strpos('/'.$p,'/'.rtrim($x,'/'))!==false)return true;
  }
  if(!$s['include_uploads']&&(strpos($p,'uploads/')===0||strpos($p,'media/')===0))return true;
  return false;
 }

 private function reports(array $s){
  $a="EXISTING ARCHIVES FOUND INSIDE CRM\\n\\n";
  foreach($s['archives'] as $x)$a.=$x['path'].' | '.$x['size']." bytes\\n";
  file_put_contents($s['job_dir'].'/EXISTING ARCHIVES REPORT.txt',$a);
  file_put_contents($s['job_dir'].'/EXCLUDED FILES.txt',"FILES EXCLUDED FROM THIS BACKUP\\n\\n".implode("\\n",$s['excluded']));
 }

 private function safeId($id){
  $id=preg_replace('/[^A-Za-z0-9_-]/','',(string)$id);
  if($id==='')throw new InvalidArgumentException('Invalid backup job.');
  return $id;
 }
}
