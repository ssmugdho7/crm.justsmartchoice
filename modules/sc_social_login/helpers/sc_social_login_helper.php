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
function scsl_pending_for($audience)
{
    $CI = &get_instance();
    $pending = $CI->session->userdata('scsl_pending');
    if (!is_array($pending) || ($pending['audience'] ?? '') !== $audience
        || ($pending['expires'] ?? 0) <= time()
        || !in_array($pending['provider'] ?? '', ['google','facebook','apple'], true)
        || ($pending['client_id'] ?? '') !== get_option('scsl_' . $pending['provider'] . '_client_id')) { return null; }
    return $pending;
}
function scsl_confirmation($audience)
{
    if (!is_https() || !scsl_license()) { return; }
    $pending = scsl_pending_for($audience);
    if (!$pending) { return; }
    get_instance()->load->view('sc_social_login/confirmation', [
        'pending'=>$pending, 'audience'=>$audience,
        'can_register'=>$audience==='client' && empty($pending['actor_id']) && !empty($pending['email'])
            && get_option('allow_registration')==='1' && get_option('scsl_client_registration')==='1',
    ]);
}
function scsl_registration_profile($audience = 'client')
{
    $CI = &get_instance();
    if (!in_array($audience, ['staff','client'], true)
        || !$CI->app_modules->is_active('sc_social_login') || !is_https() || !scsl_license()
        || !scsl_ready('google', $audience)
        || ($audience === 'client' && (get_option('allow_registration') !== '1'
            || get_option('scsl_client_registration') !== '1'))) { return null; }
    $pending = scsl_pending_for($audience);
    if (!$pending || $pending['provider'] !== 'google' || ($pending['verified'] ?? false) !== true
        || !empty($pending['actor_id']) || empty($pending['subject'])
        || !filter_var($pending['email'] ?? '', FILTER_VALIDATE_EMAIL)) { return null; }
    return $pending;
}
function scsl_buttons($audience, $registration = false)
{
    if (!is_https() || !scsl_license()) { return; }
    $CI = &get_instance();
    if (!$registration && scsl_pending_for($audience)) { return; }
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
function scsl_save_initial_password($audience)
{
    $CI = &get_instance();
    $logged = $audience==='staff' ? is_staff_logged_in() : is_client_logged_in();
    if (!$logged || $CI->input->method()!=='post') { show_404(); return; }
    $password = $CI->input->post('newpassword', false);
    $repeat = $CI->input->post('newpasswordr', false);
    $id = $audience==='staff' ? get_staff_user_id() : get_contact_user_id();
    $ok = is_string($password) && is_string($repeat) && hash_equals($password, $repeat)
        && $CI->sc_identity_model->setInitialPassword($audience, (int)$id, $password);
    set_alert($ok?'success':'danger', _l($ok?'scsl_password_saved':'scsl_password_failed'));
}
