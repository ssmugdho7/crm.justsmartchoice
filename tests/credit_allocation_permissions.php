<?php
define('BASEPATH',__DIR__);
#[AllowDynamicProperties] class AdminController{}
function staff_cant($p,$f){return !in_array($f.':'.$p,$GLOBALS['permissions'],true);}
function get_staff_user_id(){return 7;}
function user_can_view_invoice($id){return (string)$id==='2';}
function access_denied($f){throw new RuntimeException('denied');}
function update_invoice_status($id,$force){$GLOBALS['updated'][]=$id;}
function set_alert($kind,$message){$GLOBALS['alerts'][]=[$kind,$message];}
function _l($k){return $k;}function admin_url($p){return '/admin/'.$p;}function redirect($p){}
require dirname(__DIR__).'/application/controllers/admin/Credit_notes.php';
require dirname(__DIR__).'/application/controllers/admin/Invoices.php';
class AllocInput{public $amounts=[2=>'10.00'];function post($key=null){return $key==='amount'?$this->amounts:true;}}
class AllocModel{public $owner=7,$applied=[];function get($id){return (object)['addedfrom'=>$this->owner];}function apply_credits($id,$data){$this->applied[]=[$id,$data];return true;}}
function controller($class){$c=(new ReflectionClass($class))->newInstanceWithoutConstructor();$c->input=new AllocInput;$c->credit_notes_model=new AllocModel;return $c;}
function resetCase($perms){$GLOBALS['permissions']=$perms;$GLOBALS['updated']=[];$GLOBALS['alerts']=[];}
function check($v,$m){if(!$v)throw new RuntimeException($m);}
resetCase(['credit_notes:edit','credit_notes:view']);$c=controller('Credit_notes');$c->apply_credits_to_invoices(1);check(count($c->credit_notes_model->applied)===1 && count($updated)===1,'Authorized credit allocation');check($alerts===[['success','credits_successfully_applied_to_invoices']],'Success flag typo repaired');
resetCase(['credit_notes:view']);$c=controller('Credit_notes');try{$c->apply_credits_to_invoices(1);throw new RuntimeException('Access expanded');}catch(RuntimeException $e){check($e->getMessage()==='denied','Edit denied');}check(!$c->credit_notes_model->applied,'Read-only staff cannot mutate');
resetCase(['credit_notes:edit','credit_notes:view_own']);$c=controller('Credit_notes');$c->credit_notes_model->owner=8;try{$c->apply_credits_to_invoices(1);throw new RuntimeException('Other owner exposed');}catch(RuntimeException $e){check($e->getMessage()==='denied','Own scope retained');}
resetCase(['credit_notes:edit','credit_notes:view']);$c=controller('Credit_notes');$c->input->amounts=[99=>'10'];$c->apply_credits_to_invoices(1);check(!$c->credit_notes_model->applied && !$alerts,'Invisible invoice rejected without false success');
$c->input->amounts='bad';$c->apply_credits_to_invoices(1);check(!$c->credit_notes_model->applied,'Malformed batch rejected');
resetCase(['invoices:edit','credit_notes:view']);$c=controller('Invoices');$c->input->amounts=[1=>'10'];$c->apply_credits(2);check(count($c->credit_notes_model->applied)===1 && $alerts===[['success','invoice_credits_applied']],'Invoice-side route preserved');
resetCase(['credit_notes:view']);$c=controller('Invoices');try{$c->apply_credits(2);throw new RuntimeException('Invoice readonly allowed');}catch(RuntimeException $e){check($e->getMessage()==='denied','Invoice edit required');}
echo "PASS both allocation routes: edit and record visibility, own scope, malformed batches, real-success feedback and readonly denial\n";
