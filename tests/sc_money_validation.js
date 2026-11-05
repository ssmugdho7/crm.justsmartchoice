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
const invoice = {...amount,dataset:{},rules:{max:4870,number:true},closest:selector=>selector.includes('#invoice-form')?{}:null};
const payment = {...amount,dataset:{},rules:{max:4870,number:true},closest:selector=>selector.includes('#record_payment_form')?{}:null};
const batch = {...amount,dataset:{},rules:{max:4870,number:true},closest:selector=>selector.includes('#batch-payment-form')?{}:null};
const editPayment = {...amount,dataset:{},rules:{max:4870,number:true},closest:selector=>selector.includes('form.payment-form')?{}:null};
const document = {querySelectorAll:()=>[amount,secret,invoice,payment,batch,editPayment],addEventListener:(name,handler)=>{events[name]=handler;}};
vm.runInNewContext(fs.readFileSync(__dirname+'/../assets/js/sc_money_format.js','utf8'),{window:{jQuery:$},document});
assert.equal(amount.value,'4,870.00','Unrelated money fields keep their existing grouping');
for(const input of [invoice,payment,batch,editPayment]) {
  assert.equal(input.value,'4870.00','Invoice/payment initial values do not insert grouping commas');
  for(const [typed,expected] of [['2435.50','2435.50'],['2,435.50','2435.50'],['4870.01','4870.01'],['-1234.56','-1234.56'],['1234.','1234.']]) {
    input.value=typed;input.selectionStart=typed.length;
    events.input({target:input});
    assert.equal(input.value,expected,'Invoice/payment typing and paste use canonical decimal text');
  }
  input.value='2,435.50';
  events.submit({target:{querySelectorAll:()=>[input]}});
  assert.equal(input.value,'2435.50','Invoice/payment submission stays canonical even after a grouped paste');
  assert.equal(input.rules.max,4870,'Invoice/payment maximum remains intact');
  assert.equal(input.rules.number,true,'Invoice/payment number validation remains intact');
  assert.equal(Number(input.rules.normalizer.call(input,'4,870.01'))<=input.rules.max,false,'Out-of-range invoice payment still fails max');
}

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
console.log('Ungrouped invoice/payment entry, unrelated money grouping validation, maximum boundary, normalizer chaining, late initialization, protected fields, and submission checks passed.');
