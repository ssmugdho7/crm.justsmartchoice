const fs=require('fs'),vm=require('vm'),assert=require('assert');
for(const file of ['modules/sales_center/assets/js/sales_center.js','modules/sc_admin_fixes/assets/js/saleshub.js']){
 const all=fs.readFileSync(file,'utf8');let start=all.indexOf('    async function submitMassDelete');if(start<0)start=all.indexOf('    function submitMassDelete');
 const end=all.indexOf('    function ',start+15);const code=all.slice(start,end);
 const calls=[];const context={window:{scBulkActions:{deleteSelected(source){calls.push(source);}}},alert(){throw Error('Incorrect empty-selection alert');}};
 vm.createContext(context);vm.runInContext(code,context);
 for(const feature of ['invoices','estimates','proposals','credit_notes','payments']){const source={feature};context.submitMassDelete(feature,[],source);assert.equal(calls.at(-1),source,'Legacy handler defers selection to correct table');}
 assert.equal(calls.length,5);console.log('PASS '+file+': native Mass Delete delegates without false selection warning');
}
