<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap smart-choice-normalized-module">
  <div class="google-meet-header"><div><h1><i class="fa fa-heartbeat"></i> Google Meet Health</h1><p>Checks module tables, settings, customer portal, API preferences, notification channels, and repair status.</p></div><div><a href="<?php echo admin_url('google_meet/health'); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Refresh</a> <a href="<?php echo admin_url('google_meet/fix_health'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-wrench"></i> Fix Issues</a></div></div>
  <?php $this->load->view('google_meet/_nav'); ?>
  <div class="panel_s google-meet-card"><div class="panel-body">
    <div class="gm-table-toolbar"><input type="text" class="form-control input-sm gm-health-search" placeholder="Search health checks..."><select class="form-control input-sm gm-health-filter"><option value="all">All Statuses</option><option value="pass">Pass</option><option value="setup">Needs Setup</option></select></div>
    <div class="gm-health-grid">
      <?php foreach((array)$checks as $c){ $ok=!empty($c['status']); ?>
        <div class="gm-health-box" data-status="<?php echo $ok ? 'pass' : 'setup'; ?>">
          <div class="gm-health-top"><strong><?php echo html_escape($c['name'] ?? 'System Check'); ?></strong><?php echo $ok ? '<span class="gm-badge gm-badge-green">Pass</span>' : '<span class="gm-badge gm-badge-red">Needs Setup</span>'; ?></div>
          <p><?php echo html_escape($c['detail'] ?? ($ok ? 'No action required.' : 'Open Settings or click Fix Issues to rebuild safe module tables/options.')); ?></p>
        </div>
      <?php } ?>
    </div>
  </div></div>
</div></div></div>
<script>
document.addEventListener('DOMContentLoaded',function(){
  var search=document.querySelector('.gm-health-search');
  var filter=document.querySelector('.gm-health-filter');
  function apply(){
    var q=(search&&search.value?search.value:'').toLowerCase();
    var f=(filter&&filter.value?filter.value:'all');
    document.querySelectorAll('.gm-health-box').forEach(function(box){
      var ok=(f==='all'||box.getAttribute('data-status')===f);
      var text=box.innerText.toLowerCase();
      box.style.display=(ok && text.indexOf(q)>-1)?'block':'none';
    });
  }
  if(search){search.addEventListener('keyup',apply);} if(filter){filter.addEventListener('change',apply);}
});
</script>
<?php init_tail(); ?>
