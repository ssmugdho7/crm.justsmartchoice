<?php

defined('BASEPATH') or exit('No direct script access allowed');

$applicationId = trim((string) $gateway->getSetting('application_id'));
$locationId = trim((string) $gateway->getSetting('location_id'));
$sandbox = (int) $gateway->getSetting('sandbox') === 1;
$ready = $applicationId !== '' && $locationId !== '' && trim((string) $gateway->secret('access_token')) !== '';
$invoiceId = (int) $invoice->id;
$customer = $customer ?? [];
?>
<!doctype html>
<html lang="<?= html_escape(get_option('active_language') === 'spanish' ? 'es' : 'en'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape(_l('smart_square_pay_securely')); ?></title>
    <style>
        body{margin:0;padding:32px 16px;background:#f5f7fa;color:#172b37;font:16px/1.5 system-ui,sans-serif}
        .smart-square-checkout{max-width:600px;margin:32px auto;padding:28px;background:linear-gradient(145deg,#fbf9ff,#f0e9fb);border:1px solid #e1d7f0;border-radius:16px;box-shadow:0 12px 36px #492b7712;box-sizing:border-box}
        .checkout-header{display:grid;grid-template-columns:100px 1fr 100px;align-items:center;gap:12px;margin-bottom:24px}.checkout-logo img{max-width:100%;height:auto;max-height:70px;object-fit:contain}.checkout-logo a{margin:0}
        h1{font-size:22px;line-height:1.3;margin:0;text-align:center;color:#422566}button{border:0;border-radius:8px;background:#6b46a2;color:white;padding:13px 20px;font:inherit;font-weight:600;cursor:pointer;width:100%}button:disabled{opacity:.55;cursor:wait}
        .checkout-customer{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:0 0 24px;padding:18px;background:#ffffffbd;border:1px solid #e5ddf0;border-radius:10px}.checkout-customer div{min-width:0}.checkout-customer dt{font-size:12px;font-weight:600;color:#69567f;margin-bottom:4px}.checkout-customer dd{margin:0;overflow-wrap:anywhere}.checkout-address{grid-column:1/-1}.checkout-note{font-size:13px;color:#655b71;margin:16px 0 0;line-height:1.6}
        @media(max-width:480px){body{padding:16px 12px}.smart-square-checkout{padding:20px;margin:16px auto}.checkout-header{grid-template-columns:64px 1fr 64px;gap:6px}h1{font-size:18px}.checkout-customer{grid-template-columns:1fr;gap:12px}}
        #smart-square-status{margin:16px 0;overflow-wrap:anywhere}a{display:block;margin-top:18px;color:#18795b}
    </style>
</head>
<body>
<div class="panel panel-default smart-square-checkout">
    <div class="panel-body">
        <header class="checkout-header">
            <div class="checkout-logo"><?php get_dark_company_logo(); ?></div>
            <h1><?= html_escape(_l('smart_square_pay_securely')); ?></h1>
            <span aria-hidden="true"></span>
        </header>
        <dl class="checkout-customer" aria-label="<?= html_escape(_l('smart_square_customer_details')); ?>">
            <?php foreach (['name', 'email', 'phone', 'address'] as $field) { ?>
                <div class="<?= $field === 'address' ? 'checkout-address' : ''; ?>">
                    <dt><?= html_escape(_l('smart_square_customer_' . $field)); ?></dt>
                    <dd><?= html_escape($customer[$field] ?? '') ?: html_escape(_l('smart_square_not_provided')); ?></dd>
                </div>
            <?php } ?>
        </dl>
        <?php if ($sandbox) { ?><p role="status"><?= html_escape(_l('smart_square_sandbox_notice')); ?></p><?php } ?>
        <?php if (!$ready) { ?>
            <div class="alert alert-danger"><?= html_escape(_l('smart_square_not_configured_contact')); ?></div>
        <?php } else { ?>
            <div id="smart-square-card" style="min-height:110px"></div>
            <div id="smart-square-status" role="alert" aria-live="polite"></div>
            <button type="button" class="btn btn-primary" id="smart-square-pay" disabled>
                <?= html_escape(_l('smart_square_pay_amount', app_format_money($amount, $invoice->currency_name ?? 'USD'))); ?>
            </button>
            <p class="checkout-note"><?= html_escape(_l('smart_square_receipt_notice')); ?></p>
            <script src="<?= $sandbox ? 'https://sandbox.web.squarecdn.com/v1/square.js' : 'https://web.squarecdn.com/v1/square.js'; ?>"></script>
            <script>
            (function() {
                'use strict';
                const button = document.getElementById('smart-square-pay');
                const status = document.getElementById('smart-square-status');
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

                button.addEventListener('click', async function() {
                    if (!card) {
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

                        const body = new URLSearchParams();
                        body.set('invoice_id', String(invoiceId));
                        body.set('invoice_hash', invoiceHash);
                        body.set('source_id', sourceToken);
                        body.set(csrfName, csrfHash);

                        requestSent = true;
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: body.toString()
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
        <a href="<?= html_escape(site_url('invoice/' . $invoiceId . '/' . $invoice->hash)); ?>"><?= html_escape(_l('smart_square_back_to_invoice')); ?></a>
    </div>
</div>
</body>
</html>
