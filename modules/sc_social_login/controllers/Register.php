<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Register extends App_Controller
{
    public function index()
    {
        require_once dirname(__DIR__).'/helpers/sc_social_login_helper.php';
        load_admin_language();
        $this->output->set_header('Cache-Control: no-store');
        if (is_staff_logged_in() || is_client_logged_in()) { redirect(admin_url()); return; }
        $profile=scsl_registration_profile('staff');
        if (!$profile) { redirect(admin_url('authentication')); return; }
        $this->load->model('sc_social_login/sc_identity_model');
        if ($this->sc_identity_model->emailExists('staff',$profile['email'])) {
            redirect(admin_url('authentication')); return;
        }
        $this->load->library('form_validation');
        $this->form_validation->set_rules('firstname',_l('staff_add_edit_firstname'),'trim|required|max_length[50]');
        $this->form_validation->set_rules('lastname',_l('staff_add_edit_lastname'),'trim|required|max_length[50]');
        $terms=is_gdpr() && get_option('gdpr_enable_terms_and_conditions')==='1';
        if ($terms) { $this->form_validation->set_rules('accept_terms_and_conditions',_l('terms_and_conditions'),'required'); }
        if ($this->input->method()==='post' && $this->form_validation->run()) {
            try {
                $id=$this->sc_identity_model->registerStaff($profile,$this->input->post('firstname',true),$this->input->post('lastname',true));
                redirect($this->sc_identity_model->login('staff',$id)); return;
            } catch (Throwable $e) { set_alert('danger',_l('scsl_failed')); }
        }
        $this->load->view('sc_social_login/register_staff',['profile'=>$profile,'terms'=>$terms,'title'=>_l('scsl_staff_signup')]);
    }
}
