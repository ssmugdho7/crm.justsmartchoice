<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Templates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('landingpage_model');
    }

     /* List all templates */
    public function index()
    {
        if (!has_permission('landingpages-templates', '', 'view')) {
            access_denied('landingpages-templates');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('zillapage', 'templates/table'));
        }
        $data['title']                 = _l('admin_templates');

        $this->load->view('templates/index', $data);
    }

    public function previewtemplate($id)
    {
        if (!has_permission('landingpages-templates', '', 'view')) {
            access_denied('landingpages-templates');
        }
        $item = $this->landingpage_model->get_template($id);
        $item = zillapageReplaceVarContentStyle($item);

        $data['title']                 = _l('admin_landing_pages');
        $data['item'] = $item;

        $this->load->view('templates/preview_template', $data);
    }

    public function framemainpage($id){
        
        if ($id) {
            $item = $this->landingpage_model->get_template($id);
            $data['item'] = $item;
            $this->load->view('templates/frame_main_page', $data);
            
        }
    }
    public function framethankyoupage($id){
        
        if ($id) {
            $item = $this->landingpage_model->get_template($id);
            $data['item'] = $item;
            $this->load->view('templates/frame_thank_you_page', $data);
            
        }
    }

    public function gettemplatejson($id)
    {
        $template = $this->landingpage_model->get_template($id);
        $template = zillapageReplaceVarContentStyle($template);
        $blocks_css = $this->landingpage_model->get_landing_page_setting('blockscss');
        $blockscss = zillapageReplaceVarContentStyle($blocks_css->value);
        $template->content = preg_replace('/<!--(.*?)-->/s', '', $template->content);
        $template->thank_you_page = preg_replace('/<!--(.*?)-->/s', '', $template->thank_you_page);
        header('Content-Type: application/json');
        echo json_encode([
            'blockscss'=>$blockscss, 
            'style' => $template->style,
            'content'=>$template->content, 
            'thank_you_page' => $template->thank_you_page,
        ]); die;
    }

    /* Edit client or add new client*/
    public function template($id = '')
    {
        if (!has_permission('landingpages-templates', '', 'view')) {
            if ($id != '' && !is_customer_admin($id)) {
                access_denied('landingpages-templates');
            }
        }

        if ($this->input->post()) {

            if ($id == '') {
                if (!has_permission('landingpages-templates', '', 'create')) {
                    access_denied('landingpages-templates');
                }

                $data = $this->input->post();
                $id = $this->landingpage_model->add_template($data);
                
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('template')));
                    redirect(admin_url('zillapage/templates/template/' . $id));
                }
            } else {
                if (!has_permission('landingpages-templates', '', 'edit')) {
                    access_denied('landingpages-templates');
                }
                $item = $this->landingpage_model->get_template($id);

                $success = $this->landingpage_model->update_template($this->input->post(), $item);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully', _l('client')));
                }
                redirect(admin_url('zillapage/templates/template/' . $id));
            }
        }

        if ($id == '') {
            $title = _l('add_new', _l('template'));
        } else {

            $data['item'] = $this->landingpage_model->get_template($id);

            $title = _l('edit', _l('template'));
        }

        $data['title']     = $title;

        $this->load->view('zillapage/templates/template', $data);
    }
   
    public function delete($id)
    {
        if (!has_permission('landingpages-templates', '', 'delete')) {
            access_denied('landingpages-templates');
        }
        if (!$id) {
            redirect(admin_url('zillapage/templates/index'));
        }
        $item = $this->landingpage_model->get_template($id);

        $response = $this->landingpage_model->delete_template($item);
        if ($response == true) {
            set_alert('success', _l('deleted'));
        } else {
            set_alert('warning', _l('problem_deleting'));
        }
        redirect(admin_url('zillapage/templates/index'));
    }
    public function sample_header()
    {
        if (!has_permission('landingpages-templates', '', 'view')) { access_denied('landingpages-templates'); }
        header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="zillapage_template_sample.csv"');
        $out=fopen('php://output','w'); fputcsv($out,['name','thumb','content','thank_you_page','style','active']);
        fputcsv($out,['Facebook Kitchen Campaign','facebook_template_1.png','<section>Landing page HTML</section>','<section>Thank you HTML</section>','.example{color:#1f3c88}',1]); fclose($out); exit;
    }

    public function export_csv()
    {
        if (!has_permission('landingpages-templates', '', 'view')) { access_denied('landingpages-templates'); }
        header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="zillapage_templates_'.date('Ymd_His').'.csv"');
        $out=fopen('php://output','w'); fputcsv($out,['name','thumb','content','thank_you_page','style','active']);
        foreach($this->landingpage_model->find_template() as $row){ fputcsv($out,[$row['name'],$row['thumb'],$row['content'],$row['thank_you_page'],$row['style'],$row['active']]); } fclose($out); exit;
    }

    public function import_csv()
    {
        if (!has_permission('landingpages-templates', '', 'create')) { access_denied('landingpages-templates'); }
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) { set_alert('danger',_l('zillapage_import_file_required')); redirect(admin_url('zillapage/templates')); }
        $fh=fopen($_FILES['import_file']['tmp_name'],'r'); $header=fgetcsv($fh); $count=0;
        while(($row=fgetcsv($fh))!==false){ $data=array_combine($header,array_pad($row,count($header),'')); if(empty($data['name'])) continue; $data['active']=isset($data['active'])?(int)$data['active']:1; $data['created_at']=date('Y-m-d H:i:s'); $this->db->insert(db_prefix().'landing_page_templates',$data); $count++; } fclose($fh);
        set_alert('success',sprintf(_l('zillapage_templates_imported'),$count)); redirect(admin_url('zillapage/templates'));
    }

}

