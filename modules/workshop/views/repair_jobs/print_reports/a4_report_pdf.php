<?php

defined('BASEPATH') or exit('No direct script access allowed');

        // Background color
$backgound_gray = "#f0f0f0";
$dimensions = $this->getPageDimensions();
$font_size = get_option('pdf_font_size');
$pdf_font_size = ($font_size +5);
$barcode_path  = site_url('modules/workshop/uploads/repair_job_barcodes/' . md5($repair_job_label->job_tracking_number ?? '').'.svg');

$qrcode_path  = '<br /><span class="pull-right mleft10 qrcode-border">
'.workshop_get_qrcode('repair_job', $repair_job_label->id, $repair_job_label->hash, 'images_w_table', '70').'
</span><br />';

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<b style="font-size:' . ($font_size + 14) . 'px">' . _l('wshop_job_tracking_number') . '</b><br />';
$info_right_column .= '<b class="text-muted" style=""># ' . $repair_job_label->job_tracking_number . '</b>';

$invoice_number = '';
if(is_numeric($repair_job_label->invoice_id)){
	$invoice_number .= format_invoice_number($repair_job_label->invoice_id);
}
if(count($repair_job_label->inspection) > 0){
	if(is_numeric($repair_job_label->inspection[0]['invoice_id'])){
		$invoice_number .= '<br />'.format_invoice_number($repair_job_label->inspection[0]['invoice_id']);

	}
}

$info_right_column .= '<br /><table cellpadding="2"  style="font-size:' . ($font_size + 4) . 'px">';
$info_right_column .= '
<tr>
<td align="left" width="40%"><strong>' . _l('wshop_device') . ':</strong></td>
<td align="right" width="60%">' .($repair_job_label->device->name ?? '') . '</td>
</tr>

<tr>
<td align="left" width="40%"><strong>'._l('wshop_appointment_type').':</strong></td>
<td align="right" width="60%">'.wshop_get_appointment_type_name($repair_job_label->appointment_type_id ?? '').'</td>
</tr>
<tr>
<td align="left" width="40%"><strong>'._l('wshop_mechanic').':</strong></td>
<td align="right" width="60%">'.get_staff_full_name($repair_job_label->sale_agent).'</td>
</tr>
<tr>
<td align="left" width="40%"><strong>'._l('wshop_appointment_date').':</strong></td>
<td align="right" width="60%">'._dt($repair_job_label->appointment_date ?? '').'</td>
</tr>
<tr>
<td align="left" width="40%"><strong>'._l('wshop_status').':</strong></td>
<td align="right" width="60%">'.str_replace('_', ' ', $repair_job_label->status).'</td>
</tr>
<tr>
<td align="left" width="40%"><strong>'._l('invoice').':</strong></td>
<td align="right" width="60%">'.$invoice_number.'</td>
</tr>


';

$info_right_column .= '</table>';



        // Add logo
$info_left_column .= pdf_logo_url();
$info_left_column .= '<br /><img src="'.html_entity_decode($barcode_path) .'" alt="Barcode" width="160px">';
        // Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $this, ($dimensions['wk'] / 2) - $dimensions['lm']);

$organization_info = '<div style="font-size: '.$pdf_font_size.'px">';
$organization_info .= format_organization_info();
$organization_info .= '</div>';

$estimate_info = '';

if(is_numeric($repair_job_label->client_id) && $repair_job_label->client_id != 0){
        // Estimate to
	$estimate_info .= '<span style="font-size: '.$pdf_font_size.'px"><b>' . _l('estimate_to') . '</b></span>';
	$estimate_info .= '<div  style="font-size: '.$pdf_font_size.'px">';
	$estimate_info .= format_customer_info($repair_job_label->client, 'invoice', 'billing');
	$estimate_info .= '</div>';
}


$swap = false;
$left_info  = $swap == '1' ? $estimate_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $estimate_info;


$user_to = '';
$company_name = '';
if(is_numeric($repair_job_label->sale_agent) && $repair_job_label->sale_agent != 0){
	$user_to = get_staff_full_name($repair_job_label->sale_agent) ;
}
if(is_numeric($repair_job_label->client_id) && $repair_job_label->client_id != 0){
	$company_name = get_company_name($repair_job_label->client_id) ;
}
$this->Ln(5);

pdf_multi_row($left_info, $right_info, $this, ($dimensions['wk'] / 2) - $dimensions['lm']);

// Device information
$category_name = '';
$manufacturer_name = '';
$model = wshop_get_model($repair_job_label->device->model_id);
if($model){
	$category_name = wshop_get_category_name($model->category_id);
	$manufacturer_name = wshop_get_manufacturer_name($model->manufacturer_id);
}

$tbltotal = '';
$tbltotal .= '<table cellpadding="2" bgcolor="#eeeeee" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
<td align="left" width="15%"><strong>' . _l('wshop_device_name') . ':</strong></td>
<td align="left" width="43%">' .($repair_job_label->device->name ?? '') . '</td>
<td align="left" width="22%"><strong>' . _l('wshop_prod_date') . ':</strong></td>
<td align="left" width="20%">' ._dt($repair_job_label->device->prod_date ?? '') . '</td>
</tr>';
$tbltotal .= '
<tr>
<td align="left" width="15%"><strong>' . _l('wshop_device_code') . ':</strong></td>
<td align="left" width="43%">' .($repair_job_label->device->code ?? '') . '</td>
<td align="left" width="22%"><strong>' . _l('wshop_purchase_date') . ':</strong></td>
<td align="left" width="20%">' ._dt($repair_job_label->device->purchase_date ?? '') . '</td>
</tr>';

$tbltotal .= '
<tr>
<td align="left" width="15%"><strong>' . _l('wshop_category') . ':</strong></td>
<td align="left" width="43%">' .($category_name ?? '') . '</td>
<td align="left" width="22%"><strong>' . _l('wshop_warranty_start_date') . ':</strong></td>
<td align="left" width="20%">' ._d($repair_job_label->device->warranty_start_date ?? '') . '</td>
</tr>';
$tbltotal .= '
<tr>
<td align="left" width="15%"><strong>' . _l('wshop_model') . ':</strong></td>
<td align="left" width="43%">' .wshop_get_model_name($repair_job_label->device->model_id ?? '') . '</td>
<td align="left" width="22%"><strong>' . _l('wshop_warranty_period_months') . ':</strong></td>
<td align="left" width="20%">' .($repair_job_label->device->warranty_period_months ?? '') .' '. _l('wshop_month_s'). '</td>
</tr>';
$tbltotal .= '
<tr>
<td align="left" width="15%"><strong>' . _l('wshop_manufacturer') . ':</strong></td>
<td align="left" width="43%">' .($manufacturer_name ?? '') . '</td>
<td align="left" width="22%"><strong>' . _l('wshop_warranty_expiry_date') . ':</strong></td>
<td align="left" width="20%">' ._d($repair_job_label->device->warranty_expiry_date ?? '') . '</td>
</tr>';

$fieldset_id = wshop_get_fieldset_id_by_model($repair_job_label->device->model_id);
$cf = wshop_get_custom_fields('fieldset_'.$fieldset_id);
$custom_field_index = 0;

if(count($cf) > 0){
	$html_child='';
	$item_index=0;
	foreach ($cf as $key => $custom_field) {
		$custom_field_index = $key;
		$val = wshop_get_custom_field_value($repair_job_label->device->id, $custom_field['id'], 'fieldset_'.$fieldset_id);
		if ($custom_field['type'] == 'textarea') {
			$val = clear_textarea_breaks($val);
		}
		$custom_field_value = $val;
		$html_child .= '
		<td align="left" width="15%"><strong>' . $custom_field['name'] . ':</strong></td>
		<td align="left" width="35%">' .check_for_links($custom_field_value) . '</td>
		';

		if(($item_index+1)%2 == 0 ){
			$tbltotal .= '<tr>'.$html_child.'</tr>';

			$html_child='';
		}elseif(($item_index+1)%2 != 0 && ($item_index+1 == count($cf))){
			$tbltotal .= '<tr>'.$html_child.'</tr>';

			$html_child='';
		}
		$item_index++;

	}

}

$cf = get_custom_fields('wshop_device');
$custom_field_index = 0;
if(count($cf) > 0){
	$html_child='';
	$item_index=0;

	foreach ($cf as $key => $custom_field) {
		$custom_field_index = $key;
		$val = get_custom_field_value($repair_job_label->device->id, $custom_field['id'], 'wshop_device');
		if ($custom_field['type'] == 'textarea') {
			$val = clear_textarea_breaks($val);
		}
		$custom_field_value = $val;

		$html_child .= '
		<td align="left" width="15%"><strong>' . $custom_field['name'] . ':</strong></td>
		<td align="left" width="35%">' .check_for_links($custom_field_value) . '</td>
		';

		if(($item_index+1)%2 == 0 ){
			$tbltotal .= '<tr>'.$html_child.'</tr>';

			$html_child='';
		}elseif(($item_index+1)%2 != 0 && ($item_index+1 == count($cf))){
			$tbltotal .= '<tr>'.$html_child.'</tr>';

			$html_child='';
		}
		$item_index++;

	}
}

$tbltotal .= '</table>';

$this->Ln(5);
$pdf->writeHTML($tbltotal, true, false, false, false, '');

////////////////
// repair job //
////////////////
if(isset($repair_job_label->repair_job_labour_products) && count($repair_job_label->repair_job_labour_products) > 0){
	$table_font_size = 'font-size:12px;';
	$items = '';
	$items = '<b style="font-size:' . ($font_size + 10) . 'px">' . _l('wshop_Labour_Product') . '</b><br />';
	$items .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop"  cellpadding="2" >
	<thead>';
	$items .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';'.$font_size.'; ">';
	$items.='
	<th align="center" width="5%" >#</th>
	<th colspan="1" width="30%" class="product" align="left">'. _l('wshop_product') .'</th>
	<th width="10%" class="unit_price" align="right">'. _l('wshop_unit_price') .'</th>
	<th width="10%" class="estimated_hours" align="right">'. _l('wshop_estimated_hours') .'</th>
	<th width="10%" class="estimated_hours" align="right">'. _l('wshop_quantity') .'</th>
	<th width="10%" class="vat" align="right">'. _l('wshop_vat') .'</th> 
	<th width="10%" class="discount " align="right">'. _l('wshop_discount_percent_') .'</th> 
	<th width="15%" class="sub_total" align="right">'. _l('wshop_sub_total') .'</th>
	</tr>
	</thead>
	<tbody class="tbody-main"  style="'.$font_size.'" >';

// render item table start
	foreach ($repair_job_label->repair_job_labour_products as $key => $labour_product) {

		$itemHTML = '';
			// Open table row
		$itemHTML .= '<tr style="'.$font_size.'">';
		
		$itemHTML .= '<td width="5%">'. ($key+1) .'</td>';
		$itemHTML .= '<td width="30%">'. ($labour_product['name']) .'</td>';
		$itemHTML .= '<td width="10%" align="right" >'. app_format_money($labour_product['unit_price'], $repair_job_label->currency) .'</td>';
		$itemHTML .= '<td width="10%" align="right" >'. ($labour_product['estimated_hours']) .'</td>';
		$itemHTML .= '<td width="10%" align="right" >'. ($labour_product['qty']) .'</td>';
		$itemHTML .=  wshop_render_taxes_html(wshop_convert_item_taxes($labour_product['tax_id'], $labour_product['tax_rate'], $labour_product['tax_name']), 10); 
		$itemHTML .= '<td width="10%" align="right" >'. ($labour_product['discount']) .'</td>';
		$itemHTML .= '<td width="15%" align="right" >'. app_format_money($labour_product['subtotal'], $repair_job_label->currency) .'</td>';


		$itemHTML .= '</tr>';

		$items .= $itemHTML;
	}

	$items.= '</tbody>
	</table>';

	$tblhtml = $items;
	$pdf->writeHTML($tblhtml, true, false, false, false, '');
}

$pdf->Ln(1);

$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
<td align="right" width="85%"><strong>' . _l('wshop_estimated_labour_sub_total') . '</strong></td>
<td align="right" width="15%">' . app_format_money((float)$repair_job_label->estimated_labour_subtotal, $repair_job_label->currency) . '</td>
</tr>';

$tbltotal .= $repair_job_label->tax_labour_data['pdf_html_currency'];

$tbltotal .= '<tr>
<td align="right" width="85%"><strong>' . _l('total_discount') . '</strong></td>
<td align="right" width="15%">' . app_format_money((float)$repair_job_label->estimated_labour_discount_total, $repair_job_label->currency) . '</td>
</tr>';

$tbltotal .= '
<tr bgcolor="#f0f0f0">
<td align="right" width="85%"><strong>' . _l('wshop_estimated_labour_total') . '</strong></td>
<td align="right" width="15%">' . app_format_money($repair_job_label->estimated_labour_total, $repair_job_label->currency) . '</td>
</tr>';

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

/////////////////////
// repair job Part //
/////////////////////
if(isset($repair_job_label->repair_job_labour_materials) && count($repair_job_label->repair_job_labour_materials) > 0){
	$table_font_size = 'font-size:12px;';
	$items = '';
	$items = '<b style="font-size:' . ($font_size + 10) . 'px">' . _l('wshop_parts') . '</b><br />';
	$items .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop"  cellpadding="2" >
	<thead>';
	$items .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';'.$font_size.'; ">';
	$items.='
	<th align="center" width="5%" >#</th>
	<th colspan="1" width="30%" class="product" align="left">'. _l('wshop_product') .'</th>
	<th width="12%" class="estimated_hours" align="right">'. _l('wshop_unit_price') .'</th>
	<th width="12%" class="estimated_hours" align="right">'. _l('wshop_actual_qty') .'</th>
	<th width="12%" class="vat" align="right">'. _l('wshop_vat') .'</th> 
	<th width="12%" class="discount " align="right">'. _l('wshop_discount_percent_') .'</th> 
	<th width="17%" class="sub_total" align="right">'. _l('wshop_sub_total') .'</th>
	</tr>
	</thead>
	<tbody class="tbody-main"  style="'.$font_size.'" >';

// render item table start
	foreach ($repair_job_label->repair_job_labour_materials as $key => $part) {

		$itemHTML = '';
			// Open table row
		$itemHTML .= '<tr style="'.$font_size.'">';
		$itemHTML .= '<td width="5%">'. ($key+1) .'</td>';
		$itemHTML .= '<td width="30%">'. ($part['name']) .'</td>';
		$itemHTML .= '<td width="12%" align="right" >'. app_format_money($part['rate'], $repair_job_label->currency) .'</td>';
		$itemHTML .= '<td width="12%" align="right" >'. ($part['qty']) .'</td>';
		$itemHTML .=  wshop_render_taxes_html(wshop_convert_item_taxes($part['tax_id'], $part['tax_rate'], $part['tax_name']), 12); 
		$itemHTML .= '<td width="12%" align="right" >'. ($part['discount']) .'</td>';
		$itemHTML .= '<td width="17%" align="right" >'. app_format_money((float)$part['subtotal'], $repair_job_label->currency) .'</td>';
		$itemHTML .= '</tr>';
		$items .= $itemHTML;
	}

	$items.= '</tbody>
	</table>';

	$tblhtml = $items;
	$pdf->writeHTML($tblhtml, true, false, false, false, '');
}

$pdf->Ln(1);

$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
<td align="right" width="85%"><strong>' . _l('wshop_estimated_part_sub_total') . '</strong></td>
<td align="right" width="15%">' . app_format_money((float)$repair_job_label->estimated_material_subtotal, $repair_job_label->currency) . '</td>
</tr>';

$tbltotal .= $repair_job_label->tax_part_data['pdf_html_currency'];

$tbltotal .= '<tr>
<td align="right" width="85%"><strong>' . _l('wshop_discount') . '</strong></td>
<td align="right" width="15%">' . app_format_money((float)$repair_job_label->estimated_material_discount_total, $repair_job_label->currency) . '</td>
</tr>';

$tbltotal .= '
<tr bgcolor="#f0f0f0">
<td align="right" width="85%"><strong>' . _l('wshop_estimated_part_total') . '</strong></td>
<td align="right" width="15%">' . app_format_money($repair_job_label->estimated_material_total, $repair_job_label->currency) . '</td>
</tr>';

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

// repair job total
$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
<td align="right" width="100%" colspan="2"><span class="tw-font-semibold">----------------------------------------------------------</span></td>
</tr>
<tr>
<td align="right" width="85%"><strong>' . _l('wshop_estimated_sub_total') . '</strong></td>
<td align="right" width="15%">' . app_format_money((float)$repair_job_label->subtotal, $repair_job_label->currency) . '</td>
</tr>';

$tbltotal .= '<tr>
<td align="right" width="85%"><strong>' . _l('wshop_total_tax') . '</strong></td>
<td align="right" width="15%">' . app_format_money((float)$repair_job_label->total_tax, $repair_job_label->currency) . '</td>
</tr>';
$tbltotal .= '<tr>
<td align="right" width="85%"><strong>' . _l('wshop_discount') . '</strong></td>
<td align="right" width="15%">' . app_format_money((float)$repair_job_label->discount_total, $repair_job_label->currency) . '</td>
</tr>';

$tbltotal .= '
<tr bgcolor="#f0f0f0">
<td align="right" width="85%"><strong>' . _l('wshop_estimated_total') . '</strong></td>
<td align="right" width="15%">' . app_format_money($repair_job_label->total, $repair_job_label->currency) . '</td>
</tr>';

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

////////////////
// inspection //
////////////////
if(isset($repair_job_label->inspection_data)){
	if(isset($repair_job_label->inspection_labour_products) && count($repair_job_label->inspection_labour_products) > 0){
		$table_font_size = 'font-size:12px;';
		$items = '';
		$items = '<b style="font-size:' . ($font_size + 10) . 'px">' . _l('wshop_inspection') . '</b><br />';
		$items .= '<b style="font-size:' . ($font_size + 10) . 'px">' . _l('wshop_Labour_Product') . '</b><br />';
		$items .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop"  cellpadding="2" >
		<thead>';
		$items .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';'.$font_size.'; ">';
		$items.='
		<th align="center" width="5%" >#</th>
		<th colspan="1" width="30%" class="product" align="left">'. _l('wshop_product') .'</th>
		<th width="12%" class="unit_price" align="right">'. _l('wshop_unit_price') .'</th>
		<th width="12%" class="estimated_hours" align="right">'. _l('wshop_estimated_hours') .'</th>
		<th width="12%" class="estimated_hours" align="right">'. _l('wshop_quantity') .'</th>
		<th width="12%" class="vat" align="right">'. _l('wshop_vat') .'</th> 
		<th width="17%" class="sub_total" align="right">'. _l('wshop_sub_total') .'</th>
		</tr>
		</thead>
		<tbody class="tbody-main"  style="'.$font_size.'" >';

// render item table start
		foreach ($repair_job_label->inspection_labour_products as $key => $labour_product) {

			$itemHTML = '';
			// Open table row
			$itemHTML .= '<tr style="'.$font_size.'">';

			$itemHTML .= '<td width="5%">'. ($key+1) .'</td>';
			$itemHTML .= '<td width="30%">'. ($labour_product['name']) .'</td>';
			$itemHTML .= '<td width="12%" align="right" >'. app_format_money($labour_product['unit_price'], $repair_job_label->currency) .'</td>';
			$itemHTML .= '<td width="12%" align="right" >'. ($labour_product['estimated_hours']) .'</td>';
			$itemHTML .= '<td width="12%" align="right" >'. ($labour_product['qty']) .'</td>';
			$itemHTML .=  wshop_render_taxes_html(wshop_convert_item_taxes($labour_product['tax_id'], $labour_product['tax_rate'], $labour_product['tax_name']), 12); 
			$itemHTML .= '<td width="17%" align="right" >'. app_format_money($labour_product['subtotal'], $repair_job_label->currency) .'</td>';


			$itemHTML .= '</tr>';

			$items .= $itemHTML;
		}

		$items.= '</tbody>
		</table>';

		$tblhtml = $items;
		$pdf->writeHTML($tblhtml, true, false, false, false, '');

	$pdf->Ln(1);

	if(1==2){
		$tbltotal = '';
		$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
		$tbltotal .= '
		<tr>
		<td align="right" width="85%"><strong>' . _l('wshop_estimated_labour_sub_total') . '</strong></td>
		<td align="right" width="15%">' . app_format_money((float)$repair_job_label->inspection_data->estimated_labour_subtotal, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';

		$tbltotal .= '
		<tr>
		<td align="right" width="85%"><strong>' . _l('wshop_total_tax') . '</strong></td>
		<td align="right" width="15%">' . app_format_money((float)$repair_job_label->inspection_data->estimated_labour_total_tax, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';


		$tbltotal .= '
		<tr style="background-color:'.$backgound_gray.';">
		<td align="right" width="85%"><strong>' . _l('wshop_estimated_labour_total') . '</strong></td>
		<td align="right" width="15%">' . app_format_money($repair_job_label->inspection_data->estimated_labour_total, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';

		$tbltotal .= '</table>';
		$pdf->writeHTML($tbltotal, true, false, false, false, '');
	}
	}

	/////////////////////
	// inspection Part //
	/////////////////////
	
	if(isset($repair_job_label->inspection_parts) && count($repair_job_label->inspection_parts) > 0){
		$table_font_size = 'font-size:12px;';
		$items = '';
		if(!isset($repair_job_label->inspection_labour_products) || (isset($repair_job_label->inspection_labour_products) && count($repair_job_label->inspection_labour_products) == 0)){

			$items .= '<b style="font-size:' . ($font_size + 10) . 'px">' . _l('wshop_inspection') . '</b><br />';
		}
		$items .= '<b style="font-size:' . ($font_size + 10) . 'px">' . _l('wshop_parts') . '</b><br />';
		$items .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop"  cellpadding="2" >
		<thead>';
		$items .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';'.$font_size.'; ">';
		$items.='
		<th align="center" width="5%" >#</th>
		<th colspan="1" width="30%" class="product" align="left">'. _l('wshop_product') .'</th>
		<th width="15%" class="estimated_hours" align="right">'. _l('wshop_unit_price') .'</th>
		<th width="15%" class="estimated_hours" align="right">'. _l('wshop_actual_qty') .'</th>
		<th width="15%" class="vat" align="right">'. _l('wshop_vat') .'</th> 
		<th width="20%" class="sub_total" align="right">'. _l('wshop_sub_total') .'</th>
		</tr>
		</thead>
		<tbody class="tbody-main"  style="'.$font_size.'" >';

// render item table start
		foreach ($repair_job_label->inspection_parts as $key => $part) {

			$itemHTML = '';
			// Open table row
			$itemHTML .= '<tr style="'.$font_size.'">';
			$itemHTML .= '<td width="5%">'. ($key+1) .'</td>';
			$itemHTML .= '<td width="30%">'. ($part['name']) .'</td>';
			$itemHTML .= '<td width="15%" align="right" >'. app_format_money($part['rate'], $repair_job_label->currency) .'</td>';
			$itemHTML .= '<td width="15%" align="right" >'. ($part['qty']) .'</td>';
			$itemHTML .=  wshop_render_taxes_html(wshop_convert_item_taxes($part['tax_id'], $part['tax_rate'], $part['tax_name']), 15); 
			$itemHTML .= '<td width="20%" align="right" >'. app_format_money((float)$part['subtotal'], $repair_job_label->currency) .'</td>';
			$itemHTML .= '</tr>';
			$items .= $itemHTML;
		}

		$items.= '</tbody>
		</table>';

		$tblhtml = $items;
		$pdf->writeHTML($tblhtml, true, false, false, false, '');
	}

	$pdf->Ln(1);

	if(1==2){

		$tbltotal = '';
		$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
		$tbltotal .= '
		<tr>
		<td align="right" width="85%"><strong>' . _l('wshop_estimated_part_sub_total') . '</strong></td>
		<td align="right" width="15%">' . app_format_money((float)$repair_job_label->inspection_data->estimated_material_subtotal, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';

		$tbltotal .= '
		<tr>
		<td align="right" width="85%"><strong>' . _l('wshop_total_tax') . '</strong></td>
		<td align="right" width="15%">' . app_format_money((float)$repair_job_label->inspection_data->estimated_material_total_tax, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';

		$tbltotal .= '<tr>
		<td align="right" width="85%"><strong>' . _l('wshop_discount') . '</strong></td>
		<td align="right" width="15%">' . app_format_money((float)$repair_job_label->inspection_data->estimated_material_discount_total, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';

		$tbltotal .= '
		<tr style="background-color:'.$backgound_gray.';">
		<td align="right" width="85%"><strong>' . _l('wshop_estimated_part_total') . '</strong></td>
		<td align="right" width="15%">' . app_format_money($repair_job_label->inspection_data->estimated_material_total, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';

		$tbltotal .= '</table>';
		$pdf->writeHTML($tbltotal, true, false, false, false, '');
	}



	// inspection job total
	if($repair_job_label->inspection_data->subtotal > 0){
		$tbltotal = '';
		$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
		$tbltotal .= '
		<tr>
		<td align="right" width="100%" colspan="2"><span class="tw-font-semibold">----------------------------------------------------------</span></td>
		</tr>
		<tr>
		<td align="right" width="85%"><strong>' . _l('wshop_estimated_sub_total') . '</strong></td>
		<td align="right" width="15%">' . app_format_money((float)$repair_job_label->inspection_data->subtotal, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';

		$tbltotal .= '<tr>
		<td align="right" width="85%"><strong>' . _l('wshop_total_tax') . '</strong></td>
		<td align="right" width="15%">' . app_format_money((float)$repair_job_label->inspection_data->total_tax, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';
		$tbltotal .= '<tr>
		<td align="right" width="85%"><strong>' . _l('wshop_discount') . '</strong></td>
		<td align="right" width="15%">' . app_format_money((float)$repair_job_label->inspection_data->discount_total, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';

		$tbltotal .= '
		<tr bgcolor="#f0f0f0">
		<td align="right" width="85%"><strong>' . _l('wshop_estimated_total') . '</strong></td>
		<td align="right" width="15%">' . app_format_money($repair_job_label->inspection_data->total, $repair_job_label->inspection_data->currency) . '</td>
		</tr>';

		$tbltotal .= '</table>';
		$pdf->writeHTML($tbltotal, true, false, false, false, '');
	}

}



if (!empty($repair_job_label->issue_description)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('wshop_issue_description'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $repair_job_label->issue_description, 0, 1, false, true, 'L', true);
}
if (!empty($repair_job_label->job_description)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('wshop_job_description'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $repair_job_label->job_description, 0, 1, false, true, 'L', true);
}
if (!empty($repair_job_label->additional_description)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('wshop_additional_description'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $repair_job_label->additional_description, 0, 1, false, true, 'L', true);
}
if (!empty($repair_job_label->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $repair_job_label->terms, 0, 1, false, true, 'L', true);
}
