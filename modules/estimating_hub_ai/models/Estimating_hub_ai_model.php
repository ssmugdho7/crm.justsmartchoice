<?php defined('BASEPATH') or exit('No direct script access allowed');

class Estimating_hub_ai_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('estimating_hub_ai/estimating_hub_ai');
        $this->ensure_schema();
    }

    public function ensure_schema()
    {
        require_once(module_dir_path('estimating_hub_ai').'install.php');
        if (function_exists('estimating_hub_ai_install')) {
            estimating_hub_ai_install(false);
        }
    }

    private function table($name){ return db_prefix().$name; }
    public function safe_count($table)
    {
        $table = $this->table($table);
        return $this->db->table_exists($table) ? (int)$this->db->count_all($table) : 0;
    }

    public function dashboard_stats()
    {
        return [
            'estimates'   => $this->safe_count('est_ai_estimates'),
            'cost_items'  => $this->safe_count('est_ai_cost_database'),
            'documents'   => $this->safe_count('est_ai_training_documents'),
            'photos'      => $this->safe_count('est_ai_photo_intake'),
            'home_depot'  => $this->safe_count('est_ai_vendor_price_links'),
            'collector'   => $this->safe_count('est_ai_external_price_sources'),
        ];
    }

    public function get_estimates($limit = 50)
    {
        if (!$this->db->table_exists($this->table('est_ai_estimates'))) return [];
        return $this->db->order_by('id','desc')->limit($limit)->get($this->table('est_ai_estimates'))->result_array();
    }
    public function get_estimate($id)
    {
        if (!$this->db->table_exists($this->table('est_ai_estimates'))) return null;
        return $this->db->where('id',(int)$id)->get($this->table('est_ai_estimates'))->row();
    }
    public function get_items($estimate_id)
    {
        if (!$this->db->table_exists($this->table('est_ai_items'))) return [];
        return $this->db->where('estimate_id',(int)$estimate_id)->order_by('id','asc')->get($this->table('est_ai_items'))->result_array();
    }
    public function get_cost_items($q = '', $limit = 2000)
    {
        if (!$this->db->table_exists($this->table('est_ai_cost_database'))) return [];
        if ($q !== '') {
            $this->db->group_start();
            foreach(['trade','category','item_name','description','unit','region','source','source_type'] as $field){
                $this->db->or_like($field, $q);
            }
            $this->db->group_end();
        }
        return $this->db->order_by('trade','asc')->order_by('category','asc')->limit($limit)->get($this->table('est_ai_cost_database'))->result_array();
    }
    public function get_training_documents($limit = 500)
    {
        if (!$this->db->table_exists($this->table('est_ai_training_documents'))) return [];
        return $this->db->order_by('id','desc')->limit($limit)->get($this->table('est_ai_training_documents'))->result_array();
    }
    public function get_photos($limit = 500)
    {
        if (!$this->db->table_exists($this->table('est_ai_photo_intake'))) return [];
        return $this->db->order_by('id','desc')->limit($limit)->get($this->table('est_ai_photo_intake'))->result_array();
    }
    public function get_vendor_links($limit = 1000)
    {
        if (!$this->db->table_exists($this->table('est_ai_vendor_price_links'))) return [];
        return $this->db->order_by('id','desc')->limit($limit)->get($this->table('est_ai_vendor_price_links'))->result_array();
    }
    public function get_external_sources($limit = 1000)
    {
        if (!$this->db->table_exists($this->table('est_ai_external_price_sources'))) return [];
        return $this->db->order_by('id','desc')->limit($limit)->get($this->table('est_ai_external_price_sources'))->result_array();
    }

    public function create_ai_estimate($data)
    {
        $scope = trim($data['scope_summary'] ?? '');
        $name  = trim($data['name'] ?? 'AI Estimate Draft');
        if ($name === '') $name = 'AI Estimate Draft';
        if ($scope === '') $scope = $name;

        $insert = [
            'name' => $name,
            'clientid' => !empty($data['clientid']) ? (int)$data['clientid'] : null,
            'project_id' => !empty($data['project_id']) ? (int)$data['project_id'] : null,
            'lead_id' => !empty($data['lead_id']) ? (int)$data['lead_id'] : null,
            'status' => 'draft',
            'project_address' => trim($data['project_address'] ?? ''),
            'scope_summary' => $scope,
            'ai_questions' => $this->build_follow_up_questions($scope),
            'subtotal' => 0,
            'overhead' => 0,
            'profit' => 0,
            'tax' => 0,
            'total' => 0,
            'confidence_score' => 65,
            'created_by' => get_staff_user_id(),
            'datecreated' => date('Y-m-d H:i:s'),
            'dateupdated' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert($this->table('est_ai_estimates'), $insert);
        $id = (int)$this->db->insert_id();
        $this->create_seed_items_from_scope($id, $scope);
        $this->recalculate_estimate($id);
        $this->log('Estimate Draft Created', $name);
        return $id;
    }

    private function build_follow_up_questions($scope)
    {
        $scope = strtolower($scope);
        $q = ['Verify measurements, site access, customer-selected material grade, permit requirements, and inspection needs before sending.'];
        if (strpos($scope,'kitchen') !== false) $q[] = 'Kitchen: verify cabinet count, countertop SF, backsplash SF, appliance changes, electrical circuits, plumbing changes, flooring SF.';
        if (strpos($scope,'bath') !== false) $q[] = 'Bathroom: verify shower/tub, tile SF, waterproofing, vanity size, toilet, plumbing relocation, exhaust fan, glass door.';
        if (strpos($scope,'garage') !== false) $q[] = 'Garage conversion: verify conditioned space, insulation, egress, bathroom/kitchenette, HVAC, electrical, plumbing, permits.';
        if (strpos($scope,'roof') !== false) $q[] = 'Roof: verify roof squares, pitch, deck repair, underlayment, flashing, vents, permit, disposal.';
        if (strpos($scope,'panel') !== false || strpos($scope,'electrical') !== false) $q[] = 'Electrical: verify panel size, service, grounding, utility requirements, permit, inspection, circuit distances.';
        return implode("\n", $q);
    }

    public function create_seed_items_from_scope($estimate_id, $scope)
    {
        $scope_l = strtolower($scope);
        $this->seed_cost_items_if_empty();
        $matches = [];
        $rules = [
            'kitchen' => ['Kitchen','Cabinets','Countertops','Electrical','Plumbing','Flooring','Painting'],
            'cabinet' => ['Cabinets'],
            'bath' => ['Bathroom','Plumbing','Tile','Electrical','Drywall','Painting'],
            'toilet' => ['Plumbing'],
            'garage' => ['Garage Conversion','Framing','Drywall','Electrical','HVAC','Flooring','Painting'],
            'roof' => ['Roofing'],
            'electrical' => ['Electrical'],
            'panel' => ['Electrical'],
            'floor' => ['Flooring'],
            'paint' => ['Painting'],
            'drywall' => ['Drywall'],
            'concrete' => ['Concrete'],
            'window' => ['Openings'],
            'door' => ['Openings'],
        ];
        foreach($rules as $key=>$trades){
            if (strpos($scope_l, $key) !== false) { $matches = array_merge($matches, $trades); }
        }
        if (empty($matches)) { $matches = ['Template','Demolition','Drywall','Painting']; }
        $matches = array_unique($matches);
        $this->db->group_start();
        foreach($matches as $m){ $this->db->or_like('trade',$m)->or_like('category',$m)->or_like('item_name',$m); }
        $this->db->group_end();
        $costs = $this->db->limit(12)->get($this->table('est_ai_cost_database'))->result_array();
        if (empty($costs)) $costs = $this->get_cost_items('', 8);
        foreach($costs as $c){
            $qty = $this->guess_qty($scope_l, $c['unit']);
            $typical = (float)($c['material_cost_typical'] ?: $c['material_cost_low'] ?: 0);
            $labor = ((float)$c['labor_hours']) * ((float)$c['labor_rate']);
            $equipment = (float)($c['equipment_cost_typical'] ?? 0);
            $base = ($typical + $labor + $equipment) * $qty;
            $waste = $base * (float)($c['waste_factor'] ?? 0);
            $markup = (float)get_option('estimating_hub_ai_default_margin');
            if ($markup <= 0) $markup = 25;
            $total = ($base + $waste) * (1 + ($markup/100));
            $this->db->insert($this->table('est_ai_items'), [
                'estimate_id' => $estimate_id,
                'cost_item_id' => (int)$c['id'],
                'division' => $c['division'] ?? '',
                'trade' => $c['trade'] ?? '',
                'category' => $c['category'] ?? '',
                'description' => $c['item_name'].(!empty($c['description']) ? ' - '.$c['description'] : ''),
                'unit' => $c['unit'] ?: 'each',
                'qty' => $qty,
                'material_cost' => $typical,
                'labor_hours' => (float)$c['labor_hours'],
                'labor_rate' => (float)$c['labor_rate'],
                'equipment_cost' => $equipment,
                'waste_factor' => (float)($c['waste_factor'] ?? 0),
                'markup_percent' => $markup,
                'total' => $total,
                'source' => $c['source'] ?? 'Cost Database',
            ]);
        }
    }

    private function guess_qty($scope, $unit)
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*(sf|sq ft|square feet)/i', $scope, $m)) return (float)$m[1];
        if (preg_match('/(\d+(?:\.\d+)?)\s*(lf|linear feet)/i', $scope, $m)) return (float)$m[1];
        if (strpos($scope,'8x5') !== false || strpos($scope,'8 x 5') !== false) {
            if ($unit === 'sf') return 40;
            if ($unit === 'project') return 1;
        }
        return in_array($unit, ['project','each','ea','fixture','circuit']) ? 1 : (in_array($unit, ['lf']) ? 10 : 100);
    }

    public function recalculate_estimate($id)
    {
        $items = $this->get_items($id);
        $subtotal = 0;
        foreach($items as $i){ $subtotal += (float)$i['total']; }
        $overhead_rate = (float)get_option('estimating_hub_ai_default_overhead');
        if ($overhead_rate <= 0) $overhead_rate = 12;
        $overhead = $subtotal * ($overhead_rate/100);
        $profit = $subtotal * .10;
        $total = $subtotal + $overhead + $profit;
        $this->db->where('id',(int)$id)->update($this->table('est_ai_estimates'), ['subtotal'=>$subtotal,'overhead'=>$overhead,'profit'=>$profit,'total'=>$total,'dateupdated'=>date('Y-m-d H:i:s')]);
    }

    public function save_photo_intake($file, $data)
    {
        $valid = $this->validate_upload($file, ['jpg','jpeg','png','gif','webp','heic','pdf']);
        if ($valid !== true) return $valid;
        $dir = module_dir_path('estimating_hub_ai').'uploads/photos/';
        if (!is_dir($dir)) @mkdir($dir,0755,true);
        $name = time().'_'.preg_replace('/[^A-Za-z0-9_\.\-]/','_', $file['name']);
        if (!move_uploaded_file($file['tmp_name'], $dir.$name)) return 'Could not save uploaded photo.';
        $path = 'modules/estimating_hub_ai/uploads/photos/'.$name;
        $project_id = !empty($data['project_id']) ? (int)$data['project_id'] : null;
        $this->db->insert($this->table('est_ai_photo_intake'), [
            'estimate_id'=>!empty($data['estimate_id'])?(int)$data['estimate_id']:null,
            'project_id'=>$project_id,
            'clientid'=>!empty($data['clientid'])?(int)$data['clientid']:null,
            'file_name'=>$name,
            'file_path'=>$path,
            'notes'=>trim($data['notes'] ?? ''),
            'ai_observations'=>'Photo saved. Use Create Estimate From Photo to generate a draft from notes and linked project context.',
            'created_by'=>get_staff_user_id(),
            'datecreated'=>date('Y-m-d H:i:s')
        ]);
        $photo_id = (int)$this->db->insert_id();
        if (!empty($data['create_estimate'])) {
            $eid = $this->create_ai_estimate(['name'=>'Photo Estimate '.$photo_id,'scope_summary'=>trim($data['notes'] ?? 'Photo estimate'),'project_id'=>$project_id]);
            $this->db->where('id',$photo_id)->update($this->table('est_ai_photo_intake'), ['estimate_id'=>$eid]);
        }
        return true;
    }

    public function save_training_document($file, $data)
    {
        $valid = $this->validate_upload($file, ['txt','pdf','doc','docx','md','csv']);
        if ($valid !== true) return $valid;
        $dir = module_dir_path('estimating_hub_ai').'uploads/training/';
        if (!is_dir($dir)) @mkdir($dir,0755,true);
        $name = time().'_'.preg_replace('/[^A-Za-z0-9_\.\-]/','_', $file['name']);
        if (!move_uploaded_file($file['tmp_name'], $dir.$name)) return 'Could not save uploaded document.';
        $content = '';
        if (in_array(strtolower(pathinfo($name,PATHINFO_EXTENSION)), ['txt','md','csv'])) {
            $content = substr((string)@file_get_contents($dir.$name), 0, 500000);
        }
        $this->db->insert($this->table('est_ai_training_documents'), [
            'title'=>trim($data['title'] ?? $file['name']),
            'type'=>'document',
            'file_name'=>$name,
            'file_path'=>'modules/estimating_hub_ai/uploads/training/'.$name,
            'content'=>$content,
            'status'=>'stored',
            'processed'=>0,
            'created_by'=>get_staff_user_id(),
            'datecreated'=>date('Y-m-d H:i:s')
        ]);
        return true;
    }

    public function add_home_depot_item($data)
    {
        $price = $this->money_to_float($data['last_price'] ?? 0);
        if ($price > 99999.99) $price = 99999.99;
        $this->db->insert($this->table('est_ai_vendor_price_links'), [
            'vendor'=>'Home Depot',
            'sku'=>trim($data['sku'] ?? ''),
            'item_name'=>trim($data['item_name'] ?? 'Home Depot Item'),
            'url'=>trim($data['url'] ?? ''),
            'unit'=>trim($data['unit'] ?? 'each'),
            'last_price'=>$price,
            'dateupdated'=>date('Y-m-d H:i:s'),
            'source'=>'Manual',
        ]);
        $id = (int)$this->db->insert_id();
        $this->save_external_price_source(['vendor'=>'Home Depot','sku'=>$data['sku'] ?? '','item_name'=>$data['item_name'] ?? 'Home Depot Item','unit'=>$data['unit'] ?? 'each','last_price'=>$price,'source_url'=>$data['url'] ?? '','source_type'=>'Home Depot Pro List']);
        return $id;
    }

    public function sync_existing_sales_sources()
    {
        $count = 0; $checked = [];
        if ($this->db->table_exists(db_prefix().'itemable')) {
            $checked[] = 'itemable';
            $types = [];
            if (get_option('estimating_hub_ai_sync_estimates') === '1') $types[]='estimate';
            if (get_option('estimating_hub_ai_sync_invoices') === '1') $types[]='invoice';
            if (get_option('estimating_hub_ai_sync_proposals') === '1') $types[]='proposal';
            if (!empty($types)) {
                $rows = $this->db->where_in('rel_type',$types)->limit(2500)->get(db_prefix().'itemable')->result_array();
                foreach($rows as $r){ if ($this->import_itemable_row($r)) $count++; }
            }
        }
        if ($this->db->table_exists(db_prefix().'items')) {
            $checked[] = 'items';
            $rows = $this->db->limit(2500)->get(db_prefix().'items')->result_array();
            foreach($rows as $r){ if ($this->import_crm_item_row($r)) $count++; }
        }
        $this->log('CRM Source Sync', $count.' records imported. Checked: '.implode(', ', $checked));
        return ['count'=>$count, 'checked'=>$checked];
    }

    private function import_itemable_row($r)
    {
        $desc = trim(($r['description'] ?? '').' '.($r['long_description'] ?? ''));
        if ($desc === '') return false;
        $rate = $this->money_to_float($r['rate'] ?? 0);
        $source = 'CRM '.ucwords((string)($r['rel_type'] ?? 'Sales'));
        $exists = $this->db->where('source',$source)->where('source_id',(int)$r['id'])->get($this->table('est_ai_cost_database'))->row();
        $data = [
            'division'=>'CRM','trade'=>'CRM Sales','category'=>ucwords((string)($r['rel_type'] ?? 'Sales')),
            'item_name'=>mb_substr($r['description'] ?? $desc,0,190),'description'=>$desc,'unit'=>$r['unit'] ?? 'each',
            'material_cost_low'=>$rate*.85,'material_cost_typical'=>$rate,'material_cost_high'=>$rate*1.25,
            'labor_hours'=>0,'labor_rate'=>0,'equipment_cost_low'=>0,'equipment_cost_typical'=>0,'equipment_cost_high'=>0,
            'waste_factor'=>0,'production_rate'=>'Imported from CRM source','permit_required'=>0,'inspection_required'=>0,
            'region'=>'Tampa Bay','active'=>1,'source'=>$source,'source_type'=>'CRM Source','source_id'=>(int)$r['id'],'datecreated'=>date('Y-m-d H:i:s')
        ];
        if ($exists) { unset($data['datecreated']); $this->db->where('id',(int)$exists->id)->update($this->table('est_ai_cost_database'),$data); }
        else { $this->db->insert($this->table('est_ai_cost_database'),$data); }
        return true;
    }

    private function import_crm_item_row($r)
    {
        $desc = trim(($r['description'] ?? $r['name'] ?? '').' '.($r['long_description'] ?? ''));
        if ($desc === '') return false;
        $rate = $this->money_to_float($r['rate'] ?? 0);
        $source = 'CRM Items';
        $exists = $this->db->where('source',$source)->where('source_id',(int)$r['id'])->get($this->table('est_ai_cost_database'))->row();
        $data = [
            'division'=>'CRM','trade'=>'CRM Items','category'=>'Products And Services','item_name'=>mb_substr($r['description'] ?? $r['name'] ?? 'CRM Item',0,190),'description'=>$desc,'unit'=>$r['unit'] ?? 'each',
            'material_cost_low'=>$rate*.85,'material_cost_typical'=>$rate,'material_cost_high'=>$rate*1.25,'labor_hours'=>0,'labor_rate'=>0,'waste_factor'=>0,
            'production_rate'=>'Imported from CRM Items','permit_required'=>0,'inspection_required'=>0,'region'=>'Tampa Bay','active'=>1,'source'=>$source,'source_type'=>'CRM Source','source_id'=>(int)$r['id'],'datecreated'=>date('Y-m-d H:i:s')
        ];
        if ($exists) { unset($data['datecreated']); $this->db->where('id',(int)$exists->id)->update($this->table('est_ai_cost_database'),$data); }
        else { $this->db->insert($this->table('est_ai_cost_database'),$data); }
        return true;
    }

    public function source_counts()
    {
        if (!$this->db->table_exists($this->table('est_ai_cost_database'))) return [];
        return $this->db->select('source, COUNT(*) as total')->group_by('source')->get($this->table('est_ai_cost_database'))->result_array();
    }

    public function global_search($q)
    {
        $out=['estimates'=>[],'cost_items'=>[],'documents'=>[],'photos'=>[],'home_depot'=>[],'collector'=>[]];
        if ($q==='') return $out;
        if ($this->db->table_exists($this->table('est_ai_estimates'))) {
            $this->db->group_start()->like('name',$q)->or_like('scope_summary',$q)->or_like('project_address',$q)->group_end();
            $out['estimates']=$this->db->limit(50)->get($this->table('est_ai_estimates'))->result_array();
        }
        if ($this->db->table_exists($this->table('est_ai_cost_database'))) {
            $this->db->group_start(); foreach(['item_name','description','trade','category','source','region'] as $f){$this->db->or_like($f,$q);} $this->db->group_end();
            $out['cost_items']=$this->db->limit(100)->get($this->table('est_ai_cost_database'))->result_array();
        }
        if ($this->db->table_exists($this->table('est_ai_training_documents'))) {
            $this->db->group_start()->like('title',$q)->or_like('file_name',$q)->or_like('content',$q)->group_end();
            $out['documents']=$this->db->limit(50)->get($this->table('est_ai_training_documents'))->result_array();
        }
        if ($this->db->table_exists($this->table('est_ai_photo_intake'))) {
            $this->db->group_start()->like('file_name',$q)->or_like('notes',$q)->or_like('ai_observations',$q)->group_end();
            $out['photos']=$this->db->limit(50)->get($this->table('est_ai_photo_intake'))->result_array();
        }
        if ($this->db->table_exists($this->table('est_ai_vendor_price_links'))) {
            $this->db->group_start()->like('sku',$q)->or_like('item_name',$q)->or_like('url',$q)->group_end();
            $out['home_depot']=$this->db->limit(50)->get($this->table('est_ai_vendor_price_links'))->result_array();
        }
        if ($this->db->table_exists($this->table('est_ai_external_price_sources'))) {
            $this->db->group_start()->like('sku',$q)->or_like('item_name',$q)->or_like('brand',$q)->or_like('vendor',$q)->or_like('source_url',$q)->group_end();
            $out['collector']=$this->db->limit(50)->get($this->table('est_ai_external_price_sources'))->result_array();
        }
        return $out;
    }

    public function health_checks()
    {
        $checks=[];
        foreach(['est_ai_estimates','est_ai_items','est_ai_cost_database','est_ai_training_documents','est_ai_photo_intake','est_ai_vendor_price_links','est_ai_external_price_sources','est_ai_price_history','est_ai_activity_log'] as $t){
            $checks[]=['name'=>'Database Table '.ucwords(str_replace('_',' ', $t)),'status'=>$this->db->table_exists($this->table($t)),'message'=>$this->db->table_exists($this->table($t))?'Ready':'Missing'];
        }
        foreach(['uploads/training','uploads/photos','uploads/documents','uploads/exports'] as $d){
            $path=module_dir_path('estimating_hub_ai').$d;
            if(!is_dir($path)) @mkdir($path,0755,true);
            $checks[]=['name'=>'Folder '.ucwords(str_replace('/',' ', $d)),'status'=>is_dir($path)&&is_writable($path),'message'=>is_dir($path)?(is_writable($path)?'Writable':'Not writable'):'Missing'];
        }
        $salesTable = $this->db->table_exists(db_prefix().'itemable') ? 'itemable' : ($this->db->table_exists(db_prefix().'items') ? 'items' : '');
        $checks[]=['name'=>'CRM Estimate Invoice Proposal Sync','status'=>$salesTable!=='','message'=>$salesTable!==''?'Can read CRM source table: '.$salesTable:'No CRM source table found'];
        $checks[]=['name'=>'OpenAI Key','status'=> (bool)(get_option('openai_api_key') || get_option('perfex_chat_openai_api_key') || get_option('estimating_hub_ai_openai_api_key')),'message'=>'Uses existing CRM/chatbot key or override key.'];
        $checks[]=['name'=>'Collector API','status'=> get_option('estimating_hub_ai_collector_enabled')==='1','message'=>'Endpoint: '.admin_url('estimating_hub_ai/collector_api')];
        return $checks;
    }

    public function import_cost_database_csv($file)
    {
        $valid=$this->validate_upload($file,['csv']); if($valid!==true) return $valid;
        $h=fopen($file['tmp_name'],'r'); if(!$h) return 'Could not read CSV file.';
        $header=fgetcsv($h); if(!$header) return 'CSV header is missing.';
        $map=[]; foreach($header as $i=>$name){ $map[strtolower(trim($name))]=$i; }
        $get=function($row,$names,$default='') use ($map){ foreach((array)$names as $n){ $k=strtolower(trim($n)); if(isset($map[$k]) && isset($row[$map[$k]])) return $row[$map[$k]]; } return $default; };
        $count=0; $now=date('Y-m-d H:i:s');
        while(($r=fgetcsv($h))!==false){
            if(count($r)<4) continue;
            $item=$get($r,['Item Name','item_name','Item'],''); if(trim($item)==='') continue;
            $data=[
                'division'=>$get($r,['CSI Division','Division'],'SCIE'),
                'trade'=>$get($r,['Trade'],'General'),
                'category'=>$get($r,['Category'],'Imported'),
                'subcategory'=>$get($r,['Subcategory'],''),
                'item_name'=>$item,
                'description'=>$get($r,['Description'],''),
                'unit'=>$get($r,['Unit'],'each'),
                'material_cost_low'=>$this->money_to_float($get($r,['Material Low','Material Cost Low','Material Low'],0)),
                'material_cost_typical'=>$this->money_to_float($get($r,['Material Typical','Material Cost Typical','Material Typical'],0)),
                'material_cost_high'=>$this->money_to_float($get($r,['Material High','Material Cost High','Material High'],0)),
                'labor_cost_low'=>$this->money_to_float($get($r,['Labor Low'],0)),
                'labor_cost_typical'=>$this->money_to_float($get($r,['Labor Typical'],0)),
                'labor_cost_high'=>$this->money_to_float($get($r,['Labor High'],0)),
                'equipment_cost_low'=>$this->money_to_float($get($r,['Equipment Low'],0)),
                'equipment_cost_typical'=>$this->money_to_float($get($r,['Equipment Typical'],0)),
                'equipment_cost_high'=>$this->money_to_float($get($r,['Equipment High'],0)),
                'installed_cost_low'=>$this->money_to_float($get($r,['Installed Low'],0)),
                'installed_cost_typical'=>$this->money_to_float($get($r,['Installed Typical'],0)),
                'installed_cost_high'=>$this->money_to_float($get($r,['Installed High'],0)),
                'labor_hours'=>(float)$this->money_to_float($get($r,['Labor Hours','Labor Hours Typical'],0)),
                'labor_rate'=>$this->money_to_float($get($r,['Labor Rate'],0)),
                'waste_factor'=>(float)$this->money_to_float($get($r,['Waste Factor'],0)),
                'production_rate'=>$get($r,['Production Rate'],''),
                'permit_required'=>(int)$this->money_to_float($get($r,['Permit Required'],0)),
                'inspection_required'=>(int)$this->money_to_float($get($r,['Inspection Required'],0)),
                'region'=>$get($r,['Region'],'Tampa Bay'),
                'zip_basis'=>$get($r,['ZIP Basis'],''),
                'source_type'=>$get($r,['Source Type'],'CSV Import'),
                'source'=>$get($r,['Source Reference','Source'],'CSV Import'),
                'pricing_basis'=>$get($r,['Pricing Basis'],''),
                'confidence'=>$get($r,['Confidence'],'Medium'),
                'notes'=>$get($r,['Notes'],''),
                'active'=>1,
                'datecreated'=>$now,
            ];
            $this->db->insert($this->table('est_ai_cost_database'),$data); $count++;
        }
        fclose($h); $this->log('Cost Database Import',$count.' rows'); return $count.' cost items imported successfully.';
    }

    public function import_home_depot_csv($file)
    {
        $valid=$this->validate_upload($file,['csv']); if($valid!==true) return $valid;
        $h=fopen($file['tmp_name'],'r'); if(!$h) return 'Could not read CSV file.'; $header=fgetcsv($h); $count=0;
        while(($r=fgetcsv($h))!==false){ if(count($r)<2) continue; $this->add_home_depot_item(['sku'=>$r[0]??'','item_name'=>$r[1]??'Home Depot Item','url'=>$r[2]??'','unit'=>$r[3]??'each','last_price'=>$r[4]??0]); $count++; }
        fclose($h); return $count.' Home Depot items imported successfully.';
    }

    public function collector_api_receive()
    {
        if(get_option('estimating_hub_ai_collector_enabled') !== '1') return ['success'=>false,'message'=>'Collector API is disabled.'];
        $token = $this->input->get_request_header('X-SCIE-Token', true) ?: $this->input->post('token', true);
        if(!$token || !hash_equals((string)get_option('estimating_hub_ai_collector_token'), (string)$token)) return ['success'=>false,'message'=>'Invalid collector token.'];
        $raw = file_get_contents('php://input'); $payload = json_decode($raw, true); if(!$payload) $payload = $this->input->post(null, true);
        $items = (isset($payload['items']) && is_array($payload['items'])) ? $payload['items'] : [$payload];
        $saved=0; foreach($items as $item){ if($this->save_external_price_source($item)) $saved++; }
        return ['success'=>true,'saved'=>$saved,'message'=>$saved.' records saved successfully.'];
    }

    public function save_external_price_source($item)
    {
        if(!$this->db->table_exists($this->table('est_ai_external_price_sources'))) return 0;
        $vendor=trim($item['vendor'] ?? $item['source'] ?? 'Public Source');
        $name=trim($item['item_name'] ?? $item['title'] ?? 'Collected Item');
        $sku=trim($item['sku'] ?? ''); $url=trim($item['source_url'] ?? $item['url'] ?? '');
        $mid=$this->money_to_float($item['price_mid'] ?? $item['mid_price'] ?? $item['last_price'] ?? $item['price'] ?? 0);
        $low=$this->money_to_float($item['price_low'] ?? $item['low_price'] ?? 0); if($low<=0)$low=$mid;
        $high=$this->money_to_float($item['price_high'] ?? $item['high_price'] ?? 0); if($high<=0)$high=$mid;
        $last=$this->money_to_float($item['last_price'] ?? $item['price'] ?? $mid);
        $data=[
            'source_type'=>trim($item['source_type'] ?? 'Collector App'),'vendor'=>$vendor,'sku'=>$sku,'model_number'=>trim($item['model_number'] ?? ''),'upc'=>trim($item['upc'] ?? ''),'brand'=>trim($item['brand'] ?? ''),'item_name'=>$name,'description'=>trim($item['description'] ?? ''),'category'=>trim($item['category'] ?? ''),'unit'=>trim($item['unit'] ?? 'each'),'price_low'=>$low,'price_mid'=>$mid,'price_high'=>$high,'last_price'=>$last,'zip_code'=>trim($item['zip_code'] ?? get_option('estimating_hub_ai_collector_default_zip') ?: ''),'store_id'=>trim($item['store_id'] ?? ''),'source_url'=>$url,'image_url'=>trim($item['image_url'] ?? ''),'raw_json'=>json_encode($item),'status'=>'active','last_checked'=>date('Y-m-d H:i:s'),'datecreated'=>date('Y-m-d H:i:s')
        ];
        $existing=null; if($sku!=='') $existing=$this->db->where('vendor',$vendor)->where('sku',$sku)->get($this->table('est_ai_external_price_sources'))->row(); if(!$existing && $url!=='') $existing=$this->db->where('source_url',$url)->get($this->table('est_ai_external_price_sources'))->row();
        if($existing){ unset($data['datecreated']); $this->db->where('id',(int)$existing->id)->update($this->table('est_ai_external_price_sources'),$data); $id=(int)$existing->id; }
        else { $this->db->insert($this->table('est_ai_external_price_sources'),$data); $id=(int)$this->db->insert_id(); }
        $this->db->insert($this->table('est_ai_price_history'),['source_id'=>$id,'vendor'=>$vendor,'sku'=>$sku,'item_name'=>$name,'price_low'=>$low,'price_mid'=>$mid,'price_high'=>$high,'last_price'=>$last,'zip_code'=>$data['zip_code'],'source_url'=>$url,'datecreated'=>date('Y-m-d H:i:s')]);
        $costData=['division'=>'External','trade'=>$vendor,'category'=>$data['category'] ?: 'Materials','item_name'=>$name,'description'=>$data['description'],'unit'=>$data['unit'],'material_cost_low'=>$low,'material_cost_typical'=>$mid,'material_cost_high'=>$high,'labor_hours'=>0,'labor_rate'=>0,'waste_factor'=>0,'production_rate'=>'Collected '.date('m/d/Y'),'permit_required'=>0,'inspection_required'=>0,'region'=>'Tampa Bay','active'=>1,'source'=>'Collector: '.$vendor,'source_type'=>$data['source_type'],'source_id'=>$id,'datecreated'=>date('Y-m-d H:i:s')];
        $costExists=$this->db->where('source','Collector: '.$vendor)->where('source_id',$id)->get($this->table('est_ai_cost_database'))->row();
        if($costExists){ unset($costData['datecreated']); $this->db->where('id',(int)$costExists->id)->update($this->table('est_ai_cost_database'),$costData); } else { $this->db->insert($this->table('est_ai_cost_database'),$costData); }
        return $id;
    }

    public function collect_public_url($url, $vendor = 'Public Source')
    {
        $url=trim($url); if($url==='') return 'Please enter a public product or cost URL.';
        $html=@file_get_contents($url, false, stream_context_create(['http'=>['timeout'=>12,'header'=>'User-Agent: SmartChoiceEstimator/1.0']]));
        if(!$html) return 'Could not read the public URL from this server. Import CSV or use the Python collector for this source.';
        $title='Collected Public Price'; if(preg_match('/<title[^>]*>(.*?)<\/title>/is',$html,$m)) $title=trim(html_entity_decode(strip_tags($m[1])));
        $price=0; if(preg_match('/\$\s*([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?|[0-9]+(?:\.[0-9]{2})?)/',$html,$m)) $price=$this->money_to_float($m[1]);
        $id=$this->save_external_price_source(['vendor'=>$vendor,'item_name'=>$title,'last_price'=>$price,'price_low'=>$price,'price_mid'=>$price,'price_high'=>$price,'source_url'=>$url,'source_type'=>'Public URL Collector','category'=>'Public Pricing','unit'=>'each']);
        return $id ? 'Public URL collected successfully.' : 'Could not save collected URL.';
    }

    public function import_collector_csv($file)
    {
        $valid=$this->validate_upload($file,['csv']); if($valid!==true) return $valid;
        $h=fopen($file['tmp_name'],'r'); if(!$h) return 'Could not read CSV file.'; fgetcsv($h); $count=0;
        while(($r=fgetcsv($h))!==false){ $item=['vendor'=>$r[0]??'Public Source','sku'=>$r[1]??'','brand'=>$r[2]??'','item_name'=>$r[3]??'Collected Item','category'=>$r[4]??'Materials','unit'=>$r[5]??'each','price_low'=>$r[6]??0,'price_mid'=>$r[7]??0,'price_high'=>$r[8]??0,'last_price'=>$r[9]??0,'zip_code'=>$r[10]??'','source_url'=>$r[11]??'','image_url'=>$r[12]??'','source_type'=>'CSV Import']; if($this->save_external_price_source($item)) $count++; }
        fclose($h); return $count.' collector records imported successfully.';
    }

    public function export_cost_database_csv(){ $this->csv('estimating_hub_ai_cost_database.csv',['Division','Trade','Category','Item Name','Description','Unit','Material Low','Material Typical','Material High','Labor Hours','Labor Rate','Waste Factor','Production Rate','Permit Required','Inspection Required','Region','Source'], array_map(function($r){return [$r['division'],$r['trade'],$r['category'],$r['item_name'],$r['description'],$r['unit'],$r['material_cost_low'],$r['material_cost_typical'],$r['material_cost_high'],$r['labor_hours'],$r['labor_rate'],$r['waste_factor'],$r['production_rate'],$r['permit_required'],$r['inspection_required'],$r['region'],$r['source'] ?? 'Manual'];},$this->get_cost_items('',50000))); }
    public function export_home_depot_material_list(){ $this->csv('home_depot_pro_desk_material_list.csv',['SKU','Item Name','Quantity','Unit','Target Price','Notes','Project Link'], array_map(function($r){return [$r['sku'],$r['item_name'],'1',$r['unit'],$r['last_price'],'Verify price and availability with Pro Desk',$r['url']];},$this->get_vendor_links(50000))); }
    public function export_training_documents_csv(){ $this->csv('estimating_hub_ai_training_documents.csv',['Title','File Name','Status','Created'], array_map(function($r){return [$r['title'],$r['file_name'],$r['status'],$r['datecreated']];},$this->get_training_documents(50000))); }
    public function export_collector_csv(){ $this->csv('estimating_hub_ai_material_price_collector.csv',['Vendor','SKU','Brand','Item Name','Category','Unit','Low Price','Mid Price','High Price','Last Price','ZIP Code','Source URL','Image URL','Last Checked'], array_map(function($r){return [$r['vendor'],$r['sku'],$r['brand'],$r['item_name'],$r['category'],$r['unit'],$r['price_low'],$r['price_mid'],$r['price_high'],$r['last_price'],$r['zip_code'],$r['source_url'],$r['image_url'],$r['last_checked']];},$this->get_external_sources(50000))); }
    public function export_reports_csv(){ $this->csv('estimating_hub_ai_report_summary.csv',['Metric','Total'], [['Estimates',$this->safe_count('est_ai_estimates')],['Cost Items',$this->safe_count('est_ai_cost_database')],['Training Documents',$this->safe_count('est_ai_training_documents')],['Photos',$this->safe_count('est_ai_photo_intake')],['Home Depot Items',$this->safe_count('est_ai_vendor_price_links')],['Collector Records',$this->safe_count('est_ai_external_price_sources')]]); }
    private function csv($filename,$header,$rows){ header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="'.$filename.'"'); $out=fopen('php://output','w'); fputcsv($out,$header); foreach($rows as $r) fputcsv($out,$r); exit; }
    public function sample_cost_database_csv(){ $this->csv('sample_estimating_cost_database.csv',['Division','Trade','Category','Item Name','Description','Unit','Material Low','Material Typical','Material High','Labor Hours','Labor Rate','Waste Factor','Production Rate','Permit Required','Inspection Required','Region','Source'], [['09','Drywall','Board And Finish','1/2 Inch Drywall','Hang finish texture','sf','2.25','3.85','5.75','0.045','65','0.08','500-900 sf/day','0','0','Tampa Bay','Sample']]); }
    public function sample_home_depot_csv(){ $this->csv('sample_home_depot_material_list.csv',['SKU','Item Name','Home Depot URL','Unit','Target Price'], [['123456','Example Material','https://www.homedepot.com/','each','99.99']]); }
    public function sample_training_documents_csv(){ $this->csv('sample_training_documents.csv',['Title','File Name','Status'], [['Florida Estimating SOP','estimating_sop.pdf','stored']]); }
    public function sample_collector_csv(){ $this->csv('sample_material_price_collector.csv',['Vendor','SKU','Brand','Item Name','Category','Unit','Low Price','Mid Price','High Price','Last Price','ZIP Code','Source URL','Image URL'], [['Home Depot','123456','Example Brand','Example 2x4 Lumber','Lumber','each','3.25','4.10','5.75','4.10','34606','https://www.homedepot.com/','']]); }

    public function mass_delete_table($table,$ids){ if(empty($ids)||!is_array($ids)) return; foreach($ids as $id){$this->db->where('id',(int)$id)->delete($table);} $this->log('Mass Delete',count($ids).' rows from '.$table); }
    public function log($action,$desc=''){ if($this->db->table_exists($this->table('est_ai_activity_log'))) $this->db->insert($this->table('est_ai_activity_log'),['action'=>$action,'description'=>$desc,'staff_id'=>get_staff_user_id(),'datecreated'=>date('Y-m-d H:i:s')]); }
    public function validate_upload($file,$exts){ $ext=strtolower(pathinfo($file['name'] ?? '',PATHINFO_EXTENSION)); if(!in_array($ext,$exts)) return 'File type not allowed. Allowed: '.implode(', ',$exts); if(empty($file['tmp_name']) || (!is_uploaded_file($file['tmp_name']) && !file_exists($file['tmp_name']))) return 'Upload failed. Please choose the file again.'; return true; }
    public function money_to_float($v){ return estimating_hub_ai_clean_money($v); }

    public function seed_cost_items_if_empty(){ if($this->safe_count('est_ai_cost_database')==0){ require_once(module_dir_path('estimating_hub_ai').'install.php'); if(function_exists('estimating_hub_ai_seed_cost_items')) estimating_hub_ai_seed_cost_items(); if(function_exists('estimating_hub_ai_seed_expanded_cost_items')) estimating_hub_ai_seed_expanded_cost_items(); if(function_exists('estimating_hub_ai_seed_labor_templates_v104')) estimating_hub_ai_seed_labor_templates_v104(); } }
}
