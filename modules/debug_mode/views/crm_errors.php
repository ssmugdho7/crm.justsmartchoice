<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$plain = '';
if (empty($error_report)) { $plain = 'No recent CRM error lines found.'; }
else { foreach ((array) $error_report as $row) { $plain .= '[' . $row['file'] . '] ' . $row['line'] . "\n"; } }
?>
<div id="wrapper" class="debug-mode-page"><div class="content"><div class="panel_s debug-mode-card"><div class="panel-body">
    <div class="debug-mode-hero debug-mode-hero-compact"><div><h1><i class="fa fa-exclamation-triangle"></i> CRM Error Report</h1><p>Recent PHP, CodeIgniter, module, and migration errors from the CRM log files.</p></div><div class="debug-mode-inline-actions"><a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode/crm_errors'); ?>"><i class="fa fa-refresh"></i> Refresh</a><button type="button" class="btn btn-info btn-sm" id="debugCopyErrors"><i class="fa fa-copy"></i> Copy All</button><a class="btn btn-success btn-sm" href="<?php echo admin_url('debug_mode/export_errors'); ?>"><i class="fa fa-download"></i> Export</a><a href="<?php echo admin_url('debug_mode'); ?>" class="btn btn-default btn-xs">Back</a></div></div>
    <div class="debug-mode-bottom-nav debug-mode-top-nav"><a href="<?php echo admin_url('debug_mode'); ?>">Utilities</a><a href="<?php echo admin_url('debug_mode/health'); ?>">Health</a><a class="active" href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Errors</a><a  href="<?php echo admin_url('debug_mode/file_structure'); ?>">Structure</a><a href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a><a href="<?php echo admin_url('debug_mode/monitoring'); ?>">Monitoring</a></div>
    <div class="row mtop15"><div class="col-md-8"><input type="text" class="form-control input-sm" id="debugErrorSearch" placeholder="Search/filter errors..."></div><div class="col-md-4 text-right"><span class="label label-default">Lines: <?php echo count((array)$error_report); ?></span></div></div>
    <textarea id="debugErrorTextarea" class="form-control debug-mode-error-editor mtop15" rows="24" readonly><?php echo html_escape($plain); ?></textarea>
</div></div></div></div>
<script>
(function(){
  var original = document.getElementById('debugErrorTextarea') ? document.getElementById('debugErrorTextarea').value : '';
  var search = document.getElementById('debugErrorSearch');
  var ta = document.getElementById('debugErrorTextarea');
  if (search && ta) search.addEventListener('keyup', function(){ var q=this.value.toLowerCase(); if(!q){ta.value=original;return;} ta.value=original.split('\n').filter(function(l){return l.toLowerCase().indexOf(q)!==-1;}).join('\n'); });
  var copy = document.getElementById('debugCopyErrors');
  if (copy && ta) copy.addEventListener('click', function(){ ta.select(); document.execCommand('copy'); if (typeof alert_float === 'function') alert_float('success','Errors copied.'); });
})();
</script>
<?php init_tail(); ?>
