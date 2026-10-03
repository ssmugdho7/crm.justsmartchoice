// Run with NODE_PATH pointing to an installed jsdom runtime.
const fs = require('node:fs');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
for (const asset of ['assets/js/main.js', 'assets/js/main.min.js']) {
  const dom = new JSDOM('<form><select name="role"><option value=""></option><option value="1">One</option><option value="2">Two</option></select><input name="administrator" type="checkbox"><table class="roles"><tr data-name="prchat"><td><input type="checkbox" class="capability" id="prchat_view"><input type="checkbox" class="capability" id="prchat_edit"></td></tr></table></form>', {runScripts:'outside-only'});
  const w=dom.window;w.eval(fs.readFileSync('assets/plugins/jquery/jquery.js','utf8'));
  const $=w.jQuery, requests=[], alerts=[];
  w.alert_float=(type,message)=>alerts.push(message);
  w.requestGetJSON=path=>{const d=$.Deferred();const req=d.promise();req.abort=()=>{};requests.push({path,d});return req;};
  const source=fs.readFileSync(asset,'utf8');w.eval(source.slice(source.indexOf('// Called when editing member profile'), source.indexOf('// Show/hide full table')));
  function submitAllowed() { const e=new w.Event('submit',{bubbles:true,cancelable:true});return w.document.querySelector('form').dispatchEvent(e); }
  let formHandlerCalls=0;$('form').on('submit',e=>{formHandlerCalls++;});
  $('select').val('1');w.init_roles_permissions('1',true);assert.equal(submitAllowed(),false);assert.equal(formHandlerCalls,0,'Capture gate runs before form validation/AJAX handlers');
  $('select').val('2');w.init_roles_permissions('2',true);requests[0].d.resolve({prchat:['view']});assert.equal($('#prchat_view').prop('checked'),false,'Old response ignored');
  requests[1].d.resolve({prchat:['edit']});assert.equal($('#prchat_edit').prop('checked'),true);assert.equal($('#prchat_view').prop('checked'),false);assert.equal(submitAllowed(),true);
  $('select').val('1');w.init_roles_permissions('1',true);requests[2].d.reject({},'error');assert.equal(submitAllowed(),false,'Failed load cannot save stale permissions');assert(alerts.length);
  $('select').val('2');w.init_roles_permissions('2',true);requests[3].d.resolve({});assert.equal($('#prchat_edit').prop('checked'),false,'Empty role clears rights');assert.equal(submitAllowed(),true);
  $('select').val('1');w.init_roles_permissions('1',true);$('select').val('');w.init_roles_permissions('',true);requests[4].d.resolve({prchat:['view']});assert.equal($('#prchat_view').prop('checked'),false,'Clearing role invalidates pending response');assert.equal(submitAllowed(),true);
  $('select').val('1');w.init_roles_permissions('1',true);$('input[name="administrator"]').prop('checked',true);requests[5].d.resolve({prchat:['view']});assert.equal($('#prchat_view').prop('checked'),false,'Admin selection invalidates pending response');
  console.log('PASS '+asset+': role response race, submit during load, failed load, empty role, cleared role, administrator switch');dom.window.close();
}
