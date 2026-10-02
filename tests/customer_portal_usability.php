<?php
// Isolated presentation checks; no app bootstrap, sessions, database, or mutations.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function _l($v) { return $v; }
function site_url($v='') { return 'https://portal.example/'.$v; }
function has_contact_permission($v) { global $support; return $v === 'support' && $support; }
function hooks() { return new class { function do_action($v) {} }; }
function form_open_multipart($route,$attributes) { return '<form action="'.site_url($route).'" id="'.$attributes['id'].'"><input type="hidden" name="csrf_test" value="existing-token">'; }
function form_close() { return '</form>'; }
function set_value($key) { return $key === 'message' ? 'Existing draft text' : ''; }
function form_error($v) { return ''; }
function db_prefix() { return 'tbl'; }
function get_client_user_id() { return 42; }
function total_rows($table,$where) { if ($where !== ['clientid'=>42]) throw new RuntimeException('Unexpected scope'); return 0; }
function get_option($key) { return $key === 'ticket_attachments_file_extensions' ? '.pdf,.png' : ''; }
function render_custom_fields() { return '<input name="custom_fields[tickets][7]">'; }
function file_upload_max_size() { return 1000000; }
function get_ticket_form_accepted_mimes() { return '.pdf,.png'; }
function get_custom_fields() { return []; }
function get_template_part($name,$data=[]) { global $proposals; extract($data); include dirname(__DIR__).'/application/views/themes/smartchoice/template_parts/'.$name.'.php'; }
function check($ok,$why) { if (!$ok) throw new RuntimeException($why); }
$departments=$priorities=$services=[];
ob_start(); include dirname(__DIR__).'/application/views/themes/smartchoice/views/open_ticket.php'; $html=ob_get_clean();
$d=new DOMDocument(); @$d->loadHTML($html); $x=new DOMXPath($d);
foreach (['subject','department','priority'] as $id) {
 check($x->query('//*[@id="'.$id.'"]/@aria-required')->item(0)->value==='true','Required indicator: '.$id);
 check(strpos($x->query('//label[@for="'.$id.'"]')->item(0)->textContent,'required')!==false,'Visible/accessible marker: '.$id);
}
check($x->query('//textarea[@name="message"]')->item(0)->textContent==='Existing draft text','Preserve posted message');
check($x->query('//*[@name="csrf_test"]')->length===1,'Native form retains CSRF field');
check($x->query('//*[@name="custom_fields[tickets][7]"]')->length===1,'Keep module custom fields');
check($x->query('//*[@name="attachments[0]"]')->length===1,'Preserve upload payload');
check($x->query('//*[@id="department"]/@required')->length===0,'Do not add native validation to a hidden selectpicker');
foreach ([true,false] as $support) {
 $proposals=[];ob_start(); include dirname(__DIR__).'/application/views/themes/smartchoice/views/proposals.php'; $html=ob_get_clean();
 check(strpos($html,'No proposals yet')!==false,'Empty-state title reaches the template');
 check(strpos($html,$support?'clients/open_ticket':'clients/profile')!==false,'Permission-aware empty-state action');
}
echo "PASS: required labels, native payload/CSRF/custom fields, preserved draft, and permission-aware empty states\n";
