const {JSDOM}=require('jsdom');const fs=require('node:fs'),assert=require('node:assert/strict');
const code=fs.readFileSync('modules/sales_center/assets/js/sales-actions.js','utf8');
async function check(route,html){
 const dom=new JSDOM(html,{url:'https://crm.justsmartchoice.com/admin/'+route,runScripts:'outside-only'}),w=dom.window,d=w.document;
 const toolbar=d.querySelector('.sales-center-generated-toolbar');let clicks=0;toolbar.addEventListener('click',()=>clicks++);
 const href=d.querySelector('#create')?.href;
 w.eval(code);d.dispatchEvent(new w.Event('DOMContentLoaded'));await new Promise(r=>setTimeout(r,0));
 assert.equal(d.querySelector('._buttons,.sc-sales-action-bar').contains(toolbar),true);
 toolbar.querySelector('button').click();assert.equal(clicks,1,'Existing handlers survive toolbar organization');
 if(href)assert.equal(d.querySelector('#create').href,href);
 assert.equal(d.querySelectorAll('#create').length,href?1:0,'No duplicate or unauthorized Create button');
 assert.equal(d.querySelectorAll('.sales-center-generated-toolbar').length,1);
 w.close();
}
(async()=>{
 const tb='<div class="sales-center-toolbar-holder"><div class="sales-center-generated-toolbar"><button type="button">Refresh</button></div></div>';
 await check('proposals','<div id="wrapper"><div class="_buttons"><a id="create" href="/admin/proposals/proposal">Create</a></div><div class="panel-body">'+tb+'</div></div>');
 await check('estimates','<div id="wrapper"><div><a id="create" class="new-estimate-btn" href="/admin/estimates/estimate">Create</a><a class="switch-pipeline">Pipeline</a><div class="clearfix"></div><div class="panel-body">'+tb+'</div></div></div>');
 await check('payments','<div id="wrapper"><div class="panel-body">'+tb+'</div></div>');
 console.log('PASS shared Sales action organization, original handlers/URLs and permission-filtered controls');
})().catch(error=>{console.error(error);process.exitCode=1;});
