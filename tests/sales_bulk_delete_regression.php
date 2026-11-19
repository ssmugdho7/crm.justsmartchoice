<?php
define('BASEPATH',true);class AdminController {}
require dirname(__DIR__).'/application/controllers/admin/Smart_choice_bulk.php';
class BulkInputFixture {public $ajax=true,$verb='POST',$data=['ids'=>['11','12'],'delete_confirmation'=>'DELETE'];function is_ajax_request(){return $this->ajax;}function method($upper=true){return $this->verb;}function post($key){return $this->data[$key]??null;}}
class BulkOutputFixture {public $json;function set_content_type($t){return $this;}function set_output($text){$this->json=json_decode($text,true);return $this;}}
class BulkLoadFixture {function model($name){}}
class BulkModelFixture {public $deleted=[],$missing=[],$refused=[],$owner=7;function get($id){return in_array($id,$this->missing)?false:(object)['id'=>$id,'addedfrom'=>$this->owner];}function delete($id){$this->deleted[]=$id;return !in_array($id,$this->refused);}}
function show_404(){throw new RuntimeException('404');}function show_error($message,$status){throw new RuntimeException((string)$status);}function ajax_access_denied(){throw new RuntimeException('403');}
$allowed=true;$global=true;$admin=true;
function staff_cant($cap,$module){return !$GLOBALS['allowed'];}function staff_can($cap,$module){return $GLOBALS['global'];}function is_admin(){return $GLOBALS['admin'];}function get_staff_user_id(){return 7;}function log_message($a,$b){}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
function fixture(){ $c=(new ReflectionClass(Smart_choice_bulk::class))->newInstanceWithoutConstructor();$c->input=new BulkInputFixture();$c->output=new BulkOutputFixture();$c->load=new BulkLoadFixture();foreach(['invoices','estimates','proposals','contracts','credit_notes','payments'] as $feature){$name=$feature.'_model';$c->$name=new BulkModelFixture();}return $c;}
function denied($c,$feature,$status,$message){try{$c->delete($feature);throw new LogicException('Expected rejection');}catch(RuntimeException $e){check($e->getMessage()===$status,$message);}foreach(['invoices','estimates','proposals','contracts','credit_notes','payments'] as $f){$name=$f.'_model';check($c->$name->deleted===[],'Rejected request leaves '.$f.' untouched');}}
foreach(['invoices','estimates','proposals','contracts','credit_notes','payments'] as $feature){$c=fixture();$c->delete($feature);$name=$feature.'_model';check(count($c->$name->deleted)===2&&$c->output->json['deleted']===2,'Confirmed selection reaches native '.$feature.' model');}
$c=fixture();check($c->input->data['ids']===['11','12'],'Fixture uses explicit selected IDs');$c->delete('invoices');check($c->invoices_model->deleted===[12,11],'Invoice deletion preserves last-invoice rule by processing newest first');
$c=fixture();$c->invoices_model->refused=[12];$c->delete('invoices');check($c->output->json['deleted']===1&&$c->output->json['failed']===[12],'Native refusal remains visible in partial results');
$c=fixture();$c->input->data['delete_confirmation']='';denied($c,'invoices','422','Missing typed confirmation rejected');
$c=fixture();$c->input->verb='GET';denied($c,'invoices','405','GET rejected');
$c=fixture();$c->input->data['ids']=['11','12invalid'];denied($c,'invoices','422','Malformed IDs rejected before deletion');
$c=fixture();$c->invoices_model->missing=[12];denied($c,'invoices','403','All objects checked before the first deletion');
$allowed=false;denied(fixture(),'invoices','403','Delete permission enforced');$allowed=true;
$admin=false;$global=false;$c=fixture();$c->invoices_model->owner=9;denied($c,'invoices','403','Own-only staff cannot delete another staff record');
