'use strict';
const fs=require('fs'),assert=require('node:assert/strict'),{JSDOM}=require('jsdom');
const wait=ms=>new Promise(r=>setTimeout(r,ms));
(async()=>{
 const dom=new JSDOM('<html lang="en"><body class="scaf-enabled scaf-invoices"><div id="wrapper"><div id="invoice"><form id="record_payment_form" method="post" action="/admin/invoices/record_payment"><input name="csrf_token" value="real-native-token"><input name="invoiceid" value="71"><input name="amount" type="number" required max="100.10" value="50.10"><input name="paymentmode" required value="4"><button type="submit" name="save" value="record">Submit</button></form></div></div></body></html>',{url:'https://crm.example/admin/invoices/list_invoices/71',runScripts:'outside-only',pretendToBeVisual:true});
 const w=dom.window,d=w.document,$=require('jquery')(w);w.$=w.jQuery=$;w.scafConfig={page:'invoices',domain:'sales',decimals:2,adminUrl:'/admin/',labels:{}};
 w.matchMedia=()=>({matches:false,addEventListener(){},removeEventListener(){},addListener(){},removeListener(){}});w.ResizeObserver=class{observe(){}disconnect(){}};
 $.fn.modal=function(action){this.attr('data-modal-state',action);if(action==='hide')this.trigger('hidden.bs.modal');return this;};
 for(const file of ['vendor-sonner','common','sales'])w.eval(fs.readFileSync('modules/sc_admin_fixes/assets/js/'+file+'.js','utf8'));
 await wait(80);const form=d.querySelector('#record_payment_form'),submit=form.querySelector('button');
 assert(form.closest('#scaf-payment-dialog'),'Native form moves into modal without replacing fields');assert.equal(d.querySelectorAll('#record_payment_form').length,1);assert.equal(form.action,'https://crm.example/admin/invoices/record_payment');
 assert.equal(new w.FormData(form).get('csrf_token'),'real-native-token');assert.equal(new w.FormData(form).get('invoiceid'),'71');assert.equal(new w.FormData(form).get('amount'),'50.10');
 const amount=form.querySelector('[name=amount]');amount.value='150.10';form.dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true}));assert.equal(form.getAttribute('aria-busy'),null,'Invalid amount above invoice max not locked');
 amount.value='50.10';const first=new w.Event('submit',{bubbles:true,cancelable:true});form.dispatchEvent(first);assert.equal(form.getAttribute('aria-busy'),'true');assert(submit.classList.contains('is-loading'));assert(!submit.disabled,'Native submitter remains successful control');assert.equal(submit.name,'save');assert.equal(submit.value,'record');
 const repeat=new w.Event('submit',{bubbles:true,cancelable:true});form.dispatchEvent(repeat);assert(repeat.defaultPrevented,'Duplicate payment submission blocked');
 assert.equal(d.querySelectorAll('[data-sonner-toast]').length,0,'No success announced merely submitting payment');
 w.SCAF.busy(form)();
 // Real native flash callback after a successful redirect is converted to actual Sonner.
 w.alert_float('success','Payment recorded by server');await wait(120);assert(d.querySelector('[data-sonner-toast]'));assert(d.body.textContent.includes('Payment recorded by server'));
 assert.equal(form.querySelector('[name=csrf_token]').value,'real-native-token');w.close();
 console.log('PASS actual common+Sonner+Sales integration: nativepaymentmodal/CSRF/amount/mode/submitter retained,invalid+duplicate guard,no premature success,serverflash Sonner');
})().catch(e=>{console.error(e);process.exitCode=1});
