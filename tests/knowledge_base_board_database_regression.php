<?php
// Read-only comparison against the existing board queries; no database mutations.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$site=getenv('CRM_SITE_ROOT');
if(!$site || !is_file($site.'/application/config/app-config.php')) throw new RuntimeException('Set CRM_SITE_ROOT');
define('BASEPATH',dirname(__DIR__).'/system/');
require $site.'/application/config/app-config.php';
function db_prefix() { return defined('APP_DB_PREFIX')?APP_DB_PREFIX:'tbl'; }
class App_Model { public $db; function __construct() {} }
require dirname(__DIR__).'/application/models/Knowledge_base_model.php';
$db=new mysqli(APP_DB_HOSTNAME,APP_DB_USERNAME,APP_DB_PASSWORD,APP_DB_NAME);
$db->set_charset('utf8mb4');
$p=db_prefix();$baseline=[];$totals=[];
$groups=$db->query('SELECT groupid FROM '.$p.'knowledge_base_groups')->fetch_all(MYSQLI_ASSOC);
$start=microtime(true);
foreach($groups as $group) {
 $id=(int)$group['groupid'];
 $rows=$db->query('SELECT articleid,articlegroup,subject,slug,active,staff_article,(SELECT COUNT(*) FROM '.$p.'views_tracking WHERE rel_type="kb_article" AND rel_id=kb.articleid) total_views FROM '.$p.'knowledge_base kb WHERE articlegroup='.$id.' ORDER BY article_order')->fetch_all(MYSQLI_ASSOC);
 $baseline[$id]=$rows;$totals[$id]=count($rows);
}
$old=microtime(true)-$start;
$adapter=new class($db) {
 public $calls=0;private $db;function __construct($db){$this->db=$db;}
 function query($sql){$this->calls++;return new class($this->db->query($sql)){private $result;function __construct($result){$this->result=$result;}function result_array(){return $this->result->fetch_all(MYSQLI_ASSOC);}};}
};
$model=new Knowledge_base_model();$model->db=$adapter;
$start=microtime(true);$all=$model->get_admin_board(true);$new=microtime(true)-$start;
$active=$model->get_admin_board(false);
foreach($baseline as $group=>$rows) {
 // Equal article_order values have no defined relative order; compare by ID.
 $sort=static function(&$items){usort($items,static fn($a,$b)=>(int)$a['articleid']<=>(int)$b['articleid']);};
 $actual=$all['articles'][$group]??[];$sort($rows);$sort($actual);
 if($actual!==$rows || ($all['totals'][$group]??0)!==$totals[$group])throw new RuntimeException('Board contents or totals changed');
 $expected=array_values(array_filter($rows,static fn($r)=>(int)$r['active']===1));
 $actual=$active['articles'][$group]??[];$sort($actual);
 if($actual!==$expected)throw new RuntimeException('Visibility changed');
}
if($adapter->calls!==2)throw new RuntimeException('Expected one query per board');
echo 'PASS unchanged article metadata, view counts, group totals and active-only visibility'.PHP_EOL;
echo 'Existing board queries: '.round($old,3).'s; optimized board: '.round($new,3).'s'.PHP_EOL;
