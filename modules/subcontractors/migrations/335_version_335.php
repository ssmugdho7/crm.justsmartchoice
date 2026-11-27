<?php
 defined('BASEPATH') or exit('No direct script access allowed');
 class Migration_Version_335 extends App_module_migration {
 public function up() {
 $CI=&get_instance(); $table=db_prefix().'emailtemplates';
 if (!$CI->db->table_exists($table)) { return; }
 $slug='smartsource-subcontractor-application-received';
 if ($CI->db->where('slug',$slug)->where('language','spanish')->count_all_results($table)) { return; }
 $data=['type'=>'smartsource_subcontractors','slug'=>$slug,'language'=>'spanish',
 'name'=>'Confirmación de solicitud de subcontratista',
 'subject'=>'Recibimos su solicitud de subcontratista',
 'message'=>'<p>Hola {contact_name},</p><p>Gracias por enviar su solicitud a Smart Choice Contractors USA.</p><p>Hemos recibido su información. Nuestro equipo revisará su solicitud. Si necesitamos documentos adicionales, licencia o seguro, nos comunicaremos con usted.</p><p>Puede acceder al portal aquí: <a href="{portal_link}">{portal_link}</a></p><p>Gracias,<br>Smart Choice Contractors USA</p>',
 'fromname'=>'{companyname}','active'=>1];
 foreach (array_keys($data) as $field) { if (!$CI->db->field_exists($field,$table)) unset($data[$field]); }
 $CI->db->insert($table,$data);
 }
 }
