const {chromium}=require('playwright'),fs=require('fs'),assert=require('assert');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE});
 try{
  const page=await browser.newPage();
  const fixture=fs.readFileSync('tests/project_shared_selection.js','utf8').split("new JSDOM('")[1].split("',{runScripts")[0];
  await page.setContent(fixture);
  await page.addScriptTag({path:'assets/plugins/jquery/jquery.min.js'});
  await page.addScriptTag({content:'window.admin_url="/admin/";window.SCAF={};'});
  await page.addScriptTag({path:'assets/js/smart_choice_bulk.js'});
  await page.addScriptTag({path:'modules/sc_admin_fixes/assets/js/projects.js'});
  await page.locator('.sc-bulk-select-all').check();
  assert.equal(await page.locator('tbody input:checked').count(),2);
  assert.equal(await page.locator('[data-scaf-selected]').innerText(),'2');
  assert.equal(await page.locator('tbody tr').count(),await page.locator('tbody input[type=checkbox]').count());
  await page.locator('[data-scaf-batch-clear]').click();assert.equal(await page.locator('tbody input:checked').count(),0);assert.equal(await page.locator('.sc-bulk-select-all').isChecked(),false);
  await page.locator('tbody input').first().check();assert.equal(await page.locator('.sc-bulk-select-all').evaluate(e=>e.indeterminate),true);
  console.log('PASS browser: one selector per row, shared select-all/count, clear and partial selection');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
