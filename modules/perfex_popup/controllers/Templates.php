<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Templates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('popup_model');
    }

     /* List all templates */
    public function index()
    {
        if (!has_permission('popups-templates', '', 'view')) {
            access_denied('popups-templates');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('perfex_popup', 'templates/table'));
        }
        $data['title']                 = _l('admin_templates');

        $this->load->view('templates/index', $data);
    }

    public function previewtemplate($id)
    {
        if (!has_permission('popups-templates', '', 'view')) {
            access_denied('popups-templates');
        }
        $item = $this->popup_model->get_template($id);
        $item = perfexPopupReplaceVarContentStyle($item);

        $data['title']                 = _l('admin_popups');
        $data['item'] = $item;

        $this->load->view('templates/preview_template', $data);
    }

    public function framemainpage($id){
        
        if ($id) {
            $item = $this->popup_model->get_template($id);
            $data['item'] = $item;
            $this->load->view('templates/frame_main_page', $data);
            
        }
    }
    public function framethankyoupage($id){
        
        if ($id) {
            $item = $this->popup_model->get_template($id);
            $data['item'] = $item;
            $this->load->view('templates/frame_thank_you_page', $data);
            
        }
    }

    public function gettemplatejson($id)
    {
        $template = $this->popup_model->get_template($id);
        $template = perfexPopupReplaceVarContentStyle($template);
        $blocks_css = $this->popup_model->get_landing_page_setting('blockscss');
        $blockscss = perfexPopupReplaceVarContentStyle($blocks_css->value);
        header('Content-Type: application/json');
        echo json_encode([
            'blockscss'=>$blockscss, 
            'style' => $template->style,
            'content'=>$template->content, 
            'thank_you_page' => $template->thank_you_page,
        ]); die;
    }

    public function template($id = '')
    {
        if (!has_permission('popups-templates', '', 'view')) {
            if ($id != '' && !is_customer_admin($id)) {
                access_denied('popups-templates');
            }
        }

        if ($this->input->post()) {

            if ($id == '') {
                if (!has_permission('popups-templates', '', 'create')) {
                    access_denied('popups-templates');
                }

                $data = $_POST;
               
                $code = guidV4(); 
                $data['code'] = $code;
                $action = $data['action'];
                unset($data['action']);
                $id = $this->popup_model->add_template($data);
                
                if ($id) {
                    if($action=="save_and_builder"){ 
                        // redirect builder
                        redirect(admin_url('perfex_popup/popups/builder/'. $code."/main-content/template"));
                    }else{
                        set_alert('success', _l('added_successfully', _l('template')));
                        redirect(admin_url('perfex_popup/templates/template/' . $id));
                    }
                }
            } else {
                if (!has_permission('popups-templates', '', 'edit')) {
                    access_denied('popups-templates');
                }
                $item = $this->popup_model->get_template($id);
                $data = $_POST;
                $action = $data['action'];
                unset($data['action']);

                $success = $this->popup_model->update_template($data, $item);
                if ($success == true) {

                    if($action=="save_and_builder"){ 
                        // redirect builder
                        redirect(admin_url('perfex_popup/popups/builder/'. $item->code."/main-content/template"));
                    }else{
                        set_alert('success', _l('updated_successfully', _l('template')));
                        redirect(admin_url('perfex_popup/templates/template/' . $id));
                    }
                }
            }
        }

        if ($id == '') {
            $title = _l('add_new', _l('template'));
        } else {

            $item = $this->popup_model->get_template($id);
            $data['item'] = $item;
            $title = _l('edit', _l('template'));
        }

        $data['title']     = $title;

        $this->load->view('perfex_popup/templates/template', $data);
    }
   
    public function delete($id)
    {
        if (!has_permission('popups-templates', '', 'delete')) {
            access_denied('popups-templates');
        }
        if (!$id) {
            redirect(admin_url('perfex_popup/templates/index'));
        }
        $item = $this->popup_model->get_template($id);

        $response = $this->popup_model->delete_template($item);
        if ($response == true) {
            set_alert('success', _l('deleted'));
        } else {
            set_alert('warning', _l('problem_deleting'));
        }
        redirect(admin_url('perfex_popup/templates/index'));
    }
}

