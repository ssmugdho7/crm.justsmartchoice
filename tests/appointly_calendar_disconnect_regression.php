<?php
define('BASEPATH', __DIR__);define('APPPATH',dirname(__DIR__).'/application/');
chdir(dirname(__DIR__));
require 'modules/appointly/libraries/Googleplus.php';
$actor=7;
function get_staff_user_id(){global $actor;return $actor;}
function db_prefix(){return 'tbl';}
class DisconnectMemoryDb {
 public $rows=[7=>'synthetic7',8=>'synthetic8'],$where=[];
 function where($k,$v){$this->where[$k]=$v;return $this;}
 function delete($table){unset($this->rows[$this->where['staff_id']]);$this->where=[];return true;}
}
$ci=(object)['db'=>new DisconnectMemoryDb];
function &get_instance(){global $ci;return $ci;}
class DisconnectClient {public $allow=false,$seen=[];function revokeToken($v){$this->seen[]=$v;return $this->allow;}}
class DisconnectProbe extends Googleplus {public function __construct($client){$this->client=$client;}public function getTokenType($type){return 'synthetic'.get_staff_user_id();}}
$client=new DisconnectClient;$m=new DisconnectProbe($client);
if($m->revokeToken()!==false||count($ci->db->rows)!==2)throw new Exception('Failed provider revocation removed a connection');
$client->allow=true;if(!$m->revokeToken()||isset($ci->db->rows[7])||!isset($ci->db->rows[8]))throw new Exception('Disconnect affected wrong staff');
$actor=0;if($m->revokeToken()!==false||!isset($ci->db->rows[8]))throw new Exception('Anonymous revocation allowed');
echo "PASS disconnect revokes only the current grant, preserves other staff, and keeps rows on provider failure\n";
