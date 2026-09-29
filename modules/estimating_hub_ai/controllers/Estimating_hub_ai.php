<?php defined('BASEPATH') or exit('No direct script access allowed');

class Estimating_hub_ai extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('estimating_hub_ai/Estimating_hub_ai_model');
        $this->load->helper(['form','url','estimating_hub_ai/estimating_hub_ai']);
        $this->load->library('user_agent');
    }

    private function render($view, $data = [])
    {
        $data['nav'] = $this->nav();
        $data['title'] = $data['title'] ?? 'Estimating Hub AI';
        $this->load->view('estimating_hub_ai/'.$view, $data);
    }

    private function nav()
    {
        return [
            ['Dashboard','estimating_hub_ai','fa fa-tachometer-alt'],
            ['AI Estimate Wizard','estimating_hub_ai/wizard','fa fa-magic'],
            ['Cost Database','estimating_hub_ai/cost_database','fa fa-database'],
            ['Camera Estimator','estimating_hub_ai/camera','fa fa-camera'],
            ['Training Documents','estimating_hub_ai/documents','fa fa-file-alt'],
            ['Home Depot Pro Lists','estimating_hub_ai/home_depot','fa fa-shopping-cart'],
            ['Material Price Collector','estimating_hub_ai/material_collector','fa fa-cloud-download-alt'],
            ['Reports','estimating_hub_ai/reports','fa fa-chart-line'],
            ['Search','estimating_hub_ai/search','fa fa-search'],
            ['Settings','estimating_hub_ai/settings','fa fa-cog'],
            ['Help Guide','estimating_hub_ai/help','fa fa-question-circle'],
            ['Health Check','estimating_hub_ai/health','fa fa-heartbeat'],
        ];
    }

    public function index()
    {
        $this->render('dashboard', [
            'title'=>'Estimating Hub AI',
            'stats'=>$this->Estimating_hub_ai_model->dashboard_stats(),
            'estimates'=>$this->Estimating_hub_ai_model->get_estimates(10),
            'documents'=>$this->Estimating_hub_ai_model->get_training_documents(5),
            'photos'=>$this->Estimating_hub_ai_model->get_photos(6),
            'links'=>$this->Estimating_hub_ai_model->get_vendor_links(6),
            'sources'=>$this->Estimating_hub_ai_model->get_external_sources(6),
        ]);
    }

    public function wizard()
    {
        if ($this->input->post()) {
            $id = $this->Estimating_hub_ai_model->create_ai_estimate($this->input->post(null, true));
            set_alert('success', 'Estimate draft created successfully with line items.');
            redirect(admin_url('estimating_hub_ai/view/'.$id));
        }
        $this->render('wizard', ['title'=>'AI Estimate Wizard']);
    }

    public function view($id)
    {
        $this->render('view', [
            'title'=>'Estimate Draft',
            'estimate'=>$this->Estimating_hub_ai_model->get_estimate($id),
            'items'=>$this->Estimating_hub_ai_model->get_items($id),
        ]);
    }

    public function cost_database()
    {
        $q = trim($this->input->get('q', true) ?: '');
        $this->render('cost_database', ['title'=>'Cost Database','q'=>$q,'items'=>$this->Estimating_hub_ai_model->get_cost_items($q)]);
    }

    public function camera()
    {
        if (!empty($_FILES['photo']['name'])) {
            $result = $this->Estimating_hub_ai_model->save_photo_intake($_FILES['photo'], $this->input->post(null, true));
            set_alert($result === true ? 'success' : 'danger', $result === true ? 'Photo saved successfully.' : $result);
            redirect(admin_url('estimating_hub_ai/camera'));
        }
        $this->render('camera', ['title'=>'Camera Estimator','photos'=>$this->Estimating_hub_ai_model->get_photos(),'projects'=>$this->get_projects_for_select()]);
    }

    public function create_estimate_from_photo($id)
    {
        $photo = null;
        if ($this->db->table_exists(db_prefix().'est_ai_photo_intake')) {
            $photo = $this->db->where('id',(int)$id)->get(db_prefix().'est_ai_photo_intake')->row_array();
        }
        if (!$photo) { set_alert('warning','Photo not found.'); redirect(admin_url('estimating_hub_ai/camera')); }
        $eid = $this->Estimating_hub_ai_model->create_ai_estimate([
            'name'=>'Photo Estimate '.$id,
            'scope_summary'=>$photo['notes'] ?: 'Create estimate from uploaded job photo.',
            'project_id'=>$photo['project_id'] ?? null,
            'clientid'=>$photo['clientid'] ?? null,
        ]);
        $this->db->where('id',(int)$id)->update(db_prefix().'est_ai_photo_intake', ['estimate_id'=>$eid]);
        set_alert('success','Estimate draft created from photo.');
        redirect(admin_url('estimating_hub_ai/view/'.$eid));
    }

    public function documents()
    {
        if (!empty($_FILES['training_file']['name'])) {
            $result = $this->Estimating_hub_ai_model->save_training_document($_FILES['training_file'], $this->input->post(null, true));
            set_alert($result === true ? 'success' : 'danger', $result === true ? 'Training document stored successfully.' : $result);
            redirect(admin_url('estimating_hub_ai/documents'));
        }
        $this->render('documents', ['title'=>'Training Documents','documents'=>$this->Estimating_hub_ai_model->get_training_documents()]);
    }

    public function home_depot()
    {
        if ($this->input->post()) {
            $this->Estimating_hub_ai_model->add_home_depot_item($this->input->post(null, true));
            set_alert('success','Home Depot material item saved successfully.');
            redirect(admin_url('estimating_hub_ai/home_depot'));
        }
        $this->render('home_depot', ['title'=>'Home Depot Pro Lists','links'=>$this->Estimating_hub_ai_model->get_vendor_links()]);
    }

    public function material_collector()
    {
        $this->render('material_collector', [
            'title'=>'Material Price Collector',
            'sources'=>$this->Estimating_hub_ai_model->get_external_sources(),
            'endpoint'=>admin_url('estimating_hub_ai/collector_api'),
        ]);
    }

    public function collect_public_url()
    {
        $result = $this->Estimating_hub_ai_model->collect_public_url($this->input->post('source_url', true), $this->input->post('vendor', true) ?: 'Public Source');
        set_alert(strpos($result,'successfully') !== false ? 'success' : 'warning', $result);
        redirect(admin_url('estimating_hub_ai/material_collector'));
    }

    public function collector_sample(){ $this->Estimating_hub_ai_model->sample_collector_csv(); }
    public function collector_export(){ $this->Estimating_hub_ai_model->export_collector_csv(); }
    public function collector_import()
    {
        $r = !empty($_FILES['import_file']['name']) ? $this->Estimating_hub_ai_model->import_collector_csv($_FILES['import_file']) : 'Please choose a CSV file before importing.';
        set_alert(strpos($r,'successfully') !== false ? 'success' : 'danger', $r);
        redirect(admin_url('estimating_hub_ai/material_collector'));
    }
    public function collector_mass_delete()
    {
        $ids=$this->input->post('ids');
        if(empty($ids)||!is_array($ids)){ set_alert('warning','Please select at least one record.'); redirect(admin_url('estimating_hub_ai/material_collector')); }
        $this->Estimating_hub_ai_model->mass_delete_table(db_prefix().'est_ai_external_price_sources',$ids);
        set_alert('success','Selected collector records deleted successfully.');
        redirect(admin_url('estimating_hub_ai/material_collector'));
    }
    public function collector_api()
    {
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($this->Estimating_hub_ai_model->collector_api_receive()));
    }

    public function reports()
    {
        $this->render('reports', [
            'title'=>'Estimating Reports',
            'stats'=>$this->Estimating_hub_ai_model->dashboard_stats(),
            'items'=>$this->Estimating_hub_ai_model->get_cost_items('', 500),
            'sources'=>$this->Estimating_hub_ai_model->source_counts(),
        ]);
    }

    public function search()
    {
        $q=trim($this->input->get('q',true) ?: '');
        $this->render('search', ['title'=>'Search Estimating Hub AI','q'=>$q,'results'=>$this->Estimating_hub_ai_model->global_search($q)]);
    }

    public function settings()
    {
        if($this->input->post()){
            foreach($this->input->post(null,true) as $k=>$v){ if(strpos($k,'estimating_hub_ai_')===0){ update_option($k, is_array($v)?json_encode($v):$v); } }
            set_alert('success','Settings saved successfully.');
            redirect(admin_url('estimating_hub_ai/settings'));
        }
        $this->render('settings',['title'=>'Estimating Hub AI Settings']);
    }

    public function health(){ $this->render('health',['title'=>'Health Check','checks'=>$this->Estimating_hub_ai_model->health_checks()]); }
    public function help(){ $this->render('help',['title'=>'Help Guide']); }

    public function sync_sources()
    {
        $result = $this->Estimating_hub_ai_model->sync_existing_sales_sources();
        set_alert('success', $result['count'].' source records imported. Checked tables: '.implode(', ', $result['checked']));
        redirect(admin_url('estimating_hub_ai/cost_database'));
    }

    public function load_sample_database()
    {
        $file = module_dir_path('estimating_hub_ai').'samples/tampa_bay_cost_database_4_97mb.csv';
        if (!file_exists($file)) { set_alert('warning','Large Tampa Bay sample database file is missing from samples folder.'); redirect(admin_url('estimating_hub_ai/cost_database')); }
        $result = $this->Estimating_hub_ai_model->import_cost_database_csv(['name'=>'tampa_bay_cost_database_4_97mb.csv','tmp_name'=>$file]);
        set_alert(strpos($result,'successfully')!==false?'success':'danger',$result);
        redirect(admin_url('estimating_hub_ai/cost_database'));
    }

    public function export_cost_database(){ $this->Estimating_hub_ai_model->export_cost_database_csv(); }
    public function export_home_depot(){ $this->Estimating_hub_ai_model->export_home_depot_material_list(); }
    public function export_training_documents(){ $this->Estimating_hub_ai_model->export_training_documents_csv(); }
    public function export_reports(){ $this->Estimating_hub_ai_model->export_reports_csv(); }
    public function sample_cost_database(){ $this->Estimating_hub_ai_model->sample_cost_database_csv(); }
    public function sample_home_depot(){ $this->Estimating_hub_ai_model->sample_home_depot_csv(); }
    public function sample_training_documents(){ $this->Estimating_hub_ai_model->sample_training_documents_csv(); }
    public function print_home_depot(){ $this->render('print_home_depot',['title'=>'Home Depot Pro Material List','links'=>$this->Estimating_hub_ai_model->get_vendor_links()]); }

    public function import_cost_database()
    {
        $result = !empty($_FILES['import_file']['name']) ? $this->Estimating_hub_ai_model->import_cost_database_csv($_FILES['import_file']) : 'Please choose a CSV file before importing.';
        set_alert(strpos($result,'successfully')!==false?'success':'danger',$result);
        redirect(admin_url('estimating_hub_ai/cost_database'));
    }
    public function import_home_depot()
    {
        $result = !empty($_FILES['import_file']['name']) ? $this->Estimating_hub_ai_model->import_home_depot_csv($_FILES['import_file']) : 'Please choose a CSV file before importing.';
        set_alert(strpos($result,'successfully')!==false?'success':'danger',$result);
        redirect(admin_url('estimating_hub_ai/home_depot'));
    }
    public function mass_delete_cost_items(){ $this->mass_delete('est_ai_cost_database','estimating_hub_ai/cost_database','Selected cost items deleted successfully.'); }
    public function mass_delete_documents(){ $this->mass_delete('est_ai_training_documents','estimating_hub_ai/documents','Selected documents deleted successfully.'); }
    public function mass_delete_home_depot(){ $this->mass_delete('est_ai_vendor_price_links','estimating_hub_ai/home_depot','Selected Home Depot items deleted successfully.'); }
    private function mass_delete($table,$redirect,$message)
    {
        $ids=$this->input->post('ids');
        if(empty($ids)||!is_array($ids)){ set_alert('warning','Please select at least one record.'); redirect(admin_url($redirect)); }
        $this->Estimating_hub_ai_model->mass_delete_table(db_prefix().$table,$ids);
        set_alert('success',$message);
        redirect(admin_url($redirect));
    }
    public function reload(){ set_alert('success','Reload completed successfully.'); redirect($this->agent->referrer() ?: admin_url('estimating_hub_ai')); }

    private function get_projects_for_select()
    {
        if (!$this->db->table_exists(db_prefix().'projects')) return [];
        return $this->db->select('id,name')->order_by('id','desc')->limit(500)->get(db_prefix().'projects')->result_array();
    }
}
