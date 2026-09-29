<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<?php
$dashboard_links = [
    'commands' => admin_url('usi_smartchoice_seo/ai_commands'),
    'estimates' => admin_url('usi_smartchoice_seo/ai_estimates'),
    'photos' => admin_url('usi_smartchoice_seo/camera_intake'),
    'training' => admin_url('usi_smartchoice_seo/ai_training'),
    'price_index' => admin_url('usi_smartchoice_seo/pricing_engine'),
    'estimate_approvals' => admin_url('usi_smartchoice_seo/estimate_review_queue'),
    'customer_packages' => admin_url('usi_smartchoice_seo/customer_packages'),
    'field_verifications' => admin_url('usi_smartchoice_seo/field_verifications'),
    'videos' => admin_url('usi_smartchoice_seo/video_studio'),
    'voices' => admin_url('usi_smartchoice_seo/video_voices'),
    'avatars' => admin_url('usi_smartchoice_seo/video_avatars'),
    'pages' => admin_url('usi_smartchoice_seo/pages'),
    'keywords' => admin_url('usi_smartchoice_seo/keywords'),
    'reports' => admin_url('usi_smartchoice_seo/reports'),
    'memory_items' => admin_url('usi_smartchoice_seo/memory_engine'),
];
?>
<div id="wrapper"><div class="content usi-seo"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-dashboard-hero">
  <div><h4><i class="fa fa-microchip"></i> <?php echo html_escape($title); ?></h4><p>Sammy AI command center for estimating, voice, vision, field operations, purchasing, scheduling, closeout, reports, and CRM automation.</p></div>
  <div class="sc-dashboard-actions"><a class="btn btn-primary btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/voice_assistant'); ?>">Voice Assistant</a><a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimates'); ?>">AI Estimates</a><a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/executive_dashboard'); ?>">Executive Dashboard</a></div>
</div>
<div class="sc-dashboard-card-grid">
<?php foreach ($counts as $label => $count) { $url = $dashboard_links[$label] ?? admin_url('usi_smartchoice_seo/ai_dashboard'); ?>
  <a class="sc-card sc-dashboard-card sc-dashboard-link" href="<?php echo $url; ?>"><span class="sc-card-count"><?php echo (int)$count; ?></span><span class="sc-card-label"><?php echo ucwords(str_replace('_', ' ', html_escape($label))); ?></span></a>
<?php } ?>
</div>
<div class="alert alert-info mtop15">All count cards are clickable. They open the related Sammy AI section for review, updates, import, export, and action follow-up.</div>
</div></div></div></div><?php init_tail(); ?>
