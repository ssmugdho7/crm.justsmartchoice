<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper" class="debug-mode-page"><div class="content"><div class="panel_s"><div class="panel-body">
<h1>Automatic diagnostic checks</h1>
<p><a href="<?php echo admin_url('debug_mode'); ?>">Utilities</a> · <a href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Error report</a></p>
<p>Run a PHP syntax scan across every installed module, including inactive modules, without executing its code. Vendor packages, assets, uploads and symlinks are excluded. Each module is bounded to 1,000 PHP files, 2 MiB per file, 50 issues and eight seconds; incomplete results are marked. Syntax checks do not prove business workflows work.</p>
<p>Page checks request reviewed, read-only administrator routes, even if you have not visited them. They report HTTP failures, login redirects, visible PHP errors and response times. They do not execute page scripts, submit forms or test every action. Other module routes require a safety review before adding them to the route list.</p>
<label><input id="checkModules" type="checkbox" checked> Scan all <?php echo count($modules); ?> installed modules</label><br>
<label><input id="checkPages" type="checkbox" checked> Inspect <?php echo count($pages); ?> reviewed page routes</label>
<p class="mtop15"><button id="runChecks" class="btn btn-info" type="button">Run automatic checks</button> <button id="stopChecks" class="btn btn-default" type="button" disabled>Stop after current check</button> <button id="downloadChecks" class="btn btn-default" type="button" disabled>Download results</button></p>
<p id="checkStatus" role="status">Ready. Results remain in this browser until you leave or download them.</p>
<div style="max-height:500px;overflow:auto"><table class="table table-striped"><thead><tr><th>Check</th><th>Result</th></tr></thead><tbody id="checkResults"></tbody></table></div>
<hr><h2>Client-portal browser errors</h2>
<p>Capture JavaScript exceptions, rejected promises, failed resources and failed HTTP requests from signed-in contacts while Debug Mode is active. Messages are reduced to error categories; form values, response bodies and query strings are not collected. Events go to the private CRM error report, with session token checks and rate limits. Anonymous visitors are excluded. This observes visited client pages; it does not sign in as customers or exercise their workflows.</p>
<?php echo form_open(admin_url('debug_mode/client_capture')); ?>
<input type="hidden" name="audit_token" value="<?php echo html_escape($audit_token); ?>">
<label><input type="checkbox" name="enabled" value="1" <?php echo get_option('debug_mode_client_capture') === '1' ? 'checked' : ''; ?>> Enable authenticated client-portal capture</label>
<button class="btn btn-primary" type="submit">Save capture setting</button><?php echo form_close(); ?>
</div></div></div></div>
<?php init_tail(); ?>
<script>
(function(){
'use strict';
var modules = <?php echo json_encode($modules, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var pages = <?php echo json_encode(array_map('admin_url', $pages), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var auditToken = <?php echo json_encode($audit_token); ?>;
var endpoint = <?php echo json_encode(admin_url('debug_mode/audit_module')); ?>;
var run = document.getElementById('runChecks'), stop = document.getElementById('stopChecks'), download = document.getElementById('downloadChecks'), status = document.getElementById('checkStatus'), rows = document.getElementById('checkResults');
var cancelled = false, results = [];
function add(name, result) {
 results.push({check:name,result:result});
 var row=document.createElement('tr'), label=document.createElement('td'), cell=document.createElement('td');
 label.textContent=name; cell.textContent=result; row.appendChild(label); row.appendChild(cell); rows.appendChild(row);
 status.textContent=results.length+' checks completed'+(cancelled?' (stopping)':'');
}
stop.onclick=function(){cancelled=true;stop.disabled=true;};
download.onclick=function(){var link=document.createElement('a'), url=URL.createObjectURL(new Blob([JSON.stringify({date:new Date().toISOString(),results:results},null,2)],{type:'application/json'}));link.href=url;link.download='crm-diagnostic-checks.json';link.click();setTimeout(function(){URL.revokeObjectURL(url);},1000);};
async function request(url, options) {
 var controller=new AbortController(), timer=setTimeout(function(){controller.abort();},20000);
 try {return await fetch(url,Object.assign({credentials:'same-origin',cache:'no-store',signal:controller.signal},options||{}));}
 finally {clearTimeout(timer);}
}
run.onclick=async function(){
 run.disabled=true;stop.disabled=false;download.disabled=true;cancelled=false;results=[];rows.textContent='';status.textContent='Running checks…';
 try {
  if(document.getElementById('checkModules').checked) for(var module of modules) {
   if(cancelled)break;
   try {
    var body=new URLSearchParams({module:module,audit_token:auditToken});
    if(window.csrfData)body.set(csrfData.token_name,csrfData.hash);
    var response=await request(endpoint,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()});
    if(!response.ok||response.redirected)throw new Error('Request refused: HTTP '+response.status);
    var report=await response.json();
    add('Module '+module,report.error?JSON.stringify(report):report.checked+' PHP files parsed; '+report.issues.length+' syntax issues; '+report.skipped+' skipped'+(report.limited?' — INCOMPLETE':'')+(report.issues.length?'\n'+JSON.stringify(report.issues):''));
   } catch(error){add('Module '+module,'FAILED: '+error.message);}
  }
  if(document.getElementById('checkPages').checked) for(var page of pages) {
   if(cancelled)break;
   var started=performance.now();
   try {
    var response=await request(page), html=await response.text();
    var phpError=/A PHP Error was encountered|An uncaught Exception was encountered|Fatal error:|Parse error:/.test(html);
    add(new URL(page).pathname,'HTTP '+response.status+'; '+Math.round(performance.now()-started)+' ms'+(response.redirected?' — REDIRECT to '+new URL(response.url).pathname:'')+(phpError?' — PHP ERROR in response':'')+'; scripts not executed');
   }catch(error){add(new URL(page).pathname,'FAILED: '+error.message);}
  }
  status.textContent=(cancelled?'Stopped. ':'Finished. ')+results.length+' checks completed. Review failures, exclusions and incomplete results above.';
 } finally {run.disabled=false;stop.disabled=true;download.disabled=results.length===0;}
};
})();
</script>
