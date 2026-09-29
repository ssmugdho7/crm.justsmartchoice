<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content snm-wrap">
  <div class="snm-hero"><div><h1><i class="fa fa-question-circle"></i> Smart Network Manager Help Guide</h1><p>Safe setup notes for CRM, Windows Server Agent, router support, and database tools.</p></div><div class="snm-actions"><a class="btn snm-btn-light" href="<?php echo admin_url('smart_network_manager'); ?>">Back</a></div></div>
  <div class="panel_s snm-panel"><div class="panel-body snm-help-doc">
    <h3>1. How it works</h3><p>The CRM module stores devices, schedules, logs, and settings. The Windows Server Agent performs the local network scan inside your house because Bluehost cannot see your local network directly.</p>
    <h3>2. Safe mode first</h3><p>Controls are disabled by default. Keep Read Only mode until the agent and router integration are verified.</p>
    <h3>3. Security</h3><p>Do not expose the Windows Agent port to the public internet. Use LAN-only, VPN, firewall whitelist, and a strong token.</p>
    <h3>4. Database tools</h3><p>Check, Analyze, Optimize, and Repair only run on Smart Network Manager tables. They do not delete business CRM data.</p>
    <h3>5. Cache cleanup</h3><p>Cache cleanup removes files in application/cache but preserves index.html.</p>
    <h3>6. Real blocking</h3><p>Real device block/unblock requires a compatible router, firewall, or router adapter. Without that, the module logs the action and keeps the system safe.</p>
  </div></div>
</div></div>
<?php init_tail(); ?>
