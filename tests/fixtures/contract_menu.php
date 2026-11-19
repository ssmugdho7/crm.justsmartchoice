<?php
if(PHP_SAPI!=='cli')exit;
define('BASEPATH',true);$lang=[];require dirname(__DIR__,2).'/modules/sc_admin_fixes/language/english/sc_admin_fixes_lang.php';
class MenuHooksFixture{function add_action($name,$callback){}}function hooks(){return new MenuHooksFixture();}
function get_option($key){return 'draft';}function is_admin(){return true;}function staff_can($cap,$module){return true;}function _l($key){return $GLOBALS['lang'][$key]??$key;}function html_escape($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}function admin_url($p){return '/admin/'.$p;}
function form_open($url,$attrs){return '<form method="post" action="'.html_escape($url).'" class="'.html_escape($attrs['class']??'').'"><input type="hidden" name="csrf_token_name" value="fixture">';}function form_close(){return '</form>';}
require dirname(__DIR__,2).'/modules/sc_admin_fixes/includes/contract_workflows.php';
$contract=(object)['id'=>62,'project_id'=>null,'signed'=>0,'marked_as_signed'=>0];
require dirname(__DIR__,2).'/modules/sc_admin_fixes/views/contracts/workflow_actions.php';
