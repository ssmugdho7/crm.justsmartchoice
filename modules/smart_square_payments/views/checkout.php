<?php

defined('BASEPATH') or exit('No direct script access allowed');

$applicationId = trim((string) $gateway->getSetting('application_id'));
$locationId = trim((string) $gateway->getSetting('location_id'));
$sandbox = (int) $gateway->getSetting('sandbox') === 1;
$ready = $applicationId !== '' && $locationId !== '' && trim((string) $gateway->secret('access_token')) !== '';
$invoiceId = (int) $invoice->id;
?>
<!doctype html>
<html lang="<?= html_escape(get_option('active_language') === 'spanish' ? 'es' : 'en'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape(_l('smart_square_pay_securely')); ?></title>
    <style>
        body{margin:0;padding:32px 16px;background:#f5f7fa;color:#172b37;font:16px/1.5 system-ui,sans-serif}
        .smart-square-checkout{max-width:480px;margin:32px auto;padding:28px;background:#fff;border:1px solid #dce3e8;border-radius:12px;box-shadow:0 4px 16px #172b3710}
        h1{font-size:22px;margin:0 0 24px}button{border:0;border-radius:6px;background:#18795b;color:white;padding:13px 20px;font:inherit;cursor:pointer;width:100%}button:disabled{opacity:.55;cursor:wait}
        #smart-square-status{margin:16px 0;overflow-wrap:anywhere}a{display:block;margin-top:18px;color:#18795b}
    </style>
</head>
<body>
<div class="panel panel-default smart-square-checkout">
    <div class="panel-body">
        <h1><?= html_escape(_l('smart_square_pay_securely')); ?></h1>
        <?php if ($sandbox) { ?><p role="status"><?= html_escape(_l('smart_square_sandbox_notice')); ?></p><?php } ?>
        <?php if (!$ready) { ?>
            <div class="alert alert-danger"><?= html_escape(_l('smart_square_not_configured_contact')); ?></div>
        <?php } else { ?>
            <div id="smart-square-card" style="min-height:110px"></div>
            <div id="smart-square-status" role="alert" aria-live="polite"></div>
            <button type="button" class="btn btn-primary" id="smart-square-pay" disabled>
                <?= html_escape(_l('smart_square_pay_amount', app_format_money($amount, $invoice->currency_name ?? 'USD'))); ?>
            </button>
            <script src="<?= $sandbox ? 'https://sandbox.web.squarecdn.com/v1/square.js' : 'https://web.squarecdn.com/v1/square.js'; ?>"></script>
            <script>
            (function() {
                'use strict';
                const button = document.getElementById('smart-square-pay');
                const status = document.getElementById('smart-square-status');
                const applicationId = <?= json_encode($applicationId); ?>;
                const locationId = <?= json_encode($locationId); ?>;
                const endpoint = <?= json_encode(site_url('smart_square_payments/charge')); ?>;
                const invoiceId = <?= $invoiceId; ?>;
                const invoiceHash = <?= json_encode($invoice->hash); ?>;
                const csrfName = <?= json_encode($this->security->get_csrf_token_name()); ?>;
                const csrfHash = <?= json_encode($this->security->get_csrf_hash()); ?>;
                const messages = {
                    unavailable: <?= json_encode(_l('smart_square_library_unavailable')); ?>,
                    processing: <?= json_encode(_l('smart_square_processing')); ?>,
                    cardInvalid: <?= json_encode(_l('smart_square_card_invalid')); ?>,
                    statusUnavailable: <?= json_encode(_l('smart_square_status_unavailable')); ?>,
                    failed: <?= json_encode(_l('smart_square_payment_failed')); ?>
                };
                let card;

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
                        const result = await card.tokenize();
                        if (result.status !== 'OK') {
                            throw new Error(messages.cardInvalid);
                        }

                        const body = new URLSearchParams();
                        body.set('invoice_id', String(invoiceId));
                        body.set('invoice_hash', invoiceHash);
                        body.set('source_id', result.token);
                        body.set(csrfName, csrfHash);

                        const response = await fetch(endpoint, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: body.toString()
                        });
                        const data = await response.json();
                        status.textContent = data.message || messages.statusUnavailable;
                        if (data.success) {
                            window.location.reload();
                        }
                    } catch (error) {
                        status.textContent = error.message || messages.failed;
                    } finally {
                        button.disabled = false;
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
