'use strict';
const {JSDOM}=require('jsdom'), {execFileSync}=require('node:child_process'), fs=require('node:fs'), assert=require('node:assert/strict');
const script=fs.readFileSync('modules/sales_center/assets/js/sales-actions.js','utf8');
const routes={proposals:'/proposal/17/fixturehash',estimates:'/estimate/17/fixturehash',invoices:'/invoice/17/fixturehash',credit_notes:'/admin/credit_notes/list_credit_notes/17',payments:'/admin/payments/payment/17',contracts:'/contract/17/fixturehash'};
(async()=>{
 for(const [kind,url] of Object.entries(routes))for(const permission of ['admin','readonly']){
  const html=execFileSync('php',['tests/fixtures/sales_more_menu.php',kind,permission],{encoding:'utf8'});
  const dom=new JSDOM('<div id="wrapper">'+html+'</div>',{url:'https://crm.example/admin/'+kind,runScripts:'outside-only'}),w=dom.window,d=w.document,copied=[];
  Object.defineProperty(w.navigator,'clipboard',{value:{writeText:async s=>copied.push(s)}});
  w.eval(script);d.dispatchEvent(new w.Event('DOMContentLoaded'));
  const links=d.querySelectorAll('.sc-sales-copy-menu-link');assert.equal(links.length,1,kind+' has one Copy link');
  const a=links[0];assert.equal(a.tagName,'A');assert.equal(a.parentElement.tagName,'LI');assert(a.closest('.dropdown-menu'));assert(!a.classList.contains('btn'));assert.equal(a.dataset.copyUrl,'https://crm.example'+url);
  a.click();await new Promise(r=>setTimeout(r,0));assert.deepEqual(copied,['https://crm.example'+url]);assert.equal(d.querySelector('.sc-sales-toast').dataset.type,'success');
  w.navigator.clipboard.writeText=async()=>{throw Error('denied');};a.click();await new Promise(r=>setTimeout(r,0));assert.equal(d.querySelector('.sc-sales-toast:last-child').dataset.type,'error');
  assert.equal(d.querySelectorAll('.sc-copy-proposal').length,0,'No list-row Copy link');
  if(permission==='readonly')assert.equal(d.querySelectorAll('a[href*="/delete/"],a[href*="/copy/"]').length,0,'No unauthorized delete/duplicate');
  if(permission==='admin'&&kind==='invoices')assert(html.includes('Delete Invoice'));
  w.close();
 }
 console.log('PASS six native More menus: admin/read-only, plain anchor, exact URLs, clipboard success/failure, no row buttons, delete/duplicate permissions');
})().catch(e=>{console.error(e);process.exitCode=1;});
