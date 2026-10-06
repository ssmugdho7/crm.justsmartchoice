<?php
// Profile presentation checks only; never authenticate, connect to a database or submit a form.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function _l($v,...$args) { return $v; }
function site_url($v='') { return 'https://portal.example/'.$v; }
function set_value($v,$default='') { return e($default); }
function form_error($v) { return ''; }
function form_open_multipart($route,$attrs) { return '<form action="'.site_url($route).'" method="post" enctype="multipart/form-data" autocomplete="'.$attrs['autocomplete'].'"><input type="hidden" name="csrf_test" value="existing-token">'; }
function form_open($route) { return '<form action="'.site_url($route).'" method="post"><input type="hidden" name="csrf_test" value="existing-token">'; }
function form_hidden($key,$v) { return '<input type="hidden" name="'.$key.'" value="'.$v.'">'; }
function form_close() { return '</form>'; }
function get_contact_user_id() { return 7; }
function contact_profile_image_url($id,$size) { return site_url('uploads/contact_profile_images/7/thumb.png'); }
function time_ago($v) { return 'one day'; }
function render_custom_fields($type,$id,$scope) { if($type!=='contacts'||$id!==7||$scope!==['show_on_client_portal'=>1]) throw new RuntimeException('Custom-field scope changed'); return '<input name="custom_fields[contacts][9]" value="existing">'; }
function can_contact_view_email_notifications_options() { global $notifications; return $notifications; }
function has_contact_permission($permission) { global $mask; return (bool)($mask & (1<<array_search($permission,['invoices','estimates','support','contracts','projects'],true))); }
$hookCalls=[];
function hooks() { return new class { function do_action($hook) { global $hookCalls; $hookCalls[]=$hook; } }; }
function verify($ok,$why) { if(!$ok) throw new RuntimeException($why); }
$contact=(object)['id'=>7,'firstname'=>'<unsafe>','lastname'=>'Example','title'=>'Manager','email'=>'sample@example.test','phonenumber'=>'555-0100','direction'=>'rtl','invoice_emails'=>1,'credit_note_emails'=>0,'estimate_emails'=>1,'ticket_emails'=>1,'contract_emails'=>0,'project_emails'=>1,'task_emails'=>0];
foreach([null,'existing.png'] as $image) foreach([false,true] as $notifications) for($mask=0;$mask<32;$mask++) {
 $contact->profile_image=$image; $contact->last_password_change=$image? '2026-10-02':null; $hookCalls=[];
 $profileTheme = getenv('PROFILE_TEST_THEME') === 'perfex' ? 'perfex' : 'smartchoice';
 ob_start(); include dirname(__DIR__).'/application/views/themes/'.$profileTheme.'/views/profile.php'; $html=ob_get_clean();
 if (getenv('PROFILE_RENDER_HTML')) { echo $html; exit; }
 $d=new DOMDocument(); @$d->loadHTML($html); $x=new DOMXPath($d);
 verify($x->query('//form[@method="post" and @action="https://portal.example/clients/profile"]')->length===2,'Keep two native profile forms');
 verify($x->query('//form//input[@name="csrf_test"]')->length===2,'Both forms retain CSRF');
 verify($x->query('//input[@name="profile" and @value="1"]')->length===1&&$x->query('//input[@name="change_password" and @value="1"]')->length===1,'Keep independent form trigger payloads');
 foreach(['firstname','lastname','title','email','phonenumber'] as $name) verify($x->query('//input[@name="'.$name.'"]')->item(0)->getAttribute('value')===$contact->$name,'Preserve field value '.$name);
 verify($x->query('//select[@name="direction"]/option[@value="rtl" and @selected]')->length===1,'Preserve direction selection');
 verify($x->query('//input[@name="custom_fields[contacts][9]"]')->length===1,'Keep contact custom fields');
 verify($x->query('//input[@type="file" and @name="profile_image"]')->length===($image?0:1),'Keep photo upload condition');
 verify($x->query('//a[@href="https://portal.example/clients/remove_profile_image"]')->length===($image?1:0),'Keep remove-photo route and condition');
 foreach(['oldpassword','newpassword','newpasswordr'] as $name) verify($x->query('//input[@name="'.$name.'" and @type="password"]')->length===1,'Keep password field '.$name);
 foreach(['oldpassword','newpassword','newpasswordr'] as $name) verify($x->query('//button[@type="button" and @aria-controls="'.$name.'" and @aria-pressed="false"]')->length===1,'Independent accessible password toggle '.$name);
 foreach(['invoice_emails'=>'invoices','credit_note_emails'=>'invoices','estimate_emails'=>'estimates','ticket_emails'=>'support','contract_emails'=>'contracts','project_emails'=>'projects','task_emails'=>'projects'] as $field=>$permission) {
  $allowed=$notifications&&has_contact_permission($permission); $node=$x->query('//input[@name="'.$field.'"]');
  verify($node->length===($allowed?1:0),'Notification permission '.$field);
  if($allowed) verify($node->item(0)->hasAttribute('checked')===(bool)$contact->$field,'Preserve notification value '.$field);
 }
 verify(strpos($html,'<unsafe>')===false,'Escape identity and field values');
 verify($hookCalls===['before_client_profile_form_loaded','after_client_profile_form_loaded','after_client_profile_password_form_loaded'],'Keep extension hooks');
}
echo "PASS 128 profile variants: form payloads, CSRF, photo branches, values, notification permissions and hooks preserved\n";
