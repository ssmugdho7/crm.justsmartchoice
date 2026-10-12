<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('BASEPATH',__DIR__);define('APPPATH',dirname(__DIR__).'/application/');
function strafter($s,$prefix){return substr($s,strpos($s,$prefix)+strlen($prefix));}
function get_staff_user_id(){return 1;}
$layout=[];
function get_staff_meta($id,$key){return $key==='dashboard_widgets_order'?serialize($GLOBALS['layout']):'';}
function hooks(){return new class{function apply_filters($name,$widgets){return array_values(array_filter($widgets,static fn($w)=>in_array($w['path'],['admin/dashboard/widgets/smart_choice_calculator','admin/dashboard/widgets/todos'],true)));}};}
function &get_instance(){return $GLOBALS['ci'];}
$ci=(object)['load'=>new class{function view($path,$data,$return){if(strpos($path,'calculator')!==false){ob_start();include APPPATH.'views/'.$path.'.php';return ob_get_clean();}return '<div id="widget-todos" class="widget">Unchanged widget</div>';}}];
require APPPATH.'helpers/widgets_helper.php';
function render($container){ob_start();render_dashboard_widgets($container);return ob_get_clean();}
function check($ok,$why){if(!$ok)throw new RuntimeException($why);}
$html=render('middle-right-6');$root=str_get_html($html)->firstChild();
check($root->getAttribute('id')==='widget-smart-choice-calculator','Default placement is right-middle');
check(count($root->find('script'))===1,'Calculator handler survives root-only renderer');
check(count($root->find('button'))===3,'Calculate Reset and Copy are included');
$layout=['right-4'=>['widget-todos','widget-smart-choice-calculator']];
check(strpos(render('right-4'),'widget-smart-choice-calculator')===false,'Saved old calculator placement is removed');
check(strpos(render('right-4'),'widget-todos')!==false,'Other widget placement is preserved');
check(substr_count(render('middle-right-6'),'id="widget-smart-choice-calculator"')===1,'Saved layouts render calculator exactly once in right-middle');
if(in_array('--fixture',$argv,true)){echo json_encode(['script'=>$root->find('script',0)->innertext]);}
else echo "PASS root-only calculator rendering, controls and default/saved right-middle placement\n";
