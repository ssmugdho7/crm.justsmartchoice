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
function scsl_buttons($audience, $registration = false)
{
    if (!is_https() || !scsl_license()) { return; }
    $CI = &get_instance();
    if ($registration) {
        if ($audience !== 'client' || get_option('allow_registration') !== '1'
            || get_option('scsl_client_registration') !== '1') { return; }
        $pending = $CI->session->userdata('scsl_pending');
        if (is_array($pending) && ($pending['audience'] ?? '') === 'client'
            && ($pending['expires'] ?? 0) > time()) { return; }
    }
    $providers = array_values(array_filter(['google', 'facebook', 'apple'], function ($provider) use ($audience) {
        return scsl_ready($provider, $audience);
    }));
    if (!$providers) { return; }
    $CI->load->view('sc_social_login/buttons', [
        'audience'=>$audience, 'providers'=>$providers, 'registration'=>$registration,
    ]);
}
function scsl_attach_after_login($audience, $id)
{
    if (!scsl_license()) { return; }
    $CI = &get_instance();
    $CI->load->model('sc_social_login/sc_identity_model');
    $CI->sc_identity_model->attachPending($audience, (int) $id);
}
