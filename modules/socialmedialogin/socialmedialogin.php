<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
	Module Name: Social Media Login module
	Module URI: https://codecanyon.net/item/social-media-login-module-for-perfex-crm/32949433
	Description: Allow customers to register and log into Perfex CRM through their Google, Facebook, LinkedIn and Twitter account.
	Version: 1.0.0
	Requires at least: 2.3.*
	Author: Themesic Interactive
	Author URI: https://codecanyon.net/user/themesic/portfolio
*/

define('SOCIALMEDIALOGIN_MODULE', 'socialmedialogin');


require_once __DIR__.'/vendor/autoload.php';
modules\socialmedialogin\core\Apiinit::the_da_vinci_code(SOCIALMEDIALOGIN_MODULE);
modules\socialmedialogin\core\Apiinit::ease_of_mind(SOCIALMEDIALOGIN_MODULE);

hooks()->add_action('before_client_logout','google_session_logout');
hooks()->add_action('before_client_logout','facebook_session_logout');
hooks()->add_action('before_client_logout','linkedin_session_logout');
hooks()->add_action('before_client_logout','twitter_session_logout');

register_activation_hook(SOCIALMEDIALOGIN_MODULE, 'socialmedialogin_activation_hook');
register_deactivation_hook(SOCIALMEDIALOGIN_MODULE, 'socialmedialogin_deactivation_hook');

register_language_files(SOCIALMEDIALOGIN_MODULE, [SOCIALMEDIALOGIN_MODULE]);

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
hooks()->add_filter('module_socialmedialogin_action_links', 'module_socialmedialogin_action_links');

function module_socialmedialogin_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=socialmedialogin') . '">' . _l('social_login_menu_name') . '</a>';

    return $actions;
}

/*
 * Check if can have permissions then apply new tab in settings
 */
hooks()->add_action('admin_init', 'socialmedialogin_add_settings_tab');

/**
 * [socialmedialogin_add_settings_tab net menu item in setup->settings].
 *
 * @return void
 */
function socialmedialogin_add_settings_tab()
{
    $CI = &get_instance();
    // Updated to use the newer API: app->add_settings_section
    // Replaces deprecated app_tabs->add_settings_tab
    $CI->app->add_settings_section('socialmedialogin', [
        'name' => _l('social_login_menu_name'),
        'view' => 'socialmedialogin/settings',
        'position' => 36,
    ]);
}

if (!function_exists('google_session_logout')) {
function google_session_logout()
{
	redirect(site_url('socialmedialogin/google_logout'));
}
}

function facebook_session_logout()
{
	redirect(site_url('socialmedialogin/facebook_logout'));
}

function linkedin_session_logout()
{
	redirect(site_url('socialmedialogin/linkedin_logout'));
}

function twitter_session_logout()
{
	redirect(site_url('socialmedialogin/twitter_logout'));
}

function socialmedialogin_activation_hook()
{
	$curent_theme = FCPATH."/application/views/themes/".active_clients_theme()."/views/login.php";
    $my_login = FCPATH."/application/views/themes/".active_clients_theme()."/views/my_login.php";

    copy($curent_theme, $my_login);

    $filename = $my_login;
    $code_add = file_get_contents(__DIR__."/views/code.php");
    $string_to_replace = "<?php if(get_option('allow_registration') == 1) { ?>";
    $replace_with = "<?php if(get_option('allow_registration') == 1) { ?>\n".$code_add;
    $content = file_get_contents($filename);
    $content_chunks = explode($string_to_replace, $content);
    $content = implode($replace_with, $content_chunks);
    file_put_contents($filename, $content);
    $css_add = "<link href='<?php echo module_dir_url('socialmedialogin', 'assets/css/custom.css'); ?>' rel='stylesheet'>";
    file_put_contents($filename, $css_add . PHP_EOL, FILE_APPEND);

    $options = array(
        'google_key' => "",
        'google_id' => "",
        'google_btn_status' => "Inactive",
        'linkedin_key' => "",
        'linkedin_id' => "",
        'linkedin_btn_status' => "Inactive",
        'twitter_key' => "",
        'twitter_id' => "",
        'twitter_btn_status' => "Inactive",
        'facebook_key' => "",
        'facebook_id' => "",
        'facebook_btn_status' => "Inactive",
        'socialmedialogin_module_status' => "Inactive"
    );
    
    foreach ($options as $key => $value)
    {
        update_option($key, $value);
    }
}

function socialmedialogin_deactivation_hook()
{
	$File   = FCPATH.'/application/views/themes/perfex/views/my_login.php';
	if(file_exists($File))
	{
		unlink($File);
	}
	
}

hooks()->add_action('app_init', SOCIALMEDIALOGIN_MODULE.'_actLib');
function socialmedialogin_actLib()
{
    $CI = &get_instance();
    $CI->load->library(SOCIALMEDIALOGIN_MODULE.'/Socialmedialogin_aeiou');
    $envato_res = $CI->socialmedialogin_aeiou->validatePurchase(SOCIALMEDIALOGIN_MODULE);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', SOCIALMEDIALOGIN_MODULE.'_sidecheck');
function socialmedialogin_sidecheck($module_name)
{
    if (SOCIALMEDIALOGIN_MODULE == $module_name['system_name']) {
        modules\socialmedialogin\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', SOCIALMEDIALOGIN_MODULE.'_deregister');
function socialmedialogin_deregister($module_name)
{
    if (SOCIALMEDIALOGIN_MODULE == $module_name['system_name']) {
        delete_option(SOCIALMEDIALOGIN_MODULE.'_verification_id');
        delete_option(SOCIALMEDIALOGIN_MODULE.'_last_verification');
        delete_option(SOCIALMEDIALOGIN_MODULE.'_product_token');
        delete_option(SOCIALMEDIALOGIN_MODULE.'_heartbeat');
    }
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */
