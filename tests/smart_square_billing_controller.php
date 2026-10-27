<?php
// Exercise the real controller with isolated CRM and Square transport fixtures.
namespace SmartSquareBillingFixture;
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/modules/smart_square_payments/helpers/smart_square_payments_helper.php';
class State {public static $post = [], $payload, $record;}
function _l($key, ...$args) {return $key;}
function html_escape($value) {return htmlspecialchars($value, ENT_QUOTES);}
function is_client_logged_in() {return false;}
function is_payment_mode_allowed_for_invoice($mode, $id) {return $mode === 'smart_square' && $id === 123;}
function get_invoice_total_left_to_pay($id, $total) {return '2.99';}
function db_prefix() {return 'fixture_';}
function log_message(...$args) {}
function show_404() {throw new \RuntimeException('Unexpected route');}
function curl_init($url) {return new \stdClass;}
function curl_setopt_array($curl, $options) {State::$payload = json_decode($options[CURLOPT_POSTFIELDS], true);}
function curl_exec($curl) {return json_encode(['payment'=>['id'=>'fixture-payment','status'=>'COMPLETED','amount_money'=>['amount'=>299,'currency'=>'USD']]]);}
function curl_errno($curl) {return 0;}
function curl_getinfo($curl, $key) {return 200;}
function curl_close($curl) {}
class Input {function method(){return 'POST';}function is_ajax_request(){return true;}function post($key){return State::$post[$key]??null;}}
class Output {public $response;function set_content_type($type){return $this;}function set_output($text){$this->response=json_decode($text,true);return $this;}}
class Loader {function model($name){}function library($name){}function helper($name){}}
class Db {function where(...$args){return $this;}function get($table){return $this;}function row(){return false;}function insert(...$args){return true;}function update(...$args){return true;}}
class Invoices_model {const STATUS_PAID=2, STATUS_CANCELLED=5;function get($id){return (object)['id'=>123,'hash'=>'fixture-hash','clientid'=>1,'status'=>1,'total'=>'2.99','currency_name'=>'USD'];}}
class Gateway {function getSetting($key){return ['active'=>'1','location_id'=>'fixture-location','sandbox'=>'1'][$key]??'';}function secret($key){return 'fixture-token';}function addPayment($record){State::$record=$record;return true;}}
class ClientsController {
    public $input,$output,$load,$db,$invoices_model,$smart_square_gateway;
    function __construct(){$this->input=new Input;$this->output=new Output;$this->load=new Loader;$this->db=new Db;$this->invoices_model=new Invoices_model;$this->smart_square_gateway=new Gateway;}
}
$source=file_get_contents(dirname(__DIR__).'/modules/smart_square_payments/controllers/Smart_square.php');
eval('namespace '.__NAMESPACE__.';'.substr($source,5));
function check($condition,$message){if(!$condition){throw new \RuntimeException($message);}}
$billing=['address_line_1'=>'10 Test Street','locality'=>'Tampa','administrative_district_level_1'=>'FL','postal_code'=>'33601','country'=>'US'];
$base=['invoice_id'=>123,'invoice_hash'=>'fixture-hash','source_id'=>'fixture-source','amount'=>'0.01'];
State::$post=$base+['billing_address'=>$billing];$controller=new Smart_square;$controller->charge();
check($controller->output->response['success']===true,'Valid billing request completes');
check(State::$payload['billing_address']===$billing,'Address reaches Square payment payload');
check(State::$payload['amount_money']['amount']===299 && State::$record['amount']==='2.99','Server invoice balance remains authoritative');
State::$payload=null;State::$post=$base+['billing_address'=>['address_line_1'=>['nested']]];$controller=new Smart_square;$controller->charge();
check($controller->output->response['success']===false && State::$payload===null,'Invalid address is rejected before any Square request');
State::$post=$base;$controller=new Smart_square;$controller->charge();
check($controller->output->response['success']===true && !isset(State::$payload['billing_address']),'Existing clients without address remain compatible');
State::$payload=null;State::$post=array_replace($base,['invoice_hash'=>'wrong']);$controller=new Smart_square;$controller->charge();
check($controller->output->response['success']===false && State::$payload===null,'Invoice authorization remains enforced');
echo "Square billing payload, amount integrity, validation, legacy, and authorization checks passed.\n";
