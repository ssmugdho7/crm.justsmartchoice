<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Theese lines should aways at the end of the document left side. Dont indent these lines
$html = <<<EOF
    <div style="width:680px !important;">
    {$contract->content}
    </div>
    EOF;
$pdf->writeHTML($html, true, false, true, false, '');


if (!empty($contract->contract_value)) {
    $CI = &get_instance();
    $CI->load->library('app_number_to_word', ['clientid' => $contract->client], 'numberword');
    $currency = get_base_currency();
    $pdf->Ln(4);
    $pdf->writeHTML('<div style="font-size:9px;text-align:center;border-top:1px solid #ddd;padding-top:5px"><strong>' . _l('contract_value') . ':</strong> ' . app_format_money($contract->contract_value, $currency) . '<br><strong>' . _l('num_word') . ':</strong> ' . html_escape($CI->numberword->convert($contract->contract_value, $currency->name)) . '</div>', true, false, true, false, '');
}

// CUSTOMER SIGNATURE GUARANTEE
if ((int)$contract->signed === 1 && !empty($contract->signature)) {
    $sigPath = get_upload_path_by_type('contract') . $contract->id . '/' . basename((string)$contract->signature);
    if (is_file($sigPath)) {
        $sigData = base64_encode((string)file_get_contents($sigPath));
        $sigMime = function_exists('mime_content_type') ? mime_content_type($sigPath) : 'image/png';
        $pdf->Ln(8);
        $pdf->writeHTML('<div style="border-top:1px solid #ddd;padding-top:8px"><strong>Customer Signature</strong><br><img src="data:' . ($sigMime ?: 'image/png') . ';base64,' . $sigData . '" style="width:220px;max-height:90px"><br><span style="font-size:9px">Signed by ' . e($contract->acceptance_firstname . ' ' . $contract->acceptance_lastname) . ' on ' . e(_dt($contract->acceptance_date)) . ' — IP ' . e($contract->acceptance_ip) . '</span></div>', true, false, true, false, '');
    }
}
?>
