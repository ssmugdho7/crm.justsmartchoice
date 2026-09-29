<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="debug-mode-page">
      <div class="debug-mode-hero debug-mode-hero-compact">
        <div>
          <h1><i class="fa fa-signal"></i> Network Tools</h1>
          <p>Check internet reachability, test browser-to-CRM speed, review IP/routing details, and get DNS flush commands.</p>
        </div>
        <a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode'); ?>"><i class="fa fa-arrow-left"></i> Back</a>
      </div>

      <div class="debug-mode-bottom-nav debug-mode-top-nav">
        <a href="<?php echo admin_url('debug_mode'); ?>">Utilities</a>
        <a href="<?php echo admin_url('debug_mode/health'); ?>">Health</a>
        <a href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Errors</a>
        <a href="<?php echo admin_url('debug_mode/file_structure'); ?>">Structure</a>
        <a class="active" href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a><a href="<?php echo admin_url('debug_mode/monitoring'); ?>">Monitoring</a>
      </div>

      <div class="debug-mode-grid debug-mode-utility-grid">
        <div class="debug-mode-tile">
          <h3><i class="fa fa-wifi"></i> Internet Check</h3>
          <p>Checks if the CRM server can reach the internet.</p>
          <button type="button" class="btn btn-success btn-sm" id="scInternetCheckBtn">Check Internet</button>
          <div id="scInternetResult" class="debug-mode-mini-result">Not checked yet.</div>
        </div>
        <div class="debug-mode-tile">
          <h3><i class="fa fa-dashboard"></i> Speed Test</h3>
          <p>Tests browser-to-CRM download speed. This is not a full ISP test.</p>
          <div class="sc-speed-gauge"><div id="scSpeedNeedle"></div><span id="scSpeedValue">0 Mbps</span></div>
          <button type="button" class="btn btn-primary btn-sm" id="scSpeedBtn">Run Test</button>
        </div>
        <div class="debug-mode-tile">
          <h3><i class="fa fa-refresh"></i> Flush DNS</h3>
          <p>Run this on your Windows computer or server.</p>
          <textarea class="form-control debug-mode-copybox" readonly>ipconfig /flushdns
ipconfig /release
ipconfig /renew</textarea>
          <button type="button" class="btn btn-default btn-sm sc-copy-prev">Copy Commands</button>
        </div>
        <div class="debug-mode-tile">
          <h3><i class="fa fa-terminal"></i> Network Commands</h3>
          <p>Useful Windows troubleshooting commands.</p>
          <textarea class="form-control debug-mode-copybox" readonly>ipconfig /all
route print
tracert 8.8.8.8
ping 8.8.8.8
nslookup crm.justsmartchoice.com</textarea>
          <button type="button" class="btn btn-default btn-sm sc-copy-prev">Copy Commands</button>
        </div>
      </div>

      <div class="panel_s debug-mode-card"><div class="panel-body">
        <h3>Current CRM Network Information</h3>
        <div class="table-responsive debug-mode-table-wrap"><table class="table table-striped table-condensed"><tbody>
          <?php foreach ((array)$server_info as $k => $v) { ?>
            <tr><th style="width:260px"><?php echo html_escape($k); ?></th><td><code><?php echo nl2br(html_escape($v)); ?></code></td></tr>
          <?php } ?>
        </tbody></table></div>
      </div></div>

      <div class="debug-mode-bottom-nav">
        <a href="<?php echo admin_url('debug_mode'); ?>">Utilities</a>
        <a href="<?php echo admin_url('debug_mode/health'); ?>">Health</a>
        <a href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Errors</a>
        <a href="<?php echo admin_url('debug_mode/file_structure'); ?>">Structure</a>
        <a class="active" href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a><a href="<?php echo admin_url('debug_mode/monitoring'); ?>">Monitoring</a>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  var checkBtn=document.getElementById('scInternetCheckBtn'), result=document.getElementById('scInternetResult');
  if(checkBtn){ checkBtn.onclick=function(){ result.innerHTML='Checking...'; fetch(admin_url+'debug_mode/internet_check_json').then(r=>r.json()).then(function(j){ result.innerHTML=(j.online?'Online':'Offline')+' — '+j.message+' ('+j.latency_ms+' ms)'; }).catch(function(){ result.innerHTML='Internet check failed.'; }); }; }
  var speedBtn=document.getElementById('scSpeedBtn'), needle=document.getElementById('scSpeedNeedle'), value=document.getElementById('scSpeedValue');
  if(speedBtn){ speedBtn.onclick=function(){ value.innerHTML='Testing...'; var start=performance.now(); fetch(admin_url+'debug_mode/speed_payload?x='+(Date.now())).then(r=>r.arrayBuffer()).then(function(buf){ var sec=(performance.now()-start)/1000; var mbps=((buf.byteLength*8)/(sec*1000000)); value.innerHTML=mbps.toFixed(2)+' Mbps'; var deg=Math.min(180, mbps*3); needle.style.transform='rotate('+deg+'deg)'; }).catch(function(){ value.innerHTML='Test failed'; }); }; }
  document.querySelectorAll('.sc-copy-prev').forEach(function(btn){ btn.onclick=function(){ var t=btn.previousElementSibling; if(t){ t.select(); document.execCommand('copy'); if(typeof alert_float==='function') alert_float('success','Copied.'); } }; });
})();
</script>
<?php init_tail(); ?>
