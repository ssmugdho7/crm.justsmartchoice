<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Popups extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('popup_model');
        $this->load->model('roles_model');
        $this->load->model('staff_model');
        $this->load->model('leads_model');
    }

    /* List all */
    public function index()
    {
        if (!has_permission('popups', '', 'view')) {
            access_denied('popups');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('perfex_popup', 'popups/table'));
        }
        $data['title']                 = _l('popups');
        
        $this->load->view('popups/index', $data);
    }

    public function create()
    {
        $CI = &get_instance();
        if (!has_permission('popups', '', 'create')) {
            access_denied('popups');
        }
        $templates = $this->popup_model->get_all_templates();
        $data['title']                 = _l('templates');
        $data['templates']                 = $templates;

        $this->load->view('popups/create', $data);
    }
   
    public function save()
    {
        if ($this->input->post()) {
            // validate
            $template_id = $this->input->post('template_id');
            $name = $this->input->post('name');
            
            if ($template_id && $name) {

                if (!has_permission('popups', '', 'create')) {
                    access_denied('popups');
                }
                // Get template ID content and style => load builder
                $template =  $this->popup_model->find_template($template_id);
                if (!$template) {
                    set_alert('danger', _l('template_id_and_name_required'));
                    redirect(admin_url('perfex_popup/popups/create'));
                }

                $template = perfexPopupReplaceVarContentStyle($template);
                $code = guidV4();
                $user = get_staff($this->session->userdata('tfa_staffid'));
                $data = [
                    'code' => $code,
                    'responsible' => $user->staffid,
                    'name' => $name,

                    'html' => $template->html,
                    'css' => $template->css,
                    'html_components' => $template->html_components,
                    'css_styles' => $template->css_styles,
        
                    'thank_you_html' => $template->thank_you_html,
                    'thank_you_css' => $template->thank_you_css,
                    'thank_you_html_components' => $template->thank_you_html_components,
                    'thank_you_css_styles' => $template->thank_you_css_styles,

                    'width' => $template->width,
                    'height' => $template->height,
                    'settings' => json_encode($this->popup_model->get_default_settings()),
                    'popup_key' => popup_string_generate(32),
                    'is_enabled' => true,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                

                $this->db->insert(db_prefix() . 'popups_popups', $data);
                
                $insert_id = $this->db->insert_id();

                if ($insert_id) {
                    
                    log_activity('New popup added [ID:' . $insert_id . ']');
                    set_alert('success', _l('added_successfully'));
                    redirect(admin_url('perfex_popup/popups/builder/'. $code. '/main-content'));
                }
            }

            set_alert('danger', _l('item_select_template_id_and_name_required'));
            redirect(admin_url('perfex_popup/popups/create'));

        }
      
    }
    public function delete($id)
    {
        if (!has_permission('popups', '', 'delete')) {
            access_denied('popups');
        }
        if (!$id) {
            redirect(admin_url('perfex_popup/popups/index'));
        }
        $response = $this->popup_model->delete_popup($id);
        if ($response == true) {
            set_alert('success', _l('deleted'));
        } else {
            set_alert('warning', _l('problem_deleting'));
        }
        redirect(admin_url('perfex_popup/popups/index'));
    }
    
    public function setting($code = '')
    {

        if ($this->input->post()) {
            if ($code != '') {

                if (!has_permission('popups', '', 'edit')) {
                    access_denied('popups');
                }
                $popup = $this->popup_model->get_with_code($code);
                
                if ($popup) {

                    $data = $this->input->post();
                    $success = $this->popup_model->update_popup($data, $popup);
                    
                    if ($success) {
                        set_alert('success', _l('updated_successfully'));
                    }
                    set_alert('warning', _l('problem_updating'));
                    redirect(admin_url('perfex_popup/popups/setting')."/".$code);  
                } 
                
            }

            show_404();
        }

        $this->db->where('code', $code);
        $popup = $this->db->get(db_prefix() . 'popups_popups')->row();

        if ($popup) {
            # code...
            $popup->settings = json_decode($popup->settings);
            $data['item']        = $popup;
            $title = strtoupper($popup->name);
            $data['title']                 = $title;
            $data['roles']    = $this->roles_model->get();
            $data['members'] = $this->staff_model->get('', [
                'active'       => 1,
                'is_not_staff' => 0,
            ]);

            $this->load->view('popups/setting', $data);

        }else show_404();
        
    }

    public function install_script($code)
    {
        if (has_permission('popups', '', 'view')) {
            
            $popup = $this->popup_model->get_with_code($code);
          
            $script_url = admin_url('perfex_popup/install')."?key=".$popup->popup_key;
            $html = '<script async src="'.$script_url.'"></script>';

            echo json_encode([
                'success'  => $html,
            ]);

        } else {
            echo json_encode([
                'success'  => false,
            ]);
        }
    }

    public function builder($code, $type = 'main-content', $template = "none"){

        $type_arr = array('main-content','thank-content');
        if (!in_array($type, $type_arr)) {
            show_404(); die;
        }
        
        $item = "";
        $images_url = getAllImagesContentMedia();
        if($template == "template"){
            $item = $this->popup_model->get_template_with_code($code);
            $item = perfexPopupReplaceVarContentStyle($item);
        
        }
        else{
            $item = $this->popup_model->get_with_code($code);
        }

        if(!$item) {
            show_404(); die;
        }

        $all_fonts = array_merge(PERFEX_POPUP_CONFIG['google_fonts'],PERFEX_POPUP_CONFIG['all_fonts']);
        $all_icons = PERFEX_POPUP_CONFIG['all_icons'];
        $all_templates = $this->popup_model->get_all_templates();
        $data['title']                 = _l('builder'). " ".$item->name;
        $data['item']                 = $item;
        $data['all_templates']                 = $all_templates;
        $data['all_icons']                 = json_encode($all_icons, JSON_HEX_QUOT | JSON_HEX_TAG);
        $data['all_fonts']                 = json_encode($all_fonts, JSON_HEX_QUOT | JSON_HEX_TAG);
        $data['images_url'] = json_encode($images_url, JSON_HEX_QUOT | JSON_HEX_TAG);

        $this->load->view('popups/builder', $data);
    }

    
    public function loadbuilder($code, $type = 'main-content', $template = "none"){
        
        $type_arr = array('main-content','thank-content');
        
        if (!in_array($type, $type_arr)) {
            header('Content-Type: application/json');
            echo json_encode(['error'=>_l('not_found_type')]); die;
        }
        if ($code) {
            $item = "";
            if($template == "template") {
                $item = $this->popup_model->get_template_with_code($code);
                $item = perfexPopupReplaceVarContentStyle($item);
            }
            else $item = $this->popup_model->get_with_code($code);
            
            if($item){

                if ($type == 'thank-content') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'gjs-html'=>$item->thank_you_html, 
                        'gjs-css' => $item->thank_you_css,
                        'gjs-components' => json_decode($item->thank_you_html_components),
                        'gjs-styles' => json_decode($item->thank_you_css_styles),
                    ]);
                    die;
                }

                header('Content-Type: application/json');
                echo json_encode([
                    'gjs-html'=>$item->html, 
                    'gjs-css' => $item->css,
                    'gjs-components' => json_decode($item->html_components),
                    'gjs-styles' => json_decode($item->css_styles),
                ]);
                die;
            }
            
        }
        show_404(); die;
    }

    public function updatebuilder($code, $type = 'main-content', $template = "none")
    {
      
        $type_arr = array('main-content','thank-content');
    
        if (!in_array($type, $type_arr)) {
            header('Content-Type: application/json');
            echo json_encode(['error'=>_l('not_found_type')]); die;
        }

        if ($code) {
            $item = "";
            if($template == "template") {
                $item = $this->popup_model->get_template_with_code($code);
            }
            else $item = $this->popup_model->get_with_code($code);
            $data = [];
            if ($item) {
                if ($type == 'thank-content') {
                    $data['thank_you_html_components'] = $this->input->post('gjs-components');
                    $data['thank_you_css_styles'] = $this->input->post('gjs-styles');
                    $data['thank_you_html'] = $this->input->post('gjs-html');
                    $data['thank_you_css'] = $this->input->post('gjs-css');
                }
                else{
                    $data['html_components'] = $this->input->post('gjs-components');
                    $data['css_styles'] = $this->input->post('gjs-styles');
                    $data['html'] = $this->input->post('gjs-html');
                    $data['css'] = $this->input->post('gjs-css');
                }
                $table_update = 'popups_popups';
                if($template == "template"){
                    // convert image_url to ##image_url##
                    $data = convertLinkToVarContentStyle($data);
                    $table_update = 'popups_templates';
                }
                // update
                $this->db->where('code', $code);
                $this->db->update(db_prefix() . $table_update, $data);
                header('Content-Type: application/json');
                echo json_encode(['success'=> _l('updated_successfully')]); die;
            }
                
        }
        header('Content-Type: application/json');
        echo json_encode(['error'=> _l('updated_failed')]); die;
    }
    public function uploadimage(){
        
        $config['upload_path'] = FCPATH.PERFEX_POPUP_UPLOAD_PATH."/content_media";
        $config['allowed_types'] = 'gif|jpg|png|svg|jpeg';
        $config['max_size'] = 20000;
        $newname = time();
        $config['file_name'] = 'upload'.$newname;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('files')) {
            $error = array('error' => $this->upload->display_errors());

            header('Content-Type: application/json');
            echo json_encode( $error); die;
            
        } else {

            $data = $this->upload->data();

            $imagesURL = base_url(PERFEX_POPUP_UPLOAD_PATH.'/content_media')."/".$data['file_name'];
            
            header('Content-Type: application/json');
            echo json_encode($imagesURL); die;

        }

    }
    
    public function deleteimage(){
        
        $file_path = FCPATH.PERFEX_POPUP_UPLOAD_PATH."/content_media";

        $input=$this->input->post();
        $link_array = explode('/',$input['image_src']);
        $image_name = end($link_array);

        $path = $file_path."/".$image_name;

        if (file_exists($path)) {
            unlink($path);
        }
        header('Content-Type: application/json');
        echo json_encode($image_name); die;

    }
    
    public function searchicon(){
        
        if ($this->input->post()) {

            $response = "";
            $keyword = $this->input->post('keyword');

            $app_icons = PERFEX_POPUP_CONFIG['all_icons'];
            $data = json_decode($app_icons->value);

            if ($keyword) {

                $input = preg_quote($keyword, '~'); 
                $result = preg_grep('~' . $input . '~', $data);
                
                foreach ($result as $key => $value) {
                    # code...
                    $response.= '<i class="'.$value.'"></i>';
                }
                header('Content-Type: application/json');
                echo json_encode(['result'=> $response]); die;


            }
            else{

                foreach ($data as $key => $value) {
                    # code...
                    $response.= '<i class="'.$value.'"></i>';
                }
                header('Content-Type: application/json');
                echo json_encode(['result'=> $response]); die;

            }
        }

    }
    public function resize($code, $template = "none")
    {
        $item = "";
        if($template == "template") {
            $item = $this->popup_model->get_template_with_code($code);
        }
        else $item = $this->popup_model->get_with_code($code);

        if ($item) {

            $data = [
                'width' => $this->input->post('width'),
                'height' => $this->input->post('height'),
            ];
            
            $table = "popups_popups";
            if($template == "template"){
                $table = "popups_templates";
            }
            // update
            $this->db->where('code', $code);
            $this->db->update(db_prefix() . $table, $data);

            header('Content-Type: application/json');
            echo json_encode(['success'=> _l('updated_successfully'), 'data' => $data]); die;

           
        }
        header('Content-Type: application/json');
        echo json_encode(['error'=> _l('not_found')]); die;
    }
    public function loadtemplate($code, $type = 'main-content')
    {
        $item = $this->popup_model->get_template_with_code($code);
        $item = perfexPopupReplaceVarContentStyle($item);

        if ($item) {
            if ($type == 'thank-content') {
                header('Content-Type: application/json');
                echo json_encode([
                    'content'=>$item->thank_you_html, 
                    'style' => $item->thank_you_css,
                ]);
                die;
            }

            header('Content-Type: application/json');
            echo json_encode([
                'content'=>$item->html, 
                'style' => $item->css,
            ]);
            die;
        }
        header('Content-Type: application/json');
        echo json_encode(['error'=> _l('not_found')]); die;

    }
     /* List subscribers */
     public function subscribers()
     {
         if (!has_permission('popups-subscribers', '', 'view')) {
             access_denied('popups-subscribers');
         }
         if ($this->input->is_ajax_request()) {
             $this->app->get_table_data(module_views_path('perfex_popup', 'subscribers/table'));
         }
         $popups         = $this->popup_model->get_all_popups();
         $data['popups'] = $popups;
         $data['title']                 = _l('subscribers');
         
         $this->load->view('subscribers/index', $data);
    }
    public function delete_subscriber($id)
    {
        if (!has_permission('popups-subscribers', '', 'delete')) {
            access_denied('popups-subscribers');
        }
        if (!$id) {
            redirect(admin_url('perfex_popup/popups/subscribers'));
        }
        $response = $this->popup_model->delete_subscriber($id);
        if ($response == true) {
            set_alert('success', _l('deleted'));
        } else {
            set_alert('warning', _l('problem_deleting'));
        }
        redirect(admin_url('perfex_popup/popups/subscribers'));
    }

    // button convert to lead
    public function get_convert_data_to_lead($id)
    {
        if (!has_permission('popups-subscribers', '', 'edit')) {
            ajax_access_denied();
        }
        $data['members'] = $this->staff_model->get('', [
            'active'       => 1,
            'is_not_staff' => 0,
        ]);
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $data['item'] = $this->popup_model->get_subscriber($id);
        $this->load->view('subscribers/convert_data_to_lead', $data);
    }

    public function convert_to_lead()
    {
        if (!has_permission('popups-subscribers', '', 'edit')) {
            access_denied('Popups subscribers convert to leads');

        }
        if ($this->input->post()) {
            $data             = $this->input->post();
            $formdata_id = $data['formdata_id'];
            unset($data['formdata_id']);

            $insert_id      = $this->popup_model->add_convert_to_lead($data);
            $message = $insert_id ? _l('added_successfully', _l('lead')) : '';

            if ($insert_id) {
               
                handle_tags_save($tags, $insert_id, 'lead');

                if (isset($custom_fields)) {
                    handle_custom_fields_post($insert_id, $custom_fields);
                }
                
                if (is_gdpr() && get_option('gdpr_after_lead_converted_delete') == '1') {
                    // When lead is deleted
                    $this->popup_model->delete_subscriber($formdata_id);
                }

                $this->leads_model->lead_assigned_member_notification($insert_id, $data['assigned']);
                hooks()->do_action('lead_created', $insert_id);
                log_activity('Created Lead [Popups subscriber: ' . $formdata_id . ', Lead: ' . $insert_id . ']');
                
                set_alert('success', _l('convert_data_to_lead_success'));
                redirect(admin_url('leads'));
                
            }

        }
    }
    // button convert to customer
    public function get_convert_data_to_customer($id)
    {
        if (!has_permission('popups-subscribers', '', 'edit')) {
            ajax_access_denied();
        }
        if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
            $this->load->model('gdpr_model');
            $data['purposes'] = $this->gdpr_model->get_consent_purposes($id, 'lead');
        }
        $data['item'] = $this->popup_model->get_subscriber($id);
        $this->load->view('subscribers/convert_data_to_customer', $data);
    }

    public function convert_to_customer()
    {
        if (!has_permission('popups-subscribers', '', 'edit')) {
            access_denied();
        }

        if ($this->input->post()) {
            $default_country  = get_option('customer_default_country');
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            $formdata_id = $data['formdata_id'];
            $original_lead_email = $data['original_lead_email'];
            unset($data['original_lead_email']);
            unset($data['formdata_id']);

            

            if ($data['country'] == '' && $default_country != '') {
                $data['country'] = $default_country;
            }

            $data['billing_street']  = $data['address'];
            $data['billing_city']    = $data['city'];
            $data['billing_state']   = $data['state'];
            $data['billing_zip']     = $data['zip'];
            $data['billing_country'] = $data['country'];

            $data['is_primary'] = 1;
            $id                 = $this->clients_model->add($data, true);
            if ($id) {
                $primary_contact_id = get_primary_contact_user_id($id);

                if (!has_permission('customers', '', 'view') && get_option('auto_assign_customer_admin_after_lead_convert') == 1) {
                    $this->db->insert(db_prefix() . 'customer_admins', [
                        'date_assigned' => date('Y-m-d H:i:s'),
                        'customer_id'   => $id,
                        'staff_id'      => get_staff_user_id(),
                    ]);
                }

                set_alert('success', _l('convert_data_to_customer_success'));

                if (is_gdpr() && get_option('gdpr_after_lead_converted_delete') == '1') {
                    // When lead is deleted
                    $this->popup_model->delete_subscriber($formdata_id);
                }
                log_activity('Created Client [Popups subscriber: ' . $formdata_id . ', ClientID: ' . $id . ']');
                redirect(admin_url('clients/client/' . $id));
            }
        }
    }
    
    
}
