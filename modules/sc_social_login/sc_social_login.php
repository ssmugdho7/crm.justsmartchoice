<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Social Sign-In
Description: Licensed Google, Facebook and Apple sign-in for staff and clients.
Version: 1.0.0
Requires at least: 3.4.1
Author: Smart Choice Contractors USA
*/
require_once __DIR__ . '/helpers/sc_social_login_helper.php';
register_language_files('sc_social_login',['sc_social_login']);
register_activation_hook('sc_social_login','scsl_install');
function scsl_install()
{
    if (!scsl_license()) { show_error(_l('scsl_license_required'),403); exit; }
    require __DIR__ . '/install.php';
}
hooks()->add_action('admin_init', function () {
    $caps=[];
    foreach (['view_own','view','create','edit','delete','manage_settings'] as $cap) { $caps[$cap]=_l('scsl_permission_' . $cap); }
    register_staff_capabilities('sc_social_login',['capabilities'=>$caps],_l('scsl_title'));
    if (is_staff_logged_in()) {
        get_instance()->app_menu->add_setup_menu_item('sc-social-login', [
            'name'=>_l('scsl_title'),'href'=>admin_url('sc_social_login'),'position'=>61]);
    }
});
hooks()->add_action('after_admin_login_form_start', function () { scsl_confirmation('staff'); });
hooks()->add_action('clients_login_form_start', function () { scsl_confirmation('client'); });
hooks()->add_action('before_admin_login_form_close', function () { scsl_buttons('staff'); });
hooks()->add_action('clients_login_form_end', function () { scsl_buttons('client'); });
hooks()->add_action('clients_register_form_start', function () { scsl_buttons('client', true); });
hooks()->add_action('after_staff_login', function () { scsl_attach_after_login('staff',get_staff_user_id()); });
hooks()->add_action('after_contact_login', function () { scsl_attach_after_login('client',get_contact_user_id()); });
hooks()->add_action('after_client_register', function ($clientid) {
    $CI=&get_instance();
    $pending=$CI->session->userdata('scsl_pending');
    if (is_array($pending) && get_option('allow_registration') === '1' && get_option('scsl_client_registration') === '1') {
        $contact=$CI->db->where('userid',(int)$clientid)->where('email',$pending['email'] ?? '')
            ->get(db_prefix().'contacts')->row();
        if ($contact) { scsl_attach_after_login('client',$contact->id); }
    }
});
hooks()->add_action('app_customers_footer', function () {
    $CI=&get_instance();
    $pending=$CI->session->userdata('scsl_pending');
    if ($CI->router->fetch_class() === 'authentication' && $CI->router->fetch_method() === 'register'
        && is_array($pending) && ($pending['audience'] ?? '') === 'client' && ($pending['expires'] ?? 0) > time()) {
        $CI->load->view('sc_social_login/prefill',['email'=>$pending['email'] ?? '']);
    }
});
hooks()->add_action('customers_navigation_end', function () {
    if (is_client_logged_in() && scsl_license()) {
        echo '<li><a href="' . site_url('sc_social_login/account') . '">' . _l('scsl_connections') . '</a></li>';
    }
});
hooks()->add_filter('before_create_contact', function ($data) {
    $profile = defined('CONTACT_REGISTERING') ? scsl_registration_profile() : null;
    if ($profile && strcasecmp($profile['email'], $data['email'] ?? '') === 0) {
        // Google has verified this exact address; never create a usable empty password.
        $data['password'] = null;
        $data['email_verified_at'] = date('Y-m-d H:i:s');
        $data['email_verification_key'] = null;
    }
    return $data;
});
hooks()->add_action('after_client_profile_password_form_loaded', function () {
    if (is_client_logged_in() && scsl_license()) {
        echo '<p><a class="btn btn-default" href="' . site_url('sc_social_login/account') . '">' . _l('scsl_connections') . '</a></p>';
    }
});
