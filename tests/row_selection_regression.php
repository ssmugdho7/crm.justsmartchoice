<?php
define('BASEPATH',true);
class SelectionHooks {public $filters=[];function add_filter($name,$callback,...$args){$this->filters[$name][]=$callback;} function add_action(...$args){}}
function hooks(){static $h;return $h??=new SelectionHooks;}
function e($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}
function staff_can(...$args){return $GLOBALS['allow'];}function staff_cant(...$args){return !staff_can();}function db_prefix(){return 'tbl';}
require dirname(__DIR__).'/application/helpers/smart_choice_bulk_helper.php';
require dirname(__DIR__).'/modules/sc_admin_fixes/includes/projects.php';
function ck($ok,$why){if(!$ok)throw new RuntimeException($why);}
foreach(sc_bulk_table_targets() as $feature)foreach([true,false] as $allow)foreach([true,false] as $native){
 $original=($native?'<input type="hidden" value="ignore"><div class="checkbox"><input id="native-7" type="checkbox" class="native-selector" value="7"><label for="native-7"></label></div>':'').'7 <a href="/record/7">Name &amp; details</a>';
 $row=[$original];foreach(hooks()->filters[$feature.'_table_row_data'] as $filter)$row=$filter($row,['id'=>7]);
 if($feature==='projects')$row=scaf_projects_row($row,['id'=>7]);
 $dom=new DOMDocument();@$dom->loadHTML($row[0]);$x=new DOMXPath($dom);
 ck($x->query('//input[@type="checkbox"]')->length===($native||$allow||$feature==='projects'?1:0),'One checkbox per record: '.$feature);
 ck($x->query('//input[contains(@class,"sc-bulk-row")]')->length===($allow?1:0),'Delete permission: '.$feature);
 if($native)ck($x->query('//input[@id="native-7"]')->length===1,'Preserve native ID');
 if($feature==='projects')ck($x->query('//input[contains(@class,"scaf-project-select")]')->length===1,'Shared project selector');
 ck(strpos($row[0],'7 <a href="/record/7">Name &amp; details</a>')!==false,'Preserve record HTML');
 // Repeated hooks must not add a second selector.
 foreach(hooks()->filters[$feature.'_table_row_data'] as $filter)$row=$filter($row,['id'=>7]);
 if($feature==='projects')$row=scaf_projects_row($row,['id'=>7]);
 ck(substr_count($row[0],'type="checkbox"')===($native||$allow||$feature==='projects'?1:0),'Idempotent selectors');
}
echo "PASS all six shared table targets: native and injected selection, permissions, repeated filters, project actions, labels and IDs preserved\n";
