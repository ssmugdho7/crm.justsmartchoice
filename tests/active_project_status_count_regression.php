<?php
define('BASEPATH',true);
$rows=array_fill(0,17,['status'=>2]);
function db_prefix(){return 'tbl';}
function total_rows($table,$where){global $rows;return count(array_filter($rows,fn($row)=>$row['status']===$where['status']));}
function _l($key){return $key;}
function admin_url($path=''){return '/admin/'.$path;}
function html_escape($v){return htmlspecialchars($v,ENT_QUOTES);}
function smartchoice_theme_asset_url($p){return '/'.$p;}
function renderCount(){ob_start();include __DIR__.'/../application/views/admin/dashboard/widgets/sc_active_projects.php';$html=ob_get_clean();preg_match('/aria-live="polite">(\d+)</',$html,$m);return (int)$m[1];}
if(renderCount()!==17)throw new Exception('Initial active count');
$rows[0]['status']=4;$rows[1]['status']=5;
if(renderCount()!==15)throw new Exception('Finished and cancelled must be excluded');
$rows[0]['status']=2;
if(renderCount()!==16)throw new Exception('Reopened project must return to count');
echo "PASS completed/cancelled exclusion and reopened-project inclusion\n";
