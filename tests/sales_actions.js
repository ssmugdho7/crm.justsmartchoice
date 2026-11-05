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
 const dom=new JSDOM('<div id="wrapper"><a href="/proposal/17/secret">View</a><span class="sales-transaction-id">pi_&lt;unsafe&gt;</span><span class="sales-transaction-id"></span></div>',{url:'https://crm.justsmartchoice.com/admin/payments',runScripts:'outside-only'});
 const w=dom.window,d=w.document,copied=[];
 Object.defineProperty(w.navigator,'clipboard',{value:{writeText:async value=>copied.push(value)}});
 w.eval(code);d.dispatchEvent(new w.Event('DOMContentLoaded'));await new Promise(r=>setTimeout(r,0));
 assert.equal(d.querySelectorAll('.sc-copy-transaction').length,2,'Empty transaction gets no copy action');
 d.querySelector('.sc-copy-proposal').click();await new Promise(r=>setTimeout(r,0));
 assert.equal(copied[0],'https://crm.justsmartchoice.com/proposal/17/secret');
 d.querySelector('.sales-transaction-id + button').click();await new Promise(r=>setTimeout(r,0));
 assert.equal(copied[1],'pi_<unsafe>','Copies actual text, never HTML');
 assert.equal(d.querySelectorAll('.sc-sales-toast').length,2);
 assert.equal(d.querySelectorAll('.sc-copy-transaction').length,2,'Mutation callbacks do not duplicate buttons');
 w.navigator.clipboard.writeText=async()=>{throw new Error('denied')};
 d.querySelector('.sales-transaction-id + button').click();await new Promise(r=>setTimeout(r,0));
 assert.equal(d.querySelector('.sc-sales-toast:last-child').dataset.type,'error','Denied copy never announces success');
 w.close();
 console.log('PASS confirmed clipboard success/failure, escaped text, proposal URL and dynamic idempotence');
 console.log('PASS shared Sales action organization, original handlers/URLs and permission-filtered controls');
})().catch(error=>{console.error(error);process.exitCode=1;});
