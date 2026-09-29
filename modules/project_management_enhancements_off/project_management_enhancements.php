<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Project Management Enhancements
Description: Smart Choice Contractors USA project management enhancement layer for task comments, project task views, table cleanup, health checks, and Perfex CRM 3.4.x / PHP 8.5 compatibility. Updated by Smart Choice Contractors USA / Harold Cabrera.
Version: 1.2.0
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
*/

const PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME = 'project_management_enhancements';
const PROJECT_MANAGEMENT_ENHANCEMENTS_VERSION = '1.1.9';

$CI = &get_instance();
$CI->load->helper(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME . '/task_comment');

register_language_files(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME, ['project_management_enhancements']);

function pme_module_activation_hook()
{
    $CI = &get_instance();
    if (file_exists(__DIR__ . '/install.php')) {
        require_once(__DIR__ . '/install.php');
    }
    update_option('project_management_enhancements_version', PROJECT_MANAGEMENT_ENHANCEMENTS_VERSION);
}
register_activation_hook(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME, 'pme_module_activation_hook');

function pme_module_register_uninstall_hook()
{
    // Smart Choice standard: remove module settings only. Do not delete CRM projects/tasks.
    delete_option('project_management_enhancements_version');
    delete_option('pme_migrated_database');
}
register_uninstall_hook(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME, 'pme_module_register_uninstall_hook');

hooks()->add_action('admin_init', 'project_management_enhancements_admin_init');
hooks()->add_action('app_admin_head', 'project_management_enhancements_add_custom_styles');
hooks()->add_action('app_admin_footer', 'project_management_enhancements_add_custom_scripts');
hooks()->add_action('admin_init', 'pme_redirect_task_routes');

function project_management_enhancements_admin_init()
{
    $CI = &get_instance();

    if (is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('project-management-enhancements', [
            'name'     => 'Project Management',
            'href'     => admin_url('project_management_enhancements'),
            'position' => 31,
            'icon'     => 'fa fa-diagram-project',
        ]);

        $CI->app_menu->add_sidebar_children_item('project-management-enhancements', [
            'slug'     => 'project-management-enhancements-dashboard',
            'name'     => 'Dashboard',
            'href'     => admin_url('project_management_enhancements'),
            'position' => 1,
            'icon'     => 'fa fa-gauge',
        ]);

        $CI->app_menu->add_sidebar_children_item('project-management-enhancements', [
            'slug'     => 'project-management-enhancements-health',
            'name'     => 'Health',
            'href'     => admin_url('project_management_enhancements/health'),
            'position' => 2,
            'icon'     => 'fa fa-heart-pulse',
        ]);
    }
}

function pme_redirect_task_routes()
{
    // Smart Choice fix: never redirect Perfex native task modal/page routes.
    // Quick Create uses /admin/tasks/task and must stay in the native modal flow.
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/admin/tasks/task') !== false || strpos($uri, '/admin/tasks/get_task_data') !== false) {
        return;
    }
}

function project_management_enhancements_add_custom_styles()
{ ?>
    <style>
        .task-view-collapse{top:1rem;right:1rem}.task-view-collapse:not(.collapsed) i.fa-chevron-left{display:none}.task-view-collapse.collapsed i.fa-chevron-right{display:none}.task-single-col-right{display:none}@media(max-width:991px){.task-view-collapse{display:none}.task-single-col-right{display:block}}
        .tc-content.task-comment.active,.tc-content.task-parent-comment.active,.tc-content.task-child-comment.active{background-color:#fff8db}.task-child-comment{padding:5px 10px}
        .smart-choice-modules-table-fix td:first-child,.smart-choice-modules-table-fix th:first-child{width:26%!important;max-width:26%!important}.smart-choice-modules-table-fix td:nth-child(2),.smart-choice-modules-table-fix th:nth-child(2){width:52%!important;max-width:52%!important}.smart-choice-modules-table-fix .btn{min-width:84px;text-align:center}.smart-choice-setup-menu-fix #setup-menu,.smart-choice-setup-menu-fix .settings-group-tabs{min-height:100vh!important}
    </style>
<?php }

function project_management_enhancements_add_custom_scripts()
{ ?>
    <script>
    (function(){
        function fixTaskQuickCreate(){
            if (typeof window.init_task_modal !== 'undefined') { return; }
            $('body').off('click.smartChoiceTaskFix','a[href*="/admin/tasks/task"], .new-task').on('click.smartChoiceTaskFix','a[href*="/admin/tasks/task"], .new-task',function(e){
                var href=$(this).attr('href')||admin_url+'tasks/task';
                if(href.indexOf('/admin/tasks/task')===-1){return;}
                e.preventDefault();
                if(typeof requestGetJSON==='function'){
                    requestGetJSON(href).done(function(response){
                        if(response && response.html){
                            $('#task-modal').remove();
                            $('body').append(response.html);
                            $('#task-modal').modal('show');
                        } else { window.location.href=href; }
                    }).fail(function(){window.location.href=href;});
                }
            });
        }
        function fixModuleTable(){
            if(!/\/admin\/mods|\/admin\/modules/.test(window.location.pathname)){return;}
            $('table').addClass('smart-choice-modules-table-fix');
            $('td,th').each(function(){
                var html=$(this).html();
                if(!html){return;}
                html=html.replace(/★/g,'').replace(/\s*\|?\s*Help Guide\s*/g,' ');
                $(this).html(html);
            });
        }
        $(function(){fixTaskQuickCreate();fixModuleTable();setTimeout(fixModuleTable,500);});
    })();
    </script>
<?php }
