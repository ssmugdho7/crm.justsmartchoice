<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
class ClientsController {
 public $load;public $google_meet_model;public $rendered;public $payload;
 function __construct() { $this->google_meet_model=new class {function get_client_meetings($id) { if ($id!==42) throw new RuntimeException('Customer scope changed'); return []; }}; $this->load=new class {function model($name) {}}; }
 function view($template) { $this->rendered=$template; }
 function data($data) { $this->payload=$data; }
 function title($title) {}
 function layout() {}
}
function is_client_logged_in() { return true; }
function get_option($key) { return '1'; }
function get_client_user_id() { return 42; }
function google_meet_lang($key,$fallback) { return $fallback; }
function redirect($url) { throw new RuntimeException('Unexpected redirect: '.$url); }
function site_url($path='') { return 'https://portal.example/'.$path; }
require dirname(__DIR__).'/modules/google_meet/controllers/Meeting_clients.php';
$c=new Meeting_clients();$c->meetings();
if ($c->rendered!=='client/list' || $c->payload['meetings']!==[]) throw new RuntimeException('List did not render');
echo "PASS: meeting list renders through parent view without redirect, retaining customer scope\n";
