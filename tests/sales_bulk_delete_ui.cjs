const {chromium}=require('playwright');
const fs=require('fs'),path=require('path'),assert=require('assert');
const root=path.resolve(__dirname,'..');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE||undefined});
 try{
  for(const feature of ['invoices','estimates','proposals','credit_notes','contracts','payments']){
   const page=await browser.newPage();
   await page.route('https://crm.example.test/**',r=>r.fulfill({status:200,body:''}));
   await page.goto('https://crm.example.test/admin/'+feature);
   await page.setContent(`<html><body class="scaf-enabled"><div class="panel-body"><div class="dataTables_wrapper"><button class="sales-center-mass-delete">Mass Delete</button><table><thead><tr><th>Document</th></tr></thead><tbody><tr><td><input type="checkbox" class="sc-bulk-row" data-feature="${feature}" value="11">Document 11</td></tr><tr><td><input type="checkbox" class="sc-bulk-row" data-feature="${feature}" value="12">Document 12</td></tr></tbody></table></div></div></body></html>`);
   await page.addStyleTag({path:root+'/assets/plugins/bootstrap/css/bootstrap.min.css'});
   await page.addScriptTag({path:root+'/assets/js/jquery.js'});
   await page.addScriptTag({path:root+'/assets/plugins/bootstrap/js/bootstrap.min.js'});
   await page.evaluate(()=>{window.scafConfig={labels:{}};window.admin_url='/admin/';window.calls=[];window.alert=()=>{throw Error('Unexpected legacy selection alert');};window.jQuery.ajax=async(o)=>{window.calls.push(o);return {success:true,deleted:2,failed:[]};};});
   await page.addScriptTag({path:root+'/modules/sc_admin_fixes/assets/js/common.js'});
   await page.addScriptTag({path:root+'/assets/js/smart_choice_bulk.js'});
   await page.locator('.sc-bulk-select-all').check();
   assert.equal(await page.locator('.sc-bulk-row:checked').count(),2);
   await page.getByRole('button',{name:'Mass Delete',exact:true}).click();
   await page.locator('.scaf-confirm-accept').click();
   const input=page.locator('.scaf-confirm-input input');
   assert(await input.isVisible(),'Typed-delete field is visible');
   assert(await page.locator('.scaf-confirm-accept').isDisabled());
   await input.fill('wrong');assert(await page.locator('.scaf-confirm-accept').isDisabled());
   assert.equal(await page.evaluate(()=>calls.length),0,'No deletion before typed proof');
   await input.fill('DELETE');await page.locator('.scaf-confirm-accept').click();
   await page.waitForFunction(()=>window.calls.length===1);
   const call=await page.evaluate(()=>calls[0]);
   assert.equal(call.url,'/admin/smart_choice_bulk/delete/'+feature);assert.deepEqual(call.data.ids,['11','12']);assert.equal(call.data.delete_confirmation,'DELETE');
   assert.equal(call.type,'POST');
   console.log('PASS '+feature+': select-all, Mass Delete, visible typed proof, selected IDs and native endpoint');
   // Standalone tables used outside DataTables must resolve their own toolbar correctly.
   await page.evaluate(()=>{document.querySelector('.dataTables_wrapper').className='standalone';document.querySelector('.sc-bulk-row').checked=false;});
   await page.getByRole('button',{name:'Delete selected',exact:true}).click();
   await page.locator('.scaf-confirm-cancel').click();
   assert.equal(await page.evaluate(()=>calls.length),1,'Cancellation never sends a delete request');
   await page.close();
  }
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
