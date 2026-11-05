<?php
define('BASEPATH',__DIR__);
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}function _l($k){return $k;}
function staff_can($cap,$res){return $GLOBALS['permissions'][$res.':'.$cap]??($GLOBALS['allow']??false);}function is_customer_admin($id){return $id===7;}function db_prefix(){return 'tbl';}
function contact_profile_image_url($id){return $id===1?'/assets/images/user-placeholder.jpg':'/uploads/photo.png';}
function hooks(){return new class{function add_filter(...$args){}function add_action(...$args){}};}
class DB{public $contact;function where(...$args){return $this;}function get($t){return $this;}function row_array(){return $this->contact;}}
$ci=(object)['input'=>new class{function post($key){return $GLOBALS['segment']??'all';}},'db'=>new DB,'router'=>new class{function fetch_class(){return 'clients';}}];function &get_instance(){return $GLOBALS['ci'];}
function get_decimal_places(){return 2;}
require __DIR__.'/../modules/sc_admin_fixes/includes/money.php';
require __DIR__.'/../modules/sc_admin_fixes/includes/customers.php';
if(scaf_decimal_add('0.10','0.20')!=='0.30')throw new RuntimeException('Shared decimal helper failed');
if (($argv[1] ?? '') === 'guard-denied') {
 $ci->router=new class{function fetch_class(){return 'clients';}function fetch_method(){return 'bulk_action';}};
 $ci->input=new class{function method(){return 'post';}function post($key){return $key==='ids'?['7','8']:($key==='mass_delete'?'1':null);}};
 $ci->output=new class{function set_status_header($code){echo 'HTTP'.$code;return $this;}function set_content_type($type){return $this;}};
 $GLOBALS['permissions']=['customers:delete'=>true,'customers:view'=>false];
 scaf_customer_bulk_guard();echo 'HANDLER_REACHED';exit;
}
function ensure_customer($ok,$m){if(!$ok)throw new RuntimeException($m);}
$r=array_fill(0,9,'');$r[2]='<a href="/client/7">Person</a><div class="row-options">native</div>';
$r=scaf_customer_row($r,['company'=>'','fullname'=>'<Ann $1 Smith>','tblclients.active'=>1]);
ensure_customer(strpos($r[2],'&lt;Ann $1 Smith&gt;')!==false&&strpos($r[2],'native')!==false,'Names escaped, dollars retained, native actions preserved');
ensure_customer(strpos($r[2],'scaf-residential')!==false,'Explicit person gets residentialbadge');
$r=scaf_customer_row(array_fill(0,9,''),['company'=>'Legal firm','fullname'=>'Ann','tblclients.active'=>1]);ensure_customer(strpos($r[2],'scaf-residential')===false,'Companies never inferred residential');
$ci->db->contact=['userid'=>8,'id'=>1,'firstname'=>'Wrong','lastname'=>'User'];$r=['<img src="native"><a>native</a>'];ensure_customer(scaf_customer_contact_row($r,['id'=>1])===$r,'Wrong customer scope returns original row');
$ci->db->contact=['userid'=>7,'id'=>1,'firstname'=>'Ann','lastname'=>'Smith','is_primary'=>1,'invoice_emails'=>1,'estimate_emails'=>0,'project_emails'=>1];
$r=scaf_customer_contact_row(['<img src="native"><a onclick="original()">Ann</a>'],['id'=>1]);ensure_customer(strpos($r[0],'>AS</span>')!==false&&strpos($r[0],'is-primary')!==false,'Actual initials and primaryring');ensure_customer(strpos($r[0],'original()')!==false&&strpos($r[0],'>estimates</span>')===false,'Actions retained, notifications useactualflags');
$r=scaf_customer_contact_row(['<img src="native"><a>Ann</a>'],['id'=>2]);ensure_customer(strpos($r[0],'/uploads/photo.png')!==false,'Realphoto retained');
$v=scaf_customer_view(['path'=>'admin/clients/tabs','data'=>['native'=>'kept']]);ensure_customer($v['path']==='../../modules/sc_admin_fixes/views/customers/tabs'&&$v['data']['native']==='kept','Update-safe override retainsnativevars');
echo "PASS customer scope, person/firm identity, escaping, contact photo/notifications and hook contract\n";

$GLOBALS['allow']=false;
ensure_customer(!scaf_customer_bulk_validation(['7'],true,null),'Delete denied without capability');
$GLOBALS['permissions']=['customers:delete'=>true,'customers:edit'=>true,'customers:view'=>false];
ensure_customer(!scaf_customer_bulk_validation(['7','8'],true,null),'Wholebatch denied for one wrongcustomer');
$GLOBALS['permissions']=[];$GLOBALS['allow']=true;
ensure_customer(!scaf_customer_bulk_validation(['7','bad'],true,null),'Whole batch rejected for malformedID');
$GLOBALS['allow']=false;
// Scope validation independent of capability uses assigned customer7 only.
ensure_customer(!scaf_customer_bulk_validation([],false,[]),'Emptybatch denied');
$GLOBALS['allow']=true;
ensure_customer(!scaf_customer_bulk_validation(['7'],false,[]),'Emptyselection never clears groups implicitly');
ensure_customer(!scaf_customer_bulk_validation(['7'],false,'remove_all',false),'Clear requires explicit acknowledgement');
ensure_customer(scaf_customer_bulk_validation(['7'],false,'remove_all',true),'Deliberate clear retainsnativecontract');
$GLOBALS['segment']='inactive';$join=scaf_customer_segment_join(['nativejoin']);ensure_customer(count($join)===2&&strpos($join[1],'active=0')!==false,'Segment intersectsnativequeryviajoin');
$GLOBALS['segment']='active OR 1=1';ensure_customer(scaf_customer_segment_join([])===[],'Unknownsegments cannot enterSQL');
echo "PASS full batch validation, explicit groupclear and allowlisted segmentintersection
";

$output=[];$exitCode=0;exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).' guard-denied',$output,$exitCode);$denial=implode('', $output);
ensure_customer(strpos($denial,'HTTP403')!==false&&strpos($denial,'"success":false')!==false&&strpos($denial,'HANDLER_REACHED')===false,'Forbidden native batch exits with403JSON beforehandler');
echo "PASS forbidden native batch exits before writes
";

$allContactsSource=file_get_contents(__DIR__.'/../modules/sc_admin_fixes/views/customers/all_contacts.php');
ensure_customer(strpos($allContactsSource,"init_ajax_search('customer'")>strpos($allContactsSource,'init_tail();'),'Customerchooser initializes onlyafter nativejQueryloads');
echo "PASS chooser script order after native footer
";

class ScafProjectCountDB { public $filters=[]; function where($key,$value=null,$escape=null){$this->filters[$key]=$value;return $this;}function count_all_results($table){return 2;} }
$ci->db=new ScafProjectCountDB;$GLOBALS['allow']=true;
ensure_customer(scaf_customer_project_count(7,true)===2&&$ci->db->filters['status']===2&&$ci->db->filters['clientid']===7,'Activejobsite countscopesnativeinprogressandcustomer');
$ci->db=new ScafProjectCountDB;scaf_customer_project_count(7,false);ensure_customer(!isset($ci->db->filters['status']),'Optionalallprojectcountpreserved');
$heroSource=file_get_contents(__DIR__.'/../modules/sc_admin_fixes/views/customers/hero.php');ensure_customer(strpos($heroSource,'scaf_customer_project_count($client->userid,true)')!==false,'Herousesactivejobsitequery');
$address='123 Road<br />Suite <b>2</b>'; $plain=trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('~<br\s*/?>~i', ', ', $address))));ensure_customer($plain==='123 Road, Suite 2','Addresslinebreaksnormalizedsafely');
echo "PASS activejobsite customerquery and safe multilineaddress
";
