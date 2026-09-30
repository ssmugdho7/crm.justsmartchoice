<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="<?= html_escape($form->language ?? 'en'); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title ?? _l('appointment_successfully_scheduled')); ?></title>
    <?php app_external_form_header($form); ?>
    <style>
        body.sc-appointment-success-page { min-height: 100vh; margin: 0; background: #f3f7f6; color: #1f2937; font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .sc-appointment-success { width: min(920px, calc(100% - 32px)); margin: 40px auto; }
        .sc-appointment-success__brand { display: flex; justify-content: center; margin-bottom: 20px; }
        .sc-appointment-success__brand img { width: auto; max-width: 220px; max-height: 96px; }
        .sc-appointment-success__shell { overflow: hidden; border: 1px solid #d9e4e1; border-radius: 8px; background: #fff; box-shadow: 0 12px 32px rgba(18, 58, 49, .09); }
        .sc-appointment-success__hero { padding: 38px 40px; background: #0e6f5b; color: #fff; text-align: center; }
        .sc-appointment-success__check { display: inline-flex; width: 58px; height: 58px; align-items: center; justify-content: center; margin-bottom: 16px; border: 2px solid rgba(255, 255, 255, .72); border-radius: 50%; font-size: 25px; }
        .sc-appointment-success__hero h1 { margin: 0 0 10px; color: #fff; font-size: 30px; font-weight: 700; line-height: 1.25; letter-spacing: 0; }
        .sc-appointment-success__hero p { max-width: 650px; margin: 0 auto 18px; color: #e5f3ef; font-size: 16px; line-height: 1.6; }
        .sc-appointment-success__status { display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px; border-radius: 999px; background: #fff4d8; color: #855800; font-size: 13px; font-weight: 700; }
        .sc-appointment-success__section { padding: 30px 34px; border-bottom: 1px solid #e7eeec; }
        .sc-appointment-success__section:last-child { border-bottom: 0; }
        .sc-appointment-success__section h2 { display: flex; align-items: center; gap: 10px; margin: 0 0 20px; color: #17332c; font-size: 20px; font-weight: 700; letter-spacing: 0; }
        .sc-appointment-success__section h2 i { color: #0e6f5b; }
        .sc-appointment-success__details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .sc-appointment-success__detail { min-width: 0; padding: 16px; border: 1px solid #dfe8e5; border-left: 4px solid #159176; border-radius: 6px; background: #f8fbfa; }
        .sc-appointment-success__detail--wide { grid-column: 1 / -1; }
        .sc-appointment-success__label { display: block; margin-bottom: 5px; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .sc-appointment-success__value { display: block; overflow-wrap: anywhere; color: #17212b; font-size: 16px; font-weight: 650; line-height: 1.45; }
        .sc-appointment-success__steps { display: grid; gap: 14px; margin: 0; padding: 0; list-style: none; }
        .sc-appointment-success__step { display: grid; grid-template-columns: 32px minmax(0, 1fr); gap: 12px; align-items: start; color: #44515f; line-height: 1.55; }
        .sc-appointment-success__step-number { display: inline-flex; width: 32px; height: 32px; align-items: center; justify-content: center; border-radius: 50%; background: #e3f2ed; color: #0e6f5b; font-weight: 800; }
        .sc-appointment-success__invoice { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 18px; border: 1px solid #d6e5e1; border-radius: 6px; background: #f6faf9; }
        .sc-appointment-success__invoice p { margin: 0; color: #3f4c58; }
        .sc-appointment-success__actions { display: flex; justify-content: center; gap: 10px; padding: 26px 34px; }
        .sc-appointment-success__actions .btn { display: inline-flex; min-height: 42px; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; border-radius: 5px; font-weight: 700; }
        .sc-appointment-success__actions .btn-primary, .sc-appointment-success__invoice .btn-primary { border-color: #0e6f5b; background: #0e6f5b; color: #fff; }
        .sc-appointment-success__actions .btn-primary:hover, .sc-appointment-success__invoice .btn-primary:hover { border-color: #095847; background: #095847; color: #fff; }
        @media (max-width: 640px) {
            .sc-appointment-success { width: min(100% - 20px, 920px); margin: 18px auto; }
            .sc-appointment-success__hero { padding: 30px 20px; }
            .sc-appointment-success__hero h1 { font-size: 24px; }
            .sc-appointment-success__section { padding: 24px 18px; }
            .sc-appointment-success__details { grid-template-columns: 1fr; }
            .sc-appointment-success__detail--wide { grid-column: auto; }
            .sc-appointment-success__invoice { align-items: stretch; flex-direction: column; }
            .sc-appointment-success__actions { padding: 22px 18px; }
            .sc-appointment-success__actions .btn { width: 100%; }
        }
    </style>
</head>
<body class="sc-appointment-success-page">
    <main class="sc-appointment-success">
        <div class="sc-appointment-success__brand"><?= get_dark_company_logo(); ?></div>
        <div class="sc-appointment-success__shell">
            <header class="sc-appointment-success__hero">
                <span class="sc-appointment-success__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                <h1><?= html_escape($title ?? _l('appointment_successfully_scheduled')); ?></h1>
                <p><?= html_escape($sub_message ?? _l('appointment_pending_approval_message')); ?></p>
                <span class="sc-appointment-success__status"><i class="fa-regular fa-clock"></i> <?= html_escape(_l('appointment_pending_approval') ?: 'Pending Approval'); ?></span>
            </header>

            <?php if (!empty($appointment)) { ?>
            <section class="sc-appointment-success__section" aria-labelledby="appointment-details-title">
                <h2 id="appointment-details-title"><i class="fa-regular fa-calendar-check"></i> <?= html_escape(_l('appointment_details') ?: 'Appointment Details'); ?></h2>
                <div class="sc-appointment-success__details">
                    <div class="sc-appointment-success__detail">
                        <span class="sc-appointment-success__label"><?= html_escape(_l('appointment_subject') ?: 'Subject'); ?></span>
                        <span class="sc-appointment-success__value"><?= html_escape($appointment['subject'] ?? _l('not_specified')); ?></span>
                    </div>
                    <div class="sc-appointment-success__detail">
                        <span class="sc-appointment-success__label"><?= html_escape(_l('appointment_date_and_time') ?: 'Date & Time'); ?></span>
                        <span class="sc-appointment-success__value"><?php
                            if (!empty($appointment['date']) && !empty($appointment['start_hour'])) {
                                echo html_escape(date('F j, Y', strtotime($appointment['date'])) . ' at ' . date('g:i A', strtotime($appointment['start_hour'])));
                            } else {
                                echo html_escape(_l('appointment_to_be_confirmed') ?: 'To be confirmed');
                            }
                        ?></span>
                    </div>
                    <?php if (!empty($appointment['service_name'])) { ?>
                    <div class="sc-appointment-success__detail">
                        <span class="sc-appointment-success__label"><?= html_escape(_l('appointment_service') ?: 'Service'); ?></span>
                        <span class="sc-appointment-success__value"><?= html_escape($appointment['service_name']); ?></span>
                    </div>
                    <?php } ?>
                    <?php if (!empty($appointment['provider_name'])) { ?>
                    <div class="sc-appointment-success__detail">
                        <span class="sc-appointment-success__label"><?= html_escape(_l('appointment_provider') ?: 'Provider'); ?></span>
                        <span class="sc-appointment-success__value"><?= html_escape($appointment['provider_name']); ?></span>
                    </div>
                    <?php } ?>
                    <?php if (!empty($appointment['address'])) { ?>
                    <div class="sc-appointment-success__detail sc-appointment-success__detail--wide">
                        <span class="sc-appointment-success__label"><?= html_escape(_l('appointment_location') ?: 'Location'); ?></span>
                        <span class="sc-appointment-success__value"><?= html_escape($appointment['address']); ?></span>
                    </div>
                    <?php } ?>
                </div>
            </section>
            <?php } ?>

            <?php
            $invoiceHash = '';
            if (get_option('appointly_show_invoice_option') == '1' && !empty($appointment['invoice_id'])) {
                $CI = &get_instance();
                $invoice = $CI->db->select('hash')->where('id', (int) $appointment['invoice_id'])->get(db_prefix() . 'invoices')->row();
                $invoiceHash = $invoice ? (string) $invoice->hash : '';
            }
            if ($invoiceHash !== '') { ?>
            <section class="sc-appointment-success__section" aria-labelledby="appointment-invoice-title">
                <h2 id="appointment-invoice-title"><i class="fa-regular fa-file-lines"></i> <?= html_escape(_l('invoice')); ?></h2>
                <div class="sc-appointment-success__invoice">
                    <p><?= html_escape(_l('invoice_created_for_appointment', format_invoice_number($appointment['invoice_id']))); ?></p>
                    <a class="btn btn-primary" href="<?= site_url('invoice/' . (int) $appointment['invoice_id'] . '/' . rawurlencode($invoiceHash)); ?>" target="_blank" rel="noopener">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> <?= html_escape(_l('view_invoice')); ?>
                    </a>
                </div>
            </section>
            <?php } ?>

            <section class="sc-appointment-success__section" aria-labelledby="appointment-next-title">
                <h2 id="appointment-next-title"><i class="fa-regular fa-circle-question"></i> <?= html_escape($whats_next ?? _l('appointment_whats_next')); ?></h2>
                <ol class="sc-appointment-success__steps">
                    <li class="sc-appointment-success__step"><span class="sc-appointment-success__step-number">1</span><span><?= html_escape($staff_review ?? _l('appointment_staff_review')); ?></span></li>
                    <li class="sc-appointment-success__step"><span class="sc-appointment-success__step-number">2</span><span><?= html_escape($email_confirmation ?? _l('appointment_email_confirmation')); ?></span></li>
                    <li class="sc-appointment-success__step"><span class="sc-appointment-success__step-number">3</span><span><?= html_escape($prepare ?? _l('appointment_prepare')); ?></span></li>
                </ol>
            </section>

            <div class="sc-appointment-success__actions">
                <a class="btn btn-primary" href="<?= site_url('appointly/appointments_public/book'); ?>">
                    <i class="fa-regular fa-calendar-plus"></i> <?= html_escape(_l('appointment_schedule_another')); ?>
                </a>
            </div>
        </div>
    </main>
    <?php app_external_form_footer($form); ?>
</body>
</html>
