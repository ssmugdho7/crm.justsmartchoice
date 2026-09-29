<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_365 extends CI_Migration
{
    public function up()
    {
        // Preserve any API key already stored under an older option name.
        add_option('crm_api_enabled', '1');
        add_option('crm_api_key', '');
        add_option('crm_api_base_url', site_url('api'));
        add_option('company_logo_contract', '');
        if (get_option('crm_api_key') === '') {
            foreach (['smart_choice_api_key','rest_api_key','api_key','perfex_api_key'] as $legacy) {
                $value = get_option($legacy);
                if ($value !== '') { update_option('crm_api_key', $value); break; }
            }
        }

        $this->seed_predefined_replies();
        $this->seed_knowledge_base();

        update_option('smart_choice_crm_build', '3.6.5 SC');
        update_option('smart_choice_core_upgrade_applied', '365');
        update_option('smart_choice_api_settings_restored', '1');
        update_option('smart_choice_contract_logo_enabled', '1');
    }

    private function seed_predefined_replies()
    {
        $table = db_prefix().'tickets_predefined_replies';
        if (!$this->db->table_exists($table)) { return; }
        $count = (int)$this->db->count_all($table);
        $departments = ['Customer Service','Sales','Estimating','Scheduling','Permits','Engineering','Accounting','Billing','Projects','Field Operations','Electrical','Plumbing','HVAC','Roofing','Remodeling','Warranty'];
        $topics = ['appointment confirmation','appointment reschedule','estimate follow-up','proposal status','invoice question','payment received','partial payment','permit update','inspection update','material delivery','project delay','technician on the way','ten minutes away','work completed','photo request','document request','change order','scope clarification','service availability','customer portal help'];
        $i=1;
        while ($count < 800) {
            $dept=$departments[($i-1)%count($departments)];
            $topic=$topics[(int)(($i-1)/count($departments))%count($topics)];
            $name='Smart Choice - '.$dept.' - '.ucwords($topic).' '.str_pad((string)$i,3,'0',STR_PAD_LEFT);
            $this->db->where('name',$name);
            if (!$this->db->get($table)->row()) {
                $message='<p>Hello {contact_firstname},</p><p>Thank you for contacting Smart Choice Contractors USA regarding '.htmlspecialchars($topic,ENT_QUOTES,'UTF-8').'. Our '.$dept.' team is reviewing the request and will provide the next update as soon as possible.</p><p>For immediate assistance, please reply to this message or contact our office.</p><p>Done Right Through Professional Service.</p>';
                $this->db->insert($table,['name'=>$name,'message'=>$message]);
                $count++;
            }
            $i++; if ($i>1200) break;
        }
    }

    private function seed_knowledge_base()
    {
        $gt=db_prefix().'knowledge_base_groups'; $at=db_prefix().'knowledge_base';
        if (!$this->db->table_exists($gt) || !$this->db->table_exists($at)) { return; }
        $groups=['Getting Started'=>'#169179','Appointments & Scheduling'=>'#3598db','Estimates & Proposals'=>'#f59e0b','Invoices & Payments'=>'#7c3aed','Projects & Field Service'=>'#ef4444','Permits & Engineering'=>'#0ea5e9','Customer Portal'=>'#14b8a6','Construction Services'=>'#f97316'];
        $groupIds=[]; $order=1;
        foreach($groups as $name=>$color){
            $this->db->where('name',$name); $row=$this->db->get($gt)->row();
            if($row){$gid=$row->groupid;} else {
                $data=['name'=>$name,'active'=>1];
                if($this->db->field_exists('color',$gt))$data['color']=$color;
                if($this->db->field_exists('group_order',$gt))$data['group_order']=$order;
                if($this->db->field_exists('group_slug',$gt))$data['group_slug']=slug_it($name);
                $this->db->insert($gt,$data); $gid=$this->db->insert_id();
            }
            $groupIds[$name]=$gid; $order++;
        }
        $topics=['How to request an appointment','How to reschedule an appointment','How to prepare for a site visit','How estimates are prepared','How to approve a proposal','How partial payments work','How to view an invoice','How to make a payment','How project updates are delivered','How installer arrival notifications work','How to upload project photos','How permits and inspections work','How engineering drawings are prepared','How to use the customer portal','How to download documents','How to contact customer service','How change orders work','How service warranties are documented','How to confirm project completion','How to request additional work'];
        $n=1;
        $audiences=['Homeowners','Property Managers','Commercial Customers','New Customers','Existing Customers'];
        foreach($groupIds as $gname=>$gid){ foreach($topics as $topic){ foreach($audiences as $audience){
            $subject=$topic.' for '.$audience.' — '.$gname;
            $this->db->where('subject',$subject); if($this->db->get($at)->row()){continue;}
            $data=['articlegroup'=>$gid,'subject'=>$subject,'description'=>'<p>This guide explains '.htmlspecialchars(strtolower($topic),ENT_QUOTES,'UTF-8').' for '.htmlspecialchars($audience,ENT_QUOTES,'UTF-8').' working with Smart Choice Contractors USA.</p><h3>Steps</h3><ol><li>Open the appropriate section in the customer portal.</li><li>Review the project, appointment, estimate, or invoice information shown.</li><li>Use the available action button or reply to the assigned Smart Choice team.</li><li>Confirm that the update appears in your portal and keep the confirmation for your records.</li></ol><h3>What happens next</h3><p>The responsible department reviews the request and sends the next status, document, or scheduling update through the customer portal and configured notification channels.</p><p>For help, contact Smart Choice Contractors USA through the customer portal.</p>','slug'=>slug_it($subject),'active'=>1,'staff_article'=>0,'datecreated'=>date('Y-m-d H:i:s')];
            if($this->db->field_exists('article_order',$at))$data['article_order']=$n++;
            $this->db->insert($at,$data);
        }}}
    }
}
