<?php
/** Exercise real guest checkout, customer login, session rotation and native return redirect. */
namespace ForceUTF8 { class Encoding { public static function toUTF8($value) { return $value; } } }
namespace {
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
#[\AllowDynamicProperties] class App_Model {}
#[\AllowDynamicProperties] class ClientsController { function __construct() {} }
class FlowStop extends \RuntimeException {}
function redirect($url, $method = null) { throw new FlowStop($url); }
function site_url($path = '') { return 'https://crm.example.test/'.$path; }
function db_prefix() { return 'tbl'; }
function get_option($key) { return 0; }
function show_recaptcha_in_customers_area() { return false; }
function get_cookie(...$args) { return false; }
function app_set_remember_cookie(...$args) {}
function log_activity($message) {}
function hooks() { static $h; return $h ??= new class { function do_action(...$args) {} function apply_filters($name,$value) { return $value; } }; }
$app_hasher=new class { function CheckPassword($plain,$hash) { return $plain==='fixture-password' && $hash==='fixture-hash'; } };
class SessionFixture {
    public $data=[], $rotations=0;
    function userdata($key) { return $this->data[$key]??null; }
    function has_userdata($key) { return isset($this->data[$key]); }
    function set_userdata($key,$value=null) { if(is_array($key))$this->data=array_merge($this->data,$key);else $this->data[$key]=$value; }
    function unset_userdata($key) { unset($this->data[$key]); }
    function set_flashdata(...$args) {}
    function sess_regenerate($destroy) { if(!$destroy)throw new \RuntimeException('Session must rotate securely');++$this->rotations; }
}
class InputFixture { public $postData=[]; function post($key=null,$xss=false) { return $key===null?$this->postData:($this->postData[$key]??null); } function ip_address() { return '192.0.2.1'; } }
class LoadFixture { function model(...$args) {} function helper(...$args) {} }
$ci=(object)['session'=>new SessionFixture,'lang'=>new class { function line($key,...$args) { return $key; } }];
function &get_instance() { return $GLOBALS['ci']; }
$root=dirname(__DIR__);
require $root.'/application/helpers/general_helper.php';
require $root.'/modules/products/controllers/Client.php';
require $root.'/application/models/Authentication_model.php';
require $root.'/application/controllers/Authentication.php';
$checks=0;
function check($ok,$message) { if(!$ok)throw new \RuntimeException($message);++$GLOBALS['checks']; }
function destination($action) { try {$action();}catch(FlowStop $e){return $e->getMessage();}throw new \RuntimeException('Expected redirect'); }
$shop=new \Client;$shop->session=$ci->session;$shop->input=new InputFixture;$shop->load=new LoadFixture;
$cart=[['product_id'=>7,'product_variation_id'=>90,'quantity'=>2],['product_id'=>8,'product_variation_id'=>'','quantity'=>1]];
$ci->session->data=['cart_data'=>$cart,'red_url'=>'https://attacker.invalid/'];
check(destination(fn()=>$shop->place_order())===site_url('authentication/login'),'Guest checkout must go directly to customer login');
check($ci->session->userdata('red_url')===site_url('products/client/place_order'),'Checkout stores a fixed same-site return destination');
check($ci->session->userdata('cart_data')===$cart,'Guest checkout preserves base/variation quantities');
$auth=(new \ReflectionClass(\Authentication_model::class))->newInstanceWithoutConstructor();
$auth->session=$ci->session;$auth->input=new InputFixture;$auth->load=new LoadFixture;
$auth->db=new class { function where(...$args){return $this;}function set(...$args){return $this;}function update(...$args){}function get($table){return new class {function row(){return (object)['id'=>12,'userid'=>42,'password'=>'fixture-hash','active'=>1];}};} };
$login=(new \ReflectionClass(\Authentication::class))->newInstanceWithoutConstructor();
$login->Authentication_model=$auth;$login->load=new LoadFixture;$login->input=new InputFixture;
$login->form_validation=new class { function set_rules(...$args){}function run(){return true;} };
$login->announcements_model=new class {function set_announcements_as_read_except_last_one($id){} };
$login->input->postData=['email'=>'fixture@example.test','password'=>'incorrect'];
check(destination(fn()=>$login->login())===site_url('authentication/login'),'Invalid password remains on login');
check($ci->session->userdata('cart_data')===$cart && $ci->session->has_userdata('red_url'),'Failed login preserves cart and checkout intent');
$login->input->postData['password']='fixture-password';
check(destination(fn()=>$login->login())===site_url('products/client/place_order'),'Actual successful login follows native redirect back to checkout');
check($ci->session->rotations===1,'Customer login still rotates the session');
check($ci->session->userdata('cart_data')===$cart,'Cart survives actual login model and session rotation');
check(!$ci->session->has_userdata('red_url'),'Native redirect consumes checkout intent once');
check(is_client_logged_in() && get_client_user_id()===42,'Customer session is established');
$shop->products_model=new class {function get_by_cart_product($cart){$GLOBALS['checkout_cart']=$cart;throw new FlowStop('checkout-items-loaded');}};
check(destination(fn()=>$shop->place_order())==='checkout-items-loaded','Signed-in checkout reaches cart product loading without login');
check($GLOBALS['checkout_cart']===$cart,'Checkout loads the same product IDs, variation and quantities');
$ci->session->data['cart_data']=[];
check(destination(fn()=>$shop->place_order())===site_url('products/client/'),'Empty signed-in cart returns to catalog');
echo "Products guest/login checkout regression passed ($checks checks); no accounts, invoices or emails created.\n";
}
