<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_crm_fix extends AdminController{
 public function __construct(){
  parent::__construct();
  if(!staff_can('view',MASTER_CRM_FIX_MODULE_NAME))access_denied(MASTER_CRM_FIX_MODULE_NAME);
  $this->load->library('master_crm_fix/Master_crm_backup_service');
 }
 public function index(){
  $d=['title'=>_l('master_crm_fix'),'jobs'=>$this->master_crm_backup_service->jobs(),
   'backup_root'=>$this->master_crm_backup_service->backupRoot(),'zip_available'=>class_exists('ZipArchive'),
   'writable'=>is_writable($this->master_crm_backup_service->backupRoot()),
   'pack_size'=>(int)get_option('master_crm_fix_pack_size_mb'),
   'db'=>(int)get_option('master_crm_fix_include_database'),
   'archives'=>(int)get_option('master_crm_fix_exclude_archives'),
   'uploads'=>(int)get_option('master_crm_fix_include_uploads')];
  $this->load->view('manage',$d);
 }
 public function start(){
  if(!staff_can('create',MASTER_CRM_FIX_MODULE_NAME))ajax_access_denied();
  $mb=max(50,min(480,(int)$this->input->post('pack_size_mb')));
  update_option('master_crm_fix_pack_size_mb',(string)$mb);
  update_option('master_crm_fix_include_database',$this->input->post('include_database')?'1':'0');
  update_option('master_crm_fix_exclude_archives',$this->input->post('exclude_archives')?'1':'0');
  update_option('master_crm_fix_include_uploads',$this->input->post('include_uploads')?'1':'0');
  try{
   $s=$this->master_crm_backup_service->createJob(['pack_size_mb'=>$mb,
    'include_database'=>$this->input->post('include_database'),
    'exclude_archives'=>$this->input->post('exclude_archives'),
    'include_uploads'=>$this->input->post('include_uploads')]);
   echo json_encode(['success'=>true,'job_id'=>$s['job_id']]);
  }catch(Throwable $e){echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}
 }
 public function process($id){
  if(!staff_can('create',MASTER_CRM_FIX_MODULE_NAME))ajax_access_denied();
  try{
   $s=$this->master_crm_backup_service->process($this->master_crm_backup_service->load($id));
   $p=$s['total_files']?min(100,round($s['processed_files']/$s['total_files']*100,1)):($s['status']==='completed'?100:0);
   echo json_encode(['success'=>true,'status'=>$s['status'],'progress'=>$p,
    'processed'=>$s['processed_files'],'total'=>$s['total_files'],'errors'=>$s['errors']]);
  }catch(Throwable $e){echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}
 }
 public function archives(){
  $this->load->view('archives',['title'=>_l('master_crm_fix_archive_scan'),'archives'=>$this->master_crm_backup_service->archives()]);
 }
 public function health(){
  $r=$this->master_crm_backup_service->backupRoot();
  $this->load->view('health',['title'=>_l('master_crm_fix_health_check'),'checks'=>[
   _l('master_crm_fix_php_version')=>version_compare(PHP_VERSION,'8.5.0','>=')?'OK':'Review: '.PHP_VERSION,
   _l('master_crm_fix_zip_extension')=>class_exists('ZipArchive')?'OK':'Missing',
   _l('master_crm_fix_backup_folder')=>is_dir($r)?'OK':'Missing',
   _l('master_crm_fix_folder_writable')=>is_writable($r)?'OK':'Not Writable',
   _l('master_crm_fix_free_space')=>$this->bytes(@disk_free_space($r))
  ]]);
 }
 public function download($id,$name){
  try{$p=$this->master_crm_backup_service->file($id,rawurldecode($name));$this->load->helper('download');force_download(basename($p),file_get_contents($p));}
  catch(Throwable $e){show_error($e->getMessage(),404);}
 }
 public function delete($id){
  if(!staff_can('delete',MASTER_CRM_FIX_MODULE_NAME))access_denied(MASTER_CRM_FIX_MODULE_NAME);
  set_alert($this->master_crm_backup_service->delete($id)?'success':'danger',_l('master_crm_fix_deleted'));
  redirect(admin_url('master_crm_fix'));
 }
 private function bytes($b){
  $u=['B','KB','MB','GB','TB'];$b=max(0,(float)$b);$p=$b?min((int)floor(log($b,1024)),4):0;
  return round($b/(1024**$p),2).' '.$u[$p];
 }
}
