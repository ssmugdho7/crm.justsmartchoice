<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_106 extends App_module_migration {
 public function up(){
  $CI=&get_instance(); $table=db_prefix().'notes';
  if(!$CI->db->table_exists($table)){ return; }
  $fields=$CI->db->list_fields($table);
  $adds=['note_color'=>"VARCHAR(20) NULL DEFAULT '#00A651'",'priority'=>"VARCHAR(20) NULL DEFAULT 'medium'",'note_visibility'=>"VARCHAR(20) NULL DEFAULT 'normal'",'assigned_staff_id'=>"INT(11) NULL DEFAULT NULL",'attachment'=>"VARCHAR(255) NULL DEFAULT NULL",'attachment_original_name'=>"VARCHAR(255) NULL DEFAULT NULL"];
  foreach($adds as $name=>$sql){ if(!in_array($name,$fields)){ $CI->db->query("ALTER TABLE `{$table}` ADD `{$name}` {$sql}"); } }
 }
}
