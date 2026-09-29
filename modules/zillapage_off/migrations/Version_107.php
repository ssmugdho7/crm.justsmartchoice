<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_107 extends App_module_migration{
public function up(){
$CI=&get_instance();
if($CI->db->table_exists(db_prefix().'zillapage_templates')){
$rows=$CI->db->get(db_prefix().'zillapage_templates')->result();
foreach($rows as $r){
$content=preg_replace('/<!--(.*?)-->/s','',$r->content);
$thank=isset($r->thank_you_page)?preg_replace('/<!--(.*?)-->/s','',$r->thank_you_page):'';
$CI->db->where('id',$r->id)->update(db_prefix().'zillapage_templates',['content'=>$content,'thank_you_page'=>$thank]);
}}}}