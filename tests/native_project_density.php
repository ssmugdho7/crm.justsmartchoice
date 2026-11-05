<?php
require __DIR__.'/board_projects.php';
$scope=implode("\n",array_fill(0,50,'Day: <img src=x onerror=bad()> & prep'));
$html=render_project_scope_preview($scope,'<Project>');
check(strpos($html,'<img')===false,'Stored scope cannot execute HTML');
check(strpos($html,'data-scope-title="&lt;Project&gt;"')!==false,'Project title escaped');
check(substr_count($html,'Day:')===100,'Every scope line retained in label and data');
check(render_project_scope_preview('','Name')==='','Empty scope stays empty');
function staff_profile_image($id,...$args){return '<img src="staff'.$id.'">';}
$source=file_get_contents(dirname(__DIR__).'/application/views/admin/tables/projects.php');
$block=explode('$row[] = $membersOutput;',explode('$membersOutput =', $source,2)[1],2)[0];
function crew($names,$ids){global $block;$aRow=['members'=>$names,'members_ids'=>$ids];eval('$membersOutput ='.$block);return $membersOutput;}
$five=crew('Ana,Bob,Carol,David,Elena','1,2,3,4,5');
check(substr_count($five,'<img')===3 && strpos($five,'>+2</span>')!==false,'Native table caps crew at three with true overflow');
check(strpos($five,'Ana, Bob, Carol, David, Elena')!==false,'All export names retained');
check(strpos(crew('Ana','1'),'>+')===false,'No false overflow for small crew');
echo "PASS native scope escaping/full content, three-avatar limit and complete crew exports\n";
