<?php
defined('BASEPATH') or exit('No direct script access allowed');
function scsl_can($capability)
{
    return is_staff_logged_in() && (is_admin() || has_permission('sc_social_login','',$capability));
}
function scsl_ready($provider, $audience)
{
    if (!in_array($provider,['google','facebook','apple'],true) || !in_array($audience,['staff','client'],true)
        || get_option('scsl_' . $provider . '_' . $audience) !== '1'
        || !get_option('scsl_' . $provider . '_client_id') || !get_option('scsl_' . $provider . '_client_secret')) { return false; }
    return true;
}
function scsl_license()
{
    $CI = &get_instance();
    if (!$CI->app_modules->is_active('sc_module_licensing')) { return false; }
    require_once FCPATH . 'modules/sc_module_licensing/helpers/sc_module_licensing_helper.php';
    return scl_licensed('sc_social_login');
}
function scsl_buttons($audience)
{
    if (!is_https() || !scsl_license()) { return; }
    $CI = &get_instance();
    $CI->load->view('sc_social_login/buttons', ['audience'=>$audience]);
}
function scsl_attach_after_login($audience, $id)
{
    if (!scsl_license()) { return; }
    $CI = &get_instance();
    $CI->load->model('sc_social_login/sc_identity_model');
    $CI->sc_identity_model->attachPending($audience, (int) $id);
}
