<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Smart_choice_crm_manager extends AdminController {
 public function __construct(){parent::__construct();if(!is_admin())access_denied('Smart Choice CRM Manager');}
 public function index(){$this->load->view('manage',['title'=>_l('smart_choice_crm_manager')]);}
 public function upload(){
  if(!$this->input->post())show_404();
  if(empty($_FILES['package']['tmp_name'])){set_alert('danger',_l('smart_choice_crm_manager_no_file'));redirect(admin_url('smart_choice_crm_manager'));}
  if(!class_exists('ZipArchive')){set_alert('danger','ZipArchive is required.');redirect(admin_url('smart_choice_crm_manager'));}
  $zip=new ZipArchive(); if($zip->open($_FILES['package']['tmp_name'])!==true){set_alert('danger','Invalid ZIP package.');redirect(admin_url('smart_choice_crm_manager'));}
  $allowed=['application/','assets/','resources/','system/','modules/','documentation/'];
  $backup=FCPATH.'temp/core-update-backups/'.date('Y-m-d_H-i-s');@mkdir($backup,0755,true);
  $stage=FCPATH.'temp/core-update-stage/'.date('Y-m-d_H-i-s');@mkdir($stage,0755,true);
  for($i=0;$i<$zip->numFiles;$i++){
   $name=str_replace('\\','/',$zip->getNameIndex($i));
   if($name===''||strpos($name,'../')!==false||str_starts_with($name,'/'))continue;
   $ok=false;foreach($allowed as $prefix){if(str_starts_with($name,$prefix)){$ok=true;break;}}
   if(!$ok)continue;
   $target=$stage.'/'.$name;if(substr($name,-1)==='/'){@mkdir($target,0755,true);continue;}
   @mkdir(dirname($target),0755,true);file_put_contents($target,$zip->getFromIndex($i));
  }
  $zip->close();
  $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($stage,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::LEAVES_ONLY);
  $count=0;
  foreach($it as $f){if(!$f->isFile())continue;$rel=str_replace('\\','/',ltrim(str_replace($stage,'',$f->getPathname()),DIRECTORY_SEPARATOR));$dest=FCPATH.$rel;
   if(is_file($dest)){@mkdir(dirname($backup.'/'.$rel),0755,true);@copy($dest,$backup.'/'.$rel);}
   @mkdir(dirname($dest),0755,true);if(!@copy($f->getPathname(),$dest)){set_alert('danger','Update stopped while copying '.$rel);redirect(admin_url('smart_choice_crm_manager'));}$count++;
  }
  if(function_exists('opcache_reset'))@opcache_reset();clearstatcache(true);
  set_alert('success','CRM update applied. Files updated: '.$count.'. Backup: '.$backup);redirect(admin_url('smart_choice_crm_manager'));
 }
}
