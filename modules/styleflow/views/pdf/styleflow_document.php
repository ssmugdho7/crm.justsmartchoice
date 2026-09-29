<?php

defined('BASEPATH') or exit('No direct script access allowed');

$tpl = styleflow_get_template(styleflow_active_template($document_type));
if (!$tpl) $tpl = styleflow_get_template('default');

$primary   = styleflow_sanitize_color($tpl['primary_color'] ?? '', '#3598DB');
$secondary = styleflow_sanitize_color($tpl['secondary_color'] ?? '', '#F6F7F8');
$accent    = styleflow_sanitize_color($tpl['accent_color'] ?? '', '#169179');
$text      = styleflow_sanitize_color($tpl['text_color'] ?? '', '#333333');
$font      = array_key_exists($tpl['font_family'] ?? '', styleflow_supported_fonts()) ? $tpl['font_family'] : 'helvetica';
$variant   = styleflow_design_variant($tpl);
$pdf->SetFont($font, '', $font_size);
$dimensions = $pdf->getPageDimensions();

$typeLabel = ucfirst($document_type);
if ($document_type === 'invoice') $typeLabel = _l('invoice');
if ($document_type === 'estimate') $typeLabel = _l('estimate');
if ($document_type === 'proposal') $typeLabel = _l('proposal');

$date = isset($document->date) ? _d($document->date) : '';
$until = '';
$untilLabel = '';
if ($document_type === 'invoice') { $until = !empty($document->duedate) ? _d($document->duedate) : ''; $untilLabel = _l('invoice_data_duedate'); }
if ($document_type === 'estimate') { $until = !empty($document->expirydate) ? _d($document->expirydate) : ''; $untilLabel = _l('estimate_dt_table_heading_expirydate'); }
if ($document_type === 'proposal') { $until = !empty($document->open_till) ? _d($document->open_till) : ''; $untilLabel = _l('proposal_open_till'); }

$statusText = (string)($status ?? '');
if ($document_type === 'invoice' && function_exists('format_invoice_status')) $statusText = strip_tags(format_invoice_status($status, '', false));
if ($document_type === 'estimate' && function_exists('format_estimate_status')) $statusText = strip_tags(format_estimate_status($status, '', false));
if ($document_type === 'proposal' && function_exists('format_proposal_status')) $statusText = strip_tags(format_proposal_status($status, '', false));

$logo = pdf_logo_url();
$header = '';
if (in_array($variant, ['band','citrus','contrast'], true)) {
    $band = $variant === 'contrast' ? '#111827' : $primary;
    $bottom = $variant === 'contrast' ? 'border-bottom:7px solid '.$accent.';' : '';
    $header = '<table cellpadding="12" cellspacing="0" width="100%" style="background-color:'.$band.';color:#FFFFFF;'.$bottom.'"><tr>'
        . '<td width="55%">'.$logo.'</td><td width="45%" align="right"><span style="font-size:25px;color:#FFFFFF;font-weight:bold;">'.html_escape($typeLabel).'</span><br><span style="font-size:13px;color:#FFFFFF;font-weight:bold;">'.html_escape($document_number).'</span></td></tr></table>';
} elseif ($variant === 'modern-edge') {
    $header = '<table cellpadding="9" cellspacing="0" width="100%" style="border-left:10px solid '.$primary.';"><tr><td width="55%">'.$logo.'</td><td width="45%" align="right"><span style="font-size:25px;color:'.$primary.';font-weight:bold;">'.html_escape($typeLabel).'</span><br><span style="font-size:13px;">'.html_escape($document_number).'</span></td></tr></table>';
} elseif ($variant === 'blueprint') {
    $header = '<table cellpadding="10" cellspacing="0" width="100%" style="border:2px solid '.$primary.';"><tr><td width="55%">'.$logo.'</td><td width="45%" align="right"><span style="font-size:24px;color:'.$primary.';font-weight:bold;">'.html_escape($typeLabel).'</span><br><span style="font-size:13px;">'.html_escape($document_number).'</span></td></tr></table>';
} elseif ($variant === 'executive') {
    $header = '<table cellpadding="8" cellspacing="0" width="100%" style="border-top:8px solid #111827;border-bottom:1px solid #AAB0B8;"><tr><td width="55%">'.$logo.'</td><td width="45%" align="right"><span style="font-family:times;font-size:25px;color:#111827;font-weight:bold;">'.html_escape($typeLabel).'</span><br><span style="font-size:12px;color:#555;">'.html_escape($document_number).'</span></td></tr></table>';
} elseif ($variant === 'split') {
    $header = '<table cellpadding="8" cellspacing="0" width="100%"><tr><td width="52%">'.$logo.'</td><td width="48%" align="right" style="border-left:5px solid '.$primary.';"><span style="font-size:24px;color:'.$primary.';font-weight:bold;">'.html_escape($typeLabel).'</span><br><span style="font-size:13px;">'.html_escape($document_number).'</span></td></tr></table>';
} else {
    $header = '<table cellpadding="8" cellspacing="0" width="100%" style="border-bottom:3px solid '.$primary.';"><tr><td width="52%">'.$logo.'</td><td width="48%" align="right"><span style="font-size:24px;color:'.$primary.';font-weight:bold;">'.html_escape($typeLabel).'</span><br><span style="font-size:14px;color:'.$primary.';font-weight:bold;">'.html_escape($document_number).'</span></td></tr></table>';
}
$pdf->writeHTML($header, true, false, false, false, '');
$pdf->Ln(3);

$metaCellStyle = 'background-color:'.$secondary.';';
if ($variant === 'boxed' || $variant === 'executive') $metaCellStyle = 'border:1px solid #D1D5DB;background-color:#FFFFFF;';
$meta = '<table cellpadding="7" cellspacing="3" width="100%" style="color:'.$text.';"><tr>'
    . '<td width="33%" style="'.$metaCellStyle.'"><b>'._l('invoice_data_date').'</b><br>'.html_escape($date).'</td>'
    . '<td width="34%" style="'.$metaCellStyle.'"><b>'.html_escape($untilLabel).'</b><br>'.html_escape($until).'</td>'
    . '<td width="33%" style="'.$metaCellStyle.'"><b>'._l('status').'</b><br>'.html_escape($statusText).'</td>'
    . '</tr></table>';
$pdf->writeHTML($meta, true, false, false, false, '');
$pdf->Ln(5);

$organization = '<div style="color:'.$text.';">'.format_organization_info().'</div>';
if ($document_type === 'invoice' || $document_type === 'estimate') {
    $customer = '<b style="color:'.$primary.';">'._l($document_type === 'invoice' ? 'invoice_bill_to' : 'estimate_bill_to').':</b><br>';
    $customer .= format_customer_info($document, $document_type, 'billing');
    $shippingFlag = $document_type === 'invoice' ? ($document->show_shipping_on_invoice ?? 0) : ($document->show_shipping_on_estimate ?? 0);
    if (!empty($document->include_shipping) && !empty($shippingFlag)) {
        $customer .= '<br><b style="color:'.$primary.';">'._l('ship_to').':</b><br>'.format_customer_info($document, $document_type, 'shipping');
    }
} else {
    $customer = '<b style="color:'.$primary.';">'._l('proposal_to').':</b><br>';
    foreach (['proposal_to','address','city','state','zip','country','email','phone'] as $field) {
        if (!empty($document->{$field})) $customer .= html_escape($document->{$field}).'<br>';
    }
}
pdf_multi_row($organization, $customer, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
$pdf->Ln(7);

$items = styleflow_get_items_table_data($document, $document_type, 'pdf');
$pdf->writeHTML($items->table(), true, false, false, false, '');
$pdf->Ln(6);

$currency = $document->currency_name ?? '';
$totals = '<table cellpadding="6" cellspacing="0" width="100%" style="font-size:'.($font_size+1).'px;color:'.$text.';">';
$totals .= '<tr><td width="72%"></td><td width="16%" align="right"><b>'._l('styleflow_subtotal').'</b></td><td width="12%" align="right">'.app_format_money($document->subtotal ?? 0, $currency).'</td></tr>';
if (function_exists('is_sale_discount_applied') && is_sale_discount_applied($document)) {
    $label = _l('styleflow_discount');
    if (function_exists('is_sale_discount') && is_sale_discount($document, 'percent')) $label .= ' ('.app_format_number($document->discount_percent, true).'%)';
    $totals .= '<tr><td width="72%"></td><td width="16%" align="right"><b>'.$label.'</b></td><td width="12%" align="right">-'.app_format_money($document->discount_total ?? 0, $currency).'</td></tr>';
}
foreach ($items->taxes() as $tax) {
    $totals .= '<tr><td width="72%"></td><td width="16%" align="right"><b>'.html_escape($tax['taxname']).'</b></td><td width="12%" align="right">'.app_format_money($tax['total_tax'], $currency).'</td></tr>';
}
if (!empty($document->adjustment)) {
    $totals .= '<tr><td width="72%"></td><td width="16%" align="right"><b>'._l('styleflow_adjustment').'</b></td><td width="12%" align="right">'.app_format_money($document->adjustment, $currency).'</td></tr>';
}
$totalBg = $variant === 'contrast' ? '#111827' : $primary;
$totals .= '<tr style="background-color:'.$totalBg.';color:#FFFFFF;"><td width="72%"></td><td width="16%" align="right"><b>'._l('styleflow_total').'</b></td><td width="12%" align="right"><b>'.app_format_money($document->total ?? 0, $currency).'</b></td></tr>';
if ($document_type === 'invoice' && isset($document->total_left_to_pay)) {
    $totals .= '<tr style="background-color:'.$secondary.';"><td width="72%"></td><td width="16%" align="right"><b>'._l('invoice_amount_due').'</b></td><td width="12%" align="right"><b>'.app_format_money($document->total_left_to_pay, $currency).'</b></td></tr>';
}
$totals .= '</table>';
$pdf->writeHTML($totals, true, false, false, false, '');
$pdf->Ln(5);

if ($document_type === 'proposal' && !empty($document->content)) {
    $pdf->writeHTML('<div style="border-top:2px solid '.$accent.';padding-top:8px;color:'.$text.';">'.$document->content.'</div>', true, false, false, false, '');
    $pdf->Ln(4);
}
$note = $document->clientnote ?? '';
$terms = $document->terms ?? '';
if ($note) $pdf->writeHTML('<b style="color:'.$accent.';">'._l('styleflow_note').'</b><br>'.$note, true, false, false, false, '');
if ($terms) { $pdf->Ln(3); $pdf->writeHTML('<b style="color:'.$accent.';">'._l('terms_and_conditions').'</b><br>'.$terms, true, false, false, false, ''); }

// Optional employee/creator portrait. The template can use the staff member who
// created the document or a staff member explicitly selected in CRM Settings.
$staffId = styleflow_template_staff_id($tpl, $document);
if ($staffId > 0) {
    $photo = styleflow_staff_photo_source($staffId, true);
    $staffName = styleflow_staff_display_name($staffId);
    if ($photo && is_file($photo)) {
        $pdf->Ln(5);
        $y = $pdf->GetY();
        if ($y > 245) { $pdf->AddPage(); $y = $pdf->GetY(); }
        $pdf->Image($photo, $dimensions['lm'], $y, 16, 16, '', '', '', false, 300, '', false, false, 0, 'C', false, false);
        $pdf->SetXY($dimensions['lm'] + 20, $y + 2);
        $pdf->SetTextColor(hexdec(substr($text,1,2)), hexdec(substr($text,3,2)), hexdec(substr($text,5,2)));
        $pdf->SetFont($font, 'B', $font_size + 1);
        $pdf->Cell(0, 5, $staffName, 0, 1, 'L');
        $pdf->SetX($dimensions['lm'] + 20);
        $pdf->SetFont($font, '', $font_size - 1);
        $pdf->Cell(0, 5, _l('styleflow_employee_representative'), 0, 1, 'L');
    }
}
