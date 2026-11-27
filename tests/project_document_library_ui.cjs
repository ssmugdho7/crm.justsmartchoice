'use strict';
const {chromium}=require('playwright');
const fs=require('fs'),path=require('path'),assert=require('assert'),{execFileSync}=require('child_process');
const root=path.resolve(__dirname,'..');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE||'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 try{
  for(const width of [320,412,834,1280]){
   const page=await browser.newPage({viewport:{width,height:924}});const errors=[];page.on('pageerror',e=>errors.push(e.message));
   await page.route('http://fixture.test/**',async route=>{
    const url=new URL(route.request().url());
    if(url.pathname==='/')return route.fulfill({contentType:'text/html',body:execFileSync('php',[path.join(__dirname,'project_document_library_fixture.php')],{encoding:'utf8'})});
    const file=path.join(root,url.pathname);if(!file.startsWith(root+path.sep)||!fs.existsSync(file))return route.fulfill({status:404,body:''});
    return route.fulfill({contentType:file.endsWith('.css')?'text/css':'application/javascript',body:fs.readFileSync(file)});
   });
   await page.goto('http://fixture.test/');
   const search=page.getByRole('searchbox',{name:'Search project documents'});await search.waitFor();
   assert.equal(await page.locator('.project-document-row:visible').count(),5,'Existing DataTables pagination retained');
   await search.fill('approved roof');await page.waitForFunction(()=>document.querySelectorAll('tr.project-document-row').length===1);
   assert.equal(await page.locator('tr.project-document-row:visible').count(),1,'Search reaches documents beyond current page');
   assert.match(await page.locator('#project-document-count').textContent(),/1 of 18 documents/);
   await page.getByRole('link',{name:'Approved Roof Permit — Final Inspection.pdf',exact:true}).click();
   assert.deepEqual(await page.evaluate(()=>window.previews),[[18,42]],'Filename opens existing authenticated preview flow');
   assert(await page.getByLabel('Upload project documents').count()===1,'Native upload preserved');
   assert(await page.getByRole('button',{name:/Choose From Google Drive/i}).count()===1,'Cloud chooser preserved');
   assert.equal(await page.locator('tr.project-document-row input[data-switch-url]').count(),1,'Customer visibility switch preserved');
   if(width<992){
    assert.equal(await page.locator('tr.project-document-row').evaluate(e=>getComputedStyle(e).display),'flex');
    assert.equal(await page.locator('tr.project-document-row td[data-document-label]').count(),9,'Every card field retains a label');
    const box=await page.locator('tr.project-document-row').boundingBox();assert(box.x>=0&&box.x+box.width<=width+1,'Document card fits mobile/tablet');
    const action=await page.getByLabel('Delete document').boundingBox();assert(action.height>=44&&action.width>=44,'Actions have touch targets');
   }else assert.equal(await page.locator('tr.project-document-row').evaluate(e=>getComputedStyle(e).display),'table-row');
   await search.fill('nonexistent-document');assert.equal(await page.locator('tr.project-document-row:visible').count(),0);assert.match(await page.locator('#project-document-count').textContent(),/no matching documents/);
   await search.fill('');assert.equal(await page.locator('tr.project-document-row:visible').count(),5,'Clearing search restores pagination');
   await search.fill('<script>');assert.equal(await page.locator('tr.project-document-row:visible').count(),1);assert.equal(await page.locator('tr.project-document-row script').count(),0,'Document names remain escaped');
   assert.deepEqual(errors,[]);console.log('PASS project documents '+width+'px: search across pages, preview, native controls, card layout, escaping');await page.close();
  }
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
