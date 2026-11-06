<?php
defined('BASEPATH') or exit('No direct script access allowed');
hooks()->add_filter('app_view_data','scaf_representative_view',50);
hooks()->add_action('app_admin_head','scaf_representative_assets',50);
function scaf_representative_assets(){if(strtolower(get_instance()->router->fetch_class())!=='sales_center')return;scaf_asset('assets/css/representatives.css');scaf_asset('assets/js/representatives.js');}
function scaf_rep_label($en,$es){return get_staff_default_language()==='spanish'?$es:$en;}
function scaf_rep_allowed($rep){$r=(array)$rep;$staff=(int)get_staff_user_id();return has_permission('sales_center','','view')||((has_permission('sales_center','','view_own'))&&in_array($staff,[(int)($r['assigned']??0),(int)($r['staff_id']??0),(int)($r['created_by']??0)],true));}
function scaf_rep_contract_allowed($c){$r=(array)$c;$staff=(int)get_staff_user_id();return has_permission('sales_center_contracts','','view')||(has_permission('sales_center_contracts','','view_own')&&in_array($staff,[(int)($r['assigned']??0),(int)($r['created_by']??0)],true));}
function scaf_rep_initials($name){$p=preg_split('/\s+/u',trim($name),-1,PREG_SPLIT_NO_EMPTY);return $p?mb_strtoupper(mb_substr($p[0],0,1).(count($p)>1?mb_substr(end($p),0,1):'')):'?';}
function scaf_rep_signature($c){$c=(array)$c;$company=!empty($c['company_signature'])&&!empty($c['company_signed_at']);$rep=!empty($c['salesperson_signature'])&&!empty($c['salesperson_signed_at']);return $company&&$rep?'both':($company||$rep?'partial':'none');}
function scaf_rep_metrics_reduce($reps,$rows,$today){
 $result=['active'=>0,'rate_sum'=>'0','rate_count'=>0,'goals'=>'0','groups'=>[],'reps'=>[]];$month=substr($today,0,7);
 foreach($reps as $r){$id=(int)$r['id'];$result['reps'][$id]=['closed'=>[],'won'=>0,'lost'=>0];if(($r['status']??'')==='active')$result['active']++;$result['rate_sum']=scaf_decimal_add($result['rate_sum'],(string)($r['commission_rate']??'0'));$result['rate_count']++;$result['goals']=scaf_decimal_add($result['goals'],(string)($r['sales_goal']??'0'));}
 $seen=[];
 foreach($rows as $r){$id=(int)$r['salesperson_id'];if(!isset($result['reps'][$id])||substr((string)$r['closed_date'],0,7)!==$month||$r['status']!=2)continue;$key=$id.':'.$r['id'];if(isset($seen[$key]))continue;$seen[$key]=true;$currency=$r['currency_name'];$result['reps'][$id]['closed'][$currency]=scaf_decimal_add($result['reps'][$id]['closed'][$currency]??'0',$r['total']);
 // Team total counts shared invoices once, not once per linked rep.
 if(!isset($seen['team:'.$r['id']])){$seen['team:'.$r['id']]=true;$result['groups'][$currency]=scaf_decimal_add($result['groups'][$currency]??'0',$r['total']);}
 }
 return $result;
}
function scaf_rep_metrics($reps){
 $CI=&get_instance();$ids=array_map('intval',array_column($reps,'id'));$rows=[];$p=db_prefix();
 if($ids&&$CI->db->table_exists($p.'sales_center_document_links')&&(staff_can('view','invoices')||staff_can('view_own','invoices')||get_option('allow_staff_view_invoices_assigned'))){
  $CI->db->select("l.salesperson_id,i.id,i.total,i.status,c.name as currency_name,(SELECT MAX(date) FROM {$p}invoicepaymentrecords pay WHERE pay.invoiceid=i.id) as closed_date",false)->from($p.'sales_center_document_links l')->join($p.'invoices i','i.id=l.rel_id')->join($p.'currencies c','c.id=i.currency','left')->where('l.rel_type','invoice')->where_in('l.salesperson_id',$ids)->where('i.status',2);
  if(!staff_can('view','invoices'))$CI->db->where('('.str_replace($p.'invoices.','i.',get_invoices_where_sql_for_staff(get_staff_user_id())).')',null,false);
  // Native invoice helper qualifies tblinvoices: alias cannot be used with its scope.
  $rows=$CI->db->get()->result_array();
 }
 if($ids&&$CI->db->table_exists($p.'sales_center_commissions')&&(staff_can('view','invoices')||staff_can('view_own','invoices')||get_option('allow_staff_view_invoices_assigned'))){
  $CI->db->select("s.salesperson_id,i.id,i.total,i.status,c.name as currency_name,(SELECT MAX(date) FROM {$p}invoicepaymentrecords pay WHERE pay.invoiceid=i.id) as closed_date",false)->from($p.'sales_center_commissions s')->join($p.'invoices i','i.id=s.invoice_id')->join($p.'currencies c','c.id=i.currency')->where_in('s.salesperson_id',$ids)->where('i.status',2);
  if(!staff_can('view','invoices'))$CI->db->where('('.str_replace($p.'invoices.','i.',get_invoices_where_sql_for_staff(get_staff_user_id())).')',null,false);
  $rows=array_merge($rows,$CI->db->get()->result_array());
 }
 $metrics=scaf_rep_metrics_reduce($reps,$rows,date('Y-m-d'));
 foreach($reps as $rep){$metrics['reps'][(int)$rep['id']]['earned']=[];}
 if($ids&&$CI->db->table_exists($p.'sales_center_commissions')&&(has_permission('commission_reports','','view')||is_admin())){
  $earned=$CI->db->select('s.salesperson_id,s.commission_earned,c.name as currency_name')->from($p.'sales_center_commissions s')->join($p.'invoices i','i.id=s.invoice_id')->join($p.'currencies c','c.id=i.currency')->where_in('s.salesperson_id',$ids)->where('s.datecreated >=',date('Y-m-01'))->where('s.datecreated <',date('Y-m-01',strtotime('+1 month')))->get()->result_array();
  foreach($earned as $r){$id=(int)$r['salesperson_id'];$c=$r['currency_name'];$metrics['reps'][$id]['earned'][$c]=scaf_decimal_add($metrics['reps'][$id]['earned'][$c]??'0',$r['commission_earned']);}
 }
 foreach($reps as $rep){$staff=(int)($rep['staff_id']??0);if(!$staff||!$CI->db->table_exists($p.'_deals'))continue;
  if(!has_permission('deals','','view')&&(!has_permission('deals','','view_own')||$staff!==(int)get_staff_user_id()))continue;
  $deals=$CI->db->select('status')->where('default_deal_owner',$staff)->where('created_at >=',date('Y-m-01'))->where('created_at <',date('Y-m-01',strtotime('+1 month')))->get($p.'_deals')->result_array();
  foreach($deals as $deal){if(in_array($deal['status'],['won','lost'],true))$metrics['reps'][(int)$rep['id']][$deal['status']]++;}
 }
 return $metrics;
}
function scaf_rep_money($values){$out=[];foreach($values as $currency=>$value)$out[]=e(app_format_money($value,$currency));return $out?implode('<br>',$out):e(scaf_rep_label('No permitted closed invoices this month','Sin facturas cerradas autorizadas este mes'));}
function scaf_representative_view($v){
 if(strtolower(get_instance()->router->fetch_class())!=='sales_center')return $v;$path=preg_replace('/\.php$/','',$v['path']);
 // HMVC resolves module views to their basename before app_view_data runs.
 $method=strtolower(get_instance()->router->fetch_method());
 $nativePaths=['index'=>['manage'=>'admin/salespersons/manage'],'view'=>['view'=>'admin/salespersons/view'],'contracts'=>['manage'=>'admin/contracts/manage'],'contract'=>['form'=>'admin/contracts/form'],'contract_view'=>['view'=>'admin/contracts/view']];
 $path=$nativePaths[$method][$path]??$path;
 if(in_array($path,['admin/salespersons/manage','sales_center/admin/salespersons/manage'],true)){$v['data']['salespersons']=array_values(array_filter($v['data']['salespersons']??[],'scaf_rep_allowed'));$v['data']['rep_metrics']=scaf_rep_metrics($v['data']['salespersons']);$v['path']='../../modules/sc_admin_fixes/views/representatives/manage';}
 elseif(in_array($path,['admin/salespersons/view','sales_center/admin/salespersons/view'],true)){if(!scaf_rep_allowed($v['data']['salesperson'])){access_denied('sales_center');return $v;}$v['data']['contracts']=array_values(array_filter($v['data']['contracts']??[],'scaf_rep_contract_allowed'));$v['data']['rep_metrics']=scaf_rep_metrics([(array)$v['data']['salesperson']]);$v['data']['rep_native']=$path;$v['path']='../../modules/sc_admin_fixes/views/representatives/profile';}
 elseif(in_array($path,['admin/contracts/form','sales_center/admin/contracts/form','admin/contracts/view','sales_center/admin/contracts/view','admin/contracts/manage','sales_center/admin/contracts/manage'],true)){$v['data']['rep_native']=str_replace('sales_center/','',$path);$v['path']='../../modules/sc_admin_fixes/views/representatives/agreement';}
 return $v;
}
function scaf_rep_policy($id){$raw=get_option('scaf_rep_policy_'.(int)$id);$p=$raw?json_decode($raw,true):null;return is_array($p)?$p:null;}
function scaf_rep_policy_validate($data){
 if(!is_array($data)||!is_string($data['currency']??null)||!preg_match('/^[A-Z]{3}$/D',$data['currency']))throw new InvalidArgumentException('Select a valid currency.');
 $goal=(string)($data['monthly_target']??'');if(!preg_match('/^\d+(?:\.\d{1,2})?$/D',$goal)||scaf_decimal_cmp($goal,'0')<=0)throw new InvalidArgumentException('Monthly target must be positive.');
 $tiers=$data['tiers']??[];if(!is_array($tiers)||count($tiers)>20)throw new InvalidArgumentException('Invalid tier schedule.');$clean=[];$last='-1';
 foreach($tiers as $tier){$threshold=(string)($tier['threshold']??'');$multiplier=(string)($tier['multiplier']??'');if($threshold===''&&$multiplier==='')continue;if(!preg_match('/^\d+(?:\.\d{1,2})?$/D',$threshold)||!preg_match('/^\d+(?:\.\d{1,4})?$/D',$multiplier)||scaf_decimal_cmp($threshold,$last)<=0||scaf_decimal_cmp($multiplier,'0',4)<=0)throw new InvalidArgumentException('Tier thresholds must increase and multipliers must be positive.');$clean[]=['threshold'=>scaf_decimal_normalize($threshold),'multiplier'=>scaf_decimal_normalize($multiplier,4)];$last=$threshold;}
 return ['currency'=>$data['currency'],'monthly_target'=>scaf_decimal_normalize($goal),'tiers'=>$clean];
}
function scaf_rep_attainment($rep,$closed){$policy=scaf_rep_policy($rep['id']);if(!$policy)return null;$amount=$closed[$policy['currency']]??'0';$percent=(float)$amount/(float)$policy['monthly_target']*100;$tier=null;foreach($policy['tiers'] as $t)if(scaf_decimal_cmp($amount,$t['threshold'])>=0)$tier=$t;return ['policy'=>$policy,'amount'=>$amount,'percent'=>$percent,'tier'=>$tier];}
