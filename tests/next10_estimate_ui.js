const fs=require('fs'),vm=require('vm'),assert=require('assert'),{JSDOM}=require('jsdom');
const dom=new JSDOM('<body></body>',{url:'https://crm.example/admin/estimates',runScripts:'outside-only'});dom.window.jQuery=()=>({on(){}});vm.runInContext(fs.readFileSync(__dirname+'/../modules/sc_admin_fixes/assets/js/estimate_workflows.js','utf8'),dom.getInternalVMContext());
assert.equal(dom.window.SCAFEstimateDraw('100.05','50',2),'50.03');assert.equal(dom.window.SCAFEstimateDraw('999999999999999.99','50',2),'500000000000000.00');assert.equal(dom.window.SCAFEstimateDraw('10','33.33',0),'3');
console.log('PASS exact browser draw preview for odd cents, huge amounts and zero-decimal currencies');
