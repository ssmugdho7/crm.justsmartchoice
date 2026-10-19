<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
$labels = ['invoice' => 'Invoice', 'proposal' => 'Proposal', 'estimate' => 'Estimate', 'credit_note' => 'Credit Note'];
function _l($key) { global $labels; return $labels[$key]; }
require dirname(__DIR__) . '/application/helpers/smart_choice_sales_links_helper.php';
function heading_check($type, $number, $expected) {
    if (sc_sales_document_heading($type, $number) !== $expected) { throw new RuntimeException('Incorrect heading for ' . $type); }
}
heading_check('invoice', 'Invoice #0002', 'Invoice #0002');
heading_check('invoice', 'Invoice Invoice #0002', 'Invoice #0002');
heading_check('invoice', 'invoice INVOICE #0002', 'Invoice #0002');
heading_check('invoice', 'INV-0002', 'Invoice INV-0002');
heading_check('invoice', 'InvoiceX-0002', 'Invoice InvoiceX-0002');
heading_check('proposal', '02', 'Proposal 02');
heading_check('estimate', 'Estimate|261', 'Estimate |261');
heading_check('credit_note', 'Credit Note Credit Note #03', 'Credit Note #03');
$labels['invoice'] = 'Factura';
$labels['credit_note'] = 'Nota de crédito';
heading_check('invoice', 'Invoice Factura #0002', 'Factura #0002');
heading_check('credit_note', 'Nota de crédito Credit Note #03', 'Nota de crédito #03');
echo "PASS Sales headings: duplicate labels, translations, custom prefixes and leading zeros\n";
