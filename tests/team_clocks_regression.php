<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
$setting='';
function get_option($key) { if($key!=='sc_world_clocks')throw new RuntimeException('Unexpected setting');return $GLOBALS['setting']; }
function e($text) { return htmlspecialchars((string)$text,ENT_QUOTES,'UTF-8'); }
function check($ok,$label) { if(!$ok)throw new RuntimeException($label); }
require dirname(__DIR__).'/application/helpers/widgets_helper.php';
require dirname(__DIR__).'/application/third_party/simple_html_dom.php';
function renderClock() { ob_start();include dirname(__DIR__).'/application/views/admin/dashboard/widgets/smart_choice_clock.php';return ob_get_clean(); }
$configured=[['label'=>'India','zone'=>'Asia/Kolkata'],['label'=>'Pakistan','zone'=>'Asia/Karachi'],['label'=>'Philippines','zone'=>'Asia/Manila'],['label'=>'Colombia <team>','zone'=>'America/Bogota']];
$setting=json_encode($configured);
check(sc_get_team_clocks()===$configured,'Uses configured clocks and order');
// Reproduce the real dashboard renderer, which retains only firstChild().
$root=str_get_html(renderClock())->firstChild();
check($root->getAttribute('id')==='widget-smart-choice-world-clock','Preserves the widget root and dashboard identity');
check(count($root->find('.sc-core-clock'))===4,'Renders configured countries instead of three hardcoded cards');
check(count($root->find('script'))===1,'Clock initializer survives root-only dashboard rendering');
check(strpos($root->outertext,'Colombia &lt;team&gt;')!==false,'Clock labels are escaped');
check(strpos($root->outertext,'font-size:12px')!==false,'Smaller text is scoped to the clock widget');
$setting=json_encode([$configured[3]]);
check(count(str_get_html(renderClock())->find('.sc-core-clock'))===1,'Saved country removal is reflected in dashboard');
foreach(['','not-json','[]',json_encode([['label'=>'Bad','zone'=>'Invalid/Zone']])] as $setting) {
 check(count(sc_get_team_clocks())===5,'Missing or invalid configuration uses the same navbar defaults');
}
if(in_array('--fixture',$argv,true)) {
 $setting=json_encode($configured);$root=str_get_html(renderClock())->firstChild();
 echo json_encode(['script'=>$root->find('script',0)->innertext,'zones'=>array_map(static fn($c)=>$c['zone'],$configured)]);
} else { echo "PASS saved clock list, changes/removals, escaping, fallback, smaller text and retained initializer\n"; }
