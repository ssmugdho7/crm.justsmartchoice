const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const events = {};
const validator = {settings:{normalizer:value=>value.trim()}};
const form = {validator,querySelectorAll:()=>[amount]};
const amount = {name:'amount',id:'amount',className:'form-control',type:'number',value:'4870.00',dataset:{},form,rules:{max:4870,number:true},closest:()=>null};
const secret = {name:'paymentmethod_access_token',id:'',className:'',type:'password',value:'do-not-format',dataset:{},form,closest:()=>null};
function $(target) {
  if(typeof target==='function'){target();return;}
  return {data:()=>target.validator,rules(action,value){if(action==='add')Object.assign(target.rules,value);return target.rules;},ajaxComplete(){}};
}
$.validator = {};
const document = {querySelectorAll:()=>[amount,secret],addEventListener:(name,handler)=>{events[name]=handler;}};
vm.runInNewContext(fs.readFileSync(__dirname+'/../assets/js/sc_money_format.js','utf8'),{window:{jQuery:$},document});
assert.equal(amount.value,'4,870.00');
const normalize=amount.rules.normalizer;
assert.equal(normalize.call(amount,' 2,435.50 '),'2435.50','Preserve existing normalizer and remove grouping before validation');
for(const [value,valid] of [['243',true],['2,435',true],['2,435.50',true],['4,870.00',true],['4,870.01',false],['5,000',false]]) {
  assert.equal(Number(normalize.call(amount,value))<=amount.rules.max,valid,value);
}
events.focusin({target:amount});
assert.equal(amount.rules.normalizer,normalize,'Repeated focus does not stack normalizers');
assert.equal(amount.rules.max,4870,'Maximum rule remains present');
assert.equal(amount.rules.number,true,'Number rule remains present');
assert.equal(secret.type,'password','Protected credential fields remain untouched');
amount.value='2,435.50';events.submit({target:form});
assert.equal(amount.value,'2435.50','Submission retains the original canonical decimal behavior');
const late = {...amount,dataset:{},form:{validator:null},rules:{max:4870}};
events.focusin({target:late});assert.equal(late.rules.normalizer,undefined);
late.form.validator=validator;events.focusin({target:late});
assert.equal(late.rules.normalizer('2,435'),'2435','Forms initialized after the money scan are supported');
console.log('Money grouping validation, maximum boundary, normalizer chaining, late initialization, protected fields, and submission checks passed.');
