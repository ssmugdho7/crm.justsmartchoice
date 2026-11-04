<?php
define('BASEPATH',__DIR__);define('APPPATH',dirname(__DIR__).'/application/');
#[AllowDynamicProperties] class AdminController {}
$options=['ga_enabled'=>'0','ga_crm_measurement_id'=>'','ga_service_account_json'=>'synthetic-saved','ga_last_health_check'=>''];
function get_option($key){global $options;return $options[$key]??'';}
function update_option($key,$value){global $options;$options[$key]=$value;}
function is_admin(){return true;}
function &get_instance(){global $p;return $p;}
function _l($key){return $key;}
function init_head(){}
function init_tail(){}
function admin_url($path){return '/admin/'.$path;}
function html_escape($value){return htmlspecialchars((string)$value,ENT_QUOTES);}
require dirname(__DIR__).'/modules/google_analytics/helpers/google_analytics_helper.php';
require dirname(__DIR__).'/modules/google_analytics/controllers/Google_analytics.php';
class OptionalHealthProbe extends Google_analytics {
 function __construct(){
  $this->input=new class{public $test=false;function get($key){return $this->test?'1':null;}};
  $this->db=new class{function table_exists($table){return $table!=='tblga_audit_log';}};
  $this->google_analytics_model=new class{function get_access_token(){return 'synthetic';}};
  $this->load=new class{public $data;function view($view,$data){$this->data=$data;}};
 }
}
function db_prefix(){return 'tbl';}
$p=new OptionalHealthProbe;$p->health();if($p->load->data['checks']['measurement_id']!==null)throw new Exception('Disabled tracking incorrectly failed');
$options['ga_enabled']='1';$p->health();if($p->load->data['checks']['measurement_id']!==false)throw new Exception('Missing ID accepted with tracking enabled');
$options['ga_crm_measurement_id']='G-SYNTHETIC1';$p->health();if($p->load->data['checks']['measurement_id']!==true)throw new Exception('Valid ID rejected');
$p->input->test=true;$p->health();if(!$p->load->data['last_health_check']||$p->load->data['last_health_check']!==$options['ga_last_health_check'])throw new Exception('Health test timestamp stale');
$checks=['optional'=>null,'required'=>false,'working'=>true];$api_message=null;$last_health_check='2026-11-04 12:00:00';
ob_start();require dirname(__DIR__).'/modules/google_analytics/views/health.php';$html=ob_get_clean();
if(!str_contains($html,'N/A — CRM tracking disabled')||!str_contains($html,'>FAIL</span>')||!str_contains($html,'>PASS</span>')||!str_contains($html,$last_health_check))throw new Exception('Health view concealed failures or showed stale status');
echo "PASS disabled CRM tracking is N/A, enabled tracking still requires a valid ID, real failures remain visible, and test timestamp is current\n";
