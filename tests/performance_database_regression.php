<?php
// CLI only. All writes are to connection-local audit_* TEMPORARY tables.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/'); define('APPPATH', $root . '/application/'); define('ENVIRONMENT', 'testing');
function log_message(...$args) {} function is_php($v) { return version_compare(PHP_VERSION, $v, '>='); }
function show_error($m) { throw new RuntimeException($m); } function db_prefix() { return 'audit_'; }
function &get_instance() { return $GLOBALS['ci']; }
function get_contact_user_id() { return 7; } function get_client_user_id() { return $GLOBALS['customerId'] ?? 42; }
function _l($v) { return $v; } function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function site_url($v = '') { return '/'.$v; }
class CI_Model { function __get($k) { return get_instance()->{$k}; } }
class App_Model extends CI_Model { function __construct() {} }
class app {}
class TestHooks {
    public function apply_filters($name, $value) {
        if ($name === 'get_contact_permissions') { $value[] = ['id'=>99,'short_name'=>'plugin-permission','name'=>'Plugin']; }
        return $value;
    }
    public function add_action(...$args) {}
}
function hooks() { static $h; return $h ?? ($h = new TestHooks()); }
function staff_can($capability,$feature) { return $GLOBALS['globalProjectView'] ?? false; }
function get_staff_user_id() { return 42; } function get_system_favourite_colors() { return []; }
function adjust_color_brightness($color,$amount) { return $color; } function admin_url($path='') { return '/admin/'.$path; }
function register_activation_hook(...$args) {} function register_deactivation_hook(...$args) {} function register_language_files(...$args) {}
function add_option($name, $value) {
    $db = get_instance()->db;
    if (!$db->where('name', $name)->count_all_results(db_prefix().'options')) { $db->insert(db_prefix().'options', ['name'=>$name,'value'=>$value]); }
}
function get_option($name) { return $GLOBALS['excludeDraft'] ?? 0; }
$checks = 0; function check($v, $m) { global $checks; if (!$v) { throw new RuntimeException($m); } ++$checks; }
require $root.'/system/database/DB.php';
$site = getenv('CRM_SITE_ROOT'); if (!$site || !is_file($site.'/application/config/app-config.php')) { throw new RuntimeException('Set CRM_SITE_ROOT to a private checkout'); }
require $site.'/application/config/app-config.php';
$db = DB(['hostname'=>APP_DB_HOSTNAME,'username'=>APP_DB_USERNAME,'password'=>APP_DB_PASSWORD,'database'=>APP_DB_NAME,'dbdriver'=>'mysqli','dbprefix'=>'audit_','pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>true], true);
require $root.'/application/libraries/App_object_cache.php';
$ci = (object) ['db'=>$db, 'app_object_cache'=>new App_object_cache()];
foreach ([
 'product_master'=>'id INT PRIMARY KEY, product_name VARCHAR(100), product_category_id INT, is_variation INT',
 'product_categories'=>'p_category_id INT PRIMARY KEY, p_category_name VARCHAR(100)',
 'product_variations'=>'id INT PRIMARY KEY, product_id INT, variation_id INT, variation_value_id INT, rate DECIMAL(10,2)',
 'variations'=>'id INT PRIMARY KEY, name VARCHAR(100)', 'variation_values'=>'id INT PRIMARY KEY, value VARCHAR(100)',
 'wiki_books'=>'id INT PRIMARY KEY, name VARCHAR(100), short_description TEXT, customer_visible INT, updated_at DATETIME',
 'wiki_articles'=>'id INT PRIMARY KEY, book_id INT, title VARCHAR(100), description TEXT, thumbnail VARCHAR(255), updated_at DATETIME, is_publish INT, content LONGTEXT',
 'contact_permissions'=>'id INT AUTO_INCREMENT PRIMARY KEY, userid INT, permission_id INT',
 'invoices'=>'id INT PRIMARY KEY, clientid INT, status INT', 'projects'=>'id INT PRIMARY KEY, clientid INT, status INT',
 'options'=>'name VARCHAR(100) PRIMARY KEY, value TEXT',
 'project_members'=>'project_id INT, staff_id INT',
] as $table=>$columns) { check($db->query('CREATE TEMPORARY TABLE audit_'.$table.' ('.$columns.')'), 'Fixture schema: '.$table); }
require $root.'/modules/products/models/Products_model.php'; $products = new Products_model();
check($db->query('CREATE TEMPORARY TABLE audit_product_images (id INT PRIMARY KEY, product_id INT, image VARCHAR(255), is_primary INT)'), 'Gallery fixture schema');
$db->data_cache['table_names'] = array_merge($db->list_tables(), ['audit_product_images']);
foreach ([[1, 1, 'one-gallery.jpg', 0], [2, 1, 'one-primary.jpg', 1], [3, 2, 'two.jpg', 0], [4, 3, 'excluded.jpg', 1]] as [$id, $product, $image, $primary]) {
    $db->insert('audit_product_images', ['id'=>$id, 'product_id'=>$product, 'image'=>$image, 'is_primary'=>$primary]);
}
$before = count($db->queries); $galleries = $products->get_catalog_gallery_images([1, 2]);
check(count($db->queries)-$before === 1, 'Gallery lookup is batched into one query');
check(array_column($galleries[1], 'image') === ['one-primary.jpg', 'one-gallery.jpg'], 'Primary image and stable gallery order retained');
check(array_column($galleries[2], 'image') === ['two.jpg'] && !isset($galleries[3]), 'Uploaded galleries never cross products');
$before = count($db->queries); check($products->get_catalog_gallery_images([]) === [] && count($db->queries) === $before, 'Empty catalog avoids gallery reads');
$db->insert('audit_product_categories',['p_category_id'=>1,'p_category_name'=>'Fixture']);
$db->insert('audit_product_categories',['p_category_id'=>2,'p_category_name'=>'Empty']);
$db->insert('audit_variations',['id'=>1,'name'=>'Size']); $db->insert('audit_variations',['id'=>2,'name'=>'Color']);
$db->insert('audit_variation_values',['id'=>1,'value'=>'Large']);
foreach ([[1,'Zeta',1],[2,'Alpha',1],[3,'Base',0],[4,'No options',1]] as [$id,$name,$var]) { $db->insert('audit_product_master',['id'=>$id,'product_name'=>$name,'product_category_id'=>1,'is_variation'=>$var]); }
foreach ([[11,1,2],[12,1,1],[21,2,1],[31,3,1]] as [$id,$product,$variation]) { $db->insert('audit_product_variations',['id'=>$id,'product_id'=>$product,'variation_id'=>$variation,'variation_value_id'=>1,'rate'=>100]); }
$before = count($db->queries); $list = $products->get_by_id_product();
check(count($db->queries)-$before === 2, 'Catalog uses exactly two reads regardless of variation product count');
check(array_column($list,'product_name') === ['Alpha','Base','No options','Zeta'], 'Catalog sorting and all products retained');
check(is_array($list[0]) && is_object($list[0]['variations'][0]), 'List arrays and variation objects retained');
check(!isset($list[1]['variations']) && $list[2]['variations'] === [], 'Base and option-less products retain payload shape');
check(array_map(fn($v)=>(int)$v->id,$list[3]['variations']) === [12,11], 'Existing variation order retained');
foreach ($list as $p) { foreach ($p['variations'] ?? [] as $v) { check((int)$v->product_id === (int)$p['id'], 'Options never cross products'); } }
$before = count($db->queries); $filtered = $products->get_by_id_product([1,2]);
check(count($db->queries)-$before === 2 && count($filtered)===2 && is_object($filtered[0]), 'ID-array lookup batches options, preserving object return');
check(is_object($products->get_by_id_product(1)) && count($products->get_by_id_product(1)->variations)===2, 'Single product contract preserved');
check($products->get_by_id_product(404) === null, 'Missing product safely returns native null');
$before = count($db->queries); check($products->get_category_filter([2])===[], 'Empty category remains empty');
check(count($db->queries)-$before===1, 'Empty category avoids variation query');
check($products->get_category_filter([1])[0]['p_category_name']==='Fixture', 'Existing category filter retained');
check(array_map('intval',$products->get_populated_category_ids())===[1], 'Only populated categories selected');
require $root.'/modules/training_manual/models/Training_manual_books_model.php'; $books = new Training_manual_books_model();
foreach ([[1,1],[2,1],[3,0],[4,1]] as [$id,$visible]) { $db->insert('audit_wiki_books',['id'=>$id,'name'=>'Fixture '.$id,'short_description'=>'Summary','customer_visible'=>$visible,'updated_at'=>'2026-10-03 00:00:00']); }
foreach ([[1,1,1,'2026-10-02'],[2,1,1,'2026-10-03'],[3,1,0,'2026-10-03'],[4,2,1,'2026-10-03'],[5,3,1,'2026-10-03']] as [$id,$book,$published,$date]) { $db->insert('audit_wiki_articles',['id'=>$id,'book_id'=>$book,'title'=>'Guide '.$id,'description'=>'Description','thumbnail'=>'cover.webp','updated_at'=>$date.' 00:00:00','is_publish'=>$published,'content'=>str_repeat('body',10000)]); }
$before=count($db->queries); $visible=$books->get_customer_books('',true); $cards=$books->get_customer_book_article_cards(array_column($visible,'id'));
check(count($db->queries)-$before===2,'Help Library uses two reads for every visible book');
check(count($visible)===2 && count($cards)===2, 'Unpublished/private/empty books stay excluded');
check(array_map('intval',array_column($cards[1],'id'))===[2,1], 'Published guides retain most-recent-first order');
check(!isset($cards[1][0]['content']) && $cards[1][0]['thumbnail']==='cover.webp', 'Listing omits heavy article body and preserves image');
check(!isset($books->get_customer_book_article_cards([3])[3]), 'Even explicitly requested private book is excluded');
$before=count($db->queries); check($books->get_customer_book_article_cards([])===[] && count($db->queries)===$before,'Empty library avoids article reads');
require $root.'/application/helpers/clients_helper.php';
$db->insert('audit_contact_permissions',['userid'=>7,'permission_id'=>1]); $db->insert('audit_contact_permissions',['userid'=>7,'permission_id'=>99]); $db->insert('audit_contact_permissions',['userid'=>8,'permission_id'=>6]);
$before=count($db->queries);
for ($i=0;$i<20;$i++) { check(has_contact_permission('invoices') && !has_contact_permission('projects'), 'Repeated grant and denial remain correct'); }
check(count($db->queries)-$before===1, 'Forty repeated permission checks need one request-local read');
check(has_contact_permission('plugin-permission'), 'Hook-defined permission remains supported');
check(has_contact_permission('projects',8) && !has_contact_permission('invoices',8), 'Different contact has an isolated permission cache');
$before=count($db->queries); check(!has_contact_permission('unknown',7) && count($db->queries)===$before,'Unknown permission denies without reading');
$before=count($db->queries); check(!has_contact_permission('projects',404) && !has_contact_permission('invoices',404) && count($db->queries)-$before===1, 'Empty rights are cached as denied');
$db->where('userid',7)->where('permission_id',1)->delete('audit_contact_permissions'); clear_contact_permission_cache(7);
check(!has_contact_permission('invoices'), 'Revocation becomes effective after mutation invalidation');
$db->insert('audit_contact_permissions',['userid'=>7,'permission_id'=>6]); clear_contact_permission_cache(7);
check(has_contact_permission('projects'), 'Grant becomes effective after invalidation');
$db->where('userid',7)->delete('audit_contact_permissions'); clear_contact_permission_cache(7); check(!has_contact_permission('projects'), 'Deleted contact retains no rights');
$db->insert('audit_contact_permissions',['userid'=>7,'permission_id'=>1]); $ci->app_object_cache = new App_object_cache(); check(has_contact_permission('invoices'), 'Fresh request reads current authoritative rights');
foreach (['invoices','projects'] as $table) {
    foreach ([1,2,3,4,5,6] as $status) { $db->insert('audit_'.$table,['id'=>$status,'clientid'=>42,'status'=>$status]); }
    $db->insert('audit_'.$table,['id'=>99,'clientid'=>99,'status'=>1]);
    $before=count($db->queries); $counts=sc_customer_status_counts($table);
    check(count($db->queries)-$before===1 && array_sum($counts)===6 && (int)$counts[1]===1, 'One grouped read with exact customer scope: '.$table);
}
$invoiceStatusCounts=sc_customer_status_counts('invoices');
foreach ([0,1] as $excludeDraft) {
 $GLOBALS['excludeDraft']=$excludeDraft; ob_start(); include $root.'/application/views/themes/smartchoice/template_parts/invoices_stats.php'; ob_end_clean();
 check($total_invoices===($excludeDraft?4:5) && (int)$total_open===1 && (int)$total_paid===1, 'Billing denominator preserves cancelled/draft exclusions');
}
$customerId=404; check(sc_customer_status_counts('projects')===[], 'Empty customer summary');
try { sc_customer_status_counts('contacts'); check(false,'Unexpected summary table accepted'); } catch (InvalidArgumentException $e) { check(true,'Summary table allowlist'); }
require $root.'/application/models/Dashboard_model.php';
$ci->load = new class { function model($name) {} };
$ci->projects_model = new class { function get_project_statuses() { return [['id'=>2,'name'=>'Second','color'=>'#222222'],['id'=>1,'name'=>'First','color'=>'#111111'],['id'=>999,'name'=>'Empty','color'=>'#333333']]; } };
$db->insert('audit_project_members',['project_id'=>2,'staff_id'=>42]);
$db->insert('audit_project_members',['project_id'=>2,'staff_id'=>42]);
$db->insert('audit_project_members',['project_id'=>99,'staff_id'=>99]);
$dashboard = new Dashboard_model();
$before=count($db->queries); $own=$dashboard->projects_status_stats();
check(count($db->queries)-$before===1 && array_map('intval',$own['datasets'][0]['data'])===[1,0,0], 'Staff chart uses one grouped read and retains member scope without duplicate counts');
check($own['labels']===['Second','First','Empty'] && $own['datasets'][0]['statusLink']===['/admin/projects?status=2','/admin/projects?status=1','/admin/projects?status=999'], 'Staff chart retains status order and actions');
$globalProjectView=true; $all=$dashboard->projects_status_stats();
check(array_map('intval',$all['datasets'][0]['data'])===[1,2,0], 'Global staff chart remains complete and missing statuses show zero');
$ci->projects_model = new class { function get_project_statuses() { return []; } };
$before=count($db->queries); check($dashboard->projects_status_stats()['datasets'][0]['data']===[] && count($db->queries)===$before,'No configured statuses skips grouped query');
require $root.'/modules/debug_mode/debug_mode.php';
debug_mode_smartchoice_add_default_options();
$db->where('name','debug_mode_allowed_roles')->update('audit_options',['value'=>'custom-role']);
$db->where('name','debug_mode_client_visible')->update('audit_options',['value'=>'']);
$before=count($db->queries); debug_mode_smartchoice_add_default_options();
check(count($db->queries)-$before===1,'Existing debug defaults use one read and zero writes');
check($db->where('name','debug_mode_allowed_roles')->get('audit_options')->row()->value==='custom-role','Saved debug settings retained');
check($db->where('name','debug_mode_client_visible')->get('audit_options')->row()->value==='','Empty saved setting retained');
$db->where('name','debug_mode_log_level')->delete('audit_options'); debug_mode_smartchoice_add_default_options();
check($db->where('name','debug_mode_log_level')->get('audit_options')->row()->value==='basic','Missing default still self-heals');
$db->close(); echo "PASS: $checks native MySQL performance and isolation checks using temporary tables only\n";
