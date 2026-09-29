<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Dmn
Description: Module to draw Dmn Canvas
Version: 1.0.0
Author: Zonvoir
Author URI: https://zonvoir.com/
Requires at least: 2.3.*
*/
$CI = &get_instance();
define('DMN_MODULE_NAME', 'dmn');
hooks()->add_action('admin_init', 'dmn_admin_init_menu_items');
hooks()->add_action('admin_init', 'dmn_staff_permissions');
hooks()->add_action('app_customers_head', 'dmn_customer_project_tabs');
$CI->load->helper(DMN_MODULE_NAME . '/dmn');
function dmn_customer_project_tabs(){
	$CI = &get_instance();
	if ($CI->uri->segment(2) == 'project')
	{
		$project_id = $CI->uri->segment(3);?>
		<script type="text/javascript">
			$( document ).ready(function() {
				var node = '<li role="presentation" class="project_tab_dmn"><a data-group="project_dmn" href="<?php echo site_url('admin/dmn/dmn_client/project/'.$project_id);?>?group=project_dmn" role="tab"><i class="fa fa-code-fork" aria-hidden="true"></i> Dmn</a></li>';
				$('.nav-tabs').append(node);
			});
		</script>
	<?php }
	?>
	<?php
}
function dmn_staff_permissions()
{
	$capabilities = [];
	$capabilities['capabilities'] = [
		'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit'   => _l('permission_edit'),
		'delete' => _l('permission_delete'),
	];
	register_staff_capabilities('dmn', $capabilities, _l('dmn'));
}
register_activation_hook(DMN_MODULE_NAME, 'dmn_module_activation_hook');
function dmn_module_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}
register_uninstall_hook(DMN_MODULE_NAME, 'dmn_module_uninstall_hook');
function dmn_module_uninstall_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/uninstall.php');
}
register_language_files(DMN_MODULE_NAME, ['dmn']);
function dmn_admin_init_menu_items()
{
	$CI = &get_instance();
	$CI->app_menu->add_sidebar_menu_item('dmn_menu', [
		'name' => 'DMN',
		'href' => admin_url('dmn'),
		'position' => 10,
		'icon' => 'fa fa-code-fork ',
	]);
	$CI->app_tabs->add_project_tab('dmn', [
		'name'                      => _l('dmn'),
		'icon'                      => 'fa fa-code-fork',
		'view'                      => 'dmn/admin/project_dmn',
		'position'                  => 56,
	]);
}
?>