<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function verify($ok, $why) { if (!$ok) throw new RuntimeException($why); }
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function _l($v) { return $v; }
function _dt($v) { return $v; }
function site_url($v='') { return 'https://portal.example/' . $v; }
function form_open_multipart($url,$attrs) { return '<form action="'.e($url).'" method="post" id="files-upload"><input name="csrf_test" value="token">'; }
function form_open($url,$attrs) { return '<form action="'.e($url).'" method="post" onsubmit="'.e($attrs['onsubmit']).'"><input name="csrf_test" value="token">'; }
function form_close() { return '</form>'; }
function get_option($v) { global $delete; return $v==='allow_contact_to_delete_files' ? $delete : ''; }
function get_contact_user_id() { return 9; }
function get_client_user_id() { return 7; }
function get_upload_path_by_type($v) { return '/nonexistent/customer-files-fixture/'; }
function get_mime_class($v) { return 'fa fa-file'; }
function optimize_dropbox_thumbnail($v) { return $v; }
function hooks() { return new class { function do_action($v) {} function apply_filters($v,$path,...$args) { return $path; } }; }
$base=['rel_id'=>7,'attachment_key'=>'opaque-test-key','filetype'=>'image/jpeg','file_name'=>'photo <example>.JPG','dateadded'=>'2026-10-07 00:00:00','contact_id'=>9,'id'=>44,'external'=>''];
$files=[$base,array_merge($base,['file_name'=>'document.pdf']),array_merge($base,['file_name'=>'archive.zip']),array_merge($base,['file_name'=>'drive-photo.png','external'=>'gdrive','external_link'=>'https://drive.example/view','thumbnail_link'=>'https://drive.example/thumb'])];
foreach ([0,1] as $delete) {
    ob_start(); include dirname(__DIR__).'/application/views/themes/smartchoice/views/files.php'; $html=ob_get_clean();
    $doc=new DOMDocument(); @$doc->loadHTML($html); $x=new DOMXPath($doc);
    verify($x->query('//a[contains(@class,"sc-file-view")]')->length===3,'View is offered for photos, PDFs and external files');
    verify($x->query('//a[contains(@class,"sc-file-view") and contains(@href,"?preview=1")]')->length===2,'Local views use the keyed inline route');
    verify($x->query('//div[@class="sc-file-thumbnail"]/img')->length===2,'Photos have visible thumbnails');
    verify($x->query('//img[contains(@src,"preview_image?path=")]')->length===0,'Thumbnail does not expose a filesystem path');
    verify($x->query('//a[contains(@class,"sc-file-view") and @target="_blank" and @rel="noopener noreferrer"]')->length===3,'View opens safely in a new tab');
    verify(strpos($html,'photo <example>')===false,'Filenames are escaped');
    verify($x->query('//button[contains(@class,"file-delete")]')->length===($delete?4:0),'Delete option stays governed by existing permission');
    verify($x->query('//form//input[@name="csrf_test"]')->length===1+($delete?4:0),'Upload and removal forms include CSRF tokens');
}
class FakeFileDb {
    public $where=[];
    function where($key,$value) { $this->where[$key]=$value; }
    function get($table) { global $attachment; $f=$this->where; $this->where=[]; return new class($f,$attachment) { private $f; private $a; function __construct($f,$a) {$this->f=$f;$this->a=$a;} function row() { return $this->a->rel_type===($this->f['rel_type']??'')?$this->a:null; } }; }
    function count_all_results($table) { global $shared; verify($this->where===['file_id'=>44,'contact_id'=>9],'Sharing query is scoped to this file and contact');$this->where=[];return $shared?1:0; }
}
class App_Controller {
    public $db; public $load; public $input;
    function __construct() { $this->db=new FakeFileDb();$this->load=new class {function helper($v){} function model($v){}};$this->input=new class {function get($v){return '0';}}; }
}
function db_prefix() { return 'tbl'; }
function is_staff_logged_in() { global $staff; return $staff; }
function is_client_logged_in() { global $client; return $client; }
function staff_can($a,$b) { global $staffPermission; return $staffPermission; }
function is_customer_admin($id) { return false; }
function show_404() { throw new RuntimeException('denied'); }
function show_error($message,$code) { throw new RuntimeException('authorized-but-missing-fixture'); }
require dirname(__DIR__).'/application/controllers/Download.php';
foreach ([
    [false,true,true,true,7,1,'customer',true],
    [false,true,true,true,8,1,'customer',false],
    [false,true,true,false,7,1,'customer',false],
    [false,true,true,true,7,0,'customer',false],
    [false,false,true,true,7,1,'customer',false],
    [true,false,true,false,8,0,'customer',true],
    [true,false,false,false,8,0,'customer',false],
    [true,false,true,true,7,1,'task',false],
] as [$staff,$client,$staffPermission,$shared,$owner,$visible,$type,$allowed]) {
    $attachment=(object)['id'=>44,'rel_id'=>$owner,'visible_to_customer'=>$visible,'rel_type'=>$type,'file_name'=>'synthetic.jpg'];
    try { (new Download())->file('client','opaque-test-key'); throw new RuntimeException('Unexpected completion'); }
    catch (RuntimeException $error) { verify($error->getMessage()===($allowed?'authorized-but-missing-fixture':'denied'),'Customer file access decision'); }
}
echo "PASS file previews, uploads/deletion permissions, escaped names and eight file access cases; no customer files accessed\n";
