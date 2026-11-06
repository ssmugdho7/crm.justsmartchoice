<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Scaf_estimates_model extends App_Model
{
 public function convert($id,$mode,$percent,$version)
 {
  if(!staff_can('create','invoices')||!staff_can('edit','estimates')||!user_can_view_estimate($id))throw new RuntimeException('Estimate conversion is not permitted.');
  if(!in_array($mode,['full','deposit','remaining'],true))throw new InvalidArgumentException('Invalid conversion mode.');
  if(!$this->db->trans_begin())throw new RuntimeException('Unable to start conversion.');
  try{
   $row=$this->db->query('SELECT id FROM '.db_prefix().'estimates WHERE id=? FOR UPDATE',[(int)$id])->row();if(!$row||!user_can_view_estimate($id))throw new RuntimeException('Estimate not found or no longer accessible.');
   $this->load->model('estimates_model');$this->load->model('invoices_model');$estimate=$this->estimates_model->get($id);$fingerprint=scaf_estimate_fingerprint($estimate);
   $key='scaf_estimate_draw_'.(int)$id;
   // Read after the source lock: the per-request option cache may predate another conversion.
   $stored=$this->db->where('name',$key)->get(db_prefix().'options')->row();$metadata=json_decode($stored->value??'{}',true)?:[];
   if($mode==='remaining'&&!empty($metadata['balance_id']))return $this->existing($metadata['balance_id']);
   if($mode!=='remaining'&&!empty($estimate->invoiceid)){
    if((!$metadata&&$mode==='full')||($metadata&&$metadata['mode']===$mode&&($mode==='full'||scaf_decimal_cmp($metadata['percent'],$percent)===0)))return $this->existing($estimate->invoiceid);
    throw new RuntimeException('This estimate already has a different conversion.');
   }
   if(!hash_equals($fingerprint,(string)$version))throw new RuntimeException('Estimate changed. Reopen the conversion dialog.');
   $originalStatus=$estimate->status;$originalInvoice=$estimate->invoiceid;
   if($mode==='remaining'){
    if(($metadata['mode']??'')!=='deposit'||!hash_equals($metadata['fingerprint'],$fingerprint))throw new RuntimeException('The original draw or estimate changed. Review the source first.');
    $deposit=$this->invoices_model->get($metadata['invoice_id']);if(!$deposit||!empty($deposit->recurring)||scaf_decimal_cmp((string)$deposit->total,(string)$estimate->total)>=0)throw new RuntimeException('Deposit is missing or exceeds the source amount.');
    $target=scaf_decimal_sub((string)$estimate->total,(string)$deposit->total);$percent=scaf_decimal_sub('100',$metadata['percent']);
    if(scaf_decimal_cmp((string)$deposit->total,scaf_decimal_mul((string)$estimate->total,scaf_decimal_div($metadata['percent'],'100',8)))!==0)throw new RuntimeException('The deposit amount changed. Review its allocation before creating a balance.');
   }else $target=null;
   if($mode!=='full'){$draw=scaf_estimate_draw($estimate,$percent,$target);$draw['source']=$id;$draw['remaining']=scaf_decimal_sub((string)$estimate->total,$draw['total']);$GLOBALS['scaf_estimate_draw_context']=$draw;}
   $invoice=$this->estimates_model->convert_to_invoice($id,false,$mode!=='full');unset($GLOBALS['scaf_estimate_draw_context']);
   if(!$invoice)throw new RuntimeException('Invoice creation failed.');
   if($mode!=='full')$this->db->where('id',(int)$id)->update(db_prefix().'estimates',['status'=>$originalStatus,'invoiceid'=>$originalInvoice?:$invoice]);
   if($mode==='remaining'){$metadata['balance_id']=$invoice;$metadata['remaining']='0.00';}
   else $metadata=['mode'=>$mode,'percent'=>$percent,'invoice_id'=>$invoice,'fingerprint'=>$fingerprint,'remaining'=>$mode==='deposit'?$draw['remaining']:'0.00'];
   $optionData=['value'=>json_encode($metadata)];
   if($stored)$this->db->where('name',$key)->update(db_prefix().'options',$optionData);
   else{$optionData['name']=$key;$optionData['autoload']=0;$this->db->insert(db_prefix().'options',$optionData);}
   if(!$this->db->trans_status()||!$this->db->trans_commit())throw new RuntimeException('Unable to save the conversion.');
   return ['invoice_id'=>(int)$invoice,'draft'=>$mode!=='full','created'=>true,'remaining'=>$metadata['remaining']];
  }catch(Throwable $error){unset($GLOBALS['scaf_estimate_draw_context']);$this->db->trans_rollback();throw $error;}
 }
 private function existing($id){if(!$this->db->where('id',(int)$id)->get(db_prefix().'invoices')->row())throw new RuntimeException('Linked invoice is missing. Review before retrying.');if(!$this->db->trans_commit())throw new RuntimeException('Unable to confirm existing invoice.');return ['invoice_id'=>(int)$id,'created'=>false];}
}
