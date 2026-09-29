<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_121 extends App_module_migration
{
 public function up()
 {
  $CI=&get_instance();
  $options=[
   'hrp_show_crm_staff_id'=>'1','hrp_use_crm_logo'=>'1','hrp_default_payslip_language'=>'employee','hrp_default_payslip_note'=>'Thank you for your professional service.','hrp_default_pay_frequency'=>'biweekly','hrp_overtime_multiplier'=>'1.5','hrp_sync_sales_commissions'=>'1','hrp_sync_subcontractor_payments'=>'1','hrp_enable_1099_tracking'=>'1','hrp_enable_project_job_costing'=>'1','hrp_preserve_core_tables'=>'1','hrp_federal_tax_year'=>'2026','hrp_florida_state_withholding_rate'=>'0','hrp_social_security_rate'=>'6.2','hrp_medicare_rate'=>'1.45','hrp_futa_rate'=>'0.6','hrp_enable_workers_comp'=>'1'];
  foreach($options as $k=>$v){ if(get_option($k)===''){ add_option($k,$v); } }
  $table=db_prefix().'hrp_payslip_templates';
  if($CI->db->table_exists($table)){
   $base=$CI->db->order_by('id','asc')->get($table)->row_array();
   $names=['Executive Blue','Contractor Green','Orange Ledger','Classic QuickBooks','Modern Payroll','Direct Deposit','Weekly Crew','Biweekly Professional','Salary Executive','Construction Field'];
   foreach($names as $name){ $CI->db->where('templates_name',$name); if($CI->db->count_all_results($table)==0){ $row=$base?:['payslip_columns'=>'','payslip_id_copy'=>0,'department_id'=>'','role_employees'=>'','staff_employees'=>'','payslip_template_data'=>'','cell_data'=>'']; unset($row['id']); $row['templates_name']=$name; $row['date_created']=date('Y-m-d H:i:s'); $row['staff_id_created']=get_staff_user_id()?:1; $CI->db->insert($table,$row); }}
  }
  $pdf=db_prefix().'hrp_payslip_pdf_templates';
  if($CI->db->table_exists($pdf)){
   $templates=['Digital Minimal','Digital Gradient','Digital Compact','Digital Detailed','Digital Bilingual','Digital Project Cost','Digital Commission','Digital 1099','Digital Insurance','Digital Year To Date'];
   $parent=$CI->db->order_by('id','asc')->get($table)->row_array(); $parentId=$parent['id']??0;
   foreach($templates as $i=>$name){ $CI->db->where('name',$name); if($CI->db->count_all_results($pdf)==0){ $color=['#1f3c88','#169179','#f97316','#3598db','#0e6f5b'][$i%5]; $content='<div style="font-family:Arial;border:1px solid #ddd;padding:22px"><div style="background:'.$color.';color:#fff;padding:14px"><strong>{company_name}</strong><br>PAY STATEMENT</div><h3>{staff_name}</h3><p>Employee ID: {staff_id} &nbsp; Pay period: {payslip_month}</p><table width="100%" cellpadding="7" style="border-collapse:collapse"><tr><th align="left">Earnings</th><th align="right">Current</th><th align="right">YTD</th></tr><tr><td>Regular Pay</td><td align="right">{gross_pay}</td><td align="right">{gross_pay_ytd}</td></tr><tr><td>Taxes and Deductions</td><td align="right">{total_deductions}</td><td align="right">{total_deductions_ytd}</td></tr><tr><td><b>Net Pay</b></td><td align="right"><b>{net_pay}</b></td><td align="right"><b>{net_pay_ytd}</b></td></tr></table><p>{payslip_note}</p></div>'; $CI->db->insert($pdf,['name'=>$name,'payslip_template_id'=>$parentId,'content'=>$content]); }}
  }
  add_option('hr_payroll_upgrade_notice_121','1');
 }
 public function down(){}
}
