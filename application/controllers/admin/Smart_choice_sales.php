<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_sales extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_staff_logged_in()) {
            access_denied('Smart Choice Sales');
        }
    }

    public function add_status()
    {
        if (!$this->input->is_ajax_request() || !$this->input->post()) {
            show_404();
        }
        $type = strtolower(trim((string) $this->input->post('document_type')));
        $name = trim((string) $this->input->post('name'));
        $color = trim((string) $this->input->post('color'));
        if (!in_array($type, ['estimate','proposal','invoice','credit_note','payment'], true) || $name === '') {
            echo json_encode(['success'=>false,'message'=>_l('invalid_form_data')]); return;
        }
        if (!preg_match('/^#[0-9a-f]{6}$/i', $color)) { $color = '#169179'; }
        $table = db_prefix().'sc_sales_statuses';
        $exists = $this->db->where(['document_type'=>$type,'name'=>$name])->get($table)->row();
        if ($exists) { echo json_encode(['success'=>true,'id'=>$exists->id,'name'=>$exists->name]); return; }
        $this->db->insert($table,['document_type'=>$type,'name'=>$name,'color'=>$color,'sort_order'=>0,'active'=>1,'created_at'=>date('Y-m-d H:i:s')]);
        echo json_encode(['success'=>$this->db->affected_rows()>0,'id'=>$this->db->insert_id(),'name'=>$name]);
    }

    public function customer_summary()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $type      = strtolower(trim((string) $this->input->get('rel_type')));
        $id        = (int) $this->input->get('rel_id');
        $projectId = (int) $this->input->get('project_id');
        $out       = ['success' => false, 'name' => '', 'email' => '', 'phone' => '', 'address' => ''];

        if ($id <= 0) {
            echo json_encode($out);
            return;
        }

        if ($type === 'lead') {
            $lead = $this->db->where('id', $id)->get(db_prefix() . 'leads')->row_array();
            if ($lead) {
                $out['success'] = true;
                $out['name']    = trim((string)($lead['name'] ?? ''));
                $out['email']   = trim((string)($lead['email'] ?? ''));
                $out['phone']   = trim((string)($lead['phonenumber'] ?? ''));
                $out['address'] = trim((string)($lead['address'] ?? ''));
            }
        } else {
            $client = $this->db->where('userid', $id)->get(db_prefix() . 'clients')->row_array();
            if ($client) {
                $contact = $this->db->where('userid', $id)->order_by('is_primary', 'DESC')->order_by('id', 'ASC')->get(db_prefix() . 'contacts')->row_array();
                $out['success'] = true;
                $out['name']    = trim((string)($client['company'] ?? ''));
                if ($out['name'] === '' && $contact) {
                    $out['name'] = trim((string)($contact['firstname'] ?? '') . ' ' . (string)($contact['lastname'] ?? ''));
                }
                $out['email'] = $contact ? trim((string)($contact['email'] ?? '')) : '';
                $out['phone'] = trim((string)($client['phonenumber'] ?? ''));
                if ($out['phone'] === '' && $contact) {
                    $out['phone'] = trim((string)($contact['phonenumber'] ?? ''));
                }
                $out['address'] = trim((string)($client['shipping_street'] ?? '')) ?: trim((string)($client['billing_street'] ?? ''));
            }
        }

        if ($projectId > 0) {
            $fields = $this->db->where('fieldto', 'projects')->where('active', 1)->get(db_prefix() . 'customfields')->result_array();
            foreach ($fields as $field) {
                if (stripos((string)$field['name'], 'address') !== false) {
                    $value = trim((string)get_custom_field_value($projectId, $field['id'], 'projects', false));
                    if ($value !== '') {
                        $out['address'] = $value;
                        break;
                    }
                }
            }
        }

        echo json_encode($out);
    }
}
