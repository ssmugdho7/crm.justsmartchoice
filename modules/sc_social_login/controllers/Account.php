<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Account extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        require_once dirname(__DIR__).'/helpers/sc_social_login_helper.php';
        if (!$this->app_modules->is_active('sc_social_login')) { show_404(); exit; }
        if (!is_client_logged_in()) { redirect(site_url('authentication/login')); }
        $this->load->model('sc_social_login/sc_identity_model');
        $this->output->set_header('Cache-Control: no-store');
    }
    public function index()
    {
        $this->data(['audience'=>'client','identities'=>$this->sc_identity_model->rows('client',get_contact_user_id()),'title'=>_l('scsl_connections')]);
        $this->view('connections'); $this->layout();
    }
    public function remove($id)
    {
        if ($this->input->method()!=='post') { show_404(); return; }
        try {
            $this->sc_identity_model->remove((int)$id,'client',get_contact_user_id());
            set_alert('success',_l('scsl_saved'));
        } catch (Throwable $e) { set_alert('danger',_l('scsl_change_failed')); }
        redirect(site_url('sc_social_login/account'));
    }
}
