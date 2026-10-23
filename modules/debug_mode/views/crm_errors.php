<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$plain = '';
if (empty($error_report)) { $plain = 'No recent CRM error lines found.'; }
else { foreach ((array) $error_report as $row) { $plain .= '[' . $row['file'] . '] ' . $row['line'] . "\n\n"; } }
?>
<div id="wrapper" class="debug-mode-page"><div class="content"><div class="panel_s debug-mode-card"><div class="panel-body">
    <div class="debug-mode-hero debug-mode-hero-compact"><div><h1><i class="fa fa-exclamation-triangle"></i> CRM Error Report</h1><p>Recent PHP, CodeIgniter, module, and migration errors from the CRM log files.</p></div><div class="debug-mode-inline-actions"><a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode/crm_errors'); ?>"><i class="fa fa-refresh"></i> Refresh</a><button type="button" class="btn btn-info btn-sm" id="debugCopyErrors"><i class="fa fa-copy"></i> Copy Visible</button><a class="btn btn-success btn-sm" href="<?php echo admin_url('debug_mode/export_errors'); ?>"><i class="fa fa-download"></i> Export</a><a href="<?php echo admin_url('debug_mode'); ?>" class="btn btn-default btn-xs">Back</a></div></div>
    <div class="debug-mode-bottom-nav debug-mode-top-nav"><a href="<?php echo admin_url('debug_mode'); ?>">Utilities</a><a href="<?php echo admin_url('debug_mode/health'); ?>">Health</a><a class="active" href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Errors</a><a  href="<?php echo admin_url('debug_mode/file_structure'); ?>">Structure</a><a href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a><a href="<?php echo admin_url('debug_mode/monitoring'); ?>">Monitoring</a></div>
    <div class="debug-mode-coverage"><strong>Coverage:</strong> Newest first, last 7 days, up to 500 events, reading at most the last 1 MiB of each configured CRM/PHP log. Multiline traces stay with their error. Browser JavaScript, promise, resource and HTTP failures are captured on visited administrator pages while Debug Mode is active. This is observed runtime evidence, not a complete module test. Historical entries remain after a repair. Credentials and URL query strings are redacted; review exports before sharing.</div>
    <div class="debug-mode-error-filters">
      <label for="debugErrorSearch">Search<input type="search" class="form-control" id="debugErrorSearch" placeholder="Search message, file or route"></label>
      <label for="debugErrorModule">Module<select class="form-control" id="debugErrorModule"><option value="">All modules</option><?php $modules = array_unique(array_column((array)$error_report, 'module')); sort($modules); foreach ($modules as $module) { ?><option value="<?php echo html_escape($module); ?>"><?php echo html_escape($module); ?></option><?php } ?></select></label>
      <label for="debugErrorSeverity">Type<select class="form-control" id="debugErrorSeverity"><option value="">All types</option><option value="error">Errors</option><option value="warning">Warnings</option><option value="notice">Notices / translations</option><option value="browser">Browser</option></select></label>
    </div>
    <p id="debugErrorCount" role="status">Events: <?php echo count((array)$error_report); ?></p>
    <textarea id="debugErrorTextarea" class="form-control debug-mode-error-editor mtop15" rows="24" readonly><?php echo html_escape($plain); ?></textarea>
</div></div></div></div>
<script>
(function(){
  var records = <?php echo json_encode(array_values((array)$error_report), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?>;
  var search = document.getElementById('debugErrorSearch'), module = document.getElementById('debugErrorModule'), severity = document.getElementById('debugErrorSeverity'), ta = document.getElementById('debugErrorTextarea');
  function filter(){
    var q = search.value.toLowerCase();
    var rows = records.filter(function(row){return (!module.value || row.module === module.value) && (!severity.value || row.severity === severity.value) && (!q || row.line.toLowerCase().indexOf(q)!==-1 || row.file.toLowerCase().indexOf(q)!==-1);});
    ta.value = rows.map(function(row){return '['+row.file+'] '+row.line;}).join('\n\n');
    document.getElementById('debugErrorCount').textContent = 'Showing ' + rows.length + ' of ' + records.length + ' events';
  }
  search.addEventListener('input',filter); module.addEventListener('change',filter); severity.addEventListener('change',filter);
  document.getElementById('debugCopyErrors').addEventListener('click',function(){
    if(navigator.clipboard && navigator.clipboard.writeText){navigator.clipboard.writeText(ta.value).then(function(){if(typeof alert_float==='function')alert_float('success','Visible errors copied.');},function(){if(typeof alert_float==='function')alert_float('warning','Copy failed. Select the report and copy it manually.');});}
    else {ta.select();document.execCommand('copy');}
  });
})();
</script>
<?php init_tail(); ?>
