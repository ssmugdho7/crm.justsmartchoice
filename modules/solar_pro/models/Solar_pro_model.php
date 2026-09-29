<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Solar_pro_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('solar_pro/solar_pro');
    }

    public function dashboardStats($staffId = null, $global = false)
    {
        if (!$global && $staffId) {
            $this->db->where('created_by', (int) $staffId);
        }
        $total = $this->db->count_all_results(db_prefix() . 'solar_analyses');

        $this->db->select('SUM(system_kw) kw, SUM(system_price) revenue, SUM(panel_count) panels, SUM(annual_production_kwh) production');
        if (!$global && $staffId) {
            $this->db->where('created_by', (int) $staffId);
        }
        $sum = $this->db->get(db_prefix() . 'solar_analyses')->row_array() ?: [];
        return [
            'analyses' => $total,
            'kw' => (float) ($sum['kw'] ?? 0),
            'revenue' => (float) ($sum['revenue'] ?? 0),
            'panels' => (int) ($sum['panels'] ?? 0),
            'production' => (float) ($sum['production'] ?? 0),
        ];
    }

    public function getAnalyses($staffId = null, $global = false)
    {
        $this->db->order_by('id', 'DESC');
        if (!$global && $staffId) {
            $this->db->where('created_by', (int) $staffId);
        }
        return $this->db->get(db_prefix() . 'solar_analyses')->result_array();
    }

    public function getAnalysis($id)
    {
        $analysis = $this->db->where('id', (int) $id)->get(db_prefix() . 'solar_analyses')->row_array();
        if (!$analysis) {
            return null;
        }
        $analysis['monthly'] = $this->db->where('analysis_id', (int) $id)->order_by('month_number', 'ASC')->get(db_prefix() . 'solar_production_monthly')->result_array();
        $analysis['contract'] = $this->db->where('analysis_id', (int) $id)->order_by('id', 'DESC')->get(db_prefix() . 'solar_contracts')->row_array();
        $analysis['proposal'] = $this->db->where('analysis_id', (int) $id)->order_by('id', 'DESC')->get(db_prefix() . 'solar_proposals')->row_array();
        $analysis['panel_equipment'] = !empty($analysis['panel_equipment_id']) ? $this->db->where('id',(int)$analysis['panel_equipment_id'])->get(db_prefix().'solar_equipment')->row_array() : null;
        $analysis['inverter_equipment'] = !empty($analysis['inverter_equipment_id']) ? $this->db->where('id',(int)$analysis['inverter_equipment_id'])->get(db_prefix().'solar_equipment')->row_array() : null;
        return $analysis;
    }

    public function getByToken($token)
    {
        $row = $this->db->where('public_token', $token)->get(db_prefix() . 'solar_analyses')->row_array();
        return $row ? $this->getAnalysis($row['id']) : null;
    }

    public function saveAnalysis(array $input, array $result, $createdBy = 0)
    {
        $data = [
            'public_token' => solar_pro_random_token(),
            'status' => 'draft',
            'analysis_method' => trim((string) ($input['analysis_method'] ?? 'auto')),
            'first_name' => trim((string) ($input['first_name'] ?? '')),
            'last_name' => trim((string) ($input['last_name'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'phone' => trim((string) ($input['phone'] ?? '')),
            'address' => trim((string) ($input['address'] ?? '')),
            'city' => trim((string) ($input['city'] ?? '')),
            'state' => trim((string) ($input['state'] ?? 'FL')),
            'zip' => trim((string) ($input['zip'] ?? '')),
            'latitude' => $input['latitude'] ?? null,
            'longitude' => $input['longitude'] ?? null,
            'utility_id' => !empty($input['utility_id']) ? (int) $input['utility_id'] : null,
            'utility_rate_id' => !empty($input['utility_rate_id']) ? (int) $input['utility_rate_id'] : null,
            'annual_consumption_kwh' => (float) ($input['annual_consumption_kwh'] ?? 0),
            'average_monthly_bill' => (float) ($input['average_monthly_bill'] ?? 0),
            'panel_equipment_id' => !empty($input['panel_equipment_id']) ? (int) $input['panel_equipment_id'] : null,
            'inverter_equipment_id' => !empty($input['inverter_equipment_id']) ? (int) $input['inverter_equipment_id'] : null,
            'panel_watts' => $result['panel_watts'],
            'panel_count' => $result['panel_count'],
            'system_kw' => $result['system_kw'],
            'annual_production_kwh' => $result['annual_production_kwh'],
            'solar_offset_pct' => $result['solar_offset_pct'],
            'system_price' => $result['system_price'],
            'year1_savings' => $result['year1_savings'],
            'payback_years' => $result['payback_years'],
            'lifetime_savings' => $result['lifetime_savings'],
            'analysis_source' => $result['analysis_source'],
            'imagery_quality' => $result['imagery_quality'],
            'google_building_name' => $result['google_building_name'],
            'google_response_json' => isset($input['_google_response']) ? json_encode($input['_google_response']) : null,
            'assumptions_json' => json_encode($result['assumptions']),
            'created_by' => (int) $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(db_prefix() . 'solar_analyses', $data);
        $id = (int) $this->db->insert_id();
        foreach ($result['monthly'] as $row) {
            $row['analysis_id'] = $id;
            $this->db->insert(db_prefix() . 'solar_production_monthly', $row);
        }
        return $id;
    }

    public function createLeadFromAnalysis($analysisId)
    {
        $analysis = $this->getAnalysis($analysisId);
        if (!$analysis || !empty($analysis['lead_id']) || empty($analysis['email'])) {
            return $analysis['lead_id'] ?? null;
        }
        $this->load->model('leads_model');
        $source = $this->ensureLeadSource();
        $status = $this->db->order_by('statusorder', 'ASC')->get(db_prefix() . 'leads_status')->row();
        $leadData = [
            'name' => trim($analysis['first_name'] . ' ' . $analysis['last_name']) ?: _l('solar_pro_solar_lead'),
            'email' => $analysis['email'],
            'phonenumber' => $analysis['phone'],
            'address' => $analysis['address'],
            'city' => $analysis['city'],
            'state' => $analysis['state'],
            'zip' => $analysis['zip'],
            'source' => $source,
            'status' => $status ? $status->id : 1,
            'description' => _l('solar_pro_lead_description', $analysis['system_kw'] . ' kW / ' . $analysis['panel_count']),
            'assigned' => $analysis['created_by'],
        ];
        $leadId = $this->leads_model->add($leadData);
        if ($leadId) {
            $this->db->where('id', $analysisId)->update(db_prefix() . 'solar_analyses', ['lead_id' => $leadId, 'status' => 'lead_created', 'updated_at' => date('Y-m-d H:i:s')]);
        }
        return $leadId;
    }

    private function ensureLeadSource()
    {
        $row = $this->db->where('name', 'Solar Portal')->get(db_prefix() . 'leads_sources')->row();
        if ($row) {
            return $row->id;
        }
        $this->db->insert(db_prefix() . 'leads_sources', ['name' => 'Solar Portal']);
        return (int) $this->db->insert_id();
    }

    public function utilities()
    {
        return $this->db->order_by('name', 'ASC')->get(db_prefix() . 'solar_utilities')->result_array();
    }

    public function utilityRates($utilityId = null)
    {
        if ($utilityId) {
            $this->db->where('utility_id', (int) $utilityId);
        }
        return $this->db->order_by('effective_date', 'DESC')->get(db_prefix() . 'solar_utility_rates')->result_array();
    }

    public function equipment($type = null)
    {
        if ($type) {
            $this->db->where('type', $type);
        }
        return $this->db->order_by('manufacturer', 'ASC')->order_by('model', 'ASC')->get(db_prefix() . 'solar_equipment')->result_array();
    }
    public function saveDocuments($analysisId, array $files, $uploadedBy = 0)
    {
        $analysisId = (int) $analysisId;
        if ($analysisId <= 0 || empty($files)) {
            return 0;
        }
        $base = FCPATH . 'uploads/solar_pro/' . $analysisId . '/';
        if (!is_dir($base) && !@mkdir($base, 0755, true) && !is_dir($base)) {
            log_message('error', 'Solar Pro could not create upload directory: ' . $base);
            return 0;
        }
        $allowed = ['jpg','jpeg','png','webp','pdf'];
        $count = 0;
        foreach ($files as $documentType => $group) {
            if (!is_array($group) || !isset($group['name'])) { continue; }
            $names = is_array($group['name']) ? $group['name'] : [$group['name']];
            $tmps  = is_array($group['tmp_name']) ? $group['tmp_name'] : [$group['tmp_name']];
            $errs  = is_array($group['error']) ? $group['error'] : [$group['error']];
            $sizes = is_array($group['size']) ? $group['size'] : [$group['size']];
            $types = is_array($group['type']) ? $group['type'] : [$group['type']];
            foreach ($names as $i => $original) {
                if (($errs[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($tmps[$i]) || !is_uploaded_file($tmps[$i])) { continue; }
                if (($sizes[$i] ?? 0) > 15 * 1024 * 1024) { continue; }
                $ext = strtolower(pathinfo((string) $original, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) { continue; }
                $stored = uniqid('solar_', true) . '.' . $ext;
                if (!@move_uploaded_file($tmps[$i], $base . $stored)) { continue; }
                $this->db->insert(db_prefix() . 'solar_documents', [
                    'analysis_id' => $analysisId,
                    'contract_id' => null,
                    'document_type' => substr((string) $documentType, 0, 40),
                    'file_name' => substr((string) $original, 0, 255),
                    'stored_name' => $stored,
                    'file_type' => substr((string) ($types[$i] ?? ''), 0, 100),
                    'file_size' => (int) ($sizes[$i] ?? 0),
                    'uploaded_by' => (int) $uploadedBy,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }
        }
        return $count;
    }

    public function documents($analysisId)
    {
        return $this->db->where('analysis_id', (int) $analysisId)->order_by('id', 'ASC')->get(db_prefix() . 'solar_documents')->result_array();
    }

    public function ensureCustomerFromAnalysis($analysisId)
    {
        $analysis = $this->getAnalysis((int) $analysisId);
        if (!$analysis) { return 0; }
        if (!empty($analysis['client_id'])) { return (int) $analysis['client_id']; }

        if (!empty($analysis['email'])) {
            $contact = $this->db->where('email', $analysis['email'])->limit(1)->get(db_prefix() . 'contacts')->row_array();
            if ($contact) {
                $clientId = (int) $contact['userid'];
                $this->db->where('id', (int) $analysisId)->update(db_prefix() . 'solar_analyses', ['client_id' => $clientId, 'updated_at' => date('Y-m-d H:i:s')]);
                return $clientId;
            }
        }

        $this->load->model('clients_model');
        $company = trim($analysis['first_name'] . ' ' . $analysis['last_name']);
        if ($company === '') { $company = _l('solar_pro_solar_customer'); }
        $clientId = $this->clients_model->add([
            'company' => $company,
            'firstname' => $analysis['first_name'],
            'lastname' => $analysis['last_name'],
            'email' => $analysis['email'],
            'phonenumber' => $analysis['phone'],
            'address' => $analysis['address'],
            'city' => $analysis['city'],
            'state' => $analysis['state'],
            'zip' => $analysis['zip'],
            'country' => 0,
            'active' => 1,
            'donotsendwelcomeemail' => true,
        ], true);
        if ($clientId) {
            $this->db->where('id', (int) $analysisId)->update(db_prefix() . 'solar_analyses', ['client_id' => (int) $clientId, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        return (int) $clientId;
    }


    public function contractTemplates($activeOnly = false)
    {
        if ($activeOnly) { $this->db->where('active',1); }
        return $this->db->order_by('is_system_default','DESC')->order_by('id','ASC')->get(db_prefix().'solar_contract_templates')->result_array();
    }

    public function getProposal($id)
    {
        $proposal=$this->db->where('id',(int)$id)->get(db_prefix().'solar_proposals')->row_array();
        if(!$proposal){ return null; }
        $proposal['analysis']=$this->getAnalysis((int)$proposal['analysis_id']);
        $proposal['comments']=$this->db->where('proposal_id',(int)$id)->order_by('id','ASC')->get(db_prefix().'solar_proposal_comments')->result_array();
        $proposal['template']=$this->getProposalTemplate((int)($proposal['template_id'] ?? 0));
        return $proposal;
    }

    public function getProposalByToken($token)
    {
        $row=$this->db->where('public_token',(string)$token)->get(db_prefix().'solar_proposals')->row_array();
        return $row ? $this->getProposal((int)$row['id']) : null;
    }

    public function proposalTemplates($activeOnly = false)
    {
        if ($activeOnly) { $this->db->where('active',1); }
        return $this->db->order_by('is_default','DESC')->order_by('id','ASC')->get(db_prefix().'solar_proposal_templates')->result_array();
    }

    public function getProposalTemplate($id = 0)
    {
        if ($id) { return $this->db->where('id',(int)$id)->get(db_prefix().'solar_proposal_templates')->row_array(); }
        $row=$this->db->where('active',1)->where('is_default',1)->get(db_prefix().'solar_proposal_templates')->row_array();
        if (!$row) { $row=$this->db->where('active',1)->order_by('id','ASC')->get(db_prefix().'solar_proposal_templates')->row_array(); }
        return $row ?: null;
    }

    public function createProposal($analysisId, $createdBy = 0, $templateId = 0)
    {
        $analysis=$this->getAnalysis((int)$analysisId); if(!$analysis){ return 0; }
        $existing=$this->db->where('analysis_id',(int)$analysisId)->where_in('status',['draft','sent','accepted'])->order_by('id','DESC')->get(db_prefix().'solar_proposals')->row_array();
        if($existing){ return (int)$existing['id']; }
        $template=$this->getProposalTemplate((int)$templateId);
        $first=trim((string)$analysis['first_name']);
        $display=$first!==''?$first:trim($analysis['first_name'].' '.$analysis['last_name']);
        if($display===''){ $display=_l('solar_pro_customer'); }
        $this->db->insert(db_prefix().'solar_proposals',[
            'analysis_id'=>(int)$analysisId,
            'template_id'=>$template?(int)$template['id']:null,
            'title'=>$display.' '._l('solar_pro_solar_proposal'),
            'status'=>'draft','public_token'=>solar_pro_random_token(),'expires_on'=>date('Y-m-d',strtotime('+30 days')),
            'created_by'=>(int)$createdBy,'created_at'=>date('Y-m-d H:i:s')
        ]);
        return (int)$this->db->insert_id();
    }

    public function createIndependentContract($analysisId, $templateId, $createdBy = 0, $title = '')
    {
        $analysis=$this->getAnalysis((int)$analysisId); if(!$analysis){ return 0; }
        $template=$this->db->where('id',(int)$templateId)->where('active',1)->get(db_prefix().'solar_contract_templates')->row_array();
        if(!$template){ return 0; }
        $content=solar_pro_apply_merge_fields($template['content'],$analysis);
        $name=trim($analysis['first_name'].' '.$analysis['last_name']);
        $this->db->insert(db_prefix().'solar_contracts',[
            'analysis_id'=>(int)$analysisId,'native_contract_id'=>null,'template_id'=>(int)$templateId,
            'title'=>$title!==''?$title:($template['name'].' - '.$name),'contract_value'=>(float)$analysis['system_price'],
            'start_date'=>date('Y-m-d'),'end_date'=>null,'content'=>$content,'status'=>'draft','version_no'=>1,
            'public_token'=>solar_pro_random_token(),'created_by'=>(int)$createdBy,'created_at'=>date('Y-m-d H:i:s')
        ]);
        return (int)$this->db->insert_id();
    }

}
