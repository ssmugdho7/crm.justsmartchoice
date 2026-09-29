<?php

defined('BASEPATH') or exit('No direct script access allowed');


$companyUploadPath         = get_upload_path_by_type('company');
$pdf_logo_Url = get_option('company_logo');
$barcode_path  = site_url('modules/workshop/uploads/repair_job_barcodes/' . md5($repair_job_label->job_tracking_number ?? '').'.svg');

$parts_name = '';
if(isset($repair_job_label->repair_job_labour_materials)){
	foreach ($repair_job_label->repair_job_labour_materials as $key => $part) {
		if($parts_name != ''){
		$parts_name .= '; ';
		}
		$parts_name .= $part['name'];
	}
}

$inspection_id = '';
$inspection_type = '';
$inspection_description = '';
$inspection_total = 0;

if(count($repair_job_label->inspection) > 0){
	$_inspection = $repair_job_label->inspection;
	$inspection_id = format_inspection_number($_inspection[0]['id']);
	$inspection_type = wshop_get_category_name($_inspection[0]['inspection_type_id']);
	$inspection_description = $_inspection[0]['description'];
	$inspection_total = $_inspection[0]['total'];
}

$items = '';
$items .= '
<table class="pdf-print-report print-label-number" border="0" cellpadding="1" cellspacing="2">
<tbody>

<tr>
<td align="center" width="100%" colspan="2"><img src="'.base_url('uploads/company/' . $pdf_logo_Url).'" class="img-responsive " width="250px"></td>
</tr>

<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_repair_job').'</span> :'.format_repair_job_number($repair_job_label->id).'</td>
</tr>

<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_job_tracking_number').'</span> :'.($repair_job_label->job_tracking_number).'</td>
</tr>

<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('client').'</span> :'.get_company_name($repair_job_label->client_id).'</td>
</tr>

<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">----------------------------------------------------------</span></td>
</tr>

<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_customer_fault_description').'</span> :<br>'.($repair_job_label->issue_description).'</td>
</tr>
<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_job_description').'</span> :<br>'.($repair_job_label->job_description).'</td>
</tr>
<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_parts_required').'</span> :<br>'.($parts_name).'</td>
</tr>
<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_additional_description').'</span> :<br>'.($repair_job_label->additional_description).'</td>
</tr>

<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">----------------------------------------------------------</span></td>
</tr>

<tr align="center">
<th colspan="2">
<img src="'.html_entity_decode($barcode_path) .'" alt="Barcode" width="160px"></th>
</tr>

<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">----------------------------------------------------------</span></td>
</tr>';
if(count($repair_job_label->inspection) > 0){
$items .= '<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_inspection_id').'</span> :'.($inspection_id).'</td>
</tr>
<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_inspection_type').'</span> :'.($inspection_type).'</td>
</tr>
<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_description').'</span> :'.($inspection_description).'</td>
</tr>';
}

if(count($repair_job_label->workshops) > 0){
	foreach($repair_job_label->workshops as $log){ 
		$items .= '<br><tr>
		<td align="left" colspan="2"><span class="tw-font-semibold">'.$log['name'].'</span></td>
		</tr>
		<tr>
		<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_Report_Type').'</span> :'.wshop_get_category_name($log['report_type_id']).'</td>
		</tr>
		<tr>
		<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_Report_Status').'</span> :'.wshop_get_category_name($log['report_status_id']).'</td>
		</tr>
		
		<tr>
		<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_parts_information').'</span> :'.($log['parts_information']).'</td>
		</tr>
		<tr>
		<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_notes').'</span> :'.($log['description']).'</td>
		</tr>
		
		';
	}
}

$items .= '<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">----------------------------------------------------------</span></td>
</tr>';

$items .= '<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_date_requested').'</span> :'._dt($repair_job_label->appointment_date).'</td>
</tr>
<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_completed').'</span> :'._dt($repair_job_label->estimated_completion_date).'</td>
</tr>
<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_repair_warranty').'</span> :'.($repair_job_label->device->warranty_period_months ?? '').' '. _l('wshop_month_s').'</td>
</tr>
<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_repair_cost').'</span> :'.app_format_money((float)$repair_job_label->total + (float)$inspection_total, $repair_job_label->currency).'</td>
</tr>

';

$items .= '<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">----------------------------------------------------------</span></td>
</tr>';

$items .= '<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">'._l('wshop_delivery_return_method').'</span> :'.wshop_get_category_name($repair_job_label->delivery_type_id).'</td>
</tr>
<tr>
<td align="left" colspan="2">
<address>
<span class="billing_street">
'.($repair_job_label->billing_street == '' ? '--' :$repair_job_label->billing_street).'
</span><br>
<span class="billing_city">
'.($repair_job_label->billing_city == '' ? '--' :$repair_job_label->billing_city).'
</span>,
<span class="billing_state">
'.($repair_job_label->billing_state == '' ? '--' :$repair_job_label->billing_state).'
</span>
<br />
<span class="billing_country">
'.(get_country_short_name($repair_job_label->billing_country) == '' ? '--' :get_country_short_name($repair_job_label->billing_country)).'
</span>,
<span class="billing_zip">
'.($repair_job_label->billing_zip == '' ? '--' :$repair_job_label->billing_zip).'
</span>
</address>
</td>
</tr>';

$items .= '<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">----------------------------------------------------------</span></td>
</tr>';

$items .= '<tr>
<td align="left">'._l('wshop_view_qr_code_description').'</td>
<td align="left">'.workshop_get_qrcode('repair_job', $repair_job_label->id, $repair_job_label->hash, 'images_w_table', '110').'</td>
</tr>
';

$items .= '<tr>
<td align="left" colspan="2"><span class="tw-font-semibold">----------------------------------------------------------</span></td>
</tr>';

$items .= '<tr>
<td align="left" colspan="2">'.get_option('wshop_report_footer').'</td>
</tr>
';

$items .= '

</tbody>
 
</table>';

$items .= '<link href="' . FCPATH.'modules/workshop/assets/css/repair_jobs/print_report.css' . '"  rel="stylesheet" type="text/css" />';

$pdf->writeHTMLCell(50, 25, 1, 1, $items, 0, 1, 0, true, '', true);