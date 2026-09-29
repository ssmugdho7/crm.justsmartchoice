<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_362 extends CI_Migration {
 public function up(){
  update_option('smart_choice_crm_build','3.6.2 SC');
  $table=db_prefix().'emailtemplates';
  if($this->db->table_exists($table)){
   $rows=$this->db->select('emailtemplateid,message')->get($table)->result();
   foreach($rows as $r){
    $m=$r->message;
    $m=preg_replace('/(^|>)(\s*)ear(\s+)/i','$1$2Dear$3',$m,1);
    if($m!==$r->message){$this->db->where('emailtemplateid',$r->emailtemplateid)->update($table,['message'=>$m]);}
   }
  }
 }
 public function down(){}
}
