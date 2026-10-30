/* DOM behavior tests with synthetic records; no live CRM writes. */
const fs=require('node:fs'),assert=require('node:assert/strict');
const {JSDOM}=require('jsdom'),jquery=require('jquery');
const code=fs.readFileSync('modules/sc_projects_workspace/assets/js/projects.js','utf8');
const dom=new JSDOM('<body class="scpr-projects"><a class="prj-kpi-card is-active" aria-pressed="true"></a><a class="prj-kpi-card" aria-pressed="false"></a><section class="scpr-punchlist" data-error="Denied"><input type="checkbox" class="scpr-task-toggle" data-task-id="12"></section></body>',{runScripts:'outside-only',url:'https://fixture.invalid/admin/projects/view/1'});
const w=dom.window,$=jquery(w);w.jQuery=$;let route,request,errors=[];
w.requestGetJSON=r=>{route=r;return request=$.Deferred();};w.alert_float=(type,message)=>errors.push([type,message]);w.eval(code);
$('.prj-kpi-card').eq(1).trigger('click');assert.equal($('.prj-kpi-card').eq(0).attr('aria-pressed'),'false');assert.equal($('.prj-kpi-card').eq(1).attr('aria-pressed'),'true');
let box=$('.scpr-task-toggle');box.prop('checked',true).trigger('change');assert.equal(route,'tasks/mark_as/5/12?single_task=false');assert(box.prop('disabled'));request.resolve({success:false});assert(!box.prop('checked'));assert(!box.prop('disabled'));assert.deepEqual(errors,[['danger','Denied']]);
box.prop('checked',false).trigger('change');assert.equal(route,'tasks/unmark_complete/12?single_task=false');request.reject();assert(box.prop('checked'));assert(!box.prop('disabled'));
route=null;box.attr('data-task-id','bad').trigger('change');assert.equal(route,null);
// Native form submission, CSRF helpers, date/rate controls, modals and workspace actions survive.
for(const file of ['project','view','gantt']){
 const a=fs.readFileSync('application/views/admin/projects/'+file+'.php','utf8'),b=fs.readFileSync('modules/sc_projects_workspace/views/projects/'+file+'.php','utf8');
 for(const m of a.matchAll(/(?:name|id)="([^"<]+)"/g))assert(b.includes(m[0]),file+' retains '+m[0]);
 for(const m of a.matchAll(/(?:render_input|render_select|render_date_input|render_textarea|form_hidden|form_open_multipart|form_open)\(\s*['"]([^'"]+)/g))assert(b.includes(m[0]),file+' retains '+m[0]);
}
for(const file of ['project_overview','project_tasks']) assert(fs.readFileSync('modules/sc_projects_workspace/views/projects/'+file+'.php','utf8').includes("include APPPATH . 'views/admin/projects/"+file+".php'"),'Keep native '+file);
// Exercise the real directory Gantt zoom handler (the inherited selector was malformed).
const ganttDom=new JSDOM('<body><div id="gantt"></div><select name="gantt_view"><option>Month</option><option>Week</option></select></body>',{runScripts:'outside-only'});
const gw=ganttDom.window;gw.$=jquery(gw);let mode;
gw.Gantt=function(){this.change_view_mode=v=>{mode=v};};
let ganttCode=fs.readFileSync('modules/sc_projects_workspace/views/projects/gantt.php','utf8').match(/<script>([\s\S]*?)<\/script>/)[1];
ganttCode=ganttCode.replace(/<\?= json_encode\(\$gantt_data\); \?>/g,'[{"project_id":1}]').replace(/<\?= admin_url\('projects\/view'\); \?>/g,'https://fixture.invalid/admin/projects/view');
gw.eval(ganttCode);gw.$('select[name="gantt_view"]').val('Week').trigger('change');assert.equal(mode,'Week');ganttDom.window.close();
const other=new JSDOM('<body class="clients"></body>',{runScripts:'outside-only'});other.window.jQuery=jquery(other.window);other.window.eval(code);other.window.close();dom.window.close();
console.log('PASS: status selection, task completion/reopen routes, denial/network recovery, input validation, native form/CSRF/control preservation and client isolation');
