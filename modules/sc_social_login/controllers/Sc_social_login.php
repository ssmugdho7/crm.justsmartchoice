<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sc_social_login extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        require_once dirname(__DIR__).'/helpers/sc_social_login_helper.php';
        if (!$this->app_modules->is_active('sc_social_login')) { show_404(); exit; }
        $this->load->model('sc_social_login/sc_identity_model');
        $this->output->set_header('Cache-Control: no-store');
    }
    public function index() { redirect(admin_url('sc_social_login/'.(scsl_can('view') || scsl_can('view_own') || scsl_can('create')?'connections':'settings'))); }
    public function connections()
    {
        if (!(scsl_can('view') || scsl_can('view_own') || scsl_can('create'))) { access_denied('sc_social_login'); }
        $global=scsl_can('view') && $this->input->get('scope')==='all';
        $this->page('connections',['global'=>$global,'identities'=>$this->sc_identity_model->rows('staff',get_staff_user_id(),$global,(int)$this->input->get('before'))]);
    }
    public function remove($id)
    {
        $disable=$this->input->post('action',true)==='disable';
        if ($this->input->method()!=='post' || !scsl_can($disable?'edit':'delete')) { access_denied('sc_social_login'); }
        try {
            $this->sc_identity_model->remove((int)$id,'staff',get_staff_user_id(),scsl_can('view'),$disable);
            set_alert('success',_l('scsl_saved'));
        } catch (Throwable $e) { set_alert('danger',_l('scsl_change_failed')); }
        redirect(admin_url('sc_social_login/connections'));
    }
    public function settings()
    {
        if (!scsl_can('manage_settings')) { access_denied('sc_social_login'); }
        $this->load->library('encryption');
        if ($this->input->method()==='post') {
            $values=[];
            try {
                foreach (['google','facebook','apple'] as $provider) {
                    $id=$this->input->post($provider.'_client_id',true);
                    $secret=$this->input->post($provider.'_client_secret',false);
                    if (!is_string($id) || strlen($id)>255 || !is_string($secret) || strlen($secret)>12000) { throw new RuntimeException(); }
                    $values['scsl_'.$provider.'_client_id']=trim($id);
                    if ($secret!=='') {
                        $encrypted=$this->encryption->encrypt($secret);
                        if (!$encrypted) { throw new RuntimeException(); }
                        $values['scsl_'.$provider.'_client_secret']=$encrypted;
                    }
                    foreach (['staff','client'] as $audience) { $values['scsl_'.$provider.'_'.$audience]=$this->input->post($provider.'_'.$audience)==='1'?'1':'0'; }
                }
                $version=$this->input->post('graph_version',true);
                if (!is_string($version) || !preg_match('/^v[0-9]{1,2}\.0$/D',$version)) { throw new RuntimeException(); }
                $values['scsl_facebook_graph_version']=$version;
                $values['scsl_client_registration']=$this->input->post('client_registration')==='1'?'1':'0';
                foreach ($values as $key=>$value) { update_option($key,$value); }
                set_alert('success',_l('scsl_saved'));
            } catch (Throwable $e) { set_alert('danger',_l('scsl_settings_failed')); }
            redirect(admin_url('sc_social_login/settings'));
        }
        $this->page('settings',[]);
    }
    public function help()
    {
        if (!(scsl_can('view') || scsl_can('view_own') || scsl_can('create') || scsl_can('manage_settings'))) { access_denied('sc_social_login'); }
        $this->page('help',[]);
    }
    private function page($page,array $data)
    {
        $this->load->view('sc_social_login/manage',array_merge($data,['page'=>$page,'title'=>_l('scsl_title'),'audience'=>'staff']));
    }
}
