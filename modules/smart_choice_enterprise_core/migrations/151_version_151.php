<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_151 extends App_module_migration
{
 public function up(){ $CI=&get_instance();$table=db_prefix().'sce_field_permissions';if($CI->db->table_exists($table)){if(!$CI->db->field_exists('can_view_own',$table))$CI->db->query("ALTER TABLE `{$table}` ADD `can_view_own` TINYINT(1) NOT NULL DEFAULT 0 AFTER `can_view`");if(!$CI->db->field_exists('can_view_global',$table))$CI->db->query("ALTER TABLE `{$table}` ADD `can_view_global` TINYINT(1) NOT NULL DEFAULT 0 AFTER `can_view_own`");}$options=['smart_choice_enterprise_core_version'=>'1.5.1','enterprise_api_base_url'=>'','enterprise_api_key'=>'','enterprise_api_status'=>'inactive'];foreach($options as $k=>$v){get_option($k)===''?add_option($k,$v):update_option($k,$v);} }
 public function down(){}
}
