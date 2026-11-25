<?php
define('BASEPATH',__DIR__);class App_Model{public $db;}
function db_prefix(){return 'tbl';}
require __DIR__.'/../application/models/Staff_activity_model.php';
$m=(new ReflectionClass(Staff_activity_model::class))->newInstanceWithoutConstructor();
$m->db=new class{public $params;function table_exists($t){return true;}function query($q,$p){$this->params=$p;return $this;}function result_array(){return [['tracked_date'=>date('Y-m-d'),'seconds'=>120],['tracked_date'=>date('Y-m-d',strtotime('first day of last month')),'seconds'=>45]];}};
$r=$m->summary(45);if($r['total']!==165||$r['this_month']!==120||$r['last_month']!==45||$m->db->params!==[45])throw new Exception('Incorrect summary or staff scope');
echo "PASS staff-scoped task-independent active time totals and monthly boundaries\n";
