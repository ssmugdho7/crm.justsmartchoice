<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $isRTL = (is_rtl() ? 'true' : 'false'); ?>

<!DOCTYPE html>
<html lang="<?= e($locale); ?>"
    dir="<?= ($isRTL == 'true') ? 'rtl' : 'ltr' ?>">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>
        <?= $title ?? get_option('companyname'); ?>
    </title>

    <?= app_compile_css(); ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/sc-sales-breadcrumbs.css?v=1'); ?>">
    <script type="application/json" id="sc-sales-breadcrumbs-config"><?= json_encode([
        'adminUrl' => admin_url(),
        'dashboard' => _l('als_dashboard'),
        'sales' => _l('als_sales'),
        'title' => trim(strip_tags((string) ($title ?? ''))),
        'view' => _l('view'),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    <script defer src="<?= base_url('assets/js/sc-sales-breadcrumbs.js?v=2'); ?>"></script>
    <?php render_admin_js_variables(); ?>

    <script>
        var totalUnreadNotifications = <?= e($current_user->total_unread_notifications); ?> ,
            proposalsTemplates = <?= json_encode(get_proposal_templates()); ?> ,
            contractsTemplates = <?= json_encode(get_contract_templates()); ?> ,
            billingAndShippingFields = ['billing_street', 'billing_city', 'billing_state', 'billing_zip',
                'billing_country',
                'shipping_street', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'
            ],
            isRTL = '<?= e($isRTL); ?>',
            taskid, taskTrackingStatsData, taskAttachmentDropzone, taskCommentAttachmentDropzone, newsFeedDropzone,
            expensePreviewDropzone, taskTrackingChart, cfh_popover_templates = {},
            _table_api;
    </script>
    <?php
    // Smart Choice 4.3.4: prevent duplicate Highcharts core registrations from modules/hooks.
    // Highcharts add-ons remain untouched.
    ob_start();
    app_admin_head();
    $scAdminHead = ob_get_clean();
    $scHighchartsCoreSeen = false;
    $scAdminHead = preg_replace_callback(
        '~<script\b[^>]*\bsrc="[^"]*highcharts(?:\.min)?\.js[^"]*"[^>]*>\s*</script>~i',
        static function ($match) use (&$scHighchartsCoreSeen) {
            if ($scHighchartsCoreSeen) {
                return '';
            }
            $scHighchartsCoreSeen = true;
            return $match[0];
        },
        $scAdminHead
    );
    echo $scAdminHead;
    ?>
</head>

<body <?= admin_body_class($bodyclass ?? ''); ?>>
    <?php hooks()->do_action('after_body_start'); ?>
