<?php
defined('BASEPATH') or exit('No direct script access allowed');
$portalMetrics = [];
if (has_contact_permission('projects')) {
    $projectStatusCounts = sc_customer_status_counts('projects');
    $portalMetrics[] = ['clients/projects', 'fa-solid fa-diagram-project', _l('clients_my_projects'), array_sum($projectStatusCounts), 'work'];
}
if (has_contact_permission('invoices')) {
    $invoiceStatusCounts = sc_customer_status_counts('invoices');
    $invoiceMetricTotal = array_sum($invoiceStatusCounts);
    if (get_option('exclude_invoice_from_client_area_with_draft_status') == 1) { $invoiceMetricTotal -= $invoiceStatusCounts[Invoices_model::STATUS_DRAFT] ?? 0; }
    $portalMetrics[] = ['clients/invoices', 'fa-solid fa-file-invoice-dollar', _l('clients_my_invoices'), $invoiceMetricTotal, 'billing'];
}
if (has_contact_permission('contracts')) {
    $portalMetrics[] = ['clients/contracts', 'fa-solid fa-file-signature', _l('clients_contracts'), total_rows(db_prefix() . 'contracts', ['client' => get_client_user_id(), 'trash' => 0, 'not_visible_to_client' => 0]), 'legal'];
}
if (has_contact_permission('support')) {
    $ticketWhere = ['userid' => get_client_user_id()];
    if (!can_logged_in_contact_view_all_tickets()) { $ticketWhere['contactid'] = get_contact_user_id(); }
    $portalMetrics[] = ['clients/tickets', 'fa-solid fa-headset', _l('clients_nav_support'), total_rows(db_prefix() . 'tickets', $ticketWhere), 'support'];
}
?>
<div class="col-md-12 section-client-dashboard sc-dashboard">
    <div class="sc-dashboard-welcome">
        <div>
            <div class="sc-dashboard-eyebrow">Customer Portal / <?= e(_l('dashboard_string')); ?></div>
            <h1>Welcome back, <?= e($contact->firstname); ?>!</h1>
            <p>Your projects and account, at a glance.</p>
        </div>
        <div class="sc-dashboard-actions">
            <?php if (has_contact_permission('support')) { ?>
            <a class="btn btn-primary" href="<?= site_url('clients/open_ticket'); ?>"><i class="fa-solid fa-headset" aria-hidden="true"></i> Support Request</a>
            <?php } ?>
            <?php if (has_contact_permission('invoices')) { ?>
            <a class="btn btn-default" href="<?= site_url('clients/statement'); ?>"><i class="fa-regular fa-file-lines" aria-hidden="true"></i> <?= e(_l('view_account_statement')); ?></a>
            <?php } ?>
        </div>
    </div>
    <?php if ($portalMetrics) { ?>
    <div class="sc-dashboard-metrics">
        <?php foreach ($portalMetrics as [$route, $icon, $label, $count, $type]) { ?>
        <a class="sc-dashboard-metric sc-metric-<?= e($type); ?>" href="<?= site_url($route); ?>"><i class="<?= e($icon); ?>" aria-hidden="true"></i><div><strong><?= e($count); ?></strong><span><?= e($label); ?></span><?php if ((int) $count === 0) { ?><small>Nothing shared yet</small><?php } ?></div></a>
        <?php } ?>
    </div>
    <?php } ?>
    <div class="sc-dashboard-columns">
        <?php if (has_contact_permission('projects')) {
            // Mirror the existing customer project scope and cap the dashboard query.
            $recentProjects = get_instance()->db->select('id,name,status,deadline')->where('clientid', get_client_user_id())->order_by('id', 'DESC')->limit(3)->get(db_prefix() . 'projects')->result_array();
        ?>
        <section class="sc-dashboard-section" aria-labelledby="sc-recent-projects">
            <div class="sc-dashboard-section-title"><h2 id="sc-recent-projects">Recent projects</h2><a href="<?= site_url('clients/projects'); ?>">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
            <?php foreach ($recentProjects as $project) {
                $status = get_project_status_by_id($project['status']);
                $progress = max(0, min(100, (int) get_instance()->projects_model->calc_progress($project['id'])));
            ?>
            <article class="sc-dashboard-project">
                <div class="sc-dashboard-project-header"><h3><a href="<?= site_url('clients/project/' . $project['id']); ?>"><?= e($project['name']); ?></a></h3><span class="label label-default"><?= e($status['name'] ?? ''); ?></span></div>
                <p><?= e(_l('project_deadline')); ?>: <?= $project['deadline'] ? e(_d($project['deadline'])) : 'Not set'; ?></p>
                <div class="progress"><div class="progress-bar" role="progressbar" aria-label="Project progress" aria-valuenow="<?= e($progress); ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= e($progress); ?>%"></div></div>
                <small><?= e($progress); ?>% complete</small>
            </article>
            <?php } if (!$recentProjects) { ?>
            <div class="sc-dashboard-empty"><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><strong>No projects yet</strong><p>Your projects will appear here when the team adds them to your account.</p><?php if (has_contact_permission('support')) { ?><a class="btn btn-default" href="<?= site_url('clients/open_ticket'); ?>">Ask the team</a><?php } ?></div>
            <?php } ?>
        </section>
        <?php } ?>
        <?php if (has_contact_permission('invoices')) { ?>
        <section class="sc-dashboard-section" aria-labelledby="sc-billing-snapshot">
            <div class="sc-dashboard-section-title"><h2 id="sc-billing-snapshot">Billing snapshot</h2><a href="<?= site_url('clients/invoices'); ?>">View invoices <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
            <div class="sc-dashboard-billing">
                <?php get_template_part('invoices_stats', ['invoiceStatusCounts' => $invoiceStatusCounts]); ?>
                <a class="btn btn-default btn-block" href="<?= site_url('clients/statement'); ?>"><?= e(_l('view_account_statement')); ?></a>
            </div>
        </section>
        <?php } ?>
    </div>
    <?php if (has_contact_permission('projects')) { ?>
    <section class="sc-dashboard-summary" aria-labelledby="sc-project-summary"><div class="sc-dashboard-section-title"><h2 id="sc-project-summary"><?= e(_l('projects_summary')); ?></h2></div><?php get_template_part('projects/project_summary', ['projectStatusCounts' => $projectStatusCounts]); ?></section>
    <?php } ?>
    <?php hooks()->do_action('client_area_after_project_overview'); ?>
    <section class="sc-dashboard-section" aria-labelledby="sc-resources">
        <div class="sc-dashboard-section-title"><h2 id="sc-resources">Documents & resources</h2></div>
        <div class="sc-dashboard-resource-grid">
            <a class="sc-dashboard-resource" href="<?= site_url('clients/files'); ?>"><i class="fa-solid fa-paperclip" aria-hidden="true"></i><div><strong><?= e(_l('customer_profile_files')); ?></strong><span>Uploads and shared project files.</span></div></a>
            <a class="sc-dashboard-resource" href="<?= site_url('clients/calendar'); ?>"><i class="fa-regular fa-calendar" aria-hidden="true"></i><div><strong><?= e(_l('calendar')); ?></strong><span>Upcoming events and project dates.</span></div></a>
            <?php if (is_knowledge_base_viewable(true)) { ?>
            <a class="sc-dashboard-resource" href="<?= site_url('knowledge-base'); ?>"><i class="fa-solid fa-book-open" aria-hidden="true"></i><div><strong><?= e(_l('clients_nav_kb')); ?></strong><span>Answers, guides, and customer resources.</span></div></a>
            <?php } ?>
        </div>
    </section>
    <?php hooks()->do_action('client_area_dashboard_end'); ?>
</div>
