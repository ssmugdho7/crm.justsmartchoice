<?php

defined('BASEPATH') or exit('No direct script access allowed');

$applicationId = trim((string) $gateway->getSetting('application_id'));
$locationId = trim((string) $gateway->getSetting('location_id'));
$sandbox = (int) $gateway->getSetting('sandbox') === 1;
$ready = $applicationId !== '' && $locationId !== '' && trim((string) $gateway->secret('access_token')) !== '';
$invoiceId = (int) $invoice->id;
$customer = $customer ?? [];
$billing = [
    'address_line_1' => (string) ($invoice->billing_street ?? ''),
    'locality' => (string) ($invoice->billing_city ?? ''),
    'administrative_district_level_1' => (string) ($invoice->billing_state ?? ''),
    'postal_code' => (string) ($invoice->billing_zip ?? ''),
    'country' => 'US',
];
if (!empty($invoice->billing_country) && function_exists('get_country')) {
    $country = get_country((int) $invoice->billing_country);
    if ($country && !empty($country->iso2)) { $billing['country'] = strtoupper($country->iso2); }
}
$fullAddress = implode(', ', array_filter(array_slice($billing, 0, 4), static fn($value) => trim($value) !== ''));
$icon = static function ($name) {
    $paths = [
        'lock' => '<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/>',
        'shield' => '<path d="m12 2 8 3v7c0 5-8 10-8 10S4 17 4 12V5zM8 12l3 3 5-6"/>',
        'card' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 9h20M6 15h4"/>',
        'pin' => '<path d="M19 9c0 5-7 13-7 13S5 14 5 9a7 7 0 1 1 14 0Z"/><circle cx="12" cy="9" r="2"/>',
        'edit' => '<path d="m15 4 5 5M4 16 16 4a2 2 0 0 1 4 4L8 20H4v-4Zm-1 6h19"/>',
        'receipt' => '<path d="M5 3h14v19l-3-2-4 2-4-2-3 2V3Zm4 5h6m-6 4h6m-6 4h4"/>',
    ];
    return '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true">' . $paths[$name] . '</svg>';
};
?>
<!doctype html>
<html lang="<?= html_escape(get_option('active_language') === 'spanish' ? 'es' : 'en'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape(_l('smart_square_pay_securely')); ?></title>
    <link rel="stylesheet" href="<?= html_escape(base_url('modules/smart_square_payments/assets/checkout.css?v=20261027c')); ?>">
</head>
<body>
<main class="smart-square-checkout">
    <div class="panel-body">
        <header class="checkout-header">
            <div class="checkout-logo"><?php get_dark_company_logo(); ?></div>
            <div class="checkout-title"><h1><?= html_escape(_l('smart_square_pay_title')); ?></h1><p><?= html_escape(_l('smart_square_with_square')); ?></p></div>
            <span class="checkout-security"><?= $icon('lock'); ?><span><?= html_escape(_l('smart_square_secure_payment')); ?></span></span>
        </header>
        <dl class="checkout-customer checkout-panel" aria-label="<?= html_escape(_l('smart_square_customer_details')); ?>">
            <?php foreach (['name', 'email', 'phone', 'address'] as $field) { ?>
                <div class="<?= $field === 'address' ? 'checkout-address' : ''; ?>">
                    <dt><?= html_escape(_l('smart_square_customer_' . $field)); ?></dt>
                    <dd><?= html_escape($field === 'address' ? ($fullAddress ?: ($customer[$field] ?? '')) : ($customer[$field] ?? '')) ?: html_escape(_l('smart_square_not_provided')); ?></dd>
                </div>
            <?php } ?>
            <?php if ($ready) { ?><div class="checkout-edit-action"><dt class="sr-only"><?= html_escape(_l('smart_square_edit_address')); ?></dt><dd><button class="checkout-edit" type="button" id="smart-square-edit"><?= $icon('edit'); ?><?= html_escape(_l('smart_square_edit')); ?></button></dd></div><?php } ?>
        </dl>
        <?php if ($sandbox) { ?><p class="checkout-notice" role="status"><?= html_escape(_l('smart_square_sandbox_notice')); ?></p><?php } ?>
        <?php if (!$ready) { ?>
            <div class="alert alert-danger"><?= html_escape(_l('smart_square_not_configured_contact')); ?></div>
        <?php } else { ?>
            <form id="smart-square-checkout-form" class="checkout-panel checkout-payment">
            <div class="payment-heading"><h2><?= $icon('card'); ?><?= html_escape(_l('smart_square_payment_information')); ?></h2><div class="card-brands" aria-label="Visa, Mastercard, American Express, Discover"><span>VISA</span><span class="mastercard" aria-hidden="true"></span><span class="amex">AMERICAN<br>EXPRESS</span><span class="discover">DISCOVER</span></div></div>
            <div id="smart-square-card"></div>
            <div class="billing-heading"><?= $icon('pin'); ?><?= html_escape(_l('smart_square_customer_address')); ?></div>
            <fieldset class="billing-fields" id="smart-square-billing">
                <legend class="sr-only"><?= html_escape(_l('smart_square_customer_address')); ?></legend>
                <label class="street"><span class="sr-only"><?= html_escape(_l('smart_square_street')); ?></span><input id="square-street" name="address_line_1" autocomplete="billing street-address" maxlength="500" required placeholder="<?= html_escape(_l('smart_square_street')); ?>"></label>
                <label><span class="sr-only"><?= html_escape(_l('smart_square_city')); ?></span><input id="square-city" name="locality" autocomplete="billing address-level2" maxlength="100" required placeholder="<?= html_escape(_l('smart_square_city')); ?>"></label>
                <label><span class="sr-only"><?= html_escape(_l('smart_square_state')); ?></span><input id="square-state" name="administrative_district_level_1" autocomplete="billing address-level1" maxlength="100" required placeholder="<?= html_escape(_l('smart_square_state')); ?>"></label>
                <label class="postal"><span class="sr-only"><?= html_escape(_l('smart_square_postal')); ?></span><input id="square-postal" name="postal_code" autocomplete="billing postal-code" maxlength="20" required placeholder="<?= html_escape(_l('smart_square_postal')); ?>"></label>
            </fieldset>
            <label class="checkbox-row"><input type="checkbox" id="square-same-address"><span><?= html_escape(_l('smart_square_same_address')); ?></span></label>
            <div id="smart-square-status" role="alert" aria-live="polite"></div>
            <button type="submit" class="pay-button" id="smart-square-pay" disabled>
                <?= $icon('lock'); ?><span><?= html_escape(_l('smart_square_pay_amount', app_format_money($amount, $invoice->currency_name ?? 'USD'))); ?></span>
            </button>
            <p class="checkout-note"><?= html_escape(_l('smart_square_receipt_notice')); ?></p>
            <label class="checkbox-row terms-row"><input type="checkbox" id="square-terms" required><span><?= html_escape(_l('smart_square_terms_prefix')); ?> <a href="<?= html_escape(site_url('terms-and-conditions')); ?>" target="_blank" rel="noopener"><?= html_escape(_l('smart_square_terms')); ?></a> <?= html_escape(_l('smart_square_and')); ?> <a href="<?= html_escape(site_url('privacy-policy')); ?>" target="_blank" rel="noopener"><?= html_escape(_l('smart_square_privacy')); ?></a>.</span></label>
            </form>
            <div class="checkout-trust"><?php foreach (['shield' => 'secure_payment', 'lock' => 'processed_square', 'receipt' => 'receipt_email', 'pin' => 'private_details'] as $symbol => $key) { ?><div><?= $icon($symbol); ?><span><?= html_escape(_l('smart_square_' . $key)); ?></span></div><?php } ?></div>
            <script src="<?= $sandbox ? 'https://sandbox.web.squarecdn.com/v1/square.js' : 'https://web.squarecdn.com/v1/square.js'; ?>"></script>
            <script>
            (function() {
                'use strict';
                const button = document.getElementById('smart-square-pay');
                const status = document.getElementById('smart-square-status');
                const form = document.getElementById('smart-square-checkout-form');
                const billingFields = document.getElementById('smart-square-billing');
                const sameAddress = document.getElementById('square-same-address');
                const terms = document.getElementById('square-terms');
                const edit = document.getElementById('smart-square-edit');
                const addressInputs = {
                    address_line_1: document.getElementById('square-street'),
                    locality: document.getElementById('square-city'),
                    administrative_district_level_1: document.getElementById('square-state'),
                    postal_code: document.getElementById('square-postal'),
                };
                const customerBilling = <?= json_encode($billing, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                sameAddress.addEventListener('change', function() {
                    Object.entries(addressInputs).forEach(([key, input]) => { input.value = sameAddress.checked ? (customerBilling[key] || '') : ''; });
                });
                Object.values(addressInputs).forEach(input => input.addEventListener('input', function() { sameAddress.checked = false; }));
                edit.addEventListener('click', function() {
                    addressInputs.address_line_1.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    addressInputs.address_line_1.focus();
                });
                const applicationId = <?= json_encode($applicationId); ?>;
                const locationId = <?= json_encode($locationId); ?>;
                const endpoint = <?= json_encode(site_url('smart_square_payments/charge')); ?>;
                const invoiceUrl = <?= json_encode(site_url('invoice/' . $invoiceId . '/' . $invoice->hash)); ?>;
                const invoiceId = <?= $invoiceId; ?>;
                const invoiceHash = <?= json_encode($invoice->hash); ?>;
                const csrfName = <?= json_encode($this->security->get_csrf_token_name()); ?>;
                const csrfHash = <?= json_encode($this->security->get_csrf_hash()); ?>;
                const messages = {
                    unavailable: <?= json_encode(_l('smart_square_library_unavailable')); ?>,
                    processing: <?= json_encode(_l('smart_square_processing')); ?>,
                    cardInvalid: <?= json_encode(_l('smart_square_card_invalid')); ?>,
                    statusUnavailable: <?= json_encode(_l('smart_square_status_unavailable')); ?>,
                    uncertain: <?= json_encode(_l('smart_square_response_uncertain')); ?>,
                    expired: <?= json_encode(_l('smart_square_session_expired')); ?>,
                    failed: <?= json_encode(_l('smart_square_payment_failed')); ?>
                };
                let card;
                let sourceToken;
                let requestSent = false;
                let completed = false;
                let requestBody;

                (async function initializeCard() {
                    try {
                        if (!window.Square) {
                            throw new Error(messages.unavailable);
                        }
                        const payments = window.Square.payments(applicationId, locationId);
                        card = await payments.card();
                        await card.attach('#smart-square-card');
                        button.disabled = false;
                    } catch (error) {
                        button.disabled = true;
                        status.textContent = error.message || messages.unavailable;
                    }
                })();

                form.addEventListener('submit', async function(event) {
                    event.preventDefault();
                    if (!card || button.disabled || (!requestBody && !form.reportValidity())) {
                        return;
                    }
                    button.disabled = true;
                    status.textContent = messages.processing;

                    try {
                        if (!sourceToken) {
                            const result = await card.tokenize();
                            if (result.status !== 'OK') {
                                throw new Error(messages.cardInvalid);
                            }
                            sourceToken = result.token;
                        }

                        if (!requestBody) {
                            const body = new URLSearchParams();
                            body.set('invoice_id', String(invoiceId));
                            body.set('invoice_hash', invoiceHash);
                            body.set('source_id', sourceToken);
                            body.set(csrfName, csrfHash);
                            Object.entries(addressInputs).forEach(([key, input]) => { body.set('billing_address[' + key + ']', input.value.trim()); });
                            body.set('billing_address[country]', customerBilling.country);
                            requestBody = body.toString();
                            // A retry retains the original address as well as its card token.
                            billingFields.disabled = sameAddress.disabled = terms.disabled = edit.disabled = true;
                        }

                        requestSent = true;
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: requestBody
                        });
                        if (!response.ok) {
                            throw new Error(response.status === 419 || response.status === 403 ? messages.expired : messages.uncertain);
                        }
                        const data = await response.json();
                        if (typeof data.success !== 'boolean') {
                            throw new Error(messages.uncertain);
                        }
                        status.textContent = data.message || messages.statusUnavailable;
                        if (data.success) {
                            completed = true;
                            window.location.assign(invoiceUrl);
                        }
                        requestSent = false;
                    } catch (error) {
                        // Preserve the same token after an interrupted response so any
                        // retry reuses the provider idempotency key rather than charging twice.
                        status.textContent = requestSent ? messages.uncertain : (error.message || messages.failed);
                    } finally {
                        button.disabled = completed;
                    }
                });
            })();
            </script>
        <?php } ?>
        <a class="back-link" href="<?= html_escape(site_url('invoice/' . $invoiceId . '/' . $invoice->hash)); ?>"><?= html_escape(_l('smart_square_back_to_invoice')); ?></a>
    </div>
</main>
</body>
</html>
