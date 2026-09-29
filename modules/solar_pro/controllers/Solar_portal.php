<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Solar_portal extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('solar_pro_model');
        $this->load->library('solar_pro/Solar_pro_calculator');
        $this->load->library('solar_pro/Solar_pro_google');
        $this->load->helper('solar_pro/solar_pro');
    }

    public function estimate()
    {
        $portalLang = solar_pro_public_language_code($this->input->get('lang', true) ?: $this->input->post('portal_lang', true));
        $data['portal_lang'] = $portalLang;
        $data['field_errors'] = [];
        if (solar_pro_setting('solar_pro_public_calculator_enabled', '1') !== '1') {
            show_404();
        }
        if ($this->input->post()) {
            $input = $this->input->post(null, true);
            $data['old'] = $input;
            $firstName = trim((string)($input['first_name'] ?? ''));
            $lastName  = trim((string)($input['last_name'] ?? ''));
            $email     = strtolower(trim((string)($input['email'] ?? '')));
            $phoneRaw  = trim((string)($input['phone'] ?? ''));
            $address   = trim((string)($input['address'] ?? ''));
            $digits    = preg_replace('/\D+/', '', $phoneRaw);
            if (strlen($digits) === 11 && substr($digits, 0, 1) === '1') { $digits = substr($digits, 1); }
            if ($firstName === '') { $data['field_errors']['first_name'] = solar_pro_public_l('solar_pro_required_first_name', $portalLang); }
            if ($lastName === '') { $data['field_errors']['last_name'] = solar_pro_public_l('solar_pro_required_last_name', $portalLang); }
            if ($email === '') { $data['field_errors']['email'] = solar_pro_public_l('solar_pro_required_email', $portalLang); }
            if ($phoneRaw === '') { $data['field_errors']['phone'] = solar_pro_public_l('solar_pro_required_phone', $portalLang); }
            if ($address === '') { $data['field_errors']['address'] = solar_pro_public_l('solar_pro_required_address', $portalLang); }
            if (!empty($data['field_errors'])) {
                $data['error'] = solar_pro_public_l('solar_pro_portal_required_contact_fields', $portalLang);
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['field_errors']['email'] = solar_pro_public_l('solar_pro_invalid_email', $portalLang);
                $data['error'] = solar_pro_public_l('solar_pro_invalid_email', $portalLang);
            } elseif (strlen($digits) !== 10) {
                $data['field_errors']['phone'] = solar_pro_public_l('solar_pro_invalid_phone', $portalLang);
                $data['error'] = solar_pro_public_l('solar_pro_invalid_phone', $portalLang);
            } else {
                $duplicate = $this->portalIdentityDuplicateType($email, $digits);
                if ($duplicate !== '') {
                    $data['field_errors'][$duplicate] = solar_pro_public_l($duplicate === 'email' ? 'solar_pro_duplicate_email' : 'solar_pro_duplicate_phone', $portalLang);
                    $data['error'] = solar_pro_public_l('solar_pro_duplicate_contact', $portalLang);
                } elseif ((float) ($input['annual_consumption_kwh'] ?? 0) <= 0) {
                    $data['error'] = solar_pro_public_l('solar_pro_validation_address_consumption', $portalLang);
                } else {
                $input['email'] = $email;
                $input['phone'] = '+1 ' . substr($digits,0,3) . '-' . substr($digits,3,3) . '-' . substr($digits,6,4);
                $google = null;
                $input['analysis_method'] = in_array(($input['analysis_method'] ?? 'google_solar'), ['google_solar','quick_estimate'], true) ? $input['analysis_method'] : 'google_solar';
                if ($input['analysis_method'] === 'google_solar') {
                    $geo = $this->solar_pro_google->geocode(trim($input['address'] . ', ' . ($input['city'] ?? '') . ', ' . ($input['state'] ?? 'FL') . ' ' . ($input['zip'] ?? '')));
                    if (!empty($geo['success'])) {
                        $input['latitude'] = $geo['latitude'];
                        $input['longitude'] = $geo['longitude'];
                        $solar = $this->solar_pro_google->buildingInsights($geo['latitude'], $geo['longitude']);
                        if (!empty($solar['success'])) {
                            $google = $solar['data'];
                            $input['_google_response'] = $google;
                        }
                    }
                }
                $result = $this->solar_pro_calculator->calculate($input, $google);
                $id = $this->solar_pro_model->saveAnalysis($input, $result, 0);
                if ($id) { $this->solar_pro_model->saveDocuments($id, $_FILES, 0); }
                if ($id && solar_pro_setting('solar_pro_public_create_lead', '1') === '1' && !empty($input['email'])) {
                    try { $this->solar_pro_model->createLeadFromAnalysis($id); } catch (Throwable $e) { log_message('error', 'Solar Pro public lead: ' . $e->getMessage()); }
                }
                $analysis = $this->solar_pro_model->getAnalysis($id);
                redirect(site_url('solar_pro/portal/' . $analysis['public_token']));
                }
            }
        }
        $data['title'] = _l('solar_pro_free_solar_report');
        $data['utilities'] = $this->solar_pro_model->utilities();
        $this->load->view('public/estimate', $data);
    }

    private function portalIdentityDuplicateType($email, $phoneDigits)
    {
        $email = strtolower(trim((string) $email));
        $phoneDigits = preg_replace('/\D+/', '', (string) $phoneDigits);
        $tables = [
            [db_prefix().'solar_analyses', 'email', 'phone'],
            [db_prefix().'leads', 'email', 'phonenumber'],
            [db_prefix().'contacts', 'email', 'phonenumber'],
            [db_prefix().'staff', 'email', 'phonenumber'],
        ];
        foreach ($tables as $spec) {
            [$table, $emailCol, $phoneCol] = $spec;
            if (!$this->db->table_exists($table)) { continue; }
            if ($email !== '' && $this->db->field_exists($emailCol, $table)) {
                $this->db->where('LOWER(' . $emailCol . ') = ' . $this->db->escape($email), null, false);
                if ($this->db->count_all_results($table) > 0) { return 'email'; }
            }
            if ($phoneDigits !== '' && $this->db->field_exists($phoneCol, $table)) {
                $rows = $this->db->select($phoneCol)->where($phoneCol.' IS NOT NULL', null, false)->where($phoneCol.' !=', '')->get($table)->result_array();
                foreach ($rows as $row) {
                    $d = preg_replace('/\D+/', '', (string) ($row[$phoneCol] ?? ''));
                    if (strlen($d) === 11 && substr($d, 0, 1) === '1') { $d = substr($d, 1); }
                    if ($d !== '' && $d === $phoneDigits) { return 'phone'; }
                }
            }
        }
        return '';
    }

    public function view($token)
    {
        $analysis = $this->solar_pro_model->getByToken($token);
        if (!$analysis) {
            show_404();
        }
        $data['title'] = _l('solar_pro_your_solar_report');
        $data['analysis'] = $analysis;
        $data['months'] = solar_pro_month_names();
        $data['documents'] = $this->solar_pro_model->documents((int)$analysis['id']);
        $data['finance'] = solar_pro_finance_summary($analysis);
        $this->load->view('public/report', $data);
    }

    public function pdf($token)
    {
        $analysis = $this->solar_pro_model->getByToken($token);
        if (!$analysis) { show_404(); }
        $this->load->helper('pdf');
        $pdf = app_pdf('solar-analysis', module_dir_path('solar_pro','libraries/pdf/Solar_analysis_pdf'), $analysis);
        $pdf->Output('solar-proposal-' . (int)$analysis['id'] . '.pdf', 'D');
    }

    public function document($token, $documentId)
    {
        $analysis = $this->solar_pro_model->getByToken($token);
        if (!$analysis) { show_404(); }
        $doc = $this->db->where('id',(int)$documentId)->where('analysis_id',(int)$analysis['id'])->get(db_prefix().'solar_documents')->row_array();
        if (!$doc) { show_404(); }
        $path = FCPATH . 'uploads/solar_pro/' . (int)$analysis['id'] . '/' . basename($doc['stored_name']);
        if (!is_file($path)) { show_404(); }
        $this->output->set_content_type($doc['file_type'] ?: 'application/octet-stream');
        $this->output->set_header('Content-Disposition: inline; filename="' . str_replace('"','',basename($doc['file_name'])) . '"');
        $this->output->set_output(file_get_contents($path));
    }

    public function sign($token)
    {
        $contract = $this->db->where('public_token', $token)->get(db_prefix() . 'solar_contracts')->row_array();
        if (!$contract) {
            show_404();
        }
        if ($this->input->post()) {
            if ($contract['status'] === 'signed') {
                redirect(site_url('solar_pro/portal/' . $token));
            }
            $signature = (string) $this->input->post('signature_data', false);
            $name = trim((string) $this->input->post('signed_name', true));
            $email = trim((string) $this->input->post('signed_email', true));
            $phone = trim((string) $this->input->post('signed_phone', true));
            $firstName = trim((string) $this->input->post('signed_first_name', true));
            $lastName = trim((string) $this->input->post('signed_last_name', true));
            $initials = $this->input->post('initials', false);
            if (!is_array($initials)) { $initials = []; }
            preg_match_all('/\{solar_initial:([a-zA-Z0-9_-]+)\}/', $contract['content'], $matches);
            $requiredInitials = array_values(array_unique($matches[1] ?? []));
            $missingInitial = false;
            foreach ($requiredInitials as $fieldKey) {
                if (empty($initials[$fieldKey])) { $missingInitial = true; break; }
            }
            if ($signature === '' || $name === '' || $email === '' || $phone === '' || $missingInitial) {
                $data['error'] = _l('solar_pro_signature_initials_required');
            } else {
                foreach ($requiredInitials as $fieldKey) {
                    $this->db->where('contract_id', $contract['id'])->where('field_key', $fieldKey);
                    $existingInitial = $this->db->get(db_prefix() . 'solar_contract_initials')->row_array();
                    $initialData = ['initials_data' => (string) $initials[$fieldKey], 'ip_address' => $this->input->ip_address(), 'created_at' => date('Y-m-d H:i:s')];
                    if ($existingInitial) {
                        $this->db->where('id', $existingInitial['id'])->update(db_prefix() . 'solar_contract_initials', $initialData);
                    } else {
                        $initialData['contract_id'] = $contract['id']; $initialData['field_key'] = $fieldKey;
                        $this->db->insert(db_prefix() . 'solar_contract_initials', $initialData);
                    }
                }
                $hash = hash('sha256', $contract['content'] . $name . $email . $signature . json_encode($initials) . date('c'));
                $this->db->where('id', $contract['id'])->update(db_prefix() . 'solar_contracts', [
                    'status' => 'signed',
                    'signed_name' => $name,
                    'signed_email' => $email,
                    'signed_phone' => $phone,
                    'signed_first_name' => $firstName,
                    'signed_last_name' => $lastName,
                    'signature_data' => $signature,
                    'signature_ip' => $this->input->ip_address(),
                    'signature_user_agent' => substr((string) $this->input->user_agent(), 0, 255),
                    'signed_at' => date('Y-m-d H:i:s'),
                    'document_hash' => $hash,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $contract = $this->db->where('id', $contract['id'])->get(db_prefix() . 'solar_contracts')->row_array();
                $data['success'] = _l('solar_pro_contract_signed');
            }
        }
        $data['title'] = $contract['title'];
        $data['contract'] = $contract;
        $this->load->view('public/contract', $data);
    }

    public function proposal($token)
    {
        $proposal=$this->solar_pro_model->getProposalByToken((string)$token); if(!$proposal){show_404();}
        if($this->input->post('action')){
            $action=$this->input->post('action',true); $name=trim((string)$this->input->post('name',true)); $email=trim((string)$this->input->post('email',true)); $phone=trim((string)$this->input->post('phone',true));
            if($action==='accept'){
                if($name===''){ redirect(site_url('solar_pro/proposal/'.$token.'?error=name')); }
                if(!filter_var($email,FILTER_VALIDATE_EMAIL)){ redirect(site_url('solar_pro/proposal/'.$token.'?error=email')); }
                $digits=preg_replace('/\D+/','',$phone);
                if(strlen($digits)===11 && substr($digits,0,1)==='1'){$digits=substr($digits,1);}
                if($digits!=='' && strlen($digits)!==10){ redirect(site_url('solar_pro/proposal/'.$token.'?error=phone')); }
                $phone=$digits!==''?'+1 '.substr($digits,0,3).'-'.substr($digits,3,3).'-'.substr($digits,6,4):'';
                $this->db->where('id',$proposal['id'])->update(db_prefix().'solar_proposals',['status'=>'accepted','accepted_name'=>$name,'accepted_email'=>$email,'accepted_phone'=>$phone,'accepted_ip'=>$this->input->ip_address(),'accepted_user_agent'=>substr((string)$this->input->user_agent(),0,255),'accepted_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
                redirect(site_url('solar_pro/proposal/'.$token));
            }
            if($action==='decline'){$this->db->where('id',$proposal['id'])->update(db_prefix().'solar_proposals',['status'=>'declined','declined_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]); redirect(site_url('solar_pro/proposal/'.$token));}
            if($action==='comment'){$msg=trim((string)$this->input->post('message',true)); if($msg!==''){$this->db->insert(db_prefix().'solar_proposal_comments',['proposal_id'=>$proposal['id'],'staff_id'=>0,'contact_name'=>$name,'contact_email'=>$email,'message'=>$msg,'created_at'=>date('Y-m-d H:i:s')]);} redirect(site_url('solar_pro/proposal/'.$token.'#discussion'));}
        }
        $data['proposal']=$this->solar_pro_model->getProposal((int)$proposal['id']); $data['analysis']=$data['proposal']['analysis']; $data['finance']=solar_pro_finance_summary($data['analysis']); $data['environment']=solar_pro_environment_summary($data['analysis']); $data['title']=$proposal['title'];
        $this->load->view('public/proposal',$data);
    }

    public function proposal_pdf($token)
    {
        $proposal=$this->solar_pro_model->getProposalByToken((string)$token); if(!$proposal){show_404();}
        $this->load->helper('pdf'); $pdf=app_pdf('solar-proposal',module_dir_path('solar_pro','libraries/pdf/Solar_proposal_pdf'),$proposal); $first=trim((string)$proposal['analysis']['first_name']); $name=preg_replace('/[^A-Za-z0-9 _-]/','',$first!==''?$first:'Customer'); $pdf->Output($name.' Solar Proposal.pdf','D');
    }

    public function my()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }
        $contactId = get_contact_user_id();
        $contact = $this->db->where('id', $contactId)->get(db_prefix() . 'contacts')->row();
        $data['title'] = _l('solar_pro_my_solar');
        $data['analyses'] = [];
        if ($contact) {
            $this->db->where('client_id', $contact->userid);
            if (!empty($contact->email)) {
                $this->db->or_where('email', $contact->email);
            }
            $data['analyses'] = $this->db->order_by('id', 'DESC')->get(db_prefix() . 'solar_analyses')->result_array();
        }
        $this->load->view('public/my', $data);
    }
}
