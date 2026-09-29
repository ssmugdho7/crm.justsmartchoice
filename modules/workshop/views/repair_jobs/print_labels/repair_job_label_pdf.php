<?php

defined('BASEPATH') or exit('No direct script access allowed');


$companyUploadPath         = get_upload_path_by_type('company');
$pdf_logo_Url = get_option('company_logo');
$barcode_path  = site_url('modules/workshop/uploads/repair_job_barcodes/' . md5($repair_job_label->job_tracking_number ?? '').'.svg');

$items = '';
$items .= '
<table class="pdf-print-label print-label-number" border="0" cellpadding="2" cellspacing="2">
<tbody>
<tr>
<td align="left" width="40%"><img src="'.base_url('uploads/company/' . $pdf_logo_Url).'" class="img-responsive " width="63px"></td>
<td align="right" width="60%"><span class="print-label-number" width="23px">'.format_repair_job_number($repair_job_label->id) .'</span></td>
</tr>

<tr align="center">
<th colspan="2" rowspan="2"><span class="no-mbot">'. get_company_name($repair_job_label->client_id) .'</span><br>
<span class="no-mbot">'. wshop_get_branch_name($repair_job_label->branch_id) .'</span><br>
<img src="'.html_entity_decode($barcode_path) .'" alt="Barcode" width="160px"></th>
</tr>
</tbody>
 
</table>';
$items .= '<link href="' . FCPATH.'modules/workshop/assets/css/repair_jobs/print_label.css' . '"  rel="stylesheet" type="text/css" />';

$pdf->writeHTMLCell(50, 25, 1, 1, $items, 0, 1, 0, true, '', true);