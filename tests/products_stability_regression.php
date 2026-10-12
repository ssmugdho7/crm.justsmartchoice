<?php
/** Run with PHP CLI. Fixtures execute the real controllers/models without real invoices or accounts. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
$root = getenv('CRM_SOURCE_ROOT') ?: dirname(__DIR__);
#[AllowDynamicProperties] class App_Model { public $db; public $load; function __construct() {} }
class CI_Model extends App_Model {}
#[AllowDynamicProperties] class AdminController { function __construct() {} }
#[AllowDynamicProperties] class ClientsController { function __construct() {} }
class StopFlow extends RuntimeException {}
class Denied extends StopFlow {}
class Missing extends StopFlow {}
class PermissionChecked extends StopFlow {}
$checks = 0;
function check($condition, $message) { global $checks; if (!$condition) { throw new RuntimeException($message); } ++$checks; }
function _l($key, $args = null) { return $key; }
function db_prefix() { return 'tbl'; }
function redirect($path, $type = null) { throw new StopFlow($path); }
function access_denied($feature = null) { throw new Denied(); }
function show_404() { throw new Missing(); }
function set_alert($type, $message) { $GLOBALS['alerts'][] = [$type, $message]; }
function site_url($path = '') { return $path; }
function admin_url($path = '') { return $path; }
function get_option($key) { return $key === 'invoice_due_after' ? 30 : 0; }
function get_client_user_id() { return 42; }
function is_client_logged_in() { return true; }
function log_message($level, $message) {}
function _d($date) { return $date; }
function to_sql_date($date) { return $date; }
function has_permission($feature, $staff = '', $capability = 'view') {
    if ($GLOBALS['probe'] ?? false) {
        if ($capability !== $GLOBALS['expected']) { throw new RuntimeException('Incorrect permission: '.$feature.'/'.$capability); }
        if ($GLOBALS['grant']) { throw new PermissionChecked(); }
    }
    return in_array($capability, $GLOBALS['permissions'][$feature] ?? [], true);
}
class InputFixture {
    public $postData = [], $getData = [];
    function post($key = null, $xss = false) { return $key === null ? $this->postData : ($this->postData[$key] ?? null); }
    function get($key) { return $this->getData[$key] ?? null; }
    function is_ajax_request() { return true; }
}
class SessionFixture {
    public $data = [];
    function userdata($key) { return $this->data[$key] ?? null; }
    function set_userdata($key, $value = null) { if (is_array($key)) { $this->data = array_merge($this->data, $key); } else { $this->data[$key] = $value; } }
    function unset_userdata($key) { unset($this->data[$key]); }
}
class LoadFixture { function model($model) {} function library($library) {} }
foreach (['Products','Variations','Coupons','Products_categories','Product_notifications','Product_reviews','Product_upsell','Product_gift_cards','Exit_popups','Staff_order','Client'] as $controller) {
    require $root.'/modules/products/controllers/'.$controller.'.php';
}
require $root.'/modules/products/models/Products_model.php';
require $root.'/modules/products/models/Order_model.php';
// Guard warnings as failures: missing array keys are part of the original regressions.
set_error_handler(function ($level, $message, $file, $line) { if ($level & error_reporting()) { throw new ErrorException($message, 0, $level, $file, $line); } });
$map = [
    'Products'=>['add_product'=>'create','edit'=>'edit','delete'=>'delete','order_history'=>'view','order_report'=>'view','custom_report'=>'view','mass_delete'=>'delete','import_products'=>'create'],
    'Variations'=>['index'=>'view','add'=>'create','edit'=>'edit','delete'=>'delete'],
    'Coupons'=>['index'=>'view','add'=>'create','edit'=>'edit','delete'=>'delete'],
    'Products_categories'=>['index'=>'view','category'=>'create','delete_category'=>'delete'],
    'Product_notifications'=>['edit'=>'edit','delete'=>'delete'],
    'Product_reviews'=>['approve'=>'edit','unapprove'=>'edit','delete'=>'delete'],
    'Product_upsell'=>['save'=>'create','delete'=>'delete'],
    'Product_gift_cards'=>['template'=>'create','delete_template'=>'delete'],
    'Exit_popups'=>['edit'=>'edit','delete'=>'delete'],
    'Staff_order'=>['get_available_coupons'=>'create','get_product_data'=>'create'],
];
foreach ($map as $class=>$methods) {
    $c = (new ReflectionClass($class))->newInstanceWithoutConstructor(); $c->input = new InputFixture();
    foreach ($methods as $method=>$capability) {
        foreach ([false,true] as $grant) {
            $GLOBALS['probe'] = true; $GLOBALS['expected'] = $capability; $GLOBALS['grant'] = $grant;
            try { $c->$method($method === 'template' ? null : 1); throw new RuntimeException('No permission guard: '.$class.'::'.$method); }
            catch (PermissionChecked $e) { check($grant, 'Granted path reached expected permission'); }
            catch (Denied $e) { check(!$grant, 'Denied path stopped before data access'); }
        }
    }
}
foreach ([['Product_upsell','save',['id'=>9]],['Products_categories','category',['p_category_id'=>9]],['Product_gift_cards','template',[]]] as [$class,$method,$post]) {
    $c=(new ReflectionClass($class))->newInstanceWithoutConstructor();$c->input=new InputFixture();$c->input->postData=$post;
    $GLOBALS['expected']='edit';$GLOBALS['grant']=true;
    try {$c->$method(9);throw new RuntimeException('Missing edit guard');}catch(PermissionChecked $e){check(true,'Existing records require Edit');}
}
$GLOBALS['probe'] = false;
foreach (['view','create','edit'] as $cap) {
    $GLOBALS['permissions']=['products'=>[$cap]];
    $c=(new ReflectionClass('Variations'))->newInstanceWithoutConstructor();$c->input=new InputFixture();
    $c->variations_model=new class {function get_values($id){return [['id'=>1,'value'=>'Large']];}};
    ob_start();$c->values();$json=ob_get_clean();check(count(json_decode($json,true))===1,'Form option lookup works with '.$cap.' permission');
}
$GLOBALS['permissions']=[];
$c=(new ReflectionClass('Products'))->newInstanceWithoutConstructor();
try {$c->test();throw new RuntimeException('Inventory test URL was active');}catch(Missing $e){check(true,'Inventory debug URL disabled');}
$c=(new ReflectionClass('Client'))->newInstanceWithoutConstructor();$c->input=new InputFixture();$c->session=new SessionFixture();$c->load=new LoadFixture();
foreach ([['id'=>7],['id'=>[7,8]],['id'=>[['bad'],0]]] as $get) {
    $c->input->getData=$get;
    try {$c->my_cart();}catch(StopFlow $e){}
}
check(count($c->session->data['cart_data'])===2,'Shortcut inserts new products and rejects malformed IDs');
check($c->session->data['cart_data'][0]['quantity']===2,'Shortcut increments existing base product');
$c->session->data['cart_data'][]=['product_id'=>7,'product_variation_id'=>90,'quantity'=>3];
$c->input->getData=['id'=>7];try {$c->manualorder();}catch(StopFlow $e){}
check($c->session->data['cart_data'][0]['quantity']===3 && $c->session->data['cart_data'][2]['quantity']===3,'Base shortcut does not alter variation quantities');
$c->session->data['cart_data']=[['product_id'=>7,'quantity'=>1]];
$c->input->postData=['product_id'=>7,'product_variation_id'=>'','quantity'=>2];ob_start();$c->add_cart();ob_end_clean();
check(count($c->session->data['cart_data'])===1 && $c->session->data['cart_data'][0]['quantity']===2,'Legacy cart updates without undefined variation key');
ob_start();$c->remove_cart();ob_end_clean();check($c->session->data['cart_data']===[],'Legacy cart removes without warnings');
$sort=new ReflectionMethod('Client','sort_cart');
check($sort->invoke($c,'invalid')===[],'Malformed cart cannot cause array_keys TypeError');
check($sort->invoke($c,[['product_id'=>1],false,['product_id'=>1]])===[],'Malformed cart rows rejected before sorting');
$sorted=$sort->invoke($c,[3=>['product_id'=>1],9=>['product_id'=>2],11=>['product_id'=>1]]);
check(array_keys($sorted)===[0,1,2] && array_column($sorted,'product_id')===[1,1,2],'Sparse checkout rows reindexed and grouped');
$c->order_model=new class {public $post;function add_invoice_order($post){$this->post=$post;throw new StopFlow('checkout captured');}};
$c->input->postData=['clientid'=>999,'product_items'=>[['product_id'=>7,'qty'=>1]]];
try {$c->place_order();}catch(StopFlow $e){}
check($c->order_model->post['clientid']===42,'Customer cannot change invoice owner through POST');
$c->input->postData=['clientid'=>999];try {$c->place_order();}catch(StopFlow $e){}
check($c->order_model->post['product_items']===[],'Missing checkout items fail safely');
class ProductDbFixture {
    public $where=[]; public $products=[]; public $variations=[];
    function __call($name,$args) { if ($name==='where' || $name==='where_in') {$this->where[$args[0]]=$args[1];} return $this; }
    function get($table=null) {
        $variation=isset($this->where['tblproduct_variations.id'])||isset($this->where['product_variations.id']);
        $id=$variation?($this->where['tblproduct_variations.id']??$this->where['product_variations.id']):($this->where['id']??null);
        $row=($variation?$this->variations:$this->products)[$id]??null;
        if ($variation && isset($this->where['tblproduct_variations.product_id']) && $row && $row->product_id != $this->where['tblproduct_variations.product_id']) {$row=null;}
        $this->where=[];
        return new class($row) {function __construct(public $value) {} function row(){return $this->value?clone $this->value:null;}};
    }
}
$pm=new Products_model();$pm->db=new ProductDbFixture();
$base=(object)['id'=>7,'product_name'=>'Service','product_description'=>'Real description','quantity_number'=>5,'rate'=>100,'taxes'=>serialize([]),'is_digital'=>0,'recurring'=>0,'recurring_type'=>'','custom_recurring'=>0,'cycles'=>0];
$pm->db->products=[7=>$base];$pm->db->variations=[90=>(object)['product_id'=>7,'variation_name'=>'Size','variation_value'=>'Large','rate'=>150,'quantity_number'=>3],91=>(object)['product_id'=>8,'variation_name'=>'Size','variation_value'=>'Small','rate'=>1,'quantity_number'=>50]];
check($pm->get_by_id_product_afflect_variation([['product_id'=>7,'product_variation_id'=>91]])===[],'Foreign variation cannot supply invoice price');
check($pm->get_by_id_product_afflect_variation([['product_id'=>404,'product_variation_id'=>'']])===[],'Deleted product fails without null dereference');
check($pm->get_by_id_product_afflect_variation([['product_id'=>7,'product_variation_id'=>404]])===[],'Deleted variation fails without null dereference');
check($pm->get_by_id_product_afflect_variation([['product_id'=>7,'product_variation_id'=>90]])[0]->rate===150,'Valid variation retains database price');
check($pm->get_by_cart_product([['product_id'=>7,'product_variation_id'=>91,'quantity'=>1]])===[],'Cart also rejects foreign variation');
check(count($pm->get_by_cart_product([['product_id'=>7,'quantity'=>1]]))===1,'Base legacy cart still renders');
$om=(new ReflectionClass('Order_model'))->newInstanceWithoutConstructor();$om->products_model=$pm;
$om->clients_model=new class {function get_customer_billing_and_shipping_details($id){throw new StopFlow('validated order customer '.$id);}};
foreach ([null,'bad',[],[false],[['product_id'=>7,'qty'=>0]],[['product_id'=>7,'qty'=>-1]],[['product_id'=>7,'qty'=>INF]],[['product_id'=>7,'qty'=>['bad']]],[['product_id'=>[7],'qty'=>1]],[['product_id'=>7,'product_variation_id'=>91,'qty'=>1]],[['product_id'=>404,'qty'=>1]],[['product_id'=>7,'qty'=>5.9]]] as $items) {
    $result=$om->add_invoice_order(['clientid'=>42,'product_items'=>$items]);check($result['status']===false,'Invalid order rejected before invoice/stock side effects');
}
foreach ([1,2.5] as $qty) {
    try {$om->add_invoice_order(['clientid'=>73,'product_items'=>[5=>['product_id'=>7,'qty'=>$qty,'rate'=>1]]]);throw new RuntimeException('Valid order did not pass validation');}
    catch(StopFlow $e){check($e->getMessage()==='validated order customer 73','Staff-selected customer and valid quantities retained');}
}
// Continue valid orders through the actual invoice builder with inert persistence fixtures.
$om->load=new LoadFixture();
$om->clients_model=new class {public $missing=false;function get_customer_billing_and_shipping_details($id){return $this->missing?[]:[['billing_country'=>1,'shipping_country'=>1]];}};
$om->payment_modes_model=new class {function get(){return [['id'=>1,'selected_by_default'=>1]];}};
$om->currencies_model=new class {function get_base_currency(){return (object)['id'=>1];}};
$om->invoices_model=new class {public $requests=[];function add($post){$this->requests[]=$post;return 500+count($this->requests);}function get($id){return (object)['status'=>1,'hash'=>'fixture'];}};
$om->order_model=new class {public $requests=[];function add_order($post){$this->requests[]=$post;return true;}};
foreach ([['product_id'=>7,'qty'=>2,'rate'=>1],['product_id'=>7,'product_variation_id'=>90,'qty'=>1,'rate'=>1]] as $item) {
    $result=$om->add_invoice_order(['clientid'=>73,'product_items'=>[9=>$item]]);
    check($result['status']===true && $result['single_invoice']===true,'Valid order completes invoice pipeline');
    $invoice=end($om->invoices_model->requests);
    check((float)$invoice['total']===(float)($item['qty']*(!empty($item['product_variation_id'])?150:100)),'Submitted price replaced with database product/variation price');
    check($invoice['clientid']===73,'Staff customer selection preserved through invoice creation');
}
$recurring=clone $base;$recurring->id=8;$recurring->recurring=1;$pm->db->products[8]=$recurring;
$result=$om->add_invoice_order(['clientid'=>73,'product_items'=>[8=>['product_id'=>7,'qty'=>1],11=>['product_id'=>8,'qty'=>1]]]);
check($result['status']===true && $result['single_invoice']===false,'Mixed recurring/nonrecurring orders retain separate invoices');
$om->clients_model->missing=true;
check($om->add_invoice_order(['clientid'=>73,'product_items'=>[['product_id'=>7,'qty'=>1]]])['status']===false,'Deleted customer fails without array_merge TypeError');
foreach([null,[],0,'bad'] as $id){check($om->add_invoice_order(['clientid'=>$id,'product_items'=>[['product_id'=>7,'qty'=>1]]])['status']===false,'Malformed customer ID rejected');}
$GLOBALS['permissions']=['products'=>['create']];
$c=(new ReflectionClass('Products'))->newInstanceWithoutConstructor();$c->load=new LoadFixture();
$c->products_model=new class {public $added=[];function add_product($data){$this->added[]=$data;return count($this->added);}};
$csv=tempnam(sys_get_temp_dir(),'catalog-regression-');
try {
    file_put_contents($csv,"product_name,rate\nValid service,100\nMalformed service,100,unexpected column\n");
    $_FILES['import_file']=['tmp_name'=>$csv];$GLOBALS['alerts']=[];
    try {$c->import_products();}catch(StopFlow $e){}
    check(count($c->products_model->added)===1 && $GLOBALS['alerts'][0][0]==='warning','Malformed CSV row skipped with feedback; valid row still imports');
    file_put_contents($csv,"wrong_header\nBad service\n");$GLOBALS['alerts']=[];
    try {$c->import_products();}catch(StopFlow $e){}
    check(count($c->products_model->added)===1 && $GLOBALS['alerts'][0][0]==='danger','Wrong CSV header rejected without writes');
} finally {unlink($csv);unset($_FILES['import_file']);}
$GLOBALS['permissions']=['products'=>['edit']];
$c=(new ReflectionClass('Products_categories'))->newInstanceWithoutConstructor();$c->load=new LoadFixture();$c->input=new InputFixture();
$c->input->postData=['p_category_id'=>404,'p_category_name'=>'Deleted'];$c->product_category_model=new class {function get($id){return null;}};
ob_start();$c->category();$response=json_decode(ob_get_clean(),true);check($response['success']===false,'Deleted category handled without null dereference');
$GLOBALS['permissions']=['products'=>['delete']];
$c=(new ReflectionClass('Coupons'))->newInstanceWithoutConstructor();$c->coupons_model=new class {function delete($id){return true;}};
try {$c->delete(1);}catch(StopFlow $e){check($e->getMessage()==='products/coupons','Coupon delete returns to correct module route');}
$GLOBALS['permissions']=['products'=>['view']];
$c=(new ReflectionClass('Products'))->newInstanceWithoutConstructor();$c->input=new InputFixture();
$c->Reports_model=new class {public $args;function chart_custom_date_range(...$args){$this->args=$args;return ['date_range'=>[],'series'=>null];}};
foreach ([[],['products_name'=>[[]],'from'=>'2026-10-03','to'=>'2026-10-04'],['products_name'=>['Service'],'from'=>'2026-02-30','to'=>'2026-10-04'],['products_name'=>['Service'],'from'=>'2026-10-05','to'=>'2026-10-04']] as $post) {
    $c->input->postData=$post;ob_start();$c->custom_report();$result=json_decode(ob_get_clean(),true);
    check($result['status']==='error','Malformed report filter returns JSON error without SQL');
}
$c->input->postData=['products_name'=>['Door "Premium"'],'from'=>'2026-10-03','to'=>'2026-10-04'];ob_start();$c->custom_report();ob_end_clean();
check($c->Reports_model->args===[['Door "Premium"'],'2026-10-03','2026-10-04'],'Report passes names as an array and canonical date bounds');
echo "PASS: $checks product permission, cart, checkout ownership and order-validation checks\n";
