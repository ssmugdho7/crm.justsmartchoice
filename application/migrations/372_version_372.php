<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_372 extends CI_Migration
{
    public function up()
    {
        $contracts = db_prefix() . 'contracts';
        if ($this->db->table_exists($contracts)) {
            if (!$this->db->field_exists('initials', $contracts)) {
                $this->db->query("ALTER TABLE `{$contracts}` ADD `initials` VARCHAR(12) NULL DEFAULT NULL AFTER `signature`");
            }
            if (!$this->db->field_exists('initials_signature', $contracts)) {
                $this->db->query("ALTER TABLE `{$contracts}` ADD `initials_signature` VARCHAR(191) NULL DEFAULT NULL AFTER `initials`");
            }
        }
        $fields = [
            ['Project Address','input',10,6],['Project Scope Summary','textarea',20,12],
            ['Contract Total','input',30,6],['Down Payment','input',40,6],
            ['Partial Payment Schedule','textarea',50,12],['Remaining Balance','input',60,6],
            ['Final Payment','input',70,6],['Financing Option','input',80,6],['Financing Terms','textarea',90,12],
        ];
        $table=db_prefix().'customfields';
        if($this->db->table_exists($table)){
            foreach($fields as $f){
                $exists=$this->db->where('fieldto','contracts')->where('name',$f[0])->count_all_results($table);
                if(!$exists){
                    $row=['fieldto'=>'contracts','name'=>$f[0],'required'=>0,'type'=>$f[1],'options'=>'','field_order'=>$f[2],'active'=>1];
                    if($this->db->field_exists('show_on_table',$table))$row['show_on_table']=0;
                    if($this->db->field_exists('show_on_client_portal',$table))$row['show_on_client_portal']=1;
                    if($this->db->field_exists('disalow_client_to_edit',$table))$row['disalow_client_to_edit']=1;
                    if($this->db->field_exists('only_admin',$table))$row['only_admin']=0;
                    if($this->db->field_exists('bs_column',$table))$row['bs_column']=$f[3];
                    if($this->db->field_exists('default_value',$table))$row['default_value']='';
                    $this->db->insert($table,$row);
                }
            }
        }
        update_option('smart_choice_crm_build','3.7.2 Contract & Client Portal Verified Repair');
        update_option('smart_choice_core_upgrade_applied','372');
        update_option('sc_contract_initials_signature_enabled','1');
    }
    public function down() {}
}
