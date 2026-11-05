<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="debug-mode-page">
      <div class="debug-mode-hero debug-mode-hero-compact">
        <div>
          <h1><i class="fa fa-signal"></i> Network Tools</h1>
          <p>Check internet reachability, test the CRM connection, run a full external ISP speed test, review network details, and copy Windows troubleshooting commands individually.</p>
        </div>
        <a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode'); ?>"><i class="fa fa-arrow-left"></i> Back</a>
      </div>

      <div class="debug-mode-bottom-nav debug-mode-top-nav">
        <a href="<?php echo admin_url('debug_mode'); ?>">Utilities</a>
        <a href="<?php echo admin_url('debug_mode/health'); ?>">Health</a>
        <a href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Errors</a>
        <a href="<?php echo admin_url('debug_mode/file_structure'); ?>">Structure</a>
        <a class="active" href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a>
        <a href="<?php echo admin_url('debug_mode/monitoring'); ?>">Monitoring</a>
      </div>

      <div class="debug-mode-grid debug-mode-utility-grid">
        <div class="debug-mode-tile">
          <h3><i class="fa fa-wifi"></i> Internet Check</h3>
          <p>Checks whether the CRM server can reach the internet.</p>
          <button type="button" class="btn btn-success btn-sm" id="scInternetCheckBtn">Check Internet</button>
          <div id="scInternetResult" class="debug-mode-mini-result" aria-live="polite">Not checked yet.</div>
        </div>

        <div class="debug-mode-tile">
          <h3><i class="fa fa-dashboard"></i> Speed Test</h3>
          <p><strong>CRM Connection Test:</strong> measures download speed between this browser and your CRM server.</p>
          <div class="sc-speed-gauge"><div id="scSpeedNeedle"></div><span id="scSpeedValue">0 Mbps</span></div>
          <button type="button" class="btn btn-primary btn-sm" id="scSpeedBtn">Test CRM Connection</button>
          <hr>
          <p><strong>Full ISP Speed Test:</strong> tests your current internet connection using Cloudflare's independent speed-test service. It measures the connection from this device, not the CRM server.</p>
          <button type="button" class="btn btn-success btn-sm" id="scFullIspSpeedBtn"><i class="fa fa-external-link"></i> Run Full ISP Test</button>
          <div class="debug-mode-mini-result">Opens the external test in a new tab: download, upload, latency, and jitter.</div>
        </div>

        <div class="debug-mode-tile">
          <h3><i class="fa fa-refresh"></i> Flush DNS</h3>
          <p>Run these commands individually in Windows Command Prompt. Run Command Prompt as Administrator where required.</p>
          <div class="sc-command-list">
            <?php
            $dnsCommands = [
              ['ipconfig /flushdns', 'Clears the Windows DNS resolver cache.'],
              ['ipconfig /registerdns', 'Refreshes DHCP leases and re-registers DNS names.'],
              ['ipconfig /release', 'Releases the current DHCP IPv4 address. Internet access may disconnect temporarily.'],
              ['ipconfig /renew', 'Requests a new DHCP IPv4 address.'],
            ];
            foreach ($dnsCommands as $i => $command) { ?>
              <div class="sc-command-row">
                <div><code><?php echo html_escape($command[0]); ?></code><small><?php echo html_escape($command[1]); ?></small></div>
                <button type="button" class="btn btn-default btn-xs sc-copy-command" data-copy="<?php echo html_escape($command[0]); ?>"><i class="fa fa-copy"></i> Copy</button>
              </div>
            <?php } ?>
          </div>
        </div>

        <div class="debug-mode-tile">
          <h3><i class="fa fa-terminal"></i> Network Commands</h3>
          <p>Each command has its own Copy button. Paste it into Command Prompt or PowerShell as indicated.</p>
          <div class="sc-command-list">
            <?php
            $networkCommands = [
              ['ipconfig /all', 'Command Prompt — complete adapter, IP, gateway, and DNS details.'],
              ['route print', 'Command Prompt — routing table and network interfaces.'],
              ['tracert 8.8.8.8', 'Command Prompt — route to Google DNS.'],
              ['ping 8.8.8.8', 'Command Prompt — basic connectivity and latency test.'],
              ['nslookup crm.justsmartchoice.com', 'Command Prompt — DNS lookup for the CRM domain.'],
              ['pathping 8.8.8.8', 'Command Prompt — packet loss and route diagnostic.'],
              ['ipconfig /displaydns', 'Command Prompt — displays the local DNS resolver cache.'],
              ['Test-NetConnection 8.8.8.8 -Port 443', 'PowerShell — tests outbound HTTPS connectivity.'],
              ['Get-NetIPConfiguration', 'PowerShell — displays adapter, IP, gateway, and DNS configuration.'],
            ];
            foreach ($networkCommands as $command) { ?>
              <div class="sc-command-row">
                <div><code><?php echo html_escape($command[0]); ?></code><small><?php echo html_escape($command[1]); ?></small></div>
                <button type="button" class="btn btn-default btn-xs sc-copy-command" data-copy="<?php echo html_escape($command[0]); ?>"><i class="fa fa-copy"></i> Copy</button>
              </div>
            <?php } ?>
          </div>
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
        <a class="active" href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a>
        <a href="<?php echo admin_url('debug_mode/monitoring'); ?>">Monitoring</a>
      </div>
    </div>
  </div>
</div>
<style>
.sc-command-list{display:flex;flex-direction:column;gap:8px;margin-top:12px}
.sc-command-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 10px;background:#f7f9fa;border:1px solid #e5e9ec;border-radius:6px}
.sc-command-row>div{min-width:0;display:flex;flex-direction:column;gap:4px}
.sc-command-row code{white-space:normal;overflow-wrap:anywhere;color:#0e6f5b;font-size:12px}
.sc-command-row small{color:#66727b;font-size:11px;line-height:1.4}
.sc-command-row .btn{flex:0 0 auto}
@media(max-width:600px){.sc-command-row{align-items:flex-start}.sc-command-row .btn{margin-top:2px}}
</style>
<script>
(function(){
  var checkBtn=document.getElementById('scInternetCheckBtn');
  var result=document.getElementById('scInternetResult');
  if(checkBtn){
    checkBtn.addEventListener('click',function(){
      result.textContent='Checking...';
      fetch(admin_url+'debug_mode/internet_check_json',{credentials:'same-origin'})
        .then(function(r){if(!r.ok)throw new Error('Request failed');return r.json();})
        .then(function(j){result.textContent=(j.online?'Online':'Offline')+' — '+j.message+' ('+j.latency_ms+' ms)';})
        .catch(function(){result.textContent='Internet check failed.';});
    });
  }

  var speedBtn=document.getElementById('scSpeedBtn');
  var needle=document.getElementById('scSpeedNeedle');
  var value=document.getElementById('scSpeedValue');
  if(speedBtn){
    speedBtn.addEventListener('click',function(){
      value.textContent='Testing...';
      speedBtn.disabled=true;
      var start=performance.now();
      fetch(admin_url+'debug_mode/speed_payload?x='+Date.now(),{cache:'no-store',credentials:'same-origin'})
        .then(function(r){if(!r.ok)throw new Error('Request failed');return r.arrayBuffer();})
        .then(function(buf){
          var sec=(performance.now()-start)/1000;
          var mbps=(buf.byteLength*8)/(sec*1000000);
          value.textContent=mbps.toFixed(2)+' Mbps';
          if(needle){needle.style.transform='rotate('+Math.min(180,mbps*3)+'deg)';}
        })
        .catch(function(){value.textContent='Test failed';})
        .finally(function(){speedBtn.disabled=false;});
    });
  }

  var ispBtn=document.getElementById('scFullIspSpeedBtn');
  if(ispBtn){
    ispBtn.addEventListener('click',function(){
      window.open('https://speed.cloudflare.com/','_blank','noopener,noreferrer');
    });
  }

  function copyText(text,button){
    var done=function(){
      var old=button.innerHTML;
      button.innerHTML='<i class="fa fa-check"></i> Copied';
      if(typeof alert_float==='function'){alert_float('success','Command copied to clipboard.');}
      window.setTimeout(function(){button.innerHTML=old;},1600);
    };
    if(navigator.clipboard && window.isSecureContext){
      navigator.clipboard.writeText(text).then(done).catch(function(){fallbackCopy(text,done);});
    }else{
      fallbackCopy(text,done);
    }
  }
  function fallbackCopy(text,callback){
    var field=document.createElement('textarea');
    field.value=text;field.setAttribute('readonly','readonly');
    field.style.position='fixed';field.style.opacity='0';field.style.left='-9999px';
    document.body.appendChild(field);field.select();
    try{document.execCommand('copy');callback();}catch(e){if(typeof alert_float==='function'){alert_float('danger','Could not copy command. Select and copy it manually.');}}
    document.body.removeChild(field);
  }
  document.querySelectorAll('.sc-copy-command').forEach(function(button){
    button.addEventListener('click',function(){copyText(button.getAttribute('data-copy')||'',button);});
  });
})();
</script>
<?php init_tail(); ?>
