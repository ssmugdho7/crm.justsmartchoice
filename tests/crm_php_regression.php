<?php
if (PHP_SAPI !== 'cli') exit;
define('BASEPATH',__DIR__);
class App_Model { public $db; function __construct(){} }
function get_staff_user_id(){return 1;}
function get_staff_full_name($id){return 'Test Staff';}
function get_option($key){return '';}
function _strip_tags($s){return strip_tags($s);}
function nl2br_save_html($s){return nl2br($s);}
function db_prefix(){return 'fixture_';}
require dirname(__DIR__).'/modules/recruitment/models/Recruitment_model.php';
class MailFixture extends Recruitment_model {
 public $accepted=false;
 function rec_send_simple_email($email,$subject,$message,$fromname=''){return $this->accepted;}
}
class DbFixture { public $rows=[];function insert($table,$data){$this->rows[]=$data;return true;} }
$m=new MailFixture();$m->db=new DbFixture();
$data=['email'=>'test@example.invalid','subject'=>'Test','content'=>'Test','candidate'=>1];
if($m->send_mail_candidate($data)!==false || $m->db->rows)throw new Exception('Failed mail recorded as sent');
$m->accepted=true;if(!$m->send_mail_candidate($data)||count($m->db->rows)!==1)throw new Exception('Accepted mail not recorded');
$m->accepted=false;$m->db->rows=[];$data['email']=['test@example.invalid'];$data['candidate']=[1,2];
if($m->send_mail_list_candidate($data)!==false || $m->db->rows)throw new Exception('Failed bulk mail recorded as sent');
// Reproduce JavaScript string corruption with ordinary quotes/newlines/backslashes.
$fixture=[['message'=>"Customer's roof\nC:\\projects\\roof",'sender_fullname'=>'Test Staff']];
$messages=json_encode($fixture,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
$view=file_get_contents(dirname(__DIR__).'/modules/prchat/views/includes/search_messages_modal.php');
preg_match('/messages_history = (<\?=.*?\?>);/', $view,$matches);
ob_start();eval('?>'.$matches[1]);$rendered=ob_get_clean();
if(json_decode($rendered,true)!==$fixture)throw new Exception('History JSON round-trip failed');
echo "PASS: candidate mail failure/success, bulk failure and history JSON round-trip\n";
