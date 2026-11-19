const {chromium}=require('playwright');const {execFileSync}=require('child_process');const path=require('path'),assert=require('assert');const root=path.resolve(__dirname,'..');
(async()=>{const b=await chromium.launch({headless:true,executablePath:process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE||undefined});try{
for(const width of [1280,390]){const page=await b.newPage({viewport:{width,height:850}});const actions=execFileSync('php',[root+'/tests/fixtures/contract_menu.php'],{encoding:'utf8'});
await page.setContent(`<body class="contract"><style>body{font:14px Arial;margin:20px}.dropdown{position:relative;float:right;z-index:10}.ajax-clear-values{position:absolute;z-index:99;right:65px;top:120px}.ajax-clear-values::before{content:"×"}.dropdown-menu{display:block;position:absolute;right:0;list-style:none;background:white}.dropdown-header{font-size:12px}.divider{border-top:1px solid #ddd}</style><span class="ajax-clear-values" data-id="clientid"></span><span class="ajax-clear-values" data-id="project_id" style="top:180px"></span><div class="dropdown open"><button>More</button><ul class="dropdown-menu"><li><a href="/preview">View contract as customer</a></li>${actions}<li><a href="/copy">Copy Contract</a></li><li><a class="_delete" href="/delete">Delete Contract</a></li></ul></div><a aria-controls="attachments" href="#attachments">Attachments</a><div id="attachments">Native attachments</div></body>`);
await page.addStyleTag({path:root+'/modules/sc_admin_fixes/assets/css/contract-workflows.css'});await page.addScriptTag({path:root+'/modules/sc_admin_fixes/assets/js/contract-workflows.js'});
const menu=page.locator('.scaf-contract-menu');assert(await menu.isVisible());for(const clear of await page.locator('.ajax-clear-values').all()){
 const point=await clear.boundingBox();
 assert(await page.evaluate(({x,y})=>document.elementFromPoint(x+2,y+2).closest('.scaf-contract-menu')!==null,point),'Open menu covers underlying clear control');
}
assert.equal(await page.locator('.ajax-clear-values').count(),2,'Customer and project clear controls preserved');
await page.locator('.scaf-contract-menu-owner').evaluate(e=>e.classList.remove('open'));
const clearPoint=await page.locator('.ajax-clear-values').first().boundingBox();
assert(await page.evaluate(({x,y})=>document.elementFromPoint(x+2,y+2).classList.contains('ajax-clear-values'),clearPoint),'Reproduces original stacking without open-menu fix');
await page.locator('.scaf-contract-menu-owner').evaluate(e=>e.classList.add('open'));
const box=await menu.boundingBox();assert(box.x>=0&&box.x+box.width<=width,'Menu fits viewport');
for(const name of ['Mark as Draft','Mark as Sent','Mark as Expired','Mark as Declined'])assert.equal(await page.getByRole('button',{name,exact:true}).count(),1);
const a=await page.getByRole('link',{name:'Convert to Project',exact:true}).getAttribute('href');assert(a.endsWith('projects/project?via_contract_id=62'));
assert(await page.getByRole('button',{name:'Mark as Draft',exact:true}).isDisabled());await page.getByRole('button',{name:'Mark as Sent',exact:true}).focus();assert(await page.getByRole('button',{name:'Mark as Sent',exact:true}).evaluate(e=>getComputedStyle(e).outlineStyle!=='none'));
await page.screenshot({path:'/tmp/contract-more-'+width+'.png',fullPage:true});console.log('PASS contract More menu: '+width+'px, consistent actions, keyboard focus, fit, native project review link');await page.close();}
}finally{await b.close();}})().catch(e=>{console.error(e);process.exit(1);});
