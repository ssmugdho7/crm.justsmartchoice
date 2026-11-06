<?php
defined('BASEPATH') or exit('No direct script access allowed');
function scaf_workspace_context()
{
    $CI = &get_instance();
    return is_staff_logged_in() && strtolower($CI->router->fetch_class()) === 'recruitment'
        && (is_admin() || has_permission('recruitment','','view'));
}
hooks()->add_filter('admin_body_class','scaf_workspace_body');
function scaf_workspace_body($classes)
{
    if(scaf_workspace_context())$classes[]='scaf-workspace-recruitment';
    return $classes;
}
hooks()->add_action('app_admin_head','scaf_workspace_assets');
function scaf_workspace_assets()
{
    if(!scaf_workspace_context())return;
    foreach(['css','js'] as $type){$path='assets/'.$type.'/workspaces.'.$type;$url=e(module_dir_url('sc_admin_fixes',$path).'?v='.filemtime(dirname(__DIR__).'/'.$path));echo $type==='css'?'<link rel="stylesheet" href="'.$url.'">':'<script defer src="'.$url.'"></script>';}
}
