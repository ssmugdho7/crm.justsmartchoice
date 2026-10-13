<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Oauth extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        require_once dirname(__DIR__).'/helpers/sc_social_login_helper.php';
        require_once dirname(__DIR__).'/libraries/Sc_oauth.php';
        load_admin_language();
        if (!$this->app_modules->is_active('sc_social_login') || !scsl_license()) { show_404(); exit; }
        $this->load->model('sc_social_login/sc_identity_model');
        $this->load->library('encryption');
        $this->output->set_header('Cache-Control: no-store')->set_header('Referrer-Policy: no-referrer');
    }
    private function settings($provider,$audience)
    {
        if (!is_https() || !scsl_ready($provider,$audience)) { throw new RuntimeException('scsl_unconfigured'); }
        $secret=$this->encryption->decrypt(get_option('scsl_'.$provider.'_client_secret'));
        if (!$secret) { throw new RuntimeException('scsl_unconfigured'); }
        return ['client_id'=>get_option('scsl_'.$provider.'_client_id'),'client_secret'=>$secret,
            'graph_version'=>get_option('scsl_facebook_graph_version')];
    }
    public function start($provider,$audience,$intent='login')
    {
        try {
            $settings=$this->settings($provider,$audience);
            if (!in_array($intent,['login','link'],true)) { throw new RuntimeException('scsl_failed'); }
            $actor=$audience==='staff'?get_staff_user_id():get_contact_user_id();
            $logged=$audience==='staff'?is_staff_logged_in():is_client_logged_in();
            if ($intent==='link' && ($this->input->method()!=='post' || !$logged || ($audience==='staff' && !scsl_can('create')))) {
                throw new RuntimeException('scsl_denied');
            }
            if ($intent==='login' && (is_staff_logged_in() || is_client_logged_in())) { throw new RuntimeException('scsl_denied'); }
            $browser=$this->input->cookie('scsl_browser',false);
            if (!is_string($browser) || !preg_match('/^[a-f0-9]{64}$/D',$browser)) { $browser=bin2hex(random_bytes(32)); }
            $path=parse_url(site_url('sc_social_login'),PHP_URL_PATH);
            setcookie('scsl_browser',$browser,['expires'=>time()+600,'path'=>$path,'secure'=>true,'httponly'=>true,'samesite'=>'None']);
            $tx=$this->sc_identity_model->transaction($provider,$audience,$intent,$intent==='link'?(int)$actor:0,$browser);
            $oauth=new Sc_oauth($settings);
            redirect($oauth->authorization($provider,$tx,site_url('sc_social_login/oauth/callback/'.$provider)));
        } catch (Throwable $e) { $this->failure($audience); }
    }
    public function callback($provider)
    {
        $audience='client';
        try {
            if (!in_array($provider,['google','facebook','apple'],true)
                || ($provider==='apple' && $this->input->method()!=='post')
                || ($provider!=='apple' && $this->input->method()!=='get')) { throw new RuntimeException('scsl_failed'); }
            $state=$provider==='apple'?$this->input->post('state',false):$this->input->get('state',false);
            $tx=$this->sc_identity_model->consume($state,$this->input->cookie('scsl_browser',false),$provider);
            $audience=$tx['audience'];
            $code=$provider==='apple'?$this->input->post('code',false):$this->input->get('code',false);
            $oauth=new Sc_oauth($this->settings($provider,$audience));
            $profile=$oauth->exchange($provider,$code,$tx,site_url('sc_social_login/oauth/callback/'.$provider));
            if ($tx['intent']==='link') {
                $actor=$audience==='staff'?get_staff_user_id():get_contact_user_id();
                $logged=$audience==='staff'?is_staff_logged_in():is_client_logged_in();
                if (!$logged) {
                    // Apple form_post may omit the normal SameSite=Lax CRM session cookie.
                    // Require fresh native password/MFA proof of the original actor, never log them in here.
                    $this->pending($provider,$audience,$profile,(int)$tx['actor_id']);
                    $this->load->view('sc_social_login/finish',['audience'=>$audience,'can_register'=>false]); return;
                }
                if ((int)$actor!==(int)$tx['actor_id'] || ($audience==='staff' && !scsl_can('create'))
                    || !$this->sc_identity_model->attach($provider,$audience,$profile['subject'],(int)$actor)) { throw new RuntimeException('scsl_failed'); }
                set_alert('success',_l('scsl_connected'));
                redirect($audience==='staff'?admin_url('sc_social_login/connections'):site_url('sc_social_login/account'));
            }
            if (is_staff_logged_in() || is_client_logged_in()) { throw new RuntimeException('scsl_denied'); }
            $identity=$this->sc_identity_model->identity($provider,$audience,$profile['subject']);
            if ($identity) { redirect($this->sc_identity_model->login($audience,(int)$identity['user_id'])); }
            $email=is_string($profile['email']) && filter_var($profile['email'],FILTER_VALIDATE_EMAIL)?$profile['email']:'';
            $this->pending($provider,$audience,$profile);
            $this->load->view('sc_social_login/finish',['audience'=>$audience,
                'can_register'=>$audience==='client' && $email!=='' && get_option('allow_registration')==='1'
                    && get_option('scsl_client_registration')==='1']);
        } catch (Throwable $e) { $this->failure($audience); }
    }
    private function pending($provider,$audience,array $profile,$actor=0)
    {
        $email=is_string($profile['email']) && filter_var($profile['email'],FILTER_VALIDATE_EMAIL)?$profile['email']:'';
        $this->session->set_userdata('scsl_pending',['provider'=>$provider,'subject'=>$profile['subject'],
            'email'=>$email,'audience'=>$audience,'expires'=>time()+600,'actor_id'=>$actor,
            'client_id'=>get_option('scsl_'.$provider.'_client_id')]);
    }
    private function failure($audience)
    {
        $this->session->unset_userdata(['tfa_staffid','tfa_remember','_two_factor_auth_established','_two_factor_auth_staff_email']);
        set_alert('danger',_l('scsl_failed'));
        redirect($audience==='staff'?admin_url('authentication'):site_url('authentication/login'));
    }
}
