<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-installer-tracking"><div class="panel_s"><div class="panel-body">
<h4><?php echo html_escape($title); ?></h4>
<div class="sit-help-card"><h5>Workflow</h5><p>Create a trip from an appointment or project, assign an installer, send the client tracking link, and let the installer share location from the trip page.</p></div>
<div class="sit-help-card"><h5>Client Experience</h5><p>The client sees installer name, photo placeholder, ETA, status, destination, and current route location when live tracking is enabled.</p></div>
<div class="sit-help-card"><h5>Security</h5><p>Client links use private tokens. Dangerous actions use POST and preserve business data. API keys are masked in settings.</p></div>
<div class="sit-help-card"><h5>Google API</h5><p>Add your Google Maps API key in CRM Settings. Enable Maps JavaScript API and Distance Matrix API when you want live map rendering and route ETA.</p></div>
</div></div></div></div>
<?php init_tail(); ?>
