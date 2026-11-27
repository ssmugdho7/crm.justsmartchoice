const {chromium}=require('playwright'),fs=require('fs'),assert=require('assert');
(async()=>{const browser=await chromium.launch({headless:true,executablePath:process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE});try{
 const source=fs.readFileSync('modules/notes/views/manage_notes.php','utf8');
 const group=source.slice(source.indexOf('<div class="btn-group notes-export-dropdown">'),source.indexOf("<?php echo form_open(admin_url('notes/notes/export_csv')"));
 const html=group.replace(/<\?php echo _l\('notes_export_selected'\); \?>/g,'Export Selected');
 const app=fs.readFileSync('assets/js/app.js','utf8');const portal=app.slice(app.indexOf('  $(document).on("shown.bs.dropdown", ".table-responsive"'),app.indexOf('  // Add are you sure'));
 const bound=source.match(/\$notesActions.on\('shown.bs.dropdown hide.bs.dropdown'[\s\S]*?\n        \}\);/)[0];
 const css=source.match(/#wrapper \.notes-table-actions \.notes-export-dropdown \{[^}]+\}/)[0]+source.match(/#wrapper \.notes-export-dropdown > \.dropdown-menu \{[^}]+\}/)[0];
 for(const width of [1440,390]){
  const p=await browser.newPage({viewport:{width,height:800}});
  await p.setContent(`<div id="wrapper" style="padding:24px"><div class="table-responsive"><div class="dt-buttons btn-group" style="display:flex;flex-wrap:wrap;gap:6px"><button class="btn">Export</button><div class="notes-table-actions" style="display:flex;flex-wrap:wrap;gap:6px"><button class="btn">Filter</button>${html}<button class="btn">Import</button><button class="btn">Sample Header</button><button class="btn">Mass Delete</button></div></div></div></div>`);
  await p.addStyleTag({path:'assets/plugins/bootstrap/css/bootstrap.min.css'});await p.addStyleTag({content:css});await p.addScriptTag({path:'assets/plugins/jquery/jquery.min.js'});await p.addScriptTag({path:'assets/plugins/bootstrap/js/bootstrap.min.js'});await p.addScriptTag({content:portal});await p.addScriptTag({content:"var $notesActions=$('.notes-table-actions');"+bound+"window.notes_export_selected=function(format){window.chosen=format;};"});
  for(const format of ['CSV','Excel','PDF']){
   await p.getByRole('button',{name:'Export Selected'}).click();
   const menu=p.locator('.notes-export-dropdown>.dropdown-menu');assert(await menu.isVisible());
   const buttonBox=await p.getByRole('button',{name:'Export Selected'}).boundingBox(),menuBox=await menu.boundingBox();assert(Math.abs(buttonBox.x-menuBox.x)<=2,'Dropdown aligned to trigger');assert(menuBox.y>=buttonBox.y+buttonBox.height-1&&menuBox.y-buttonBox.y-buttonBox.height<6,'Dropdown directly below trigger');
   await p.getByRole('link',{name:format,exact:true}).click();assert.equal(await p.evaluate(()=>window.chosen),format.toLowerCase());assert(!(await menu.isVisible()));
  }
  console.log('PASS '+width+'px: menu stays attached to Export Selected, directly below button; CSV/Excel/PDF actions close normally');await p.close();
 }
}finally{await browser.close();}})().catch(e=>{console.error(e);process.exitCode=1});
