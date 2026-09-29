<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('social_lead_funnel/_nav'); ?>
<div class="panel_s slf-card"><div class="panel-body">
<h3><?php echo _l('social_lead_funnel_help'); ?></h3>
<div class="alert alert-info">This module is designed for compliant lead capture. Use approved APIs, webhooks, lead ads, page comments/messages, Nextdoor approved access, or manual entry. Do not use it for account scraping or automatic posting where the platform does not allow it.</div>
<h4>Recommended workflow</h4><ol><li>Configure Settings.</li><li>Add keywords for services you want to track.</li><li>Import or manually create a social opportunity.</li><li>Review the AI suggested reply.</li><li>Create a CRM lead.</li><li>Assign staff and follow up.</li></ol>
<h4>Facebook setup</h4><p>Use Meta Lead Ads, Page webhooks, Messenger/Page comments where permitted. Add Page ID and token in Settings. Future webhook endpoint: <code><?php echo site_url('admin/social_lead_funnel/webhook'); ?></code></p>
<h4>Nextdoor setup</h4><p>Use approved Nextdoor developer/API access or manual lead capture. Add API key in Settings if approved.</p>
<h4>AI replies</h4><p>Version 1.0.1 creates local rule-based reply drafts. A paid AI provider can be added later through the AI Provider setting.</p>
</div></div>
</div></div></div></div><?php init_tail(); ?>
