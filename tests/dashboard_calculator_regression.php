<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('BASEPATH',__DIR__);define('APPPATH',dirname(__DIR__).'/application/');
function strafter($s,$prefix){return substr($s,strpos($s,$prefix)+strlen($prefix));}
function get_staff_user_id(){return 1;}
$layout=[];
function get_staff_meta($id,$key){return $key==='dashboard_widgets_order'?serialize($GLOBALS['layout']):'';}
function hooks(){return new class{function apply_filters($name,$widgets){return array_values(array_filter($widgets,static fn($w)=>in_array($w['path'],['admin/dashboard/widgets/smart_choice_calculator','admin/dashboard/widgets/todos','admin/dashboard/widgets/sc_staff_activity'],true)));}};}
function &get_instance(){return $GLOBALS['ci'];}
$ci=(object)['load'=>new class{function view($path,$data,$return){if(strpos($path,'calculator')!==false){ob_start();include APPPATH.'views/'.$path.'.php';return ob_get_clean();}return '<div id="'.(strpos($path,'sc_staff_activity')!==false?'widget-sc_staff_activity':'widget-todos').'" class="widget">Unchanged widget</div>';}}];
require APPPATH.'helpers/widgets_helper.php';
function render($container){ob_start();render_dashboard_widgets($container);return ob_get_clean();}
function check($ok,$why){if(!$ok)throw new RuntimeException($why);}
$html=render('right-4');$root=str_get_html($html)->find('#widget-smart-choice-calculator',0);
check($root!==null,'Default placement is the right column');
check(strpos($html,'id="widget-sc_staff_activity"')<strpos($html,'id="widget-smart-choice-calculator"'),'Calculator follows Active Staff by default');
check(count($root->find('script'))===1,'Calculator handler survives root-only renderer');
check(count($root->find('button'))===3,'Calculate Reset and Copy are included');
$layout=['middle-right-6'=>['widget-smart-choice-calculator'],'right-4'=>['widget-sc_staff_activity','widget-todos']];
check(strpos(render('middle-right-6'),'widget-smart-choice-calculator')===false,'Saved old calculator placement is removed');
$right=render('right-4');
check(strpos($right,'widget-sc_staff_activity')<strpos($right,'widget-smart-choice-calculator') && strpos($right,'widget-smart-choice-calculator')<strpos($right,'widget-todos'),'Calculator follows Active Staff without changing other widget order');
check(substr_count($right,'id="widget-smart-choice-calculator"')===1,'Saved layouts render calculator exactly once');
if(in_array('--fixture',$argv,true)){echo json_encode(['script'=>$root->find('script',0)->innertext]);}
else echo "PASS root-only calculator rendering, controls and default/saved placement below Active Staff\n";
