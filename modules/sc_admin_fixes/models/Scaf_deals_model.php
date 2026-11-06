<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Scaf_deals_model extends App_Model
{
    public function convert($id)
    {
        if (!$this->db->trans_begin()) throw new RuntimeException('Unable to start conversion.');
        try {
            $deal=$this->db->query('SELECT * FROM tbl_deals WHERE id=? FOR UPDATE',[(int)$id])->row();
            if (!$deal || !scaf_deals_conversion_allowed($deal)) throw new RuntimeException('Deal is unavailable for conversion.');
            if (!empty($deal->project_id)) {
                $project=$this->db->where('id',(int)$deal->project_id)->get(db_prefix().'projects')->row();
                if (!$project) throw new RuntimeException('Linked project is missing; administrator review required.');
                if (!$this->db->trans_commit()) throw new RuntimeException('Unable to confirm existing project.');
                return ['project_id'=>(int)$deal->project_id,'created'=>false];
            }
            $stage=$this->db->where('stage_id',(int)$deal->stage_id)->get('tbl_deals_stages')->row();
            if ($deal->status !== 'won' && (!$stage || mb_strtolower(trim($stage->stage_name))!=='closed won')) throw new RuntimeException('Only Closed Won deals can become projects.');
            $client=scaf_deals_customer($deal);
            $customer=$client ? $this->db->where('userid',$client)->get(db_prefix().'clients')->row() : null;
            if (!$client || !$customer || (!staff_can('view','customers') && !is_customer_admin($client))) throw new RuntimeException('Select one accessible CRM customer on this deal before conversion.');
            $currency=get_currency($deal->currency);
            $customerCurrency=!empty($customer->default_currency)?(int)$customer->default_currency:(int)get_base_currency()->id;
            if(!$currency || (int)$currency->id!==$customerCurrency)throw new RuntimeException('Deal currency must match the customer project currency. No exchange conversion is performed.');
            $this->load->model('projects_model');
            $data=['name'=>$deal->title,'description'=>$deal->notes ?? '', 'clientid'=>$client,'billing_type'=>1,'status'=>2,'start_date'=>_d(date('Y-m-d')),'project_cost'=>$deal->deal_value,'progress'=>0];
            if(!empty($deal->days_to_close) && preg_match('/^\d{4}-\d{2}-\d{2}$/D',$deal->days_to_close))$data['deadline']=_d($deal->days_to_close);
            $project=$this->projects_model->add($data);
            if (!$project) throw new RuntimeException('Project creation failed.');
            $this->db->where('id',(int)$id)->update('tbl_deals',['project_id'=>$project,'convert_to_project'=>'on']);
            if (!$this->db->trans_status() || !$this->db->trans_commit()) throw new RuntimeException('Conversion could not be saved.');
            return ['project_id'=>(int)$project,'created'=>true];
        } catch (Throwable $error) { $this->db->trans_rollback(); throw $error; }
    }
}
