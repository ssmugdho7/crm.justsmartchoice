<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="alert alert-info">This index centralizes Smart Choice module settings links. If an older module registered a settings title without a working page, use the matching link below.</div>
<div class="table-responsive">
<table class="table table-striped">
<thead><tr><th>Setting Area</th><th>Purpose</th><th>Open</th></tr></thead>
<tbody>
<tr><td>Smart Choice Client Portal</td><td>Activate or deactivate customer portal visibility.</td><td><a class="btn btn-default btn-xs" href="<?= admin_url('settings?group=smart_choice_client_portal'); ?>">Open</a></td></tr>
<tr><td>Smart Choice AI Voice Agent</td><td>Twilio, OpenAI, Appointly, lead creation, call transfer settings.</td><td><a class="btn btn-default btn-xs" href="<?= admin_url('settings?group=smart_choice_ai_voice_agent'); ?>">Open</a></td></tr>
<tr><td>Smart Choice Company Links</td><td>EIN, CRM domain, company domain, and document link controls.</td><td><a class="btn btn-default btn-xs" href="<?= admin_url('settings?group=smart_choice_company_links'); ?>">Open</a></td></tr>
<tr><td>CRM Utilities And Debug Tools</td><td>Error report, file inventory, and repair utilities.</td><td><a class="btn btn-default btn-xs" href="<?= admin_url('settings?group=smart_choice_debug_tools'); ?>">Open</a></td></tr>
<tr><td>Smart Installer Tracking Settings</td><td>Installer tracking, appointment bridge, client tracking page.</td><td><a class="btn btn-default btn-xs" href="<?= admin_url('smart_installer_tracking/settings'); ?>">Open</a></td></tr>
<tr><td>Smart Choice Links Settings</td><td>Top bar quick links/favorites on both sides of search.</td><td><a class="btn btn-default btn-xs" href="<?= admin_url('smart_choice_links/settings'); ?>">Open</a></td></tr>
<tr><td>Purchasing Hub Settings</td><td>Purchasing, vendors, purchase orders, accounts payable settings.</td><td><a class="btn btn-default btn-xs" href="<?= admin_url('purchasing_hub/settings'); ?>">Open</a></td></tr>
<tr><td>Favorite Links Settings</td><td>Staff favorite links and quick access tools.</td><td><a class="btn btn-default btn-xs" href="<?= admin_url('favorite_links/settings'); ?>">Open</a></td></tr>
</tbody>
</table>
</div>
