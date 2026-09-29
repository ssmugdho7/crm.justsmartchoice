<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Solar_pro extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('solar_pro_model');
        $this->load->library('solar_pro/Solar_pro_calculator');
        $this->load->library('solar_pro/Solar_pro_google');
        $this->load->helper('solar_pro/solar_pro');
    }

    /**
     * Module upgrades are intentionally handled only by Perfex Setup > Modules.
     * Do not perform schema checks or migrations during normal Solar Pro requests.
     */
    private function canViewGlobal()
    {
        return is_admin() || staff_can('view', 'solar_pro');
    }

    private function requireCapability($capability)
    {
        if (!is_admin() && !staff_can($capability, 'solar_pro')) {
            access_denied('solar_pro');
        }
    }

    public function index()
    {
        if (!is_admin() && !staff_can('view', 'solar_pro') && !staff_can('view_own', 'solar_pro')) {
            access_denied('solar_pro');
        }
        $data['title'] = _l('solar_pro_dashboard');
        $data['stats'] = $this->solar_pro_model->dashboardStats(get_staff_user_id(), $this->canViewGlobal());
        $data['recent'] = array_slice($this->solar_pro_model->getAnalyses(get_staff_user_id(), $this->canViewGlobal()), 0, 8);
        $this->load->view('admin/dashboard', $data);
    }

    public function analyses()
    {
        if (!is_admin() && !staff_can('view', 'solar_pro') && !staff_can('view_own', 'solar_pro')) {
            access_denied('solar_pro');
        }
        $data['title'] = _l('solar_pro_analyses');
        $data['analyses'] = $this->solar_pro_model->getAnalyses(get_staff_user_id(), $this->canViewGlobal());
        $this->load->view('admin/analyses', $data);
    }

    public function analysis($id = null)
    {
        if ($id) {
            if (!is_admin() && !staff_can('view', 'solar_pro') && !staff_can('view_own', 'solar_pro')) {
                access_denied('solar_pro');
            }
            $data['analysis'] = $this->solar_pro_model->getAnalysis((int) $id);
            if (!$data['analysis']) {
                show_404();
            }
            if (!$this->canViewGlobal() && (int) $data['analysis']['created_by'] !== (int) get_staff_user_id()) {
                access_denied('solar_pro');
            }
            $data['title'] = _l('solar_pro_analysis_report');
            $data['months'] = solar_pro_month_names();
            $data['documents'] = $this->solar_pro_model->documents((int) $id);
            $data['finance'] = solar_pro_finance_summary($data['analysis']);
            $this->load->view('admin/report', $data);
            return;
        }

        $this->requireCapability('create');
        $data['title'] = _l('solar_pro_new_analysis');
        $data['utilities'] = $this->solar_pro_model->utilities();
        $data['rates'] = $this->solar_pro_model->utilityRates();
        $data['panels'] = $this->solar_pro_model->equipment('panel');
        $data['inverters'] = $this->solar_pro_model->equipment('inverter');
        $data['contract_templates'] = $this->solar_pro_model->contractTemplates(true);
        $this->load->view('admin/calculator', $data);
    }

    public function calculate()
    {
        $this->requireCapability('create');
        if (!$this->input->post()) {
            show_404();
        }
        $input = $this->input->post(null, true);
        $address = trim((string) ($input['address'] ?? ''));
        $annual = (float) ($input['annual_consumption_kwh'] ?? 0);
        if ($address === '' || $annual <= 0) {
            set_alert('danger', _l('solar_pro_validation_address_consumption'));
            redirect(admin_url('solar_pro/analysis'));
        }

        $googleResponse = null;
        $lat = isset($input['latitude']) ? (float) $input['latitude'] : 0;
        $lng = isset($input['longitude']) ? (float) $input['longitude'] : 0;
        $analysisMethod = in_array(($input['analysis_method'] ?? 'google_solar'), ['google_solar','quick_estimate'], true) ? $input['analysis_method'] : 'google_solar';
        $input['analysis_method'] = $analysisMethod;
        if ($analysisMethod === 'google_solar') {
            if (!$lat || !$lng) {
                $geo = $this->solar_pro_google->geocode($address . ', ' . ($input['city'] ?? '') . ', ' . ($input['state'] ?? '') . ' ' . ($input['zip'] ?? ''));
                if (!empty($geo['success'])) {
                    $lat = $geo['latitude'];
                    $lng = $geo['longitude'];
                    $input['latitude'] = $lat;
                    $input['longitude'] = $lng;
                }
            }
            if ($lat && $lng) {
                $solar = $this->solar_pro_google->buildingInsights($lat, $lng);
                if (!empty($solar['success'])) {
                    $googleResponse = $solar['data'];
                    $input['_google_response'] = $googleResponse;
                }
            }
        }

        $result = $this->solar_pro_calculator->calculate($input, $googleResponse);
        $id = $this->solar_pro_model->saveAnalysis($input, $result, get_staff_user_id());
        if ($id && !empty($input['create_contract']) && !empty($input['contract_template_id'])) {
            try { $this->solar_pro_model->createIndependentContract($id, (int)$input['contract_template_id'], get_staff_user_id()); } catch (Throwable $e) { log_message('error','Solar Pro auto contract: '.$e->getMessage()); }
        }
        if ($id && solar_pro_setting('solar_pro_public_create_lead', '1') === '1' && !empty($input['email'])) {
            try {
                $this->solar_pro_model->createLeadFromAnalysis($id);
            } catch (Throwable $e) {
                log_message('error', 'Solar Pro lead creation failed: ' . $e->getMessage());
            }
        }
        set_alert('success', _l('solar_pro_analysis_created'));
        redirect(admin_url('solar_pro/analysis/' . $id));
    }

    public function create_lead($id)
    {
        $this->requireCapability('edit');
        $leadId = $this->solar_pro_model->createLeadFromAnalysis((int) $id);
        set_alert($leadId ? 'success' : 'warning', $leadId ? _l('solar_pro_lead_created') : _l('solar_pro_lead_not_created'));
        redirect(admin_url('solar_pro/analysis/' . (int) $id));
    }

    public function utilities()
    {
        $this->requireCapability('manage_utilities');
        if ($this->input->post()) {
            $name = trim((string) $this->input->post('name', true));
            if ($name !== '') {
                $this->db->insert(db_prefix() . 'solar_utilities', [
                    'name' => $name,
                    'short_name' => trim((string) $this->input->post('short_name', true)),
                    'service_area' => trim((string) $this->input->post('service_area', true)),
                    'website' => trim((string) $this->input->post('website', true)),
                    'active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                set_alert('success', _l('solar_pro_utility_saved'));
            }
            redirect(admin_url('solar_pro/utilities'));
        }
        $data['title'] = _l('solar_pro_utilities');
        $data['utilities'] = $this->solar_pro_model->utilities();
        $data['rates'] = $this->solar_pro_model->utilityRates();
        $this->load->view('admin/utilities', $data);
    }

    public function save_rate()
    {
        $this->requireCapability('manage_utilities');
        $post = $this->input->post(null, true);
        $utilityId = (int) ($post['utility_id'] ?? 0);
        if ($utilityId <= 0 || trim((string) ($post['rate_name'] ?? '')) === '') {
            set_alert('danger', _l('solar_pro_rate_validation'));
            redirect(admin_url('solar_pro/utilities'));
        }
        $fields = ['customer_charge','energy_rate','fuel_rate','storm_charge','other_monthly_charge','gross_receipts_pct','utility_tax_pct','sales_tax_pct','minimum_bill','export_credit_rate'];
        $data = [
            'utility_id' => $utilityId,
            'rate_name' => trim((string) $post['rate_name']),
            'effective_date' => !empty($post['effective_date']) ? $post['effective_date'] : null,
            'net_metering' => !empty($post['net_metering']) ? 1 : 0,
            'notes' => trim((string) ($post['notes'] ?? '')),
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        foreach ($fields as $field) {
            $data[$field] = (float) ($post[$field] ?? 0);
        }
        $this->db->insert(db_prefix() . 'solar_utility_rates', $data);
        set_alert('success', _l('solar_pro_rate_saved'));
        redirect(admin_url('solar_pro/utilities'));
    }

    public function equipment()
    {
        $this->requireCapability('manage_equipment');
        if ($this->input->post()) {
            $post = $this->input->post(null, true);
            $type = in_array($post['type'] ?? '', ['panel', 'inverter', 'battery'], true) ? $post['type'] : 'panel';
            $equipmentData = [
                'type'=>$type,'manufacturer'=>trim((string)($post['manufacturer']??'')),'model'=>trim((string)($post['model']??'')),
                'watts'=>($post['watts']??'')!==''?(float)$post['watts']:null,'ac_watts'=>($post['ac_watts']??'')!==''?(float)$post['ac_watts']:null,
                'efficiency_pct'=>($post['efficiency_pct']??'')!==''?(float)$post['efficiency_pct']:null,'warranty_years'=>($post['warranty_years']??'')!==''?(int)$post['warranty_years']:null,
                'cost'=>(float)($post['cost']??0),'sale_price'=>(float)($post['sale_price']??0),'active'=>1,
            ];
            $id=!empty($post['id'])?(int)$post['id']:0;
            if($id){$this->db->where('id',$id)->update(db_prefix().'solar_equipment',$equipmentData);}else{$equipmentData['created_at']=date('Y-m-d H:i:s');$this->db->insert(db_prefix().'solar_equipment',$equipmentData);$id=(int)$this->db->insert_id();}
            if($id && !empty($_FILES['spec_sheet']['name'])){$this->saveEquipmentSpec($id,$_FILES['spec_sheet']);}
            set_alert('success',_l('solar_pro_equipment_saved')); redirect(admin_url('solar_pro/equipment'));
        }
        $data['title']=_l('solar_pro_equipment'); $data['equipment']=$this->solar_pro_model->equipment(); $this->load->view('admin/equipment',$data);
    }

    private function saveEquipmentSpec($id,array $file)
    {
        if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])){return false;}
        if(($file['size']??0)>25*1024*1024){set_alert('danger',_l('solar_pro_spec_too_large'));return false;}
        $ext=strtolower(pathinfo((string)$file['name'],PATHINFO_EXTENSION)); if(!in_array($ext,['pdf','jpg','jpeg','png','webp'],true)){set_alert('danger',_l('solar_pro_spec_invalid'));return false;}
        $dir=FCPATH.'uploads/solar_pro/equipment/'.(int)$id.'/'; if(!is_dir($dir)){@mkdir($dir,0755,true);} if(!is_dir($dir)){return false;}
        $stored='spec_'.date('YmdHis').'_'.bin2hex(random_bytes(4)).'.'.$ext; if(!@move_uploaded_file($file['tmp_name'],$dir.$stored)){return false;}
        $previews=[];
        if(in_array($ext,['jpg','jpeg','png','webp'],true)){$previews[]=$stored;}
        elseif(class_exists('Imagick')){try{$im=new Imagick();$im->setResolution(130,130);$im->readImage($dir.$stored);foreach($im as $i=>$page){if($i>20)break;$page->setImageFormat('png');$name='preview_'.($i+1).'.png';$page->writeImage($dir.$name);$previews[]=$name;} $im->clear();}catch(Throwable $e){log_message('error','Solar Pro datasheet preview: '.$e->getMessage());}}
        if($ext==='pdf' && !$previews && function_exists('shell_exec')){try{$base=$dir.'preview';$cmd='pdftoppm -png -r 130 -f 1 -l 20 '.escapeshellarg($dir.$stored).' '.escapeshellarg($base).' 2>/dev/null';@shell_exec($cmd);foreach(glob($base.'-*.png')?:[] as $pp){$previews[]=basename($pp);}}catch(Throwable $e){log_message('error','Solar Pro datasheet CLI preview: '.$e->getMessage());}}
        $this->db->where('id',(int)$id)->update(db_prefix().'solar_equipment',['spec_file'=>$stored,'spec_original_name'=>substr((string)$file['name'],0,255),'spec_mime'=>substr((string)($file['type']??''),0,120),'spec_preview_json'=>json_encode($previews)]);
        return true;
    }

    public function equipment_spec($id)
    {
        $this->requireCapability('manage_equipment'); $e=$this->db->where('id',(int)$id)->get(db_prefix().'solar_equipment')->row_array(); if(!$e||empty($e['spec_file']))show_404();
        $path=FCPATH.'uploads/solar_pro/equipment/'.(int)$id.'/'.$e['spec_file']; if(!is_file($path))show_404();
        header('Content-Type: '.($e['spec_mime']?:'application/octet-stream')); header('Content-Disposition: inline; filename="'.basename($e['spec_original_name']?:$e['spec_file']).'"'); readfile($path); exit;
    }

    public function equipment_sample()
    {
        $this->requireCapability('manage_equipment');
        header('Content-Type: text/csv');header('Content-Disposition: attachment; filename="solar-equipment-import-sample.csv"');
        echo "type,manufacturer,model,watts,ac_watts,efficiency_pct,warranty_years,cost,sale_price\n";echo "panel,Example Solar,440W Module,440,,21.4,25,150,275\n";exit;
    }

    public function equipment_import()
    {
        $this->requireCapability('manage_equipment');
        if(empty($_FILES['import_file']['tmp_name']) || !is_uploaded_file($_FILES['import_file']['tmp_name'])){set_alert('warning',_l('solar_pro_import_file_required'));redirect(admin_url('solar_pro/equipment'));}
        $fh=fopen($_FILES['import_file']['tmp_name'],'r');$header=fgetcsv($fh);$expected=['type','manufacturer','model','watts','ac_watts','efficiency_pct','warranty_years','cost','sale_price'];
        $header=array_map(fn($v)=>strtolower(trim((string)$v)),$header?:[]); if($header!==$expected){fclose($fh);set_alert('danger',_l('solar_pro_import_header_invalid'));redirect(admin_url('solar_pro/equipment'));}
        $added=0;$updated=0;while(($r=fgetcsv($fh))!==false){if(count($r)<9)continue;$d=array_combine($expected,array_pad($r,9,''));$type=in_array(strtolower($d['type']),['panel','inverter','battery'],true)?strtolower($d['type']):'panel';$row=['type'=>$type,'manufacturer'=>trim($d['manufacturer']),'model'=>trim($d['model']),'watts'=>$d['watts']!==''?(float)$d['watts']:null,'ac_watts'=>$d['ac_watts']!==''?(float)$d['ac_watts']:null,'efficiency_pct'=>$d['efficiency_pct']!==''?(float)$d['efficiency_pct']:null,'warranty_years'=>$d['warranty_years']!==''?(int)$d['warranty_years']:null,'cost'=>(float)$d['cost'],'sale_price'=>(float)$d['sale_price'],'active'=>1];if($row['manufacturer']===''||$row['model']==='')continue;$existing=$this->db->where('type',$type)->where('manufacturer',$row['manufacturer'])->where('model',$row['model'])->get(db_prefix().'solar_equipment')->row_array();if($existing){$this->db->where('id',(int)$existing['id'])->update(db_prefix().'solar_equipment',$row);$updated++;}else{$row['created_at']=date('Y-m-d H:i:s');$this->db->insert(db_prefix().'solar_equipment',$row);$added++;}}
        fclose($fh);set_alert('success',_l('solar_pro_import_complete',[$added,$updated]));redirect(admin_url('solar_pro/equipment'));
    }

    public function equipment_mass_delete()
    {
        $this->requireCapability('delete');$ids=$this->input->post('ids');$ids=is_array($ids)?array_values(array_filter(array_map('intval',$ids))):[];if(!$ids){set_alert('warning',_l('solar_pro_select_records'));redirect(admin_url('solar_pro/equipment'));}
        $this->db->where_in('panel_equipment_id',$ids)->update(db_prefix().'solar_analyses',['panel_equipment_id'=>null]);$this->db->where_in('inverter_equipment_id',$ids)->update(db_prefix().'solar_analyses',['inverter_equipment_id'=>null]);$this->db->where_in('id',$ids)->delete(db_prefix().'solar_equipment');set_alert('success',_l('solar_pro_equipment_deleted'));redirect(admin_url('solar_pro/equipment'));
    }

    public function proposals()
    {
        if (!is_admin() && !staff_can('view','solar_pro') && !staff_can('view_own','solar_pro')) { access_denied('solar_pro'); }
        $this->db->select(db_prefix().'solar_proposals.*,'.db_prefix().'solar_analyses.first_name,'.db_prefix().'solar_analyses.last_name,'.db_prefix().'solar_analyses.system_price');
        $this->db->join(db_prefix().'solar_analyses',db_prefix().'solar_analyses.id='.db_prefix().'solar_proposals.analysis_id','left');
        if(!$this->canViewGlobal()){ $this->db->where(db_prefix().'solar_proposals.created_by',get_staff_user_id()); }
        $data['proposals']=$this->db->order_by(db_prefix().'solar_proposals.id','DESC')->get(db_prefix().'solar_proposals')->result_array();
        $data['title']=_l('solar_pro_proposals');
        $this->load->view('admin/proposals',$data);
    }

    public function create_proposal($analysisId)
    {
        $this->requireCapability('create');
        $id=$this->solar_pro_model->createProposal((int)$analysisId,get_staff_user_id());
        if(!$id){ set_alert('danger',_l('solar_pro_proposal_create_failed')); redirect(admin_url('solar_pro/analysis/'.(int)$analysisId)); }
        set_alert('success',_l('solar_pro_proposal_created'));
        redirect(admin_url('solar_pro/proposal/'.$id));
    }

    public function proposal($id)
    {
        if (!is_admin() && !staff_can('view','solar_pro') && !staff_can('view_own','solar_pro')) { access_denied('solar_pro'); }
        $proposal=$this->solar_pro_model->getProposal((int)$id); if(!$proposal){ show_404(); }
        if(!$this->canViewGlobal() && (int)$proposal['created_by']!==get_staff_user_id()){ access_denied('solar_pro'); }
        if($this->input->post('comment')){
            $message=trim((string)$this->input->post('comment',true));
            if($message!==''){$this->db->insert(db_prefix().'solar_proposal_comments',['proposal_id'=>(int)$id,'staff_id'=>get_staff_user_id(),'message'=>$message,'created_at'=>date('Y-m-d H:i:s')]);}
            redirect(admin_url('solar_pro/proposal/'.(int)$id));
        }
        if($this->input->post('template_id')!==null){$tid=(int)$this->input->post('template_id',true);if($this->solar_pro_model->getProposalTemplate($tid)){$this->db->where('id',(int)$id)->update(db_prefix().'solar_proposals',['template_id'=>$tid,'updated_at'=>date('Y-m-d H:i:s')]);set_alert('success',_l('solar_pro_proposal_template_assigned'));}redirect(admin_url('solar_pro/proposal/'.(int)$id));}
        $data['proposal']=$proposal; $data['analysis']=$proposal['analysis']; $data['finance']=solar_pro_finance_summary($proposal['analysis']); $data['environment']=solar_pro_environment_summary($proposal['analysis']);
        $data['templates']=$this->solar_pro_model->proposalTemplates(true); $data['contract']=$proposal['analysis']['contract']??null;
        $data['title']=$proposal['title']; $this->load->view('admin/proposal',$data);
    }

    public function proposal_pdf($id)
    {
        $proposal=$this->solar_pro_model->getProposal((int)$id); if(!$proposal){ show_404(); }
        $proposal['include_contract']=$this->input->get('include_contract')==='1';
        $this->load->helper('pdf');
        $pdf=app_pdf('solar-proposal',module_dir_path('solar_pro','libraries/pdf/Solar_proposal_pdf'),$proposal);
        $first=trim((string)$proposal['analysis']['first_name']);$name=preg_replace('/[^A-Za-z0-9 _-]/','',$first!==''?$first:'Customer');
        $pdf->Output($name.' Solar Proposal.pdf','D');
    }

    public function send_proposal($id)
    {
        $this->requireCapability('edit');$proposal=$this->solar_pro_model->getProposal((int)$id);if(!$proposal)show_404();
        if(!$this->input->post()){redirect(admin_url('solar_pro/proposal/'.(int)$id));}
        $to=trim((string)$this->input->post('to',true));$cc=trim((string)$this->input->post('cc',true));$subject=trim((string)$this->input->post('subject',true));$message=html_purify($this->input->post('message',false));
        if(!filter_var($to,FILTER_VALIDATE_EMAIL)){set_alert('danger',_l('solar_pro_customer_email_required'));redirect(admin_url('solar_pro/proposal/'.(int)$id));}
        $ok=false;try{$this->load->model('emails_model');$ok=$this->emails_model->send_simple_email($to,$subject,$message);if($ok && $cc!==''){foreach(preg_split('/[,;]+/',$cc) as $c){$c=trim($c);if(filter_var($c,FILTER_VALIDATE_EMAIL)){$this->emails_model->send_simple_email($c,$subject,$message);}}}if($ok){$this->db->where('id',(int)$id)->update(db_prefix().'solar_proposals',['status'=>'sent','sent_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);}set_alert($ok?'success':'danger',$ok?_l('solar_pro_proposal_sent'):_l('solar_pro_proposal_send_failed'));}catch(Throwable $e){log_message('error','Solar Pro proposal email: '.$e->getMessage());set_alert('danger',_l('solar_pro_proposal_send_failed'));}
        redirect(admin_url('solar_pro/proposal/'.(int)$id));
    }

    public function proposal_templates()
    {
        if(!is_admin() && !staff_can('edit','solar_pro')){access_denied('solar_pro');}
        $data['title']=_l('solar_pro_proposal_templates');$data['templates']=$this->solar_pro_model->proposalTemplates(false);$this->load->view('admin/proposal_templates',$data);
    }

    public function proposal_template($id=0)
    {
        if(!is_admin() && !staff_can('edit','solar_pro')){access_denied('solar_pro');}$id=(int)$id;
        if($this->input->post()){$row=['name'=>trim((string)$this->input->post('name',true)),'hero_title'=>trim((string)$this->input->post('hero_title',true)),'intro_html'=>html_purify($this->input->post('intro_html',false)),'closing_html'=>html_purify($this->input->post('closing_html',false)),'primary_color'=>trim((string)$this->input->post('primary_color',true))?:'#0E6F5B','secondary_color'=>trim((string)$this->input->post('secondary_color',true))?:'#3598DB','accent_color'=>trim((string)$this->input->post('accent_color',true))?:'#F28C28','active'=>$this->input->post('active')?1:0,'is_default'=>$this->input->post('is_default')?1:0,'updated_at'=>date('Y-m-d H:i:s')];if($row['is_default']){$this->db->update(db_prefix().'solar_proposal_templates',['is_default'=>0]);}if($id){$this->db->where('id',$id)->update(db_prefix().'solar_proposal_templates',$row);}else{$row['created_by']=get_staff_user_id();$row['created_at']=date('Y-m-d H:i:s');unset($row['updated_at']);$this->db->insert(db_prefix().'solar_proposal_templates',$row);$id=(int)$this->db->insert_id();}set_alert('success',_l('solar_pro_proposal_template_saved'));redirect(admin_url('solar_pro/proposal_template/'.$id));}
        $data['template']=$id?$this->solar_pro_model->getProposalTemplate($id):null;$data['title']=$id?_l('solar_pro_edit_proposal_template'):_l('solar_pro_new_proposal_template');$this->load->view('admin/proposal_template',$data);
    }

    public function delete_proposal_template($id)
    {
        $this->requireCapability('delete');$id=(int)$id;if(total_rows(db_prefix().'solar_proposals',['template_id'=>$id])){set_alert('warning',_l('solar_pro_template_in_use'));}else{$this->db->where('id',$id)->delete(db_prefix().'solar_proposal_templates');set_alert('success',_l('solar_pro_template_deleted'));}redirect(admin_url('solar_pro/proposal_templates'));
    }

    public function contracts()
    {
        $this->requireCapability('manage_contracts');
        $this->db->select(db_prefix().'solar_contracts.*,'.db_prefix().'solar_analyses.first_name,'.db_prefix().'solar_analyses.last_name');
        $this->db->join(db_prefix().'solar_analyses',db_prefix().'solar_analyses.id='.db_prefix().'solar_contracts.analysis_id','left');
        $data['title'] = _l('solar_pro_contracts');
        $data['contracts'] = $this->db->order_by(db_prefix().'solar_contracts.id','DESC')->get(db_prefix().'solar_contracts')->result_array();
        $data['analyses'] = $this->solar_pro_model->getAnalyses(get_staff_user_id(), $this->canViewGlobal());
        $data['templates']=$this->solar_pro_model->contractTemplates(true);
        $this->load->view('admin/contracts', $data);
    }

    public function create_contract($analysisId)
    {
        $this->requireCapability('manage_contracts');
        redirect(admin_url('solar_pro/contract/0/'.(int)$analysisId));
    }

    public function contract($id=0,$analysisId=0)
    {
        $this->requireCapability('manage_contracts'); $id=(int)$id; $analysisId=(int)$analysisId;
        $contract=$id?$this->db->where('id',$id)->get(db_prefix().'solar_contracts')->row_array():null;
        if($id && !$contract){show_404();}
        if($this->input->post()){
            if($id){
                $row=['title'=>trim((string)$this->input->post('title',true)),'content'=>html_purify($this->input->post('content',false)),'contract_value'=>(float)$this->input->post('contract_value',true),'start_date'=>$this->input->post('start_date',true)?:null,'end_date'=>$this->input->post('end_date',true)?:null,'updated_at'=>date('Y-m-d H:i:s')];
                $this->db->where('id',$id)->update(db_prefix().'solar_contracts',$row); set_alert('success',_l('solar_pro_contract_saved')); redirect(admin_url('solar_pro/contract/'.$id));
            }
            $analysisId=(int)$this->input->post('analysis_id',true); $templateId=(int)$this->input->post('template_id',true);
            $newId=$this->solar_pro_model->createIndependentContract($analysisId,$templateId,get_staff_user_id(),trim((string)$this->input->post('title',true)));
            if(!$newId){set_alert('danger',_l('solar_pro_contract_create_failed')); redirect(admin_url('solar_pro/contracts'));}
            set_alert('success',_l('solar_pro_contract_created')); redirect(admin_url('solar_pro/contract/'.$newId));
        }
        $data['contract']=$contract; $data['analysis']=$contract?$this->solar_pro_model->getAnalysis((int)$contract['analysis_id']):($analysisId?$this->solar_pro_model->getAnalysis($analysisId):null);
        $data['analyses']=$this->solar_pro_model->getAnalyses(get_staff_user_id(),$this->canViewGlobal()); $data['templates']=$this->solar_pro_model->contractTemplates(true); $data['title']=$id?_l('solar_pro_edit_contract'):_l('solar_pro_new_contract');
        $this->load->view('admin/contract',$data);
    }

    public function delete_contract($id)
    {
        $this->requireCapability('delete'); $id=(int)$id;
        $this->db->trans_start(); $this->db->where('contract_id',$id)->delete(db_prefix().'solar_contract_initials'); $this->db->where('contract_id',$id)->delete(db_prefix().'solar_contract_comments'); $this->db->where('id',$id)->delete(db_prefix().'solar_contracts'); $this->db->trans_complete();
        set_alert('success',_l('solar_pro_contract_deleted')); redirect(admin_url('solar_pro/contracts'));
    }

    public function contract_templates()
    {
        $this->requireCapability('manage_contracts');
        $data['title'] = _l('solar_pro_templates');
        $data['templates'] = $this->solar_pro_model->contractTemplates(false);
        $this->load->view('admin/contract_templates', $data);
    }

    public function contract_template($id = 0)
    {
        $this->requireCapability('manage_contracts'); $id=(int)$id;
        if($this->input->post()){
            $row=['name'=>trim((string)$this->input->post('name',true)),'template_type'=>trim((string)$this->input->post('template_type',true))?:'custom','content'=>html_purify($this->input->post('content',false)),'active'=>$this->input->post('active')?1:0,'updated_at'=>date('Y-m-d H:i:s')];
            if($id){$this->db->where('id',$id)->update(db_prefix().'solar_contract_templates',$row);}else{$row['is_system_default']=0;$row['created_by']=get_staff_user_id();$row['created_at']=date('Y-m-d H:i:s');unset($row['updated_at']);$this->db->insert(db_prefix().'solar_contract_templates',$row);$id=(int)$this->db->insert_id();}
            set_alert('success',_l('solar_pro_contract_template_saved')); redirect(admin_url('solar_pro/contract_template/'.$id));
        }
        $data['title']=$id?_l('solar_pro_edit_contract_template'):_l('solar_pro_new_contract_template'); $data['template']=$id?$this->db->where('id',$id)->get(db_prefix().'solar_contract_templates')->row_array():null;
        $this->load->view('admin/contract_template',$data);
    }

    public function contract_template_preview($id)
    {
        $this->requireCapability('manage_contracts'); $t=$this->db->where('id',(int)$id)->get(db_prefix().'solar_contract_templates')->row_array(); if(!$t){show_404();}
        $data['template']=$t; $data['title']=$t['name']; $this->load->view('admin/contract_template_preview',$data);
    }

    public function delete_contract_template($id)
    {
        $this->requireCapability('delete'); $id=(int)$id;
        $used=total_rows(db_prefix().'solar_contracts',['template_id'=>$id]);
        if($used){set_alert('warning',_l('solar_pro_template_in_use'));}else{$this->db->where('id',$id)->delete(db_prefix().'solar_contract_templates');set_alert('success',_l('solar_pro_template_deleted'));}
        redirect(admin_url('solar_pro/contract_templates'));
    }

    public function analysis_pdf($id)
    {
        if (!is_admin() && !staff_can('view', 'solar_pro') && !staff_can('view_own', 'solar_pro')) { access_denied('solar_pro'); }
        $analysis = $this->solar_pro_model->getAnalysis((int)$id);
        if (!$analysis) { show_404(); }
        $this->load->helper('pdf');
        $pdf = app_pdf('solar-analysis', module_dir_path('solar_pro','libraries/pdf/Solar_analysis_pdf'), $analysis);
        $pdf->Output('solar-analysis-' . (int)$id . '.pdf', 'D');
    }

    public function mass_delete()
    {
        $this->requireCapability('delete');
        $ids = $this->input->post('ids');
        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            set_alert('warning', _l('solar_pro_select_records'));
            redirect(admin_url('solar_pro/analyses'));
        }
        $this->db->trans_start();
        $this->db->where_in('analysis_id', $ids)->delete(db_prefix() . 'solar_production_monthly');
        $this->db->where_in('analysis_id', $ids)->delete(db_prefix() . 'solar_consumption');
        $this->db->where_in('analysis_id', $ids)->delete(db_prefix() . 'solar_documents');
        $this->db->where_in('id', $ids)->delete(db_prefix() . 'solar_analyses');
        $this->db->trans_complete();
        set_alert($this->db->trans_status() ? 'success' : 'danger', $this->db->trans_status() ? _l('solar_pro_deleted') : _l('solar_pro_delete_failed'));
        redirect(admin_url('solar_pro/analyses'));
    }

    public function delete_analysis($id)
    {
        $this->requireCapability('delete');
        $id = (int) $id;
        $this->db->trans_start();
        $this->db->where('analysis_id', $id)->delete(db_prefix() . 'solar_production_monthly');
        $this->db->where('analysis_id', $id)->delete(db_prefix() . 'solar_consumption');
        $this->db->where('analysis_id', $id)->delete(db_prefix() . 'solar_documents');
        $this->db->where('analysis_id', $id)->delete(db_prefix() . 'solar_analyses');
        $this->db->trans_complete();
        set_alert($this->db->trans_status() ? 'success' : 'danger', $this->db->trans_status() ? _l('solar_pro_deleted') : _l('solar_pro_delete_failed'));
        redirect(admin_url('solar_pro/analyses'));
    }

    /**
     * Solar Pro settings workspace. The same partial is also registered inside
     * the native CRM Settings screen, but this route keeps the normal admin
     * header/quick-links available when Solar Pro Settings is opened from the
     * module menu.
     */
    public function settings()
    {
        if (!is_admin() && !staff_can('manage_settings', SOLAR_PRO_MODULE_NAME)) {
            access_denied(SOLAR_PRO_MODULE_NAME);
        }
        $data['title'] = _l('solar_pro_settings');
        $this->load->view('admin/settings_page', $data);
    }

    public function settings_save()
    {
        if (!is_admin() && !staff_can('manage_settings', SOLAR_PRO_MODULE_NAME)) {
            access_denied(SOLAR_PRO_MODULE_NAME);
        }

        if (!$this->input->post()) {
            redirect(admin_url('solar_pro/settings'));
        }

        $posted = $this->input->post('settings', false);
        if (!is_array($posted)) {
            $posted = [];
        }

        $allowed = solar_pro_settings_keys();
        $secretKeys = solar_pro_secret_setting_keys();
        $booleanKeys = ['solar_pro_public_calculator_enabled', 'solar_pro_public_create_lead'];

        foreach ($allowed as $key) {
            if (in_array($key, $booleanKeys, true)) {
                update_option($key, isset($posted[$key]) && (string) $posted[$key] === '1' ? '1' : '0');
                continue;
            }

            if (!array_key_exists($key, $posted)) {
                continue;
            }

            $value = is_string($posted[$key]) ? trim($posted[$key]) : $posted[$key];

            // Never erase saved API credentials just because a password field
            // was left empty on a later settings update.
            if (in_array($key, $secretKeys, true) && $value === '') {
                continue;
            }

            update_option($key, $value);
        }

        set_alert('success', _l('settings_updated'));
        $tab = preg_replace('/[^a-z_]/', '', (string) $this->input->post('solar_active_tab', true));
        if (!in_array($tab, ['general', 'production', 'panels', 'financial', 'google', 'enphase', 'portal'], true)) {
            $tab = 'general';
        }
        $ref = (string) $this->input->server('HTTP_REFERER');
        if (strpos($ref, '/admin/settings') !== false) {
            redirect(admin_url('settings?group=solar_pro&solar_tab=' . rawurlencode($tab) . '#solar_' . $tab));
        }
        redirect(admin_url('solar_pro/settings?solar_tab=' . rawurlencode($tab) . '#solar_' . $tab));
    }

    public function health()
    {
        if (!is_admin()) {
            access_denied('solar_pro');
        }

        $module = $this->app_modules->get('solar_pro');
        $requiredTables = [
            'solar_utilities',
            'solar_utility_rates',
            'solar_equipment',
            'solar_analyses',
            'solar_consumption',
            'solar_production_monthly',
            'solar_contract_templates',
            'solar_contracts',
            'solar_contract_initials',
            'solar_contract_comments',
            'solar_proposals',
            'solar_proposal_comments',
            'solar_documents',
            'solar_api_logs',
        ];

        $tables = [];
        foreach ($requiredTables as $table) {
            $tables[$table] = $this->db->table_exists(db_prefix() . $table);
        }

        $dbModule = $this->db->where('module_name', 'solar_pro')->get(db_prefix() . 'modules')->row_array();

        $data['title'] = _l('solar_pro_health');
        $data['health'] = [
            'file_version' => $module['headers']['version'] ?? SOLAR_PRO_VERSION,
            'installed_version' => $dbModule['installed_version'] ?? '',
            'active' => isset($dbModule['active']) ? (int) $dbModule['active'] : 0,
            'upgrade_required' => $this->app_modules->is_database_upgrade_required('solar_pro'),
            'tables' => $tables,
            'settings_registered' => get_option('solar_pro_default_panel_watts') !== false,
            'admin_url' => admin_url('solar_pro'),
            'public_url' => site_url('solar_pro/estimate'),
        ];
        $this->load->view('admin/health', $data);
    }

}
