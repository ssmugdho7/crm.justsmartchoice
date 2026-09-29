<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Smart_choice_core_manager extends AdminController {
 public function __construct(){ parent::__construct(); if(!is_admin()) access_denied('Smart Choice Core Manager'); }
 public function index(){
   if($this->input->post()){
     $settings=$this->input->post('settings',false)?:[];
     if(isset($settings['sc_company_ein'])) $settings['sc_company_ein']=preg_replace('/\D+/','',$settings['sc_company_ein']);
     foreach($settings as $k=>$v){ if(strpos($k,'sc_')===0) update_option($k,is_array($v)?implode(',',$v):$v); }
     set_alert('success',_l('settings_updated')); redirect(admin_url('smart_choice_core_manager'));
   }
   $data['title']=_l('sc_core_manager'); $this->load->view('settings',$data);
 }
 public function clear_cache(){
   $dirs=[APPPATH.'cache/']; $deleted=0;
   foreach($dirs as $dir){ foreach(glob($dir.'*')?:[] as $f){ if(is_file($f) && basename($f)!=='index.html' && basename($f)!=='.htaccess'){ @unlink($f); $deleted++; } } }
   $this->audit('clear_cache',['deleted'=>$deleted]); set_alert('success','CRM cache files cleared successfully.'); redirect(admin_url('smart_choice_core_manager'));
 }
 public function cron_test(){
   $stamp=date('Y-m-d H:i:s'); update_option('sc_last_cron_test',$stamp); $this->audit('cron_test',['time'=>$stamp]);
   set_alert('success','Cron diagnostic recorded. Confirm the next scheduled cron execution updates normal CRM automation activity.'); redirect(admin_url('smart_choice_core_manager'));
 }
 public function speed_test(){
   $start=microtime(true); $target=FCPATH.'temp/sc_speed_test.bin'; $bytes=2*1024*1024;
   $data=random_bytes($bytes); file_put_contents($target,$data); $write=microtime(true)-$start;
   $start=microtime(true); file_get_contents($target); $read=microtime(true)-$start; @unlink($target);
   $result=['server_write_mbps'=>round(($bytes*8/1000000)/max($write,.001),2),'server_read_mbps'=>round(($bytes*8/1000000)/max($read,.001),2),'tested_at'=>date('c')];
   update_option('sc_last_speed_test',json_encode($result)); $this->audit('speed_test',$result);
   set_alert('success','Server transfer diagnostic completed. This measures CRM server storage throughput, not the visitor\'s ISP line speed.'); redirect(admin_url('smart_choice_core_manager'));
 }
 public function download_module($name){
   $name=preg_replace('/[^a-zA-Z0-9_-]/','',$name); $path=module_dir_path($name); if(!$name||!is_dir($path)) show_404();
   $tmp=FCPATH.'temp/'.$name.'_'.date('Y-m-d').'.zip'; $this->zipDirectory($path,$tmp,$name); $this->audit('download_module',['module'=>$name]); force_download($tmp,null); @unlink($tmp);
 }
 public function download_core_manifest(){
   $data=['build'=>get_option('sc_core_build_version'),'crm_database_version'=>$this->current_db_version,'generated_at'=>date('c'),'php'=>PHP_VERSION,'manager'=>'1.0.0'];
   force_download('smart-choice-crm-build-'.preg_replace('/[^A-Za-z0-9._-]/','-',get_option('sc_core_build_version')).'.json',json_encode($data,JSON_PRETTY_PRINT));
 }
 private function zipDirectory($source,$destination,$rootName){
   $zip=new ZipArchive(); if($zip->open($destination,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) show_error('Unable to create ZIP.');
   $source=realpath($source); $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source,FilesystemIterator::SKIP_DOTS));
   foreach($it as $file){ if($file->isDir()) continue; $full=$file->getRealPath(); $rel=$rootName.'/'.substr($full,strlen($source)+1); if(preg_match('/\.(zip|log)$/i',$rel)) continue; $zip->addFile($full,$rel); } $zip->close();
 }
 private function audit($action,$details=[]){ $this->db->insert(db_prefix().'sc_core_audit',['staff_id'=>get_staff_user_id(),'action'=>$action,'details'=>json_encode($details),'created_at'=>date('Y-m-d H:i:s')]); }
}
