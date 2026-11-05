<?php
define('BASEPATH',true);
require __DIR__.'/../modules/appointly/helpers/appointly_helper.php';
$cards=appointly_dispatch_cards([
 ['date'=>'2026-11-05','status'=>'pending'],['date'=>'2026-11-05','status'=>'cancelled'],
 ['date'=>'2026-11-08','status'=>'in-progress'],['date'=>'2026-11-09','status'=>'pending'],
 ['date'=>'2026-11-01','status'=>'completed'],['date'=>'2026-10-31','status'=>'completed'],
 ['date'=>'2026-11-06','status'=>'completed'],['date'=>'2026-11-05','status'=>'no-show'],
],'2026-11-05');
if($cards!==['today'=>1,'week'=>2,'pending'=>2,'completed'=>1])throw new RuntimeException('Actual dispatch KPI date/status boundaries failed');
$table=file_get_contents(__DIR__.'/../modules/appointly/views/tables/index.php');
if(strpos($table,'btn btn-danger btn-xs')!==false)throw new RuntimeException('Duplicate red delete reintroduced');
if(strpos($table,'service_duration')===false || strpos($table,'html_escape($aRow[\'service_name\']')===false)throw new RuntimeException('Actual service duration or escaped label missing');
echo "PASS four actual appointment KPI boundaries, single delete presentation and escaped service durations\n";
