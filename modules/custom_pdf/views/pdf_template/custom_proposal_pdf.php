<?php

defined('BASEPATH') or exit('No direct script access allowed');
$organization = '<div style="color:#526675;">' . format_organization_info() . '</div>';
$recipient = '<strong>' . _l('proposal_to') . '</strong><br /><div style="color:#526675;">' . format_proposal_info($proposal, 'pdf') . '</div>';
$pdf->writeHTML(pdf_logo_url() . '<br /><br />', true, false, true, false, '');
$left = $swap == '1' ? $recipient : $organization;
$right = $swap == '1' ? $organization : $recipient;
$pdf->writeHTML('<table cellpadding="6"><tr><td width="50%">' . $left . '</td><td width="50%">' . $right . '</td></tr></table>', true, false, true, false, '');
$pdf->ln(6);

$proposal_subject = htmlspecialchars((string) $proposal->subject, ENT_QUOTES, 'UTF-8');
$proposal_date = _l('proposal_date') . ': ' . _d($proposal->date);
$open_till     = '';

if (!empty($proposal->open_till)) {
    $open_till = _l('proposal_open_till') . ': ' . _d($proposal->open_till) . '<br />';
}


$project = '';
if ($proposal->project_id != '' && get_option('show_project_on_proposal') == 1) {
    $project .= _l('project') . ': ' . get_project_name_by_id($proposal->project_id) . '<br />';
}

$qty_heading = _l('estimate_table_quantity_heading', '', false);

if ($proposal->show_quantity_as == 2) {
    $qty_heading = _l('estimate' . '_table_hours_heading', '', false);
} elseif ($proposal->show_quantity_as == 3) {
    $qty_heading = _l('estimate_table_quantity_heading', '', false) . '/' . _l('estimate_table_hours_heading', '', false);
}

// The items table
$items = customPdfItemsTableData($proposal, 'proposal', 'pdf')
        ->set_headings('estimate');

$items_html = $items->table();

$items_html .= '<br /><br />';
$items_html .= '';
$items_html .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';

$items_html .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_subtotal') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($proposal->subtotal, $proposal->currency_name) . '</td>
</tr>';

if (is_sale_discount_applied($proposal)) {
    $items_html .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('estimate_discount');
    if (is_sale_discount($proposal, 'percent')) {
        $items_html .= ' (' . app_format_number($proposal->discount_percent, true) . '%)';
    }
    $items_html .= '</strong>';
    $items_html .= '</td>';
    $items_html .= '<td align="right" width="15%">-' . app_format_money($proposal->discount_total, $proposal->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
    $items_html .= '<tr>
    <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%">' . app_format_money($tax['total_tax'], $proposal->currency_name) . '</td>
</tr>';
}

if ((float)$proposal->adjustment != 0) {
    $items_html .= '<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($proposal->adjustment, $proposal->currency_name) . '</td>
</tr>';
}

$total_bg_color = !empty(getPdfOptions('proposals', 'items_table', 'total_row_bg_color')) ? getPdfOptions('proposals', 'items_table', 'total_row_bg_color') : "#f0f0f0";
$total_text_color = !empty(getPdfOptions('proposals', 'items_table', 'total_row_text_color')) ? getPdfOptions('proposals', 'items_table', 'total_row_text_color') : "#000000";

$items_html .= '
<tr style="background-color:' . $total_bg_color . '; color:' . $total_text_color . '">
    <td align="right" width="85%"><strong>' . _l('estimate_total') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($proposal->total, $proposal->currency_name) . '</td>
</tr>';
$items_html .= '</table>';

if (get_option('total_to_words_enabled') == 1) {
    $items_html .= '<br /><br /><br />';
    $items_html .= '<strong style="text-align:center;">' . _l('num_word') . ': ' . $CI->numberword->convert($proposal->total, $proposal->currency_name) . '</strong>';
}

$proposal->content = sc_proposal_items_content($proposal->content, $items_html);
if (!empty($proposal->clientnote)) { $proposal->content .= '<br /><br /><strong>Client note</strong><br />' . $proposal->clientnote; }
if (!empty($proposal->terms)) { $proposal->content .= '<br /><br /><strong>Terms and conditions</strong><br />' . $proposal->terms; }

// Get the proposals css
// Theese lines should aways at the end of the document left side. Dont indent these lines
$html = <<<EOF
<p style="font-size:22px;color:#162b3c;"># $number
<br /><span style="font-size:13px;color:#526675;">$proposal_subject</span>
</p>
$proposal_date
<br />
$open_till
$project
<div>
$proposal->content
</div>
EOF;

$pdf->writeHTML($html, true, false, true, false, '');

if (function_exists('sc_append_sale_attachments_to_pdf')) { sc_append_sale_attachments_to_pdf($pdf, 'proposal', (int) $proposal->id); }
