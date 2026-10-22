<?php
// Read-only application code tests; database fixtures are connection-local TEMPORARY tables.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/'); define('APPPATH', $root . '/application/'); define('ENVIRONMENT', 'testing');
function log_message(...$args) {} function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function show_error($message) { throw new RuntimeException($message); }
function db_prefix() { return 'audit_exp_'; }
function &get_instance() { return $GLOBALS['ci']; }
function get_staff_user_id() { return 42; }
function staff_can($permission, $feature) { return in_array($feature . ':' . $permission, $GLOBALS['permissions'], true); }
function is_admin() { return $GLOBALS['admin']; }
function is_staff_logged_in() { return $GLOBALS['loggedIn']; }
function access_denied($feature) { throw new DomainException('denied'); }
function show_404() { throw new DomainException('not found'); }
function get_currency($id) { return (object) ['id' => (int) $id, 'name' => (int) $id === 1 ? 'USD' : 'EUR']; }
function _l($key, $value = '') { return isset($GLOBALS['lang'][$key]) ? sprintf($GLOBALS['lang'][$key], $value) : $key; }
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function app_format_money($amount, $currency) { return number_format((float) $amount, 2) . ' ' . $currency->name; }
function admin_url($uri = '') { return 'https://fixture.invalid/admin/' . $uri; }
function site_url($uri = '') { return 'https://fixture.invalid/' . $uri; }
function get_upload_path_by_type($type) { return sys_get_temp_dir() . '/scct-expense-fixtures-' . getmypid() . '/'; }
class ExpenseFixtureHooks { public $names = []; function add_filter($name, ...$args) { $this->names[] = $name; } function add_action($name, ...$args) { $this->names[] = $name; } }
function hooks() { static $hooks; return $hooks ?: $hooks = new ExpenseFixtureHooks; }
class ExpenseFixtureRouter { public $class = 'Expenses', $method = 'list_expenses'; function fetch_class() { return $this->class; } function fetch_method() { return $this->method; } }
class ExpenseFixtureInput { public $values = []; function post($key = null) { return $key === null ? $this->values : ($this->values[$key] ?? null); } }
class ExpenseFixtureUri { public $segments = []; function segment($id) { return $this->segments[$id] ?? null; } }
$checks = 0;
function check($value, $label) { global $checks; if (!$value) throw new RuntimeException($label); ++$checks; }
function denied($action, $label) { try { $action(); } catch (DomainException $e) { check(true, $label); return; } check(false, $label); }
require $root . '/system/database/DB.php';
$siteRoot = getenv('CRM_SITE_ROOT');
if (!$siteRoot || !is_file($siteRoot . '/application/config/app-config.php')) throw new RuntimeException('CRM_SITE_ROOT must point to a private site checkout');
require $siteRoot . '/application/config/app-config.php';
$db = DB(['hostname' => APP_DB_HOSTNAME, 'username' => APP_DB_USERNAME, 'password' => APP_DB_PASSWORD, 'database' => APP_DB_NAME,
    'dbdriver' => 'mysqli', 'dbprefix' => 'audit_exp_', 'pconnect' => false, 'db_debug' => false, 'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_general_ci', 'save_queries' => true], true);
$ci = (object) ['db' => $db, 'router' => new ExpenseFixtureRouter, 'input' => new ExpenseFixtureInput, 'uri' => new ExpenseFixtureUri];
$permissions = ['expenses:view']; $admin = true; $loggedIn = true;
require $root . '/modules/smart_choice_core_tools/includes/expenses.php';
foreach ([
    'CREATE TEMPORARY TABLE audit_exp_expenses (id INT PRIMARY KEY, amount DECIMAL(15,4), tax INT, tax2 INT, invoiceid INT NULL, billable INT, currency INT, date DATE, clientid INT, project_id INT, category INT, addedfrom INT)',
    'CREATE TEMPORARY TABLE audit_exp_taxes (id INT PRIMARY KEY, taxrate DECIMAL(8,4))',
    'CREATE TEMPORARY TABLE audit_exp_taxes_copy (id INT PRIMARY KEY, taxrate DECIMAL(8,4))',
    'CREATE TEMPORARY TABLE audit_exp_expenses_categories (id INT PRIMARY KEY, name VARCHAR(100))',
] as $sql) check((bool) $db->query($sql), 'Temporary schema');
// MySQL forbids joining one temporary table twice; use an identical fixture tax copy for alias t2.
$db->insert('audit_exp_taxes', ['id' => 1, 'taxrate' => 10]);
$db->insert('audit_exp_taxes', ['id' => 2, 'taxrate' => 5]);
$db->insert('audit_exp_taxes_copy', ['id' => 1, 'taxrate' => 10]);
$db->insert('audit_exp_taxes_copy', ['id' => 2, 'taxrate' => 5]);
// The production helper uses t2 against the same real table; a builder join wrapper maps only this fixture alias.
class ExpenseFixtureDB {
    private $real;
    function __construct($real) { $this->real = $real; }
    function __call($method, $args) {
        if ($method === 'join' && $args[0] === 'audit_exp_taxes t2') $args[0] = 'audit_exp_taxes_copy t2';
        $result = $this->real->$method(...$args);
        return $result === $this->real ? $this : $result;
    }
}
$ci->db = new ExpenseFixtureDB($db);
$year = date('Y'); $today = date('Y-m-d');
foreach ([[1,'100',1,2,null,1,1,$today,7,8,1,42], [2,'200',0,0,50,1,1,$today,7,8,1,99],
    [3,'50',0,0,null,0,1,$today,9,10,2,42], [4,'10',0,2,null,1,1,$today,7,8,2,42],
    [5,'999',0,0,null,1,2,$today,7,8,1,42], [6,'900',0,0,null,1,1,($year-1).'-02-01',7,8,1,42],
    [7,'0.10',0,0,null,0,1,$today,7,8,1,42], [8,'0.20',0,0,null,0,1,$today,7,8,1,42]] as $values) {
    $db->insert('audit_exp_expenses', array_combine(['id','amount','tax','tax2','invoiceid','billable','currency','date','clientid','project_id','category','addedfrom'], $values));
}
$db->insert_batch('audit_exp_expenses_categories', [['id'=>1,'name'=>'Operations'], ['id'=>2,'name'=>'Travel']]);
$s = scct_expenses_summary([], 1);
check($s['metrics']['all']['count'] === 6 && bccomp($s['metrics']['all']['amount'], '375.8', 6) === 0, 'Exact tax-inclusive total, decimals and default year/currency');
check(bccomp($s['metrics']['billable']['amount'], '325.5', 6) === 0 && $s['metrics']['billable']['count'] === 3, 'Billable sum includes tax 2 without tax 1');
check(bccomp($s['metrics']['non_billable']['amount'], '50.3', 6) === 0, 'Non-billable exact decimal sum');
check($s['metrics']['not_invoiced']['count'] === 2 && bccomp($s['metrics']['not_invoiced']['amount'], '125.5', 6) === 0, 'Uninvoiced excludes non-billable');
check($s['metrics']['billed']['count'] === 1 && bccomp($s['metrics']['billed']['amount'], '200', 6) === 0, 'Billed includes linked invoice independently of payment');
check($s['billable_ratio'] === '86.6', 'Billable ratio matches same decimal totals');
check(scct_expenses_summary(['customer_id'=>404],1)['metrics']['all']['count'] === 0 && bccomp(scct_expenses_summary(['customer_id'=>404],1)['billable_ratio'], '0', 1) === 0, 'Empty summary has zero totals and a safe ratio');
check(scct_expenses_summary([], 2)['metrics']['all']['count'] === 1, 'Currencies never combined');
check(scct_expenses_summary(['years' => [$year-1]], 1)['metrics']['all']['count'] === 1, 'Year selection');
check(scct_expenses_summary(['years' => [$year,$year-1]], 1)['metrics']['all']['count'] === 7, 'Multiple years');
check(scct_expenses_summary(['years' => ["2020) OR 1=1 --",[]]], 1)['metrics']['all']['count'] === 6, 'Malformed years cannot alter SQL');
check(scct_expenses_summary(['customer_id'=>7,'project_id'=>8], 1)['metrics']['all']['count'] === 5, 'Customer/project scope');
$permissions = ['expenses:view_own'];
$s = scct_expenses_summary([], 1);
check($s['metrics']['all']['count'] === 5 && bccomp($s['metrics']['all']['amount'], '175.8', 6) === 0, 'Own permission scopes all summaries');
check(scct_expenses_can_read((object)['addedfrom'=>42]) && !scct_expenses_can_read((object)['addedfrom'=>99]), 'Record-level own access');
scct_expenses_assert_record('1'); check(true, 'Own read allowed');
denied(fn()=>scct_expenses_assert_record('2'), 'Foreign read denied');
denied(fn()=>scct_expenses_assert_record('1','edit'), 'View does not grant edit');
$permissions[] = 'expenses:edit'; scct_expenses_assert_record('1','edit'); check(true, 'Authorized edit permitted');
$ci->uri->segments[4] = '2'; $ci->router->method = 'add_expense_attachment';
denied('scct_expenses_admin_init', 'Attachment endpoint rejects foreign expense');
$ci->uri->segments[4] = '0'; denied('scct_expenses_admin_init', 'Zero ID cannot create orphan receipt');
$ci->router->method='bulk_action'; $ci->input->values=['ids'=>['1','2']];
denied('scct_expenses_admin_init', 'Mixed bulk action rejected before any mutation');
$permissions=[]; denied(fn()=>scct_expenses_summary([],1), 'No-view permission denies aggregate');
$permissions=['expenses:view'];
$ci->router->class='Download'; $ci->router->method='file'; $ci->uri->segments=[3=>'expense',4=>'1'];
scct_expenses_download_guard(); check(true,'Global receipt read permitted');
$loggedIn=false; denied('scct_expenses_download_guard','Receipt login enforced'); $loggedIn=true;
$ci->router->class='Expenses';$ci->router->method='list_expenses';
$ci->input->values=['scct_expense_category'=>'2','scct_expense_status'=>'not_invoiced'];
$join=['JOIN audit_exp_expenses_categories ON audit_exp_expenses_categories.id = audit_exp_expenses.category'];
$filtered=scct_expenses_table_filter($join);
$rows=$db->query('SELECT audit_exp_expenses.id FROM audit_exp_expenses '.implode(' ', $filtered))->result_array();
check(array_column($rows,'id') === ['4'], 'Quick category and status execute together in SQL');
$ci->input->values=['scct_expense_category'=>"1 OR 1=1",'scct_expense_status'=>['billed']];
check(scct_expenses_table_filter($join)===$join,'Unsafe filter values ignored');
$row=[0=>'',1=>1,2=>'<a class="tw-font-medium">Travel</a><div class="row-options"><a href="https://fixture.invalid/admin/expenses/list_expenses/1" onclick="init_expense(1);return false;">View</a> | <a href="https://fixture.invalid/admin/expenses/expense/1">Edit</a> | <a href="/delete/1" class="_delete">Delete</a></div>',3=>'10',4=>'<a>Receipt expense</a>',5=>'<a href="/receipt">PDF</a>'];
$r=scct_expenses_row($row,[]);
check(strpos($r[2],'row-options')===false && strpos($r[4],'exp-btn-view-pill')!==false && strpos($r[4],'exp-btn-edit-pill')!==false && strpos($r[4],'_delete')!==false,'Native View/Edit/Delete moved beneath name');
check(strpos($r[5],'exp-receipt-badge')!==false && count($r)===count($row),'Receipt and all native columns preserved');
$ci->router->class='Clients';check(scct_expenses_row($row,[])===$row && scct_expenses_table_filter($join)===$join,'Other module tables untouched');
$view=['path'=>'admin/expenses/manage','data'=>[]];check(scct_expenses_view($view)===$view,'Other controller views untouched');
$ci->router->class='Expenses';check(is_file($root.'/application/views/'.scct_expenses_view($view)['path'].'.php'),'Module view path resolves through native loader');
$legacy=['path'=>'admin/expenses/expenses_total_template','data'=>[]];check(scct_expenses_view($legacy)===$legacy,'Non-workspace legacy totals preserved');
$spending=scct_expenses_category_spending();check(count($spending)===3,'Category totals separated by currency');
$admin=false;check(scct_expenses_category_spending()===[],'Category metrics admin-only');
$lang=[];require $root.'/modules/smart_choice_core_tools/language/english/smart_choice_core_tools_lang.php';$en=$lang;
$lang=[];require $root.'/modules/smart_choice_core_tools/language/spanish/smart_choice_core_tools_lang.php';$es=$lang;
check(array_keys($en)===array_keys($es),'English and Spanish key parity');$lang=$en;
$scct_expense_summary=$s;$expenses_years=[['year'=>$year]];$currencies=[];$_currency=1;$scct_expense_years=[$year];
ob_start();include $root.'/modules/smart_choice_core_tools/views/expenses/expenses_total_template.php';$html=ob_get_clean();
check(substr_count($html,'data-scct-expense-status=')===5 && strpos($html,'175.80 USD')!==false,'Summary view renders actual scoped fixture totals and five cards');
$folder=get_upload_path_by_type('expense').'1';mkdir($folder,0700,true);file_put_contents($folder.'/fixture.pdf',"%PDF-1.4\nfixture");
$expense=(object)['expenseid'=>1,'attachment'=>'fixture.pdf'];check(scct_expenses_receipt($expense)['exists'],'Existing receipt recognized');
$expense->attachment='../fixture.pdf';check(!scct_expenses_receipt($expense)['exists'],'Receipt traversal rejected');
$expense->attachment='missing.pdf';check(!scct_expenses_receipt($expense)['exists'],'Missing file detected without broken canvas');
unlink($folder.'/fixture.pdf');rmdir($folder);rmdir(dirname($folder));
$db->close();echo "PASS: $checks Expenses SQL, permissions, view, filter, receipt and bilingual checks (temporary fixtures only)\n";
