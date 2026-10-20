<?php
// CLI-only integration tests; all writes use connection-local temporary tables.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/'); define('APPPATH', $root . '/application/'); define('ENVIRONMENT', 'testing');
function is_php($v) { return version_compare(PHP_VERSION, $v, '>='); }
function log_message(...$args) {} function show_error($m) { throw new RuntimeException($m); }
function db_prefix() { return 'prefix_test_'; } function &get_instance() { return $GLOBALS['ci']; }
function is_staff_logged_in() { return $GLOBALS['loggedIn']; }
function staff_can($capability, $feature) { return $GLOBALS['permissions'][$feature][$capability] ?? false; }
function _l($key, ...$args) { return ['invoice'=>'Invoice','estimate'=>'Estimate','proposal'=>'Proposal','credit_note'=>'Credit Note','contract'=>'Contract'][$key] ?? $key; }
function get_option($key) { return $GLOBALS['options'][$key] ?? ''; }
function log_activity($message) { $GLOBALS['logs'][]=$message; }
function hooks() { return new class { function apply_filters($name, $value, ...$args) { return $value; } }; }
function check($ok, $message) { if (!$ok) throw new RuntimeException($message); }
class App_Model {}
require $root . '/application/models/Invoices_model.php';
require $root . '/application/libraries/App_object_cache.php';
require getenv('SC_TEST_CONFIG') ?: $root . '/application/config/app-config.php';
require $root . '/system/database/DB.php';
$db = DB(['hostname'=>APP_DB_HOSTNAME,'username'=>APP_DB_USERNAME,'password'=>APP_DB_PASSWORD,'database'=>APP_DB_NAME,'dbdriver'=>'mysqli','dbprefix'=>db_prefix(),'pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>true], true);
$ci=(object)['db'=>$db,'app_object_cache'=>new App_object_cache()];
$loggedIn=true; $permissions=[]; $logs=[];
$options=['number_padding_prefixes'=>3,'proposal_number_prefix'=>'PRO-'];
foreach (['sales','invoices','estimates','credit_notes','proposals','smart_choice_sales_links','smart_choice_numbering'] as $helper) require $root . '/application/helpers/' . $helper . '_helper.php';
foreach (sc_numbering_documents() as $type=>$config) {
    $schema = isset($config['model']) ? ',prefix VARCHAR(50),number INT,number_format INT,date DATE,status INT,formatted_number VARCHAR(100)' : '';
    check($db->query('CREATE TEMPORARY TABLE '.db_prefix().$config['table'].' (id INT PRIMARY KEY'.$schema.') ENGINE=InnoDB'), 'Create fixture '.$type);
    $row=['id'=>7];
    if (isset($config['model'])) $row+=['prefix'=>'OLD-','number'=>42,'number_format'=>4,'date'=>'2026-10-02','status'=>2,'formatted_number'=>'OLD-042/10/2026'];
    check($db->insert(db_prefix().$config['table'],$row),'Insert fixture');
    $permissions[$config['feature']]=['edit'=>true];
}
check(format_proposal_number(7)==='PRO-007','Legacy proposal numbers preserved before migration');
check(format_contract_number(7)==='7','Legacy contract IDs preserved before migration');
require $root . '/scripts/migrations/sales_document_prefixes.php';
sc_migrate_document_prefixes($db->conn_id,db_prefix());
sc_migrate_document_prefixes($db->conn_id,db_prefix());
$db->data_cache=[];
check($db->where('id',7)->get(db_prefix().'proposals')->row('prefix')===null,'Migration preserves existing defaults');
foreach (sc_numbering_documents() as $type=>$config) {
    $table=db_prefix().$config['table'];
    $expected=$type==='proposal'?'NEW-007':($type==='contract'?'NEW-7':'NEW-042/10/2026');
    $r=sc_update_document_prefix($type,7,' NEW- ');
    check($r['success'] && $r['prefix']==='NEW-' && $r['formatted_number']===$expected, $type.' saves normalized prefix and formats number');
    check($db->where('id',7)->get($table)->row('prefix')==='NEW-', $type.' persisted');
    if (isset($config['model'])) check($db->where('id',7)->get($table)->row('formatted_number')===$expected,$type.' search value updated');
    check(sc_update_document_prefix($type,7,'NEW-')['success'],$type.' identical save succeeds');
    foreach ([null,[],str_repeat('x',51),'<script>','bad'.chr(0).'prefix'] as $bad) check(!sc_update_document_prefix($type,7,$bad)['success'],$type.' rejects invalid prefix');
    $permissions[$config['feature']]['edit']=false;
    check(!sc_update_document_prefix($type,7,'DENIED-')['success'],$type.' permission enforced');
    check($db->where('id',7)->get($table)->row('prefix')==='NEW-',$type.' unauthorized save cannot mutate');
    $permissions[$config['feature']]['edit']=true;
    check(!sc_update_document_prefix($type,999,'MISSING-')['success'],$type.' missing document rejected');
    check(sc_update_document_prefix($type,7,'')['success'],$type.' prefix can be cleared');
    check(sc_update_document_prefix($type,7,'0')['prefix']==='0',$type.' zero prefix retained');
    check(sc_update_document_prefix($type,7,"O'Reilly & \"Q\"-সম্পাদনা-")['success'],$type.' punctuation/Unicode accepted as plain text');
}
$loggedIn=false;
check(!sc_update_document_prefix('proposal',7,'ANON-')['success'],'Unauthenticated save denied'); $loggedIn=true;
check(!sc_update_document_prefix('staff',7,'NO-')['success'],'Unknown module denied');
check(!sc_update_document_prefix('proposal','7 OR 1=1','NO-')['success'],'Invalid ID denied');
// Shared SQL used for list/export/search must agree with the public formatter, including quoted defaults and long IDs.
$options['proposal_number_prefix']="GLOBAL'";
$db->insert(db_prefix().'proposals',['id'=>12345]);
$expr=sc_proposal_number_sql();
foreach ($db->query('SELECT id,'.$expr.' AS n FROM '.db_prefix().'proposals')->result() as $row) check($row->n===format_proposal_number($row->id),'Proposal SQL/PHP formatting agreement');
$expr=sc_contract_number_sql();
$row=$db->query('SELECT id,'.$expr.' AS n FROM '.db_prefix().'contracts')->row();
check($row->n===format_contract_number($row->id),'Contract SQL/PHP formatting agreement');
// Confirm all four native number layouts remain unchanged apart from the prefix.
foreach ([1=>'N-042',2=>'N-2026/042',3=>'N-042-26',4=>'N-042/10/2026'] as $format=>$expected) {
    $db->where('id',7)->update(db_prefix().'estimates',['number_format'=>$format]);
    check(sc_update_document_prefix('estimate',7,'N-')['formatted_number']===$expected,'Number layout '.$format);
}
echo "PASS: migration twice, five sales documents, database persistence, formatted search/export values, permissions, validation, empty/zero/Unicode prefixes and four number layouts\n";
$db->where('id',7)->update(db_prefix().'invoices',['status'=>Invoices_model::STATUS_DRAFT]);
check(sc_update_document_prefix('invoice',7,'D-')['formatted_number']==='D-DRAFT','Draft invoice retains draft numbering');
// A failed formatted-number update must roll the prefix back as well.
$ci->db=new class($db) {
    private $db;
    function __construct($db) { $this->db=$db; }
    function __call($name,$args) {
        if ($name==='update' && isset($args[1]['formatted_number'])) { return false; }
        $result=$this->db->$name(...$args);
        return $result===$this->db ? $this : $result;
    }
};
$before=$db->where('id',7)->get(db_prefix().'estimates')->row_array();
check(!sc_update_document_prefix('estimate',7,'ROLLBACK-')['success'],'Failed formatted-number update reports failure');
check($db->where('id',7)->get(db_prefix().'estimates')->row_array()===$before,'Prefix and formatted number roll back together');
$ci->db=$db;
// Render the actual shared editor with quotes, then check native document PDFs contain the saved contract number.
function e($s) { return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function admin_url($path='') { return 'https://dev.justsmartchoice.com/admin/'.$path; }
$prefix_type='contract';$prefix_id=7;$document_prefix="O'Reilly & \"Q\"-";
ob_start();require $root.'/application/views/admin/includes/document_prefix_editor.php';$editor=ob_get_clean();
$doc=new DOMDocument();@$doc->loadHTML($editor);$xp=new DOMXPath($doc);
check($xp->query('//input')->item(0)->getAttribute('value')===$document_prefix,'Editor round-trips quotes as plain input text');
check($xp->query('//button[@type="button"]')->length===1 && $xp->query('//input[@name]')->length===0,'Prefix controls cannot submit unrelated form values');
check(sc_update_document_prefix('contract',7,'AGREEMENT-')['success'],'PDF fixture prefix saved');
foreach (['perfex','smartchoice'] as $theme) {
    $pdf=new class { public $html=''; function writeHTML($html,...$args) { $this->html.=$html; } };
    $contract=(object)['id'=>7,'content'=>'<p>Fixture content</p>','signed'=>0];
    require $root.'/application/views/themes/'.$theme.'/views/contractpdf.php';
    check(strpos($pdf->html,'AGREEMENT-7')!==false && strpos($pdf->html,'Fixture content')!==false,$theme.' contract PDF contains prefix and original content');
}
echo "PASS: atomic rollback, actual editor escaping, independent save controls and both contract PDF themes\n";
// Exercise the real HTTP controller methods, including the legacy routes, without bootstrapping a web session.
class AdminController { function __get($key) { return get_instance()->$key; } }
function show_404() { throw new RuntimeException('404'); }
$ci->load=new class { function helper($names) {} };
$ci->input=new class {
    public $verb='post'; public $value='HTTP-';
    function method() { return $this->verb; }
    function post($key) { return $this->value; }
};
$ci->output=new class {
    public $body; public $type;
    function set_content_type($value) { $this->type=$value;return $this; }
    function set_output($value) { $this->body=$value;return $this; }
};
foreach (['Smart_choice_sales','Invoices','Estimates','Credit_notes'] as $class) {
    require $root.'/application/controllers/admin/'.$class.'.php';
    $controller=(new ReflectionClass($class))->newInstanceWithoutConstructor();
    $ci->input->verb='post';
    if ($class==='Smart_choice_sales') $controller->update_number_settings('proposal','7');
    else $controller->update_number_settings('7');
    check(json_decode($ci->output->body,true)['success'],$class.' POST persists and returns JSON response');
    $ci->input->verb='get';$rejected=false;
    try {
        if ($class==='Smart_choice_sales') $controller->update_number_settings('proposal','7');
        else $controller->update_number_settings('7');
    } catch (RuntimeException $e) { $rejected=$e->getMessage()==='404'; }
    check($rejected,$class.' GET cannot change data');
}
echo "PASS: shared and three legacy controller routes persist POSTs and reject GET mutations\n";
// Exercise the real global-search queries with the newly saved identifiers.
function get_staff_user_id() { return 7; }
function startsWith($value,$prefix) { return $prefix !== '' && strpos($value,$prefix)===0; }
function strafter($value,$prefix) { return substr($value,strpos($value,$prefix)+strlen($prefix)); }
require $root.'/application/models/Misc_model.php';
class PrefixSearchFixture extends Misc_model { public $db; function __construct($db) { $this->db=$db; } }
$search=new PrefixSearchFixture($db);
check($db->query('ALTER TABLE '.db_prefix().'proposals ADD subject TEXT,ADD content TEXT,ADD proposal_to TEXT,ADD zip TEXT,ADD state TEXT,ADD city TEXT,ADD address TEXT,ADD email TEXT,ADD phone TEXT,ADD currency INT,ADD addedfrom INT,ADD assigned INT'),'Search fixture proposal columns');
check($db->query('ALTER TABLE '.db_prefix().'contracts ADD description TEXT,ADD subject TEXT,ADD addedfrom INT'),'Search fixture contract columns');
check($db->query('CREATE TEMPORARY TABLE '.db_prefix().'currencies (id INT PRIMARY KEY,name VARCHAR(30))'),'Search fixture currency');
$db->insert(db_prefix().'currencies',['id'=>1,'name'=>'USD']);
$db->where('id',7)->update(db_prefix().'proposals',['currency'=>1]);
$permissions['proposals']['view']=true;$permissions['contracts']['view']=true;
check(sc_update_document_prefix('proposal',7,"SEARCH'Q-")['success'],'Quoted proposal prefix saved');
$r=$search->_search_proposals("SEARCH'Q-007");check(count($r['result'])===1 && (int)$r['result'][0]['id']===7,'Global proposal search finds saved prefix');
$r=$search->_search_contracts('AGREEMENT-7');check(count($r['result'])===1 && (int)$r['result'][0]['id']===7,'Global contract search finds saved prefix');
echo "PASS: actual proposal/contract global search queries find persisted identifiers\n";
