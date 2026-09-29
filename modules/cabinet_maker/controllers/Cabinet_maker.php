<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Cabinet_maker extends AdminController{
 public function __construct(){ parent::__construct(); $this->load->helper('cabinet_maker/cabinet_maker'); $this->load->model('cabinet_maker/cabinet_maker_model'); }
 private function guard($cap='view'){ if(!cabinet_maker_can($cap)){ access_denied('cabinet_maker'); } }
 public function dashboard(){ $this->guard(); $this->load->model('projects_model'); $this->load->view('admin/dashboard',['title'=>_l('cabinet_maker_dashboard'),'designs'=>$this->cabinet_maker_model->get_designs(),'projects'=>$this->projects_model->get()]); }
 public function index(){ $this->guard(); if($this->input->post()){ $this->guard('create'); $id=$this->cabinet_maker_model->create_design($this->input->post(null,true)); if(!$id){ set_alert('danger',_l('cabinet_maker_create_failed')); redirect(admin_url('cabinet_maker')); } set_alert('success',_l('added_successfully',_l('cabinet_maker_design'))); redirect(admin_url('cabinet_maker/designer/'.$id)); }
  $this->load->model('clients_model'); $this->load->model('projects_model');
  $data=['title'=>_l('cabinet_maker'),'designs'=>$this->cabinet_maker_model->get_designs(),'clients'=>$this->clients_model->get(),'projects'=>$this->projects_model->get()]; $this->load->view('admin/index',$data); }
 public function designer($id){ $this->guard(); $d=$this->cabinet_maker_model->get_design($id); if(!$d) show_404(); $data=['title'=>$d->name,'design'=>$d,'materials'=>$this->cabinet_maker_model->materials(),'parts'=>$this->cabinet_maker_model->get_parts($id),'vendors'=>$this->cabinet_maker_model->vendors(),'vendor_items'=>$this->cabinet_maker_model->vendor_items((int)$d->vendor_id)]; $this->load->view('admin/designer',$data); }
 public function save($id){ $this->guard('edit'); $payload=json_decode((string)$this->input->post('payload',false),true)?:[]; $ok=$this->cabinet_maker_model->save_design($id,$payload); if(isset($payload['parts'])&&is_array($payload['parts'])) $this->cabinet_maker_model->replace_parts($id,$payload['parts']); return $this->json(['success'=>$ok,'message'=>$ok?_l('cabinet_maker_saved'):_l('cabinet_maker_save_failed'),'csrfHash'=>$this->security->get_csrf_hash()]); }
 public function share($id){ $this->guard('share'); $d=$this->cabinet_maker_model->get_design($id); if(!$d) show_404(); $share=$this->cabinet_maker_model->create_share($d,(int)get_option('cabinet_maker_share_expiry_days')); return $this->json(['success'=>true,'url'=>cabinet_maker_public_url($share),'csrfHash'=>$this->security->get_csrf_hash()]); }
 public function pdf($id){ $this->guard(); $d=$this->cabinet_maker_model->get_design($id); if(!$d) show_404(); $parts=$this->cabinet_maker_model->get_parts($id); while(ob_get_level()>0){ob_end_clean();} $pdf=app_pdf('cabinet-maker',module_libs_path('cabinet_maker','pdf/Cabinet_maker_pdf'),$d,$parts); $pdf->Output(slug_it($d->name).'-material-list.pdf','I'); exit; }
 public function delete($id){ $this->guard('delete'); $this->cabinet_maker_model->delete_design($id); set_alert('success',_l('deleted',_l('cabinet_maker_design'))); redirect(admin_url('cabinet_maker')); }
 public function materials(){ $this->guard(); if($this->input->post()){ $this->guard('settings'); $id=$this->cabinet_maker_model->add_material($this->input->post(null,true)); set_alert($id?'success':'danger',$id?_l('added_successfully',_l('cabinet_maker_materials')):_l('cabinet_maker_save_failed')); redirect(admin_url('cabinet_maker/materials')); } $this->load->view('admin/materials',['title'=>_l('cabinet_maker_materials'),'materials'=>$this->cabinet_maker_model->materials()]); }
 public function vendors(){ $this->guard('settings'); if($this->input->post('name')){$this->cabinet_maker_model->add_vendor($this->input->post(null,true));set_alert('success',_l('added_successfully',_l('cabinet_maker_vendor')));redirect(admin_url('cabinet_maker/vendors'));} $this->load->view('admin/vendors',['title'=>_l('cabinet_maker_vendors'),'vendors'=>$this->cabinet_maker_model->vendors()]); }
 public function vendor($id){ $this->guard('settings'); $vendor=$this->cabinet_maker_model->vendor($id); if(!$vendor){ set_alert('warning',_l('cabinet_maker_vendor_not_found')); redirect(admin_url('cabinet_maker/vendors')); } if($this->input->post('sku')){$saved=$this->cabinet_maker_model->save_vendor_item($id,$this->input->post(null,true));set_alert($saved?'success':'danger',$saved?_l('cabinet_maker_saved'):_l('cabinet_maker_save_failed'));redirect(admin_url('cabinet_maker/vendor/'.$id));} $this->load->view('admin/vendor',['title'=>_l('cabinet_maker_vendor_catalog'),'vendor'=>$vendor,'items'=>$this->cabinet_maker_model->vendor_items($id)]); }
 public function offcuts(){ $this->guard('manufacturing'); $this->load->view('admin/offcuts',['title'=>_l('cabinet_maker_offcuts'),'offcuts'=>$this->cabinet_maker_model->offcuts()]); }
 public function settings(){ $this->guard('settings'); if($this->input->post()){ foreach((array)$this->input->post('settings') as $k=>$v) update_option($k,$v); set_alert('success',_l('settings_updated')); redirect(admin_url('cabinet_maker/settings')); } $this->load->view('admin/settings',['title'=>_l('cabinet_maker_settings')]); }

 public function ai_render($id){
  $this->guard('edit');
  if(get_option('cabinet_maker_enable_ai')!=='1') return $this->json(['success'=>false,'message'=>'AI rendering is disabled in Cabinet Maker settings.','csrfHash'=>$this->security->get_csrf_hash()]);
  $d=$this->cabinet_maker_model->get_design($id); if(!$d) show_404();
  $payload=json_decode((string)$this->input->post('payload',false),true)?:[];
  $result=cabinet_maker_openai_image_render($payload['image']??'', $payload['prompt']??'');
  $result['csrfHash']=$this->security->get_csrf_hash(); return $this->json($result);
 }
 public function email_share($id){
  $this->guard('share'); $d=$this->cabinet_maker_model->get_design($id); if(!$d) show_404();
  $payload=json_decode((string)$this->input->post('payload',false),true)?:[]; $to=trim((string)($payload['email']??''));
  if(!filter_var($to,FILTER_VALIDATE_EMAIL)) return $this->json(['success'=>false,'message'=>'Enter a valid customer email address.','csrfHash'=>$this->security->get_csrf_hash()]);
  $share=$this->cabinet_maker_model->create_share($d,(int)get_option('cabinet_maker_share_expiry_days')); $url=cabinet_maker_public_url($share);
  $subject='Your kitchen design: '.$d->name; $message='<p>Hello,</p><p>Your Smart Choice kitchen design <strong>'.html_escape($d->name).'</strong> is ready.</p><p><a href="'.html_escape($url).'">View Kitchen Design</a></p><p>Smart Choice Contractors USA</p>';
  $sent=false;
  if(function_exists('send_mail_template')){
   try{$sent=(bool)send_mail_template('cabinet-maker-design-shared',$to,0,0,['design_name'=>$d->name,'design_share_url'=>$url]);}catch(Throwable $e){log_message('error','Cabinet Maker email template: '.$e->getMessage());}
  }
  if(!$sent){$this->load->library('email');$this->email->from(get_option('smtp_email')?:get_option('company_email'),get_option('companyname'));$this->email->to($to);$this->email->subject($subject);$this->email->message($message);$sent=(bool)$this->email->send();}
  return $this->json(['success'=>$sent,'message'=>$sent?'Design email sent successfully.':'The CRM email service could not send the message.','url'=>$url,'csrfHash'=>$this->security->get_csrf_hash()]);
 }
 public function download_module(){
  if(!is_admin()) access_denied('cabinet_maker');
  if(!class_exists('ZipArchive')) show_error('The PHP Zip extension is required to download this module.',500);
  $source=module_dir_path('cabinet_maker'); $tmp=tempnam(sys_get_temp_dir(),'cabinet_maker_'); $zipPath=$tmp.'.zip'; @unlink($tmp);
  $zip=new ZipArchive(); if($zip->open($zipPath,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) show_error('Unable to create the module package.',500);
  $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source,FilesystemIterator::SKIP_DOTS));
  foreach($it as $file){$real=$file->getRealPath();$relative='cabinet_maker/'.str_replace('\\','/',substr($real,strlen($source)));if(strpos($relative,'/.git/')!==false||preg_match('/\.(zip|log)$/i',$relative))continue;$zip->addFile($real,$relative);}
  $zip->close(); while(ob_get_level()>0){ob_end_clean();} header('Content-Type: application/zip');header('Content-Disposition: attachment; filename="cabinet_maker_v'.CABINET_MAKER_VERSION.'.zip"');header('Content-Length: '.filesize($zipPath));readfile($zipPath);@unlink($zipPath);exit;
 }


 public function health_check(){
  $this->guard();
  $checks=[];
  foreach(['designs','materials','parts','offcuts','shares','comments','vendors','vendor_items'] as $table){
   $checks[]=['label'=>cabinet_maker_table($table),'status'=>$this->db->table_exists(cabinet_maker_table($table))?'ok':'missing'];
  }
  $checks[]=['label'=>'English Language File','status'=>is_file(module_dir_path('cabinet_maker','language/english/cabinet_maker_lang.php'))?'ok':'missing'];
  $checks[]=['label'=>'Spanish Language File','status'=>is_file(module_dir_path('cabinet_maker','language/spanish/cabinet_maker_lang.php'))?'ok':'missing'];
  $checks[]=['label'=>'Migration 1.2.9','status'=>is_file(module_dir_path('cabinet_maker','migrations/129_version_129.php'))?'ok':'missing'];
  foreach(['materials','vendors','vendor_items'] as $name){ $table=cabinet_maker_table($name); $checks[]=['label'=>ucwords(str_replace('_',' ',$name)).' Records','status'=>$this->db->table_exists($table)?('ok — '.$this->db->count_all($table).' records'):'missing']; }
  $this->load->view('admin/health_check',['title'=>_l('cabinet_maker_health_check'),'checks'=>$checks]);
 }
 public function upgrade_database(){
  if(!is_admin()) access_denied('cabinet_maker');
  $this->load->helper('cabinet_maker/cabinet_maker');
  try { $report=cabinet_maker_repair_database_safely(); update_option('cabinet_maker_version',CABINET_MAKER_VERSION); set_alert($report['success']?'success':'warning',$report['message']); } catch (Throwable $e) { log_message('error','Cabinet Maker database repair failed: '.$e->getMessage()); set_alert('danger','Database repair failed: '.$e->getMessage()); }
  redirect(admin_url('cabinet_maker/health_check'));
 }
 public function export_json($id){
  $this->guard(); $d=$this->cabinet_maker_model->get_design($id); if(!$d) show_404();
  $payload=['format'=>'Smart Choice Cabinet Design','version'=>CABINET_MAKER_VERSION,'design'=>$d,'parts'=>$this->cabinet_maker_model->get_parts($id)];
  $this->download_text(slug_it($d->name).'.cabinet.json',json_encode($payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),'application/json');
 }
 public function export_cutlist($id){
  $this->guard(); $d=$this->cabinet_maker_model->get_design($id); if(!$d) show_404(); $parts=$this->cabinet_maker_model->get_parts($id);
  $fp=fopen('php://temp','w+'); fputcsv($fp,['Cabinet','Part','Type','Quantity','Width','Length','Thickness','Grain','Unit Cost','Total Cost']);
  foreach($parts as $p){fputcsv($fp,[$p->cabinet_uid,$p->part_name,$p->part_type??'sheet',$p->qty,$p->width,$p->length,$p->thickness,$p->grain,$p->unit_cost,$p->total_cost]);}
  rewind($fp); $csv=stream_get_contents($fp); fclose($fp); $this->download_text(slug_it($d->name).'-cut-list.csv',$csv,'text/csv');
 }
 public function export_dxf($id){
  $this->guard(); $d=$this->cabinet_maker_model->get_design($id); if(!$d) show_404();
  $state=json_decode($d->design_json?:'{}',true); $items=array_merge($state['cabinets']??[],$state['appliances']??[],$state['countertops']??[]);
  $lines=["0","SECTION","2","HEADER","0","ENDSEC","0","SECTION","2","ENTITIES"];
  foreach($items as $o){$x=(float)($o['x']??0);$y=(float)($o['y']??0);$w=(float)($o['w']??0);$dep=(float)($o['d']??0);$name=preg_replace('/[^A-Za-z0-9 _-]/','',(string)($o['name']??'Cabinet'));
   $pts=[[$x,$y],[$x+$w,$y],[$x+$w,$y+$dep],[$x,$y+$dep],[$x,$y]];
   for($i=0;$i<4;$i++){$lines=array_merge($lines,["0","LINE","8","CABINETS","10",(string)$pts[$i][0],"20",(string)$pts[$i][1],"30","0","11",(string)$pts[$i+1][0],"21",(string)$pts[$i+1][1],"31","0"]);} 
   $lines=array_merge($lines,["0","TEXT","8","LABELS","10",(string)$x,"20",(string)$y,"30","0","40","2.5","1",$name]);
  }
  $lines=array_merge($lines,["0","ENDSEC","0","EOF"]);
  $this->download_text(slug_it($d->name).'-plan.dxf',implode("\r\n",$lines),'application/dxf');
 }
 public function export_sketchup($id){
  $this->guard(); $d=$this->cabinet_maker_model->get_design($id); if(!$d) show_404();
  $state=json_decode($d->design_json?:'{}',true); $items=array_merge($state['cabinets']??[],$state['appliances']??[],$state['countertops']??[]);
  $json=json_encode($items,JSON_UNESCAPED_SLASHES);
  $ruby="# Smart Choice Cabinet Maker export for SketchUp 2025\nrequire 'json'\nmodel = Sketchup.active_model\nmodel.start_operation('Import Smart Choice Cabinet Design', true)\nitems = JSON.parse(".var_export($json,true).")\nroot = model.active_entities.add_group\nroot.name = ".var_export($d->name,true)."\nitems.each do |o|\n  g = root.entities.add_group\n  g.name = o['name'].to_s\n  x=o['x'].to_f.inch; y=o['y'].to_f.inch; z=(o['z']||0).to_f.inch\n  w=o['w'].to_f.inch; dep=o['d'].to_f.inch; h=o['h'].to_f.inch\n  face=g.entities.add_face([x,y,z],[x+w,y,z],[x+w,y+dep,z],[x,y+dep,z])\n  face.pushpull(h) if face\n  g.set_attribute('Smart Choice','type',o['type'])\n  g.set_attribute('Smart Choice','style',o['style'])\nend\nmodel.commit_operation\nmodel.active_view.zoom_extents\n";
  $this->download_text(slug_it($d->name).'-sketchup-2025.rb',$ruby,'text/plain');
 }
 private function download_text($filename,$content,$type){
  while(ob_get_level()>0){ob_end_clean();} header('Content-Type: '.$type.'; charset=UTF-8'); header('Content-Disposition: attachment; filename="'.$filename.'"'); header('Content-Length: '.strlen($content)); echo $content; exit;
 }


 public function sample_materials(){ $this->download_text('cabinet-maker-materials-sample.csv',"name,sku,category,width,length,thickness,unit,unit_cost\n",'text/csv'); }
 public function sample_vendors(){ $this->download_text('cabinet-maker-vendors-sample.csv',"name,contact,email,phone\n",'text/csv'); }
 public function sample_designs(){ $this->download_text('cabinet-maker-designs-sample.csv',"name,project_id,client_id,room_width,room_length,room_height\n",'text/csv'); }
 public function import_materials(){ $this->guard('create'); $count=$this->import_csv(function($r){return $this->cabinet_maker_model->add_material($r);}); set_alert('success',$count.' materials imported.'); redirect(admin_url('cabinet_maker/materials')); }
 public function import_vendors(){ $this->guard('create'); $count=$this->import_csv(function($r){return $this->cabinet_maker_model->add_vendor($r);}); set_alert('success',$count.' vendors imported.'); redirect(admin_url('cabinet_maker/vendors')); }
 public function import_designs(){ $this->guard('create'); $count=$this->import_csv(function($r){return $this->cabinet_maker_model->create_design($r);}); set_alert('success',$count.' designs imported.'); redirect(admin_url('cabinet_maker')); }
 private function import_csv($callback){ if(empty($_FILES['file']['tmp_name'])||!is_uploaded_file($_FILES['file']['tmp_name']))return 0;$h=fopen($_FILES['file']['tmp_name'],'r');$headers=fgetcsv($h);if(!$headers){fclose($h);return 0;}$headers=array_map(function($v){return trim(strtolower($v));},$headers);$count=0;while(($row=fgetcsv($h))!==false){$data=[];foreach($headers as $i=>$key){$data[$key]=$row[$i]??'';}if(call_user_func($callback,$data))$count++;}fclose($h);return $count;}

 private function json($data){ $this->output->set_content_type('application/json')->set_output(json_encode($data)); }
}
