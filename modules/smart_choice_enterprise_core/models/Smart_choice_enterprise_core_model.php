<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_enterprise_core_model extends App_Model
{
    public function get_summary()
    {
        $rows = $this->get_staff_image_health();
        $images = $this->summarize_staff_images($rows);
        return [
            'staff_total' => $this->safeCount(db_prefix() . 'staff'),
            'staff_with_images' => $images['total'],
            'staff_images_found' => $images['found'],
            'staff_images_missing' => $images['missing'],
            'active_modules' => $this->count_active_modules(),
            'php_version' => PHP_VERSION,
            'database_version' => (string) $this->db->version(),
            'pending_jobs' => $this->countWhere('sce_queue_jobs', 'status', 'pending'),
            'failed_jobs' => $this->countWhere('sce_queue_jobs', 'status', 'failed'),
            'enabled_features' => $this->countWhere('sce_feature_flags', 'is_enabled', 1),
            'audit_records' => $this->countTable('sce_audit_log'),
        ];
    }
    public function get_foundation_summary(){ return ['events'=>$this->countTable('sce_events'),'queue_total'=>$this->countTable('sce_queue_jobs'),'feature_flags'=>$this->countTable('sce_feature_flags'),'audit_records'=>$this->countTable('sce_audit_log'),'field_rules'=>$this->countTable('sce_field_permissions'),'api_clients'=>$this->countTable('sce_api_clients'),'cron_last_run'=>(string)get_option('last_cron_run'),'module_version'=>(string)get_option('smart_choice_enterprise_core_version')]; }
    public function get_queue_jobs($status=''){ $t=db_prefix().'sce_queue_jobs'; if(!$this->db->table_exists($t))return[]; $this->db->from($t); if($status!=='')$this->db->where('status',$status); return $this->db->order_by('id','DESC')->limit(500)->get()->result_array(); }
    public function retry_job($id){$t=db_prefix().'sce_queue_jobs'; return $this->db->where('id',(int)$id)->update($t,['status'=>'pending','attempts'=>0,'reserved_at'=>null,'completed_at'=>null,'failed_at'=>null,'last_error'=>null,'available_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);}
    public function delete_job($id){return $this->db->where('id',(int)$id)->delete(db_prefix().'sce_queue_jobs');}
    public function get_feature_flags(){ $t=db_prefix().'sce_feature_flags'; return $this->db->table_exists($t)?$this->db->order_by('name','ASC')->get($t)->result_array():[]; }
    public function update_feature_flag($id,$enabled){return $this->db->where('id',(int)$id)->update(db_prefix().'sce_feature_flags',['is_enabled'=>$enabled?1:0,'updated_by'=>get_staff_user_id()?:null,'updated_at'=>date('Y-m-d H:i:s')]);}
    public function get_audit_logs(){ $t=db_prefix().'sce_audit_log'; return $this->db->table_exists($t)?$this->db->order_by('id','DESC')->limit(1000)->get($t)->result_array():[]; }
    public function get_field_permissions(){ $t=db_prefix().'sce_field_permissions'; return $this->db->table_exists($t)?$this->db->order_by('resource_type','ASC')->order_by('field_key','ASC')->get($t)->result_array():[]; }
    public function save_field_permission(array $d){$t=db_prefix().'sce_field_permissions'; $where=['resource_type'=>$d['resource_type'],'field_key'=>$d['field_key'],'role_id'=>(int)$d['role_id'],'staff_id'=>(int)$d['staff_id']]; $row=$this->db->where($where)->get($t)->row_array(); $data=array_merge($where,['can_view'=>(int)$d['can_view'],'can_view_own'=>(int)($d['can_view_own']??0),'can_view_global'=>(int)($d['can_view_global']??$d['can_view']),'can_edit'=>(int)$d['can_edit'],'can_export'=>(int)$d['can_export'],'updated_by'=>get_staff_user_id()?:null,'updated_at'=>date('Y-m-d H:i:s')]); if($row)return $this->db->where('id',$row['id'])->update($t,$data); $data['created_by']=get_staff_user_id()?:null;$data['created_at']=date('Y-m-d H:i:s');return $this->db->insert($t,$data);}
    public function delete_field_permission($id){return $this->db->where('id',(int)$id)->delete(db_prefix().'sce_field_permissions');}
    public function get_roles(){return $this->db->select('roleid,name')->order_by('name','ASC')->get(db_prefix().'roles')->result_array();}
    public function get_staff_image_health()
    {
        $staff = $this->db
            ->select('staffid, firstname, lastname, email, profile_image')
            ->from(db_prefix() . 'staff')
            ->where('profile_image IS NOT NULL', null, false)
            ->where('profile_image !=', '')
            ->order_by('firstname', 'ASC')
            ->order_by('lastname', 'ASC')
            ->get()
            ->result_array();

        $rows = [];
        foreach ($staff as $member) {
            $resolved = $this->resolve_staff_image((int) $member['staffid'], (string) $member['profile_image']);
            $nativeUrl = function_exists('staff_profile_image_url')
                ? staff_profile_image_url((int) $member['staffid'], 'small')
                : base_url('assets/images/user-placeholder.jpg');

            $rows[] = [
                'staffid'       => (int) $member['staffid'],
                'employee'      => trim($member['firstname'] . ' ' . $member['lastname']),
                'email'         => (string) $member['email'],
                'profile_image' => (string) $member['profile_image'],
                'resolved_path' => $resolved ?: _l('not_found'),
                'image_url'     => $resolved ? $this->filesystemPathToUrl($resolved) : $nativeUrl,
                'status'        => $resolved ? 'Found' : 'Missing',
            ];
        }

        return $rows;
    }

    public function get_system_health()
    {
        $checks = [];
        $checks[] = $this->check('PHP 8.5 compatibility target', version_compare(PHP_VERSION, '8.5.0', '>='), PHP_VERSION);
        $checks[] = $this->check('Current PHP supported by module', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION);
        $checks[] = $this->check('MySQL 5.7 compatibility target', version_compare($this->normalizeVersion($this->db->version()), '5.7.0', '>='), $this->db->version());
        $staffRoot = defined('STAFF_PROFILE_IMAGES_FOLDER') ? STAFF_PROFILE_IMAGES_FOLDER : FCPATH . 'uploads/staff_profile_images/';
        $checks[] = $this->check('Staff profile image directory', is_dir($staffRoot), $staffRoot);
        $checks[] = $this->check('Staff profile image directory writable', is_dir($staffRoot) && is_writable($staffRoot), $staffRoot);
        $checks[] = $this->check('Application logs directory writable', is_dir(APPPATH . 'logs') && is_writable(APPPATH . 'logs'), APPPATH . 'logs');
        $checks[] = $this->check('Modules directory available', is_dir(FCPATH . 'modules'), FCPATH . 'modules');
        $checks[] = $this->check('Cron configured in options', get_option('last_cron_run') !== '', (string) get_option('last_cron_run'));
        foreach ($this->enterpriseTableSuffixes() as $suffix) {
            $checks[] = $this->check('Enterprise table ' . str_replace('_', ' ', $suffix), $this->db->table_exists(db_prefix() . $suffix), db_prefix() . $suffix);
        }
        return $checks;
    }
    public function get_customer_360_list($search='')
    {
        $clients=db_prefix().'clients'; if(!$this->db->table_exists($clients))return[];
        $this->db->select('c.userid,c.company,c.phonenumber,c.city,c.state,c.datecreated');
        $this->db->from($clients.' c');
        if($search!==''){$this->db->group_start()->like('c.company',$search)->or_like('c.phonenumber',$search)->or_like('c.city',$search)->or_like('c.state',$search)->group_end();}
        $rows=$this->db->order_by('c.company','ASC')->limit(500)->get()->result_array();
        foreach($rows as &$r){$contacts=db_prefix().'contacts';$contact=$this->db->table_exists($contacts)?$this->db->select('firstname,lastname,email,phonenumber')->where('userid',(int)$r['userid'])->order_by('is_primary','DESC')->get($contacts)->row_array():null;$r['contact_name']=$contact?trim($contact['firstname'].' '.$contact['lastname']):'';$r['email']=$contact['email']??'';$r['phone']=$r['phonenumber']?:($contact['phonenumber']??'');$r['project_count']=$this->countClientRows('projects','clientid',(int)$r['userid']);$r['invoice_count']=$this->countClientRows('invoices','clientid',(int)$r['userid']);$r['lifetime_value']=$this->get_customer_lifetime_value((int)$r['userid']);}
        return $rows;
    }
    public function get_customer_360_record($clientId){$row=$this->db->where('userid',(int)$clientId)->get(db_prefix().'clients')->row_array();if(!$row)return null;$row['contacts']=$this->db->where('userid',(int)$clientId)->get(db_prefix().'contacts')->result_array();$row['project_count']=$this->countClientRows('projects','clientid',$clientId);$row['invoice_count']=$this->countClientRows('invoices','clientid',$clientId);$row['estimate_count']=$this->countClientRows('estimates','clientid',$clientId);$row['contract_count']=$this->countClientRows('contracts','client',$clientId);$row['ticket_count']=$this->countClientRows('tickets','userid',$clientId);$row['lifetime_value']=$this->get_customer_lifetime_value($clientId);return$row;}
    public function get_customer_properties($clientId){$t=db_prefix().'sce_customer_properties';return$this->db->table_exists($t)?$this->db->where('client_id',(int)$clientId)->order_by('is_primary','DESC')->order_by('id','DESC')->get($t)->result_array():[];}
    public function get_customer_household($clientId){$t=db_prefix().'sce_customer_household';return$this->db->table_exists($t)?$this->db->where('client_id',(int)$clientId)->order_by('is_emergency_contact','DESC')->order_by('full_name','ASC')->get($t)->result_array():[];}
    public function get_customer_timeline($clientId){$t=db_prefix().'sce_customer_timeline';$rows=$this->db->table_exists($t)?$this->db->where('client_id',(int)$clientId)->order_by('event_at','DESC')->limit(250)->get($t)->result_array():[];return$rows;}
    public function get_latest_customer_score($clientId){$t=db_prefix().'sce_customer_scores';$row=$this->db->table_exists($t)?$this->db->where('client_id',(int)$clientId)->order_by('calculated_at','DESC')->get($t)->row_array():null;return$row?:['customer_score'=>0,'health_score'=>0,'lifetime_value'=>$this->get_customer_lifetime_value($clientId),'risk_level'=>'normal'];}
    public function add_customer_property(array $d){$d['created_by']=get_staff_user_id()?:null;$d['created_at']=date('Y-m-d H:i:s');if(!empty($d['is_primary']))$this->db->where('client_id',(int)$d['client_id'])->update(db_prefix().'sce_customer_properties',['is_primary'=>0]);$this->db->insert(db_prefix().'sce_customer_properties',$d);return$this->db->insert_id();}
    public function add_customer_household(array $d){$d['created_by']=get_staff_user_id()?:null;$d['created_at']=date('Y-m-d H:i:s');$this->db->insert(db_prefix().'sce_customer_household',$d);return$this->db->insert_id();}
    public function add_customer_timeline(array $d){$d['staff_id']=get_staff_user_id()?:null;$d['created_at']=date('Y-m-d H:i:s');$this->db->insert(db_prefix().'sce_customer_timeline',$d);return$this->db->insert_id();}
    public function toggle_pin($staffId,$type,$id,$label,$url){$t=db_prefix().'sce_pinned_records';$where=['staff_id'=>(int)$staffId,'record_type'=>$type,'record_id'=>$id];$row=$this->db->where($where)->get($t)->row_array();if($row)return$this->db->where('id',$row['id'])->delete($t);return$this->db->insert($t,array_merge($where,['label'=>$label,'url'=>$url,'created_at'=>date('Y-m-d H:i:s')]));}
    public function global_search($query)
    {
        $query=trim($query);if($query==='')return[];$out=[];$limit=20;
        if(has_permission('customers','','view')){$this->db->select('userid AS id,company AS title,phonenumber AS subtitle')->from(db_prefix().'clients')->group_start()->like('company',$query)->or_like('phonenumber',$query)->or_like('city',$query)->or_like('state',$query)->group_end()->limit($limit);foreach($this->db->get()->result_array()as$r)$out[]=['type'=>'Customer','id'=>$r['id'],'title'=>$r['title'],'subtitle'=>$r['subtitle'],'url'=>admin_url('clients/client/'.$r['id'])];}
        if(has_permission('leads','','view')){$this->db->select('id,name AS title,email,phonenumber')->from(db_prefix().'leads')->group_start()->like('name',$query)->or_like('email',$query)->or_like('phonenumber',$query)->group_end()->limit($limit);foreach($this->db->get()->result_array()as$r)$out[]=['type'=>'Lead','id'=>$r['id'],'title'=>$r['title'],'subtitle'=>trim($r['email'].' '.$r['phonenumber']),'url'=>admin_url('leads/index/'.$r['id'])];}
        if(has_permission('projects','','view')){$this->db->select('id,name AS title,status')->from(db_prefix().'projects')->like('name',$query)->limit($limit);foreach($this->db->get()->result_array()as$r)$out[]=['type'=>'Project','id'=>$r['id'],'title'=>$r['title'],'subtitle'=>'','url'=>admin_url('projects/view/'.$r['id'])];}
        if(has_permission('tasks','','view')){$this->db->select('id,name AS title,status')->from(db_prefix().'tasks')->like('name',$query)->limit($limit);foreach($this->db->get()->result_array()as$r)$out[]=['type'=>'Task','id'=>$r['id'],'title'=>$r['title'],'subtitle'=>'','url'=>admin_url('tasks/view/'.$r['id'])];}
        if(has_permission('invoices','','view')){$this->db->select('id,number,status,total,clientid')->from(db_prefix().'invoices')->group_start()->like('number',$query)->or_like('clientnote',$query)->group_end()->limit($limit);foreach($this->db->get()->result_array()as$r)$out[]=['type'=>'Invoice','id'=>$r['id'],'title'=>format_invoice_number($r['id']),'subtitle'=>app_format_money($r['total'],get_base_currency()),'url'=>admin_url('invoices/list_invoices/'.$r['id'])];}
        return$out;
    }
    private function get_customer_lifetime_value($clientId){$t=db_prefix().'invoices';if(!$this->db->table_exists($t))return 0.0;$this->db->select_sum('total');$this->db->where('clientid',(int)$clientId);if($this->db->field_exists('status',$t))$this->db->where_in('status',[2,5]);$row=$this->db->get($t)->row_array();return(float)($row['total']??0);}
    private function countClientRows($suffix,$field,$clientId){$t=db_prefix().$suffix;return$this->db->table_exists($t)&&$this->db->field_exists($field,$t)?(int)$this->db->where($field,(int)$clientId)->count_all_results($t):0;}


    public function get_domain_cards(array $tables){$rows=[];foreach($tables as$suffix){$table=db_prefix().$suffix;$rows[]=['table'=>$suffix,'label'=>ucwords(str_replace('_',' ',preg_replace('/^sce_/','',$suffix))),'count'=>$this->db->table_exists($table)?(int)$this->db->count_all($table):0,'available'=>$this->db->table_exists($table)];}return$rows;}
    public function get_domain_recent(array $tables){$rows=[];foreach($tables as$suffix){$table=db_prefix().$suffix;if(!$this->db->table_exists($table))continue;$fields=$this->db->list_fields($table);$select=array_values(array_intersect(['id','name','title','subject','equipment_name','item_code','status','created_at','updated_at','scheduled_start','log_date','service_date'],$fields));if(!$select)continue;$this->db->select(implode(',',$select));if(in_array('created_at',$fields,true))$this->db->order_by('created_at','DESC');elseif(in_array('id',$fields,true))$this->db->order_by('id','DESC');$records=$this->db->limit(5)->get($table)->result_array();foreach($records as$r)$rows[]=['source'=>ucwords(str_replace('_',' ',preg_replace('/^sce_/','',$suffix))),'record'=>$r];}return array_slice($rows,0,30);}

    private function resolve_staff_image($staffId, $storedFilename)
    {
        $storedFilename = trim((string) $storedFilename);
        $filename = basename(str_replace('\\', '/', $storedFilename));
        if ($filename === '') {
            return null;
        }

        $uploadBase = defined('APP_UPLOADS_FOLDER') ? trim(str_replace('\\', '/', APP_UPLOADS_FOLDER), '/') . '/' : 'uploads/';
        $relativeDirectories = [
            $uploadBase . 'staff_profile_images/' . $staffId . '/',
            'uploads/staff_profile_images/' . $staffId . '/',
            'uploads/staff_images/' . $staffId . '/',
            'uploads/staff/' . $staffId . '/',
            'media/staff_profile_images/' . $staffId . '/',
        ];
        $variants = [$filename, 'small_' . $filename, 'thumb_' . $filename];

        foreach ($relativeDirectories as $directory) {
            foreach ($variants as $variant) {
                $candidate = $directory . $variant;
                if (is_file(FCPATH . $candidate)) {
                    return $candidate;
                }
            }

            $absoluteDirectory = FCPATH . $directory;
            if (is_dir($absoluteDirectory)) {
                foreach ((array) scandir($absoluteDirectory) as $entry) {
                    foreach ($variants as $variant) {
                        if (strcasecmp($entry, $variant) === 0 && is_file($absoluteDirectory . $entry)) {
                            return $directory . $entry;
                        }
                    }
                }
            }
        }

        $storedRelative = ltrim(str_replace('\\', '/', $storedFilename), '/');
        if ($storedRelative !== '' && is_file(FCPATH . $storedRelative)) {
            return $storedRelative;
        }

        return $this->findStaffImageInUploads($filename, $staffId);
    }

    private function findStaffImageInUploads($filename, $staffId)
    {
        $root = FCPATH . 'uploads';
        if (!is_dir($root)) {
            return null;
        }

        $targets = [strtolower($filename), strtolower('small_' . $filename), strtolower('thumb_' . $filename)];
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            $checked = 0;
            foreach ($iterator as $fileInfo) {
                if (++$checked > 25000 || !$fileInfo->isFile()) {
                    continue;
                }
                if (!in_array(strtolower($fileInfo->getFilename()), $targets, true)) {
                    continue;
                }
                $normalized = str_replace('\\', '/', $fileInfo->getPathname());
                if (strpos($normalized, '/' . $staffId . '/') === false && strpos($normalized, 'staff') === false) {
                    continue;
                }
                return ltrim(str_replace(str_replace('\\', '/', FCPATH), '', $normalized), '/');
            }
        } catch (UnexpectedValueException $exception) {
            log_message('error', 'Smart Choice staff image scan failed: ' . $exception->getMessage());
        }

        return null;
    }

    private function filesystemPathToUrl($relativePath)
    {
        $segments = array_map('rawurlencode', explode('/', str_replace('\\', '/', ltrim($relativePath, '/'))));
        return rtrim(base_url(), '/') . '/' . implode('/', $segments);
    }
    public function enterpriseTableSuffixes()
    {
        return [
            'sce_events','sce_queue_jobs','sce_feature_flags','sce_audit_log','sce_field_permissions','sce_api_clients',
            'sce_customer_properties','sce_customer_household','sce_customer_preferences','sce_customer_scores','sce_customer_timeline','sce_customer_categories','sce_customer_category_links','sce_relationship_assignments','sce_pinned_records',
            'sce_opportunities','sce_sales_goals','sce_sales_commissions','sce_field_visits','sce_time_clock','sce_crews','sce_crew_members','sce_daily_logs','sce_dispatch_assignments','sce_technician_skills',
            'sce_equipment','sce_equipment_service','sce_inventory_locations','sce_inventory_items','sce_inventory_stock','sce_inventory_movements',
            'sce_project_phases','sce_rfis','sce_submittals','sce_permits','sce_change_orders','sce_document_versions','sce_document_approvals',
            'sce_communications','sce_call_logs','sce_call_recordings','sce_campaigns','sce_campaign_attribution','sce_review_requests','sce_workflows','sce_workflow_steps','sce_workflow_runs','sce_report_definitions','sce_scheduled_reports','sce_qr_codes','sce_qr_scans','sce_security_rules','sce_login_audit','sce_performance_metrics','sce_favorites','sce_quick_actions'
        ];
    }

    public function isAllowedEnterpriseTable($suffix)
    {
        return in_array($suffix, $this->enterpriseTableSuffixes(), true);
    }

    public function getEnterpriseTableData($suffix, $limit = 500)
    {
        if (!$this->isAllowedEnterpriseTable($suffix)) return ['fields'=>[], 'rows'=>[], 'exists'=>false];
        $table = db_prefix() . $suffix;
        if (!$this->db->table_exists($table)) return ['fields'=>[], 'rows'=>[], 'exists'=>false];
        $fields = $this->db->list_fields($table);
        $old = $this->db->db_debug; $this->db->db_debug = false;
        $query = $this->db->limit(max(1, min(2000, (int)$limit)))->order_by(in_array('id',$fields,true)?'id':$fields[0], 'DESC')->get($table);
        $this->db->db_debug = $old;
        return ['fields'=>$fields, 'rows'=>$query ? $query->result_array() : [], 'exists'=>true];
    }

    public function insertEnterpriseRow($suffix, array $row)
    {
        if (!$this->isAllowedEnterpriseTable($suffix)) return false;
        $table = db_prefix() . $suffix;
        if (!$this->db->table_exists($table)) return false;
        $fields = $this->db->list_fields($table);
        $data = [];
        foreach ($row as $key=>$value) if (in_array($key,$fields,true) && $key !== 'id') $data[$key] = $value === '' ? null : $value;
        if (in_array('created_at',$fields,true) && empty($data['created_at'])) $data['created_at'] = date('Y-m-d H:i:s');
        if (in_array('created_by',$fields,true) && empty($data['created_by'])) $data['created_by'] = get_staff_user_id() ?: null;
        if (!$data) return false;
        $old = $this->db->db_debug; $this->db->db_debug = false; $ok = $this->db->insert($table,$data); $this->db->db_debug = $old;
        return (bool)$ok;
    }

    public function massDeleteEnterpriseRows($suffix, array $ids)
    {
        if (!$this->isAllowedEnterpriseTable($suffix)) return 0;
        $table = db_prefix() . $suffix;
        if (!$this->db->table_exists($table) || !$this->db->field_exists('id',$table)) return 0;
        $ids = array_values(array_filter(array_map('intval',$ids)));
        if (!$ids) return 0;
        $this->db->where_in('id',$ids)->delete($table);
        return $this->db->affected_rows();
    }

    public function repairStaffImageRoot()
    {
        $root = defined('STAFF_PROFILE_IMAGES_FOLDER') ? STAFF_PROFILE_IMAGES_FOLDER : FCPATH . 'uploads/staff_profile_images/';
        if (!is_dir($root)) @mkdir($root, 0755, true);
        $staff = $this->db->select('staffid')->get(db_prefix().'staff')->result_array();
        foreach ($staff as $row) { $dir = rtrim($root, '/\\') . DIRECTORY_SEPARATOR . (int)$row['staffid']; if (!is_dir($dir)) @mkdir($dir,0755,true); }
        return is_dir($root);
    }

    private function countTable($suffix)
    {
        return $this->safeCount(db_prefix() . $suffix);
    }

    private function countWhere($suffix, $field, $value)
    {
        $table = db_prefix() . $suffix;
        if (!$this->db->table_exists($table) || !$this->db->field_exists($field, $table)) return 0;
        $old = $this->db->db_debug; $this->db->db_debug = false;
        $query = $this->db->where($field, $value)->from($table)->count_all_results();
        $this->db->db_debug = $old;
        return is_numeric($query) ? (int)$query : 0;
    }

    private function count_active_modules()
    {
        $table = db_prefix() . 'modules';
        if (!$this->db->table_exists($table)) return 0;
        $field = $this->db->field_exists('active', $table) ? 'active' : ($this->db->field_exists('is_active', $table) ? 'is_active' : null);
        return $field ? $this->countWhere('modules', $field, 1) : $this->safeCount($table);
    }

    public function get_dashboard_domain_counts()
    {
        $groups = [
            'Customers'=>['clients'], 'Sales'=>['sce_opportunities','sce_sales_goals','sce_sales_commissions'],
            'Field Operations'=>['sce_field_visits','sce_time_clock','sce_daily_logs'], 'Dispatch'=>['sce_dispatch_assignments'],
            'Equipment and Inventory'=>['sce_equipment','sce_inventory_items','sce_inventory_movements'],
            'Project Controls'=>['sce_project_phases','sce_rfis','sce_submittals','sce_permits','sce_change_orders'],
            'Documents'=>['sce_document_versions','sce_document_approvals'], 'Communications'=>['sce_communications','sce_call_logs'],
            'Marketing'=>['sce_campaigns','sce_campaign_attribution'], 'Workflows'=>['sce_workflows','sce_workflow_runs'],
            'QR Codes'=>['sce_qr_codes'], 'Reports'=>['sce_report_definitions','sce_scheduled_reports']
        ];
        $out=[]; foreach($groups as $name=>$tables){$count=0;foreach($tables as $suffix){$count += $suffix==='clients' ? $this->safeCount(db_prefix().'clients') : $this->countTable($suffix);} $out[$name]=$count;} return $out;
    }

    public function get_call_center_summary()
    {
        return ['total'=>$this->countTable('sce_call_logs'),'completed'=>$this->countWhere('sce_call_logs','status','completed'),'recorded'=>$this->countWhere('sce_call_logs','has_recording',1),'twilio_ready'=>get_option('enterprise_twilio_account_sid')!=='' && get_option('enterprise_twilio_auth_token')!=='' && get_option('enterprise_twilio_phone_number')!==''];
    }

    public function save_qr_code(array $data)
    {
        $table=db_prefix().'sce_qr_codes'; if(!$this->db->table_exists($table)) return false;
        $data['created_by']=get_staff_user_id()?:null; $data['created_at']=date('Y-m-d H:i:s');
        return $this->db->insert($table,$data) ? $this->db->insert_id() : false;
    }

    public function get_qr_codes(){ $t=db_prefix().'sce_qr_codes'; return $this->db->table_exists($t)?$this->db->order_by('id','DESC')->limit(500)->get($t)->result_array():[]; }

    public function get_sales_workspace()
    {
        $core=['invoices','estimates','proposals','payments','contracts','projects'];$cards=[];
        foreach($core as $suffix){$cards[]=['table'=>$suffix,'label'=>ucwords($suffix),'count'=>$this->safeCount(db_prefix().$suffix),'available'=>$this->db->table_exists(db_prefix().$suffix),'core'=>true];}
        foreach(['sce_opportunities','sce_sales_goals','sce_sales_commissions'] as $suffix){$cards[]=['table'=>$suffix,'label'=>ucwords(str_replace('_',' ',preg_replace('/^sce_/','',$suffix))),'count'=>$this->countTable($suffix),'available'=>$this->db->table_exists(db_prefix().$suffix),'core'=>false];}
        $recent=[];
        foreach([['invoices','date'],['estimates','date'],['proposals','datecreated'],['payments','date'],['contracts','dateadded'],['projects','date_created']] as $spec){$table=db_prefix().$spec[0];if(!$this->db->table_exists($table))continue;$fields=$this->db->list_fields($table);$select=array_values(array_intersect(['id','number','subject','name','status','total','date','datecreated','dateadded','date_created'],$fields));if(!$select)continue;$this->db->select(implode(',',$select));if(in_array($spec[1],$fields,true))$this->db->order_by($spec[1],'DESC');$rows=$this->db->limit(8)->get($table)->result_array();foreach($rows as $r){$recent[]=['source'=>ucwords($spec[0]),'record'=>$r];}}
        return ['cards'=>$cards,'recent'=>array_slice($recent,0,40)];
    }
    public function get_dispatch_workspace()
    {
        $assign=$this->getEnterpriseTableData('sce_dispatch_assignments',500);
        $staff=$this->db->table_exists(db_prefix().'staff')?$this->db->select('staffid,firstname,lastname')->where('active',1)->order_by('firstname')->get(db_prefix().'staff')->result_array():[];
        $projects=$this->db->table_exists(db_prefix().'projects')?$this->db->select('id,name,status')->order_by('id','DESC')->limit(250)->get(db_prefix().'projects')->result_array():[];
        return ['cards'=>$this->get_domain_cards(['sce_dispatch_assignments','sce_technician_skills','sce_field_visits']),'recent'=>$this->get_domain_recent(['sce_dispatch_assignments','sce_field_visits']),'assignments'=>$assign,'staff'=>$staff,'projects'=>$projects];
    }
    public function create_dispatch_assignment(array $post)
    {
        $table=db_prefix().'sce_dispatch_assignments';if(!$this->db->table_exists($table))return false;$fields=$this->db->list_fields($table);$map=['staff_id'=>(int)($post['staff_id']??0),'project_id'=>(int)($post['project_id']??0),'title'=>trim((string)($post['title']??'')),'status'=>'dispatched','scheduled_start'=>trim((string)($post['scheduled_start']??'')),'notes'=>trim((string)($post['notes']??'')),'created_by'=>get_staff_user_id(),'created_at'=>date('Y-m-d H:i:s')];$row=[];foreach($map as $k=>$v)if(in_array($k,$fields,true))$row[$k]=$v;return $row?(bool)$this->db->insert($table,$row):false;
    }
    public function get_reporting_workspace()
    {
        $currency=get_base_currency();$totals=[];
        foreach(['invoices','estimates','proposals','payments','projects','contracts','clients'] as $suffix)$totals[$suffix]=$this->safeCount(db_prefix().$suffix);
        $sum=function($suffix,$field){$table=db_prefix().$suffix;if(!$this->db->table_exists($table)||!$this->db->field_exists($field,$table))return 0.0;$row=$this->db->select_sum($field)->get($table)->row_array();return(float)($row[$field]??0);};
        return ['cards'=>$this->get_domain_cards(['sce_report_definitions','sce_scheduled_reports']),'domains'=>$this->get_dashboard_domain_counts(),'core_totals'=>$totals,'invoice_value'=>$sum('invoices','total'),'payments_value'=>$sum('payments','amount'),'currency'=>$currency];
    }
    public function import_customers_csv(array $file)
    {
        $result=['imported'=>0,'skipped'=>0];if(empty($file['tmp_name'])||!is_uploaded_file($file['tmp_name']))return$result;$h=fopen($file['tmp_name'],'rb');$headers=$h?fgetcsv($h):false;if(!$headers){if($h)fclose($h);return$result;}$headers=array_map('trim',$headers);
        while(($values=fgetcsv($h))!==false){$row=array_combine($headers,array_pad($values,count($headers),''));if(!$row||empty($row['company'])){$result['skipped']++;continue;}$client=['company'=>trim($row['company']),'phonenumber'=>trim($row['phonenumber']??''),'address'=>trim($row['address']??''),'city'=>trim($row['city']??''),'state'=>trim($row['state']??''),'zip'=>trim($row['zip']??''),'datecreated'=>date('Y-m-d H:i:s'),'active'=>1];$this->db->insert(db_prefix().'clients',$client);$id=$this->db->insert_id();if($id&&$this->db->table_exists(db_prefix().'contacts')){$this->db->insert(db_prefix().'contacts',['userid'=>$id,'firstname'=>trim($row['firstname']??''),'lastname'=>trim($row['lastname']??''),'email'=>trim($row['email']??''),'phonenumber'=>trim($row['phonenumber']??''),'is_primary'=>1,'active'=>1,'datecreated'=>date('Y-m-d H:i:s')]);}$id?$result['imported']++:$result['skipped']++;}
        fclose($h);return$result;
    }

    private function safeCount($table)
    {
        if (!$this->db->table_exists($table)) return 0;
        $old=$this->db->db_debug; $this->db->db_debug=false; $count=$this->db->count_all($table); $this->db->db_debug=$old;
        return is_numeric($count)?(int)$count:0;
    }

}
