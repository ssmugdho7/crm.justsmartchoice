<?php defined('BASEPATH') or exit('No direct script access allowed');
$scSummaryType = isset($sc_summary_type) ? (string)$sc_summary_type : (isset($estimate) ? 'estimate' : (isset($proposal) ? 'proposal' : 'display'));
if ($scSummaryType === 'estimate') {
    $scName = isset($estimate) ? (string)($estimate->sc_customer_name ?? '') : '';
    $scEmail = isset($estimate) ? (string)($estimate->sc_customer_email ?? '') : '';
    $scPhone = isset($estimate) ? (string)($estimate->sc_customer_phone ?? '') : '';
    $scAddress = isset($estimate) ? (string)($estimate->sc_project_address ?? '') : '';
?>
<div class="panel_s sc-sales-customer-summary-panel" data-sc-invoice-customer-edit>
    <div class="panel-body tw-bg-neutral-50">
        <h4 class="tw-font-semibold tw-mt-0 tw-mb-3"><i class="fa-regular fa-address-card tw-mr-1"></i> <?= _l('sc_customer_information'); ?></h4>
        <div class="row">
            <div class="col-sm-6"><?= render_input('sc_customer_name', 'sc_customer_name', $scName, 'text', ['data-sc-customer-field'=>'name']); ?></div>
            <div class="col-sm-6"><?= render_input('sc_customer_email', 'sc_customer_email', $scEmail, 'email', ['data-sc-customer-field'=>'email']); ?></div>
            <div class="col-sm-6"><?= render_input('sc_customer_phone', 'sc_customer_phone', $scPhone, 'text', ['data-sc-customer-field'=>'phone']); ?></div>
            <div class="col-sm-6"><?= render_input('sc_project_address', 'sc_project_address', $scAddress, 'text', ['data-sc-customer-field'=>'address']); ?></div>
        </div>
    </div>
</div>
<?php } else { ?>
<div class="panel_s sc-sales-customer-summary-panel" data-sc-sales-customer-summary>
    <div class="panel-body tw-bg-neutral-50">
        <h4 class="tw-font-semibold tw-mt-0 tw-mb-3"><i class="fa-regular fa-address-card tw-mr-1"></i> <?= _l('sc_customer_information'); ?></h4>
        <div class="row">
            <div class="col-md-3 col-sm-6"><strong><?= _l('sc_customer_name'); ?></strong><div data-sc-summary="name">—</div></div>
            <div class="col-md-3 col-sm-6"><strong><?= _l('sc_customer_email'); ?></strong><div data-sc-summary="email">—</div></div>
            <div class="col-md-3 col-sm-6"><strong><?= _l('sc_customer_phone'); ?></strong><div data-sc-summary="phone">—</div></div>
            <div class="col-md-3 col-sm-6"><strong><?= _l('sc_project_address'); ?></strong><div data-sc-summary="address">—</div></div>
        </div>
    </div>
</div>
<?php } ?>
