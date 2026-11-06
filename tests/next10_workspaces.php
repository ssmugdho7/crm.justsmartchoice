<?php
define('BASEPATH',__DIR__);
function hooks(){return new class{function add_filter(...$x){}function add_action(...$x){}};}function is_staff_logged_in(){return $GLOBALS['logged'];}function is_admin(){return false;}function has_permission(...$x){return $GLOBALS['allowed'];}function &get_instance(){return $GLOBALS['ci'];}
$ci=(object)['router'=>new class{public $name='recruitment';function fetch_class(){return $this->name;}}];$logged=true;$allowed=true;
require __DIR__.'/../modules/sc_admin_fixes/includes/workspaces.php';
function valid_ws($ok,$message){if(!$ok)throw new RuntimeException($message);}
valid_ws(in_array('scaf-workspace-recruitment',scaf_workspace_body(['native']),true),'Authorized recruitment styles');$allowed=false;valid_ws(scaf_workspace_body(['native'])===['native'],'Denied recruitment unchanged');$allowed=true;$logged=false;valid_ws(!scaf_workspace_context(),'No public candidate portal injection');$logged=true;$ci->router->name='clients';valid_ws(!scaf_workspace_context(),'Other domains untouched');
echo "PASS workspace authorization/module context and nativebody preservation\n";
