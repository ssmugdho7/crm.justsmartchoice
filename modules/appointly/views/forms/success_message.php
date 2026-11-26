<?php defined('BASEPATH') or exit('No direct script access allowed');

// Self-contained styles: this public page does not load Tailwind, so it must not depend on utility classes.
$sxL = function ($key, $fallback) {
    $line = _l($key);

    return ($line === '' || $line === $key) ? $fallback : $line;
};
$sxWhen = '';
if (isset($appointment) && $appointment && !empty($appointment['date'])) {
    $sxWhen = _d($appointment['date']);
    if (!empty($appointment['start_hour'])) {
        $sxWhen .= ' · ' . date(get_option('time_format') == 24 ? 'H:i' : 'g:i A', strtotime($appointment['start_hour']));
    }
}
$sxIcons = [
    'doc'      => '<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    'subject'  => '<path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>',
    'calendar' => '<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
    'service'  => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
    'person'   => '<path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
    'pin'      => '<path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'info'     => '<path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'clock'    => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'plus'     => '<path d="M12 6v12m6-6H6"/>',
    'external' => '<path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>',
];
$sxIcon = function ($name, $class = 'apsx-ico') use ($sxIcons) {
    return '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $sxIcons[$name] . '</svg>';
};
$sxDetails = [];
if (isset($appointment) && $appointment) {
    $sxDetails[] = ['subject', 'slate', $sxL('appointment_subject', 'Subject'), $appointment['subject'] ?? '', false];
    $sxDetails[] = ['calendar', 'blue', $sxL('appointment_date_and_time', 'Date & Time'), $sxWhen !== '' ? $sxWhen : $sxL('appointment_to_be_confirmed', 'To be confirmed'), false];
    if (!empty($appointment['service_name'])) {
        $sxDetails[] = ['service', 'green', $sxL('appointment_service', 'Service'), $appointment['service_name'], false];
    }
    if (!empty($appointment['provider_name'])) {
        $sxDetails[] = ['person', 'violet', $sxL('appointment_provider', 'Provider'), $appointment['provider_name'], false];
    }
    if (!empty($appointment['address'])) {
        $sxDetails[] = ['pin', 'orange', $sxL('appointment_location', 'Location'), $appointment['address'], true];
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e(get_option('active_language') === 'spanish' ? 'es' : 'en') ?>">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($sxL('appointment_successfully_scheduled', 'Appointment scheduled')) ?></title>
    <?php app_external_form_header($form); ?>
    <style>
        body.apsx-body{margin:0;min-height:100vh;background:linear-gradient(160deg,#eef6f2 0%,#e5ece9 55%,#dfe7e4 100%);font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#1f2937;-webkit-font-smoothing:antialiased}
        .apsx-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 16px;box-sizing:border-box}
        .apsx-card{width:100%;max-width:760px;background:#fff;border-radius:20px;box-shadow:0 24px 60px -20px rgba(6,48,34,.35),0 2px 6px rgba(6,48,34,.06);overflow:hidden;animation:apsx-rise .5s cubic-bezier(.2,.8,.2,1) both}
        .apsx-hero{position:relative;background:linear-gradient(135deg,#10b981 0%,#059669 55%,#047857 100%);color:#fff;text-align:center;padding:44px 28px 36px;overflow:hidden}
        .apsx-hero:before,.apsx-hero:after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.08)}
        .apsx-hero:before{width:260px;height:260px;top:-120px;left:-80px}
        .apsx-hero:after{width:200px;height:200px;bottom:-110px;right:-50px}
        .apsx-check{position:relative;width:76px;height:76px;margin:0 auto 18px;border-radius:50%;background:rgba(255,255,255,.18);box-shadow:0 0 0 8px rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;animation:apsx-pop .55s .15s cubic-bezier(.2,1.4,.4,1) both}
        .apsx-check svg{width:38px;height:38px;stroke:#fff;stroke-width:3;fill:none;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:30;stroke-dashoffset:30;animation:apsx-draw .45s .5s ease-out forwards}
        .apsx-title{position:relative;margin:0 0 10px;font-size:28px;line-height:1.25;font-weight:700;letter-spacing:-.01em;color:#fff}
        .apsx-sub{position:relative;margin:0 auto 18px;max-width:520px;font-size:15px;line-height:1.55;color:#ecfdf5}
        .apsx-badge{position:relative;display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:999px;background:#fef3c7;color:#92400e;font-size:13px;font-weight:600}
        .apsx-badge .apsx-ico{width:16px;height:16px}
        .apsx-section{padding:28px 32px;border-bottom:1px solid #eef2f0}
        .apsx-section:last-child{border-bottom:0}
        .apsx-h2{display:flex;align-items:center;gap:10px;margin:0 0 18px;font-size:19px;font-weight:700;color:#111827}
        .apsx-h2 .apsx-ico{width:22px;height:22px;color:#059669}
        .apsx-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .apsx-tile{display:flex;gap:12px;align-items:flex-start;padding:16px 18px;border-radius:14px;border-left:4px solid;min-width:0}
        .apsx-tile.apsx-wide{grid-column:1 / -1}
        .apsx-tile .apsx-ico{flex:0 0 auto;width:20px;height:20px;margin-top:2px}
        .apsx-label{margin:0 0 4px;font-size:11.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
        .apsx-value{margin:0;font-size:15.5px;font-weight:600;line-height:1.4;color:#111827;overflow-wrap:anywhere}
        .apsx-slate{background:#f8fafc;border-color:#64748b}.apsx-slate .apsx-ico,.apsx-slate .apsx-label{color:#475569}
        .apsx-blue{background:#eff6ff;border-color:#3b82f6}.apsx-blue .apsx-ico,.apsx-blue .apsx-label{color:#1d4ed8}
        .apsx-green{background:#ecfdf5;border-color:#10b981}.apsx-green .apsx-ico,.apsx-green .apsx-label{color:#047857}
        .apsx-violet{background:#f5f3ff;border-color:#8b5cf6}.apsx-violet .apsx-ico,.apsx-violet .apsx-label{color:#6d28d9}
        .apsx-orange{background:#fff7ed;border-color:#f97316}.apsx-orange .apsx-ico,.apsx-orange .apsx-label{color:#c2410c}
        .apsx-steps{list-style:none;margin:0;padding:0;counter-reset:apsx}
        .apsx-steps li{counter-increment:apsx;display:flex;gap:14px;align-items:flex-start;margin:0 0 14px;font-size:15px;line-height:1.55;color:#374151}
        .apsx-steps li:last-child{margin-bottom:0}
        .apsx-steps li:before{content:counter(apsx);flex:0 0 auto;width:28px;height:28px;border-radius:50%;background:#047857;color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;margin-top:-2px}
        .apsx-actions{text-align:center}
        .apsx-btn{display:inline-flex;align-items:center;gap:8px;padding:13px 26px;border-radius:12px;background:linear-gradient(135deg,#059669,#047857);color:#fff!important;font-size:15px;font-weight:600;text-decoration:none!important;box-shadow:0 10px 24px -10px rgba(4,120,87,.7);transition:transform .15s ease,box-shadow .15s ease}
        .apsx-btn:hover,.apsx-btn:focus-visible{transform:translateY(-1px);box-shadow:0 14px 28px -10px rgba(4,120,87,.75)}
        .apsx-btn:focus-visible{outline:3px solid #a7f3d0;outline-offset:3px}
        .apsx-btn .apsx-ico{width:18px;height:18px}
        .apsx-btn-sm{padding:9px 16px;font-size:14px;border-radius:10px;margin-top:10px;background:#2563eb;box-shadow:none}
        @keyframes apsx-rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
        @keyframes apsx-pop{from{opacity:0;transform:scale(.4)}to{opacity:1;transform:scale(1)}}
        @keyframes apsx-draw{to{stroke-dashoffset:0}}
        @media (max-width:600px){.apsx-wrap{padding:0;align-items:stretch}.apsx-card{border-radius:0;box-shadow:none}.apsx-hero{padding:36px 20px 28px}.apsx-title{font-size:23px}.apsx-section{padding:22px 18px}.apsx-grid{grid-template-columns:1fr}.apsx-btn{width:100%;justify-content:center;box-sizing:border-box}}
        @media (prefers-reduced-motion:reduce){.apsx-card,.apsx-check,.apsx-check svg{animation:none}.apsx-check svg{stroke-dashoffset:0}.apsx-btn{transition:none}}
    </style>
</head>

<body class="apsx-body">
    <main class="apsx-wrap">
        <div class="apsx-card">
            <header class="apsx-hero">
                <div class="apsx-check"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg></div>
                <h1 class="apsx-title"><?= e(isset($title) && $title !== '' ? $title : $sxL('appointment_successfully_scheduled', 'Appointment scheduled')) ?></h1>
                <p class="apsx-sub"><?= e($sxL('appointment_pending_approval_message', 'Your appointment request has been received and is pending approval.')) ?></p>
                <span class="apsx-badge"><?= $sxIcon('clock') ?><?= e($sxL('appointment_pending_approval', 'Pending approval')) ?></span>
            </header>

            <?php if ($sxDetails): ?>
                <section class="apsx-section" aria-labelledby="apsx-details-title">
                    <h2 class="apsx-h2" id="apsx-details-title"><?= $sxIcon('doc') ?><?= e($sxL('appointment_details', 'Appointment Details')) ?></h2>
                    <div class="apsx-grid">
                        <?php foreach ($sxDetails as [$icon, $tone, $label, $value, $wide]): ?>
                            <div class="apsx-tile apsx-<?= $tone ?><?= $wide ? ' apsx-wide' : '' ?>">
                                <?= $sxIcon($icon) ?>
                                <div>
                                    <p class="apsx-label"><?= e($label) ?></p>
                                    <p class="apsx-value"><?= e((string) $value !== '' ? $value : '—') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (
                get_option('appointly_show_invoice_option') == '1'
                && isset($appointment) && $appointment
                && !empty($appointment['contact_id']) && !empty($appointment['invoice_id'])
            ):
                $CI = &get_instance();
                $sxInvoice = $CI->db->select('hash')->where('id', $appointment['invoice_id'])->get(db_prefix() . 'invoices')->row();
            ?>
                <section class="apsx-section">
                    <h2 class="apsx-h2"><?= $sxIcon('doc') ?><?= e($sxL('invoice', 'Invoice')) ?></h2>
                    <div class="apsx-tile apsx-blue">
                        <?= $sxIcon('doc') ?>
                        <div>
                            <p class="apsx-value"><?= e(_l('invoice_created_for_appointment', format_invoice_number($appointment['invoice_id']))) ?></p>
                            <?php if ($sxInvoice && $sxInvoice->hash): ?>
                                <a class="apsx-btn apsx-btn-sm" target="_blank" rel="noopener" href="<?= site_url('invoice/' . (int) $appointment['invoice_id'] . '/' . e($sxInvoice->hash)) ?>"><?= $sxIcon('external') ?><?= e($sxL('view_invoice', 'View Invoice')) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <section class="apsx-section" aria-labelledby="apsx-next-title">
                <h2 class="apsx-h2" id="apsx-next-title"><?= $sxIcon('info') ?><?= e($sxL('appointment_whats_next', "What's next?")) ?></h2>
                <ol class="apsx-steps">
                    <li><?= e($sxL('appointment_staff_review', 'Our team will review your appointment request.')) ?></li>
                    <li><?= e($sxL('appointment_email_confirmation', 'You will receive an email confirmation once your appointment is approved.')) ?></li>
                    <li><?= e($sxL('appointment_prepare', 'Please prepare any necessary documents or information for your appointment.')) ?></li>
                </ol>
            </section>

            <section class="apsx-section apsx-actions">
                <a class="apsx-btn" href="<?= site_url('appointly/appointments_public/book') ?>"><?= $sxIcon('plus') ?><?= e($sxL('appointment_schedule_another', 'Schedule another')) ?></a>
            </section>
        </div>
    </main>
    <?php app_external_form_footer($form); ?>
</body>

</html>
