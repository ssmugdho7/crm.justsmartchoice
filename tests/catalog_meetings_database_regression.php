<?php
// CLI only. All fixture data is connection-local TEMPORARY data under audit_ names.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__);define('BASEPATH',$root.'/system/');define('APPPATH',$root.'/application/');define('ENVIRONMENT','testing');
function log_message(...$args){}function is_php($version){return version_compare(PHP_VERSION,$version,'>=');}
function show_error($message){throw new RuntimeException($message);}function db_prefix(){return 'audit_';}
function &get_instance(){return $GLOBALS['ci'];}
class CI_Model {function __get($key){return get_instance()->{$key};}}
class App_Model extends CI_Model {function __construct(){}}
$checks=0;function check($value,$label){global $checks;if(!$value){throw new RuntimeException($label);}++$checks;}
require $root.'/system/database/DB.php';
$siteRoot=getenv('CRM_SITE_ROOT');
if(!$siteRoot||!is_file($siteRoot.'/application/config/app-config.php')){throw new RuntimeException('CRM_SITE_ROOT must point to a private site checkout');}
require $siteRoot.'/application/config/app-config.php';
$databaseParams=['hostname'=>APP_DB_HOSTNAME,'username'=>APP_DB_USERNAME,'password'=>APP_DB_PASSWORD,'database'=>APP_DB_NAME,'dbdriver'=>'mysqli','dbprefix'=>'audit_','pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>false];
DB($databaseParams,true)->close();
class FixtureDatabase extends CI_DB_mysqli_driver {
    public function query($sql,$binds=false,$return_object=null) {
        // MySQL forbids reusing one TEMPORARY table twice in a SELECT.
        // Identical fixture copies preserve the real query joins/subquery and avoid real tables.
        $sql=str_replace('`audit_staff` `ast`','`audit_assigned_staff` `ast`',$sql);
        $sql=str_replace('FROM audit_google_meet_attendees WHERE staff_id=', 'FROM audit_scope_attendees WHERE staff_id=', $sql);
        return parent::query($sql,$binds,$return_object);
    }
}
$db=new FixtureDatabase($databaseParams);$db->initialize();
$ci=(object)['db'=>$db];
foreach([
 'CREATE TEMPORARY TABLE audit_product_master (id INT PRIMARY KEY, product_name VARCHAR(100), product_description TEXT, product_category_id INT, rate DECIMAL(10,2), quantity_number INT)',
 'CREATE TEMPORARY TABLE audit_product_categories (p_category_id INT PRIMARY KEY, p_category_name VARCHAR(100))',
 'CREATE TEMPORARY TABLE audit_product_variations (id INT PRIMARY KEY, product_id INT, variation_id INT, variation_value_id INT, rate DECIMAL(10,2), quantity_number INT)',
 'CREATE TEMPORARY TABLE audit_variations (id INT PRIMARY KEY, name VARCHAR(100))',
 'CREATE TEMPORARY TABLE audit_variation_values (id INT PRIMARY KEY, value VARCHAR(100))',
 'CREATE TEMPORARY TABLE audit_order_master (id INT PRIMARY KEY, order_date DATE)',
 'CREATE TEMPORARY TABLE audit_order_items (order_id INT, product_id INT, qty DECIMAL(10,2))',
 'CREATE TEMPORARY TABLE audit_google_meet_meetings (id INT PRIMARY KEY, created_by INT, assigned_staff_id INT, status VARCHAR(30), duration_minutes INT, start_time DATETIME, subject VARCHAR(100), title VARCHAR(100), description TEXT, notes TEXT)',
 'CREATE TEMPORARY TABLE audit_google_meet_attendees (meeting_id INT, staff_id INT)',
 'CREATE TEMPORARY TABLE audit_staff (staffid INT PRIMARY KEY, firstname VARCHAR(50), lastname VARCHAR(50))',
] as $sql){check($db->query($sql),'Fixture-only schema created');}
$db->insert('audit_product_categories',['p_category_id'=>1,'p_category_name'=>'Fixture category']);
foreach([7,8] as $id){$db->insert('audit_product_master',['id'=>$id,'product_name'=>'Fixture '.$id,'product_category_id'=>1,'rate'=>100,'quantity_number'=>5]);}
$db->insert('audit_variations',['id'=>1,'name'=>'Size']);$db->insert('audit_variation_values',['id'=>1,'value'=>'Large']);
foreach([[90,7,150],[91,8,1]] as [$id,$product,$rate]){$db->insert('audit_product_variations',['id'=>$id,'product_id'=>$product,'variation_id'=>1,'variation_value_id'=>1,'rate'=>$rate,'quantity_number'=>3]);}
require $root.'/modules/products/models/Products_model.php';$products=new Products_model();
check($products->get_by_id_product_afflect_variation([['product_id'=>7,'product_variation_id'=>91]])===[],'Foreign variation rejected in native SQL lookup');
check($products->get_by_id_product_afflect_variation([['product_id'=>404,'product_variation_id'=>'']])===[],'Missing product rejected');
check($products->get_by_id_product_afflect_variation([['product_id'=>7,'product_variation_id'=>404]])===[],'Missing variation rejected');
$valid=$products->get_by_id_product_afflect_variation([['product_id'=>7,'product_variation_id'=>90]]);
check(count($valid)===1&&(float)$valid[0]->rate===150.0&&$valid[0]->p_category_name==='Fixture category','Valid variation and category joins retained');
check($products->get_by_cart_product([['product_id'=>7,'product_variation_id'=>91,'quantity'=>1]])===[],'Foreign variation rejected in native cart query');
check(count($products->get_by_cart_product([['product_id'=>7,'quantity'=>1]]))===1,'Base cart still loads');
foreach([42,99] as $id){$db->insert('audit_staff',['staffid'=>$id,'firstname'=>'Fixture','lastname'=>(string)$id]);}
foreach([[1,42,99,'scheduled'],[2,99,42,'completed'],[3,99,99,'scheduled'],[4,99,99,'completed']] as [$id,$creator,$assigned,$status]){$db->insert('audit_google_meet_meetings',['id'=>$id,'created_by'=>$creator,'assigned_staff_id'=>$assigned,'status'=>$status,'duration_minutes'=>30,'start_time'=>'2026-10-03 10:00:00','subject'=>'Fixture '.$id,'title'=>'Fixture '.$id]);}
$db->insert('audit_google_meet_attendees',['meeting_id'=>3,'staff_id'=>42]);
// MySQL SHOW TABLES omits TEMPORARY tables. Populate only the fixture driver's metadata cache.
$db->data_cache['table_names']=['audit_product_master','audit_product_categories','audit_product_variations','audit_variations','audit_variation_values','audit_google_meet_meetings','audit_google_meet_attendees','audit_staff'];
$db->query('CREATE TEMPORARY TABLE audit_assigned_staff AS SELECT * FROM audit_staff');
$db->query('CREATE TEMPORARY TABLE audit_scope_attendees AS SELECT * FROM audit_google_meet_attendees');
require $root.'/modules/google_meet/models/Google_meet_model.php';$meetings=new Google_meet_model();
foreach([1,2,3] as $id){check($meetings->staff_has_meeting($meetings->get($id),42),'Creator/assigned/attendee record access');}
check(!$meetings->staff_has_meeting($meetings->get(4),42),'Foreign meeting not owned');
$own=$meetings->report_meetings(['staff_id'=>42]);$ids=array_column($own,'id');sort($ids);
check(array_map('intval',$ids)===[1,2,3],'Native reports contain only owned/assigned/invited rows');
check($meetings->report_summary(['staff_id'=>42])===['total'=>3,'completed'=>1,'minutes'=>90],'Summary excludes foreign row');
check(count($meetings->report_meetings([]))===4&&$meetings->report_summary()===['total'=>4,'completed'=>2,'minutes'=>120],'Global report remains complete');
check(count($meetings->report_meetings(['staff_id'=>42,'status'=>'completed']))===1,'Existing status filters retained');
check($meetings->report_summary(['staff_id'=>404])===['total'=>0,'completed'=>0,'minutes'=>0],'Empty own summary is stable');
$db->where('id',7)->update('audit_product_master',['product_name'=>'Door "Premium"']);
$db->insert('audit_order_master',['id'=>1,'order_date'=>'2026-10-03']);$db->insert('audit_order_items',['order_id'=>1,'product_id'=>7,'qty'=>2]);
$db->insert('audit_order_master',['id'=>2,'order_date'=>'2026-10-04']);$db->insert('audit_order_items',['order_id'=>2,'product_id'=>8,'qty'=>3]);
require $root.'/modules/products/models/Reports_model.php';$reports=new Reports_model();
$chart=$reports->chart_custom_date_range(['Door "Premium"'],'2026-10-03','2026-10-03');
check($chart['date_range']===['2026-10-03'] && $chart['series'][0]['data']===[2],'Quoted names and date bounds work in actual report SQL');
check($reports->chart_custom_date_range(['bad") OR 1=1 --'],'2026-10-01','2026-10-05')['date_range']===[],'Crafted product filter remains a literal SQL value');
check($reports->chart_custom_date_range(['Door "Premium"','Fixture 8'],'2026-10-01','2026-10-05')['date_range']===['2026-10-03','2026-10-04'],'Multiple products retain separate report dates/series');
$db->close();
echo "PASS: $checks native MySQL/CodeIgniter catalog and Meetings checks using temporary tables only\n";
