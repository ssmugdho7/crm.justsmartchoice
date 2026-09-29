<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_enterprise_core extends AdminController
{
    public function __construct(){parent::__construct();$this->load->model('smart_choice_enterprise_core_model');$this->load->library('smart_choice_enterprise_core/Enterprise_audit');$this->load->library('smart_choice_enterprise_core/Enterprise_events');}
    public function index(){ $this->requirePermission('view'); try{$summary=$this->smart_choice_enterprise_core_model->get_summary();$domains=$this->smart_choice_enterprise_core_model->get_dashboard_domain_counts();}catch(Throwable $e){log_message('error','Smart Choice dashboard: '.$e->getMessage());$summary=[];$domains=[];} $this->load->view('dashboard',['title'=>_l('enterprise_dashboard'),'summary'=>$summary,'domains'=>$domains]); }

    public function customer_360(){ $this->requirePermission('view_customer_360');$search=trim((string)$this->input->get('search',true));$data=['title'=>_l('enterprise_customer_360'),'rows'=>$this->smart_choice_enterprise_core_model->get_customer_360_list($search),'search'=>$search];$this->load->view('customer_360',$data); }
    public function customer_360_view($clientId){ $this->requirePermission('view_customer_360');$clientId=(int)$clientId;$record=$this->smart_choice_enterprise_core_model->get_customer_360_record($clientId);if(!$record)show_404();$data=['title'=>_l('enterprise_customer_360'),'record'=>$record,'properties'=>$this->smart_choice_enterprise_core_model->get_customer_properties($clientId),'household'=>$this->smart_choice_enterprise_core_model->get_customer_household($clientId),'timeline'=>$this->smart_choice_enterprise_core_model->get_customer_timeline($clientId),'score'=>$this->smart_choice_enterprise_core_model->get_latest_customer_score($clientId)];$this->load->view('customer_360_view',$data); }
    public function customer_360_property($clientId){$this->requirePermission('manage_customer_360');$clientId=(int)$clientId;if($this->input->post()){$data=['client_id'=>$clientId,'property_name'=>trim((string)$this->input->post('property_name',true)),'property_type'=>trim((string)$this->input->post('property_type',true)),'address'=>trim((string)$this->input->post('address',true)),'city'=>trim((string)$this->input->post('city',true)),'state'=>trim((string)$this->input->post('state',true)),'zip'=>trim((string)$this->input->post('zip',true)),'is_primary'=>$this->input->post('is_primary')?1:0,'notes'=>trim((string)$this->input->post('notes',true))];if($data['property_name']!==''){$id=$this->smart_choice_enterprise_core_model->add_customer_property($data);$this->enterprise_audit->record('customer.property.create','customer_property',(string)$id,[],$data);set_alert('success',_l('enterprise_record_saved'));}}redirect(admin_url('smart_choice_enterprise_core/customer_360_view/'.$clientId));}
    public function customer_360_household($clientId){$this->requirePermission('manage_customer_360');$clientId=(int)$clientId;if($this->input->post()){$data=['client_id'=>$clientId,'full_name'=>trim((string)$this->input->post('full_name',true)),'relationship'=>trim((string)$this->input->post('relationship',true)),'email'=>trim((string)$this->input->post('email',true)),'phone'=>trim((string)$this->input->post('phone',true)),'is_emergency_contact'=>$this->input->post('is_emergency_contact')?1:0,'is_authorized'=>$this->input->post('is_authorized')?1:0,'notes'=>trim((string)$this->input->post('notes',true))];if($data['full_name']!==''){$id=$this->smart_choice_enterprise_core_model->add_customer_household($data);$this->enterprise_audit->record('customer.household.create','customer_household',(string)$id,[],$data);set_alert('success',_l('enterprise_record_saved'));}}redirect(admin_url('smart_choice_enterprise_core/customer_360_view/'.$clientId));}
    public function customer_360_timeline($clientId){$this->requirePermission('manage_customer_360');$clientId=(int)$clientId;if($this->input->post()){$data=['client_id'=>$clientId,'event_type'=>trim((string)$this->input->post('event_type',true))?:'note','title'=>trim((string)$this->input->post('title',true)),'description'=>trim((string)$this->input->post('description',true)),'event_at'=>trim((string)$this->input->post('event_at',true))?:date('Y-m-d H:i:s')];if($data['title']!==''){$id=$this->smart_choice_enterprise_core_model->add_customer_timeline($data);$this->enterprise_audit->record('customer.timeline.create','customer_timeline',(string)$id,[],$data);set_alert('success',_l('enterprise_record_saved'));}}redirect(admin_url('smart_choice_enterprise_core/customer_360_view/'.$clientId));}
    public function global_search(){ $this->requirePermission('use_global_search');$query=trim((string)$this->input->get('query',true));$data=['title'=>_l('enterprise_global_search'),'query'=>$query,'results'=>$query!==''?$this->smart_choice_enterprise_core_model->global_search($query):[]];$this->load->view('global_search',$data); }
    public function pin_record(){ $this->requirePermission('view');if($this->input->post()){$this->smart_choice_enterprise_core_model->toggle_pin(get_staff_user_id(),trim((string)$this->input->post('record_type',true)),trim((string)$this->input->post('record_id',true)),trim((string)$this->input->post('label',true)),trim((string)$this->input->post('url',true)));set_alert('success',_l('enterprise_pin_updated'));}redirect($this->agent->referrer()?:admin_url('smart_choice_enterprise_core')); }
    public function export_customer_360(){ $this->requirePermission('export');$rows=$this->smart_choice_enterprise_core_model->get_customer_360_list(trim((string)$this->input->get('search',true)));$this->csv('customer-360',['Customer ID','Company','Primary Contact','Email','Phone','Projects','Invoices','Lifetime Value'],array_map(function($r){return[$r['userid'],$r['company'],$r['contact_name'],$r['email'],$r['phone'],$r['project_count'],$r['invoice_count'],$r['lifetime_value']];},$rows)); }
    public function customer_sample_header(){ $this->requirePermission('view_global');$this->csv('customers-sample-header',['company','firstname','lastname','email','phonenumber','address','city','state','zip'],[]); }
    public function import_customers(){ $this->requirePermission('create');$result=$this->smart_choice_enterprise_core_model->import_customers_csv($_FILES['import_file']??[]);set_alert($result['imported']?'success':'warning',sprintf(_l('enterprise_import_complete'),$result['imported'],$result['skipped']));redirect(admin_url('smart_choice_enterprise_core/customer_360')); }

    public function foundation(){ $this->requirePermission('view');$data=['title'=>_l('enterprise_foundation'),'summary'=>$this->smart_choice_enterprise_core_model->get_foundation_summary()];$this->load->view('foundation',$data); }
    public function queue(){ $this->requirePermission('view');$status=$this->input->get('status',true)?:'';$data=['title'=>_l('enterprise_queue'),'rows'=>$this->smart_choice_enterprise_core_model->get_queue_jobs($status),'status'=>$status];$this->load->view('queue',$data); }
    public function queue_retry($id){$this->requirePermission('manage_queue');$this->smart_choice_enterprise_core_model->retry_job((int)$id);$this->enterprise_audit->record('queue.retry','queue_job',(string)$id);set_alert('success',_l('enterprise_job_requeued'));redirect(admin_url('smart_choice_enterprise_core/queue'));}
    public function queue_delete($id){$this->requirePermission('delete');$this->smart_choice_enterprise_core_model->delete_job((int)$id);$this->enterprise_audit->record('queue.delete','queue_job',(string)$id);set_alert('success',_l('deleted',_l('enterprise_queue_job')));redirect(admin_url('smart_choice_enterprise_core/queue'));}
    public function queue_test(){ $this->requirePermission('manage_queue');$this->load->library('smart_choice_enterprise_core/Enterprise_queue');$id=$this->enterprise_queue->push('enterprise.noop',['requested_by'=>get_staff_user_id()],'default',null,100);$this->enterprise_events->dispatch('enterprise.queue.test_created',['job_id'=>$id],'queue_job',$id);set_alert($id?'success':'danger',$id?_l('enterprise_test_job_created'):_l('enterprise_action_failed'));redirect(admin_url('smart_choice_enterprise_core/queue')); }
    public function feature_flags(){ $this->requirePermission('view');$data=['title'=>_l('enterprise_feature_flags'),'rows'=>$this->smart_choice_enterprise_core_model->get_feature_flags()];$this->load->view('feature_flags',$data); }
    public function feature_toggle($id){$this->requirePermission('manage_features');$enabled=(int)$this->input->post('enabled');$this->smart_choice_enterprise_core_model->update_feature_flag((int)$id,$enabled);$this->enterprise_audit->record('feature.toggle','feature_flag',(string)$id,[],['is_enabled'=>$enabled]);set_alert('success',_l('enterprise_feature_updated'));redirect(admin_url('smart_choice_enterprise_core/feature_flags'));}
    public function audit_log(){ $this->requirePermission('view_audit');$data=['title'=>_l('enterprise_audit_log'),'rows'=>$this->smart_choice_enterprise_core_model->get_audit_logs()];$this->load->view('audit_log',$data); }
    public function field_permissions(){ $this->requirePermission('view');if($this->input->post()){$this->requirePermission('manage_permissions');$d=['resource_type'=>trim((string)$this->input->post('resource_type',true)),'field_key'=>trim((string)$this->input->post('field_key',true)),'role_id'=>(int)$this->input->post('role_id'),'staff_id'=>0,'can_view'=>$this->input->post('can_view_global')?1:0,'can_view_own'=>$this->input->post('can_view_own')?1:0,'can_view_global'=>$this->input->post('can_view_global')?1:0,'can_edit'=>$this->input->post('can_edit')?1:0,'can_export'=>$this->input->post('can_export')?1:0];if($d['resource_type']!==''&&$d['field_key']!==''){$this->smart_choice_enterprise_core_model->save_field_permission($d);$this->enterprise_audit->record('field_permission.save','field_permission',$d['resource_type'].':'.$d['field_key'],[],$d);set_alert('success',_l('enterprise_permission_saved'));}redirect(admin_url('smart_choice_enterprise_core/field_permissions'));}$data=['title'=>_l('enterprise_field_permissions'),'rows'=>$this->smart_choice_enterprise_core_model->get_field_permissions(),'roles'=>$this->smart_choice_enterprise_core_model->get_roles()];$this->load->view('field_permissions',$data);}
    public function field_permission_delete($id){$this->requirePermission('delete');$this->smart_choice_enterprise_core_model->delete_field_permission((int)$id);$this->enterprise_audit->record('field_permission.delete','field_permission',(string)$id);set_alert('success',_l('deleted',_l('enterprise_field_permission')));redirect(admin_url('smart_choice_enterprise_core/field_permissions'));}
    public function staff_images(){ $this->requirePermission('view');try{$rows=$this->smart_choice_enterprise_core_model->get_staff_image_health();}catch(Throwable $e){log_message('error','Staff image health: '.$e->getMessage());$rows=[];set_alert('warning',_l('enterprise_health_partial'));}$this->load->view('staff_images',['title'=>_l('staff_image_health'),'rows'=>$rows,'summary'=>$this->smart_choice_enterprise_core_model->summarize_staff_images($rows)]); }
    public function health(){ $this->requirePermission('view');try{$checks=$this->smart_choice_enterprise_core_model->get_system_health();}catch(Throwable $e){log_message('error','System health: '.$e->getMessage());$checks=[['name'=>'Module Runtime','passed'=>false,'detail'=>$e->getMessage()]];}$this->load->view('health',['title'=>_l('system_health'),'checks'=>$checks]); }
    public function export_staff_images(){ $this->requirePermission('export');$this->csv('staff-image-health',['Staff ID','Employee','Email','Database Filename','Resolved Path','Status'],array_map(function($r){return[$r['staffid'],$r['employee'],$r['email'],$r['profile_image'],$r['resolved_path'],$r['status']];},$this->smart_choice_enterprise_core_model->get_staff_image_health())); }
    public function export_queue(){ $this->requirePermission('export');$rows=$this->smart_choice_enterprise_core_model->get_queue_jobs($this->input->get('status',true)?:'');$this->csv('enterprise-queue',['ID','Queue','Handler','Status','Priority','Attempts','Available At','Created At','Error'],array_map(function($r){return[$r['id'],$r['queue_name'],$r['handler'],$r['status'],$r['priority'],$r['attempts'].'/'.$r['max_attempts'],$r['available_at'],$r['created_at'],$r['last_error']];},$rows)); }
    public function export_audit(){ $this->requirePermission('export');$rows=$this->smart_choice_enterprise_core_model->get_audit_logs();$this->csv('enterprise-audit',['ID','Action','Entity Type','Entity ID','Staff ID','IP Address','Created At'],array_map(function($r){return[$r['id'],$r['action'],$r['entity_type'],$r['entity_id'],$r['staff_id'],$r['ip_address'],$r['created_at']];},$rows)); }

    public function sales_operations(){ $this->requirePermission('view_sales');$data=$this->smart_choice_enterprise_core_model->get_sales_workspace();$data['title']=_l('enterprise_sales_operations');$this->load->view('sales_operations',$data); }
    public function field_operations(){ $this->requirePermission('view_field_ops');$this->domainPage('field_operations','enterprise_field_operations',['sce_field_visits','sce_time_clock','sce_crews','sce_daily_logs']); }
    public function dispatch(){ $this->requirePermission('view_dispatch');if($this->input->post()){ $this->requirePermission('manage_dispatch');$ok=$this->smart_choice_enterprise_core_model->create_dispatch_assignment($this->input->post());set_alert($ok?'success':'danger',$ok?_l('enterprise_dispatch_created'):_l('enterprise_action_failed'));redirect(admin_url('smart_choice_enterprise_core/dispatch'));}$data=$this->smart_choice_enterprise_core_model->get_dispatch_workspace();$data['title']=_l('enterprise_dispatch');$this->load->view('dispatch',$data); }
    public function equipment_inventory(){ $this->requirePermission('view_assets');$this->domainPage('equipment_inventory','enterprise_equipment_inventory',['sce_equipment','sce_equipment_service','sce_inventory_locations','sce_inventory_items','sce_inventory_stock','sce_inventory_movements']); }
    public function project_controls(){ $this->requirePermission('view_project_controls');$this->domainPage('project_controls','enterprise_project_controls',['sce_project_phases','sce_rfis','sce_submittals','sce_permits','sce_change_orders']); }
    public function document_center(){ $this->requirePermission('view_project_controls');$this->domainPage('document_center','enterprise_document_center',['sce_document_versions','sce_document_approvals']); }
    private function domainPage($view,$titleKey,array $tables){$data=['title'=>_l($titleKey),'cards'=>$this->smart_choice_enterprise_core_model->get_domain_cards($tables),'recent'=>$this->smart_choice_enterprise_core_model->get_domain_recent($tables)];$this->load->view($view,$data);}
    private function csv($name,array $headers,array $rows){header('Content-Type: text/csv; charset=UTF-8');header('Content-Disposition: attachment; filename="'.$name.'-'.date('Y-m-d-His').'.csv"');$o=fopen('php://output','wb');fwrite($o,"\xEF\xBB\xBF");fputcsv($o,$headers);foreach($rows as$r)fputcsv($o,$r);fclose($o);exit;}

    public function repair_schema()
    {
        $this->requirePermission('manage');
        require_once module_dir_path(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, 'libraries/Enterprise_schema.php');
        Enterprise_schema::install();
        $this->enterprise_audit->record('schema.repair', 'enterprise_schema', SMART_CHOICE_ENTERPRISE_CORE_VERSION);
        set_alert('success', _l('enterprise_schema_repaired'));
        redirect(admin_url('smart_choice_enterprise_core/health'));
    }

    public function repair_staff_images()
    {
        $this->requirePermission('manage');
        $this->smart_choice_enterprise_core_model->repairStaffImageRoot();
        set_alert('success', _l('enterprise_image_folder_repaired'));
        redirect(admin_url('smart_choice_enterprise_core/staff_images'));
    }

    public function data_manager($suffix = '')
    {
        $this->requirePermission('view_global');
        if (!$this->smart_choice_enterprise_core_model->isAllowedEnterpriseTable($suffix)) show_404();
        $data = $this->smart_choice_enterprise_core_model->getEnterpriseTableData($suffix);
        $data['title'] = _l('enterprise_data_manager');
        $data['suffix'] = $suffix;
        $this->load->view('data_manager', $data);
    }

    public function export_table($suffix)
    {
        $this->requirePermission('export');
        $data = $this->smart_choice_enterprise_core_model->getEnterpriseTableData($suffix, 2000);
        if (!$data['exists']) show_404();
        $rows = [];
        foreach ($data['rows'] as $row) { $rows[] = array_map(function($field) use ($row) { return $row[$field] ?? ''; }, $data['fields']); }
        $this->csv($suffix, $data['fields'], $rows);
    }

    public function sample_header($suffix)
    {
        $this->requirePermission('view_global');
        $data = $this->smart_choice_enterprise_core_model->getEnterpriseTableData($suffix, 1);
        if (!$data['exists']) show_404();
        $fields = array_values(array_filter($data['fields'], function($field){ return $field !== 'id'; }));
        $this->csv($suffix . '-sample-header', $fields, []);
    }

    public function import_table($suffix)
    {
        $this->requirePermission('create');
        if (!$this->smart_choice_enterprise_core_model->isAllowedEnterpriseTable($suffix)) show_404();
        $imported = 0; $skipped = 0;
        if (!empty($_FILES['import_file']['tmp_name']) && is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            $handle = fopen($_FILES['import_file']['tmp_name'], 'rb');
            $headers = $handle ? fgetcsv($handle) : false;
            if ($headers) {
                $headers = array_map(function($v){ return trim((string)$v); }, $headers);
                while (($values = fgetcsv($handle)) !== false) {
                    $row = [];
                    foreach ($headers as $i=>$header) if ($header !== '') $row[$header] = $values[$i] ?? '';
                    if ($this->smart_choice_enterprise_core_model->insertEnterpriseRow($suffix, $row)) $imported++; else $skipped++;
                }
            }
            if ($handle) fclose($handle);
        }
        $this->enterprise_audit->record('table.import', $suffix, null, [], ['imported'=>$imported,'skipped'=>$skipped]);
        set_alert('success', sprintf(_l('enterprise_import_complete'), $imported, $skipped));
        redirect(admin_url('smart_choice_enterprise_core/data_manager/' . $suffix));
    }

    public function mass_delete_table($suffix)
    {
        $this->requirePermission('delete');
        $ids = (array)$this->input->post('ids');
        $deleted = $this->smart_choice_enterprise_core_model->massDeleteEnterpriseRows($suffix, $ids);
        $this->enterprise_audit->record('table.mass_delete', $suffix, null, [], ['deleted'=>$deleted]);
        set_alert('success', _l('deleted', $deleted));
        redirect(admin_url('smart_choice_enterprise_core/data_manager/' . $suffix));
    }

    public function communications(){ $this->requirePermission('view_communications'); $this->domainPage('communications','enterprise_communications',['sce_communications','sce_call_logs','sce_call_recordings']); }
    public function call_center(){ $this->requirePermission('view_communications'); $data=['title'=>_l('enterprise_call_center'),'summary'=>$this->smart_choice_enterprise_core_model->get_call_center_summary(),'rows'=>$this->smart_choice_enterprise_core_model->getEnterpriseTableData('sce_call_logs',500)]; $this->load->view('call_center',$data); }
    public function call_settings(){ $this->requirePermission('manage_communications'); if($this->input->post()){foreach(['enterprise_twilio_account_sid','enterprise_twilio_auth_token','enterprise_twilio_phone_number','enterprise_twilio_record_calls'] as $k){$v=(string)$this->input->post($k,true); if(get_option($k)==='')add_option($k,$v);else update_option($k,$v);}set_alert('success',_l('settings_updated'));redirect(admin_url('smart_choice_enterprise_core/call_center'));} $this->load->view('call_settings',['title'=>_l('enterprise_call_settings')]); }
    public function initiate_call(){ $this->requirePermission('manage_communications'); $to=trim((string)$this->input->post('to',true)); if($to===''){set_alert('warning',_l('enterprise_phone_required'));redirect(admin_url('smart_choice_enterprise_core/call_center'));} $sid=get_option('enterprise_twilio_account_sid');$token=get_option('enterprise_twilio_auth_token');$from=get_option('enterprise_twilio_phone_number'); try{ if(!class_exists('Twilio\\Rest\\Client')) throw new Exception('Twilio SDK is unavailable.'); $client=new Twilio\Rest\Client($sid,$token);$twiml=site_url('smart_choice_enterprise_core/enterprise_360_webhook/twiml?to='.rawurlencode($to));$call=$client->calls->create($to,$from,['url'=>$twiml,'record'=>get_option('enterprise_twilio_record_calls')==='1','statusCallback'=>site_url('smart_choice_enterprise_core/enterprise_360_webhook/status'),'statusCallbackEvent'=>['initiated','ringing','answered','completed']]);$this->smart_choice_enterprise_core_model->insertEnterpriseRow('sce_call_logs',['call_sid'=>$call->sid,'direction'=>'outbound','from_number'=>$from,'to_number'=>$to,'status'=>'initiated','staff_id'=>get_staff_user_id(),'started_at'=>date('Y-m-d H:i:s'),'created_at'=>date('Y-m-d H:i:s')]);set_alert('success',_l('enterprise_call_started'));}catch(Throwable $e){log_message('error','Enterprise 360 Twilio call failed: '.$e->getMessage());set_alert('danger',$e->getMessage());}redirect(admin_url('smart_choice_enterprise_core/call_center')); }
    public function marketing(){ $this->requirePermission('view_marketing'); $this->domainPage('marketing','enterprise_marketing',['sce_campaigns','sce_campaign_attribution','sce_review_requests']); }
    public function workflows(){ $this->requirePermission('view_workflows'); $this->domainPage('workflows','enterprise_workflows',['sce_workflows','sce_workflow_steps','sce_workflow_runs']); }
    public function reporting(){ $this->requirePermission('view_reports');$data=$this->smart_choice_enterprise_core_model->get_reporting_workspace();$data['title']=_l('enterprise_reporting');$this->load->view('reporting',$data); }
    public function qr_center(){ $this->requirePermission('manage_qr'); $data=['title'=>_l('enterprise_qr_center'),'rows'=>$this->smart_choice_enterprise_core_model->get_qr_codes(),'generated'=>null]; if($this->input->post()){ $type=trim((string)$this->input->post('qr_type',true));$name=trim((string)$this->input->post('name',true));$payload=trim((string)$this->input->post('payload',false));$dynamic=$this->input->post('is_dynamic')?1:0; if($payload!==''){require_once APPPATH.'vendor/tecnickcom/tcpdf/include/barcodes/qrcode.php';$barcode=new TCPDF2DBarcode($payload,'QRCODE,H');$png=$barcode->getBarcodePngData(8,8);$data['generated']='data:image/png;base64,'.base64_encode($png);$id=$this->smart_choice_enterprise_core_model->save_qr_code(['name'=>$name?:ucwords(str_replace('_',' ',$type)),'qr_type'=>$type,'payload'=>$payload,'is_dynamic'=>$dynamic,'status'=>'active','scan_count'=>0]);$data['generated_id']=$id;}} $this->load->view('qr_center',$data); }
    public function qr_png($id){ $this->requirePermission('manage_qr');$t=db_prefix().'sce_qr_codes';$row=$this->db->where('id',(int)$id)->get($t)->row_array();if(!$row)show_404();require_once APPPATH.'vendor/tecnickcom/tcpdf/include/barcodes/qrcode.php';$barcode=new TCPDF2DBarcode($row['payload'],'QRCODE,H');header('Content-Type: image/png');header('Content-Disposition: attachment; filename="enterprise-qr-'.$id.'.png"');echo $barcode->getBarcodePngData(10,10);exit; }
    public function security_center(){ $this->requirePermission('manage_security'); $this->domainPage('security_center','enterprise_security',['sce_security_rules','sce_login_audit','sce_field_permissions','sce_audit_log']); }
    public function performance_center(){ $this->requirePermission('manage_performance'); $data=['title'=>_l('enterprise_performance'),'cards'=>$this->smart_choice_enterprise_core_model->get_domain_cards(['sce_performance_metrics','sce_queue_jobs']),'php'=>PHP_VERSION,'db'=>$this->db->version(),'memory'=>ini_get('memory_limit'),'execution'=>ini_get('max_execution_time')]; $this->load->view('performance_center',$data); }

    public function foundation_settings(){ $this->requirePermission('manage'); if($this->input->post()){foreach(['enterprise_api_base_url','enterprise_api_key','enterprise_api_status'] as $key){$value=(string)$this->input->post($key,true);get_option($key)===''?add_option($key,$value):update_option($key,$value);}set_alert('success',_l('settings_updated'));}redirect(admin_url('smart_choice_enterprise_core/foundation')); }
    public function cron_guide(){ $this->requirePermission('view');$this->load->view('cron_guide',['title'=>_l('enterprise_cron_guide')]); }
    public function send_sms(){ $this->requirePermission('manage_communications');$to=trim((string)$this->input->post('to',true));$body=trim((string)$this->input->post('message',true));try{if(!class_exists('Twilio\Rest\Client'))throw new Exception('Twilio SDK is unavailable.');$client=new Twilio\Rest\Client(get_option('enterprise_twilio_account_sid'),get_option('enterprise_twilio_auth_token'));$msg=$client->messages->create($to,['from'=>get_option('enterprise_twilio_phone_number'),'body'=>$body]);$this->smart_choice_enterprise_core_model->insertEnterpriseRow('sce_communications',['channel'=>'sms','direction'=>'outbound','recipient'=>$to,'message'=>$body,'status'=>'sent','external_id'=>$msg->sid,'created_at'=>date('Y-m-d H:i:s')]);set_alert('success',_l('enterprise_sms_sent'));}catch(Throwable $e){log_message('error','Enterprise SMS: '.$e->getMessage());set_alert('danger',$e->getMessage());}redirect(admin_url('smart_choice_enterprise_core/call_center')); }

    private function requirePermission($capability){if(!has_permission(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME,'',$capability))access_denied(_l('enterprise_360'));}
}
