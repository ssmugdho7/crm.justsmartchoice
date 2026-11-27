<?php
// Synthetic, local-only view fixture; no CRM session, uploads, or database writes.
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__) . '/application/');
define('PROJECT_ATTACHMENTS_FOLDER', dirname(__DIR__) . '/uploads/projects/');
function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function _l($key) { return ucwords(str_replace('_', ' ', $key)); }
function base_url($path) { return 'http://fixture.test/' . $path; }
function admin_url($path = '') { return base_url('admin/' . $path); }
function get_app_version() { return 'fixture'; }
function form_open_multipart($url, $attrs) { return '<form action="'.e($url).'" class="'.e($attrs['class']).'" id="'.e($attrs['id']).'">'; }
function form_open($url) { return '<form action="'.e($url).'">'; }
function form_close() { return '</form>'; }
function form_hidden($name, $value) { return '<input type="hidden" name="'.e($name).'" value="'.e($value).'">'; }
function render_input(...$args) { return ''; }
function render_textarea(...$args) { return ''; }
function get_option($name) { return ''; }
function is_admin() { return true; }
function staff_can(...$args) { return true; }
function get_staff_user_id() { return 7; }
function get_upload_path_by_type($type) { return PROJECT_ATTACHMENTS_FOLDER; }
function is_image($path) { return substr($path, -4) === '.jpg'; }
function project_file_url($file, $thumb) { return base_url('synthetic-thumbnail.jpg'); }
function _dt($date) { return $date; }
function time_ago($date) { return '2 hours ago'; }
function total_rows(...$args) { return 2; }
function db_prefix() { return 'tbl'; }
function staff_profile_image($id, $classes) { return '<span class="staff-avatar" aria-hidden="true">AB</span>'; }
function get_staff_full_name($id) { return 'Alex Builder'; }
function contact_profile_image_url(...$args) { return ''; }
function get_user_id_by_contact_id($id) { return 1; }
function get_contact_full_name($id) { return 'Customer'; }
$project = (object) ['id' => 42];
$files = [];
for ($i = 1; $i <= 18; $i++) {
    $name = $i === 18 ? 'Approved Roof Permit — Final Inspection.pdf' : 'Project document '.$i.'.pdf';
    if ($i === 2) $name = '<script>alert("document")</script>.pdf';
    $files[] = ['id'=>$i, 'project_id'=>42, 'file_name'=>'doc_'.$i.'.pdf', 'original_file_name'=>$name, 'subject'=>$name, 'filetype'=>'application/pdf', 'last_activity'=>null, 'visible_to_customer'=>0, 'staffid'=>7, 'contact_id'=>0, 'dateadded'=>'2026-11-27 12:00:00', 'external'=>'', 'thumbnail_link'=>''];
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="<?=base_url('assets/builds/vendor-admin.css')?>"><link rel="stylesheet" href="<?=base_url('assets/plugins/datatables/datatables.min.css')?>"><script src="<?=base_url('assets/plugins/jquery/jquery.min.js')?>"></script><script src="<?=base_url('assets/plugins/datatables/datatables.min.js')?>"></script><style>body{margin:0;padding:12px;font-family:Arial,sans-serif}.modal,.hide{display:none}*{box-sizing:border-box}a{color:#075e42}.checkbox input{width:20px;height:20px}</style></head><body><?php require APPPATH . 'views/admin/projects/project_files.php'; ?><script>window.previews=[];function view_project_file(id,project){window.previews.push([id,project]);} document.addEventListener('DOMContentLoaded',function(){if(location.search.indexOf('plain')===-1)jQuery('.table-project-files').DataTable({pageLength:5,order:[[7,'desc']],autoWidth:false});});</script></body></html>
