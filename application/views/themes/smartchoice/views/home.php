<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style id="sc-dashboard-card-fix-v376">
.sc-client-dashboard-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:20px 0!important}.sc-client-summary-card{display:flex!important;align-items:center!important;min-height:92px;padding:16px!important;border:1px solid #dce5ee!important;border-radius:12px!important;background:#fff!important;box-shadow:0 5px 16px rgba(31,45,61,.08)!important;text-decoration:none!important}.sc-client-summary-card i{display:flex!important;align-items:center;justify-content:center;width:46px;height:46px;margin-right:13px;border-radius:10px;background:#3598DB;color:#fff!important;font-size:21px!important}.sc-client-summary-card:nth-child(2) i{background:#F28C28}.sc-client-summary-card:nth-child(3) i{background:#169179}.sc-client-summary-card:nth-child(4) i{background:#0E6F5B}.sc-client-summary-card strong{display:block;font-size:24px;line-height:1.05;color:#1f2937}.sc-client-summary-card span{display:block;margin-top:5px;color:#52606d;font-weight:600}@media(max-width:767px){.sc-client-dashboard-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.sc-client-summary-card{min-height:82px;padding:12px!important}}
</style>

<div class="row">
    <div class="col-md-12 section-client-dashboard">
        <h3 id="greeting" class="tw-font-semibold tw-mt-0"></h3>
        <?php if (has_contact_permission('projects')) { ?>
        <h3 class="projects-summary-heading tw-text-neutral-700 tw-font-medium tw-text-lg tw-mt-7">
            <?= _l('projects_summary'); ?>
        </h3>
        <?php get_template_part('projects/project_summary'); ?>
        <?php } ?>
        <?php hooks()->do_action('client_area_after_project_overview'); ?>
        <div class="sc-client-dashboard-grid">
            <?php if (has_contact_permission('invoices')) { ?>
            <a class="sc-client-summary-card" href="<?= site_url('clients/invoices'); ?>"><i class="fa-solid fa-file-invoice-dollar"></i><div><strong><?= total_rows(db_prefix().'invoices', ['clientid'=>get_client_user_id()]); ?></strong><span>Invoices</span></div></a>
            <?php } ?>
            <?php if (has_contact_permission('contracts')) { ?>
            <a class="sc-client-summary-card" href="<?= site_url('clients/contracts'); ?>"><i class="fa-solid fa-file-signature"></i><div><strong><?= total_rows(db_prefix().'contracts', ['client'=>get_client_user_id(), 'trash'=>0]); ?></strong><span>Contracts</span></div></a>
            <?php } ?>
            <?php if (has_contact_permission('projects')) { ?>
            <a class="sc-client-summary-card" href="<?= site_url('clients/projects'); ?>"><i class="fa-solid fa-diagram-project"></i><div><strong><?= total_rows(db_prefix().'projects', ['clientid'=>get_client_user_id()]); ?></strong><span>Projects</span></div></a>
            <?php } ?>
            <?php if (has_contact_permission('support')) { ?>
            <a class="sc-client-summary-card" href="<?= site_url('clients/tickets'); ?>"><i class="fa-solid fa-headset"></i><div><strong><?= total_rows(db_prefix().'tickets', ['userid'=>get_client_user_id()]); ?></strong><span>Support Tickets</span></div></a>
            <?php } ?>
        </div>
        <?php if (has_contact_permission('invoices')) { ?><div class="text-right mtop10"><a class="btn btn-default btn-sm" href="<?= site_url('clients/statement'); ?>">View Account Statement</a></div><?php } ?>
        <?php hooks()->do_action('client_area_dashboard_end'); ?>
    </div>
    <script>
        var greetDate = new Date();
        var hrsGreet = greetDate.getHours();

        var greet;
        if (hrsGreet < 12)
            greet = "<?= _l('good_morning'); ?>";
        else if (hrsGreet >= 12 && hrsGreet <= 17)
            greet = "<?= _l('good_afternoon'); ?>";
        else if (hrsGreet >= 17 && hrsGreet <= 24)
            greet = "<?= _l('good_evening'); ?>";

        if (greet) {
            document.getElementById('greeting').innerHTML =
                '<b>' + greet + ' <?= e($contact->firstname); ?>!</b>';
        }
    </script>