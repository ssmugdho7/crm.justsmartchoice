const fs=require('fs'),vm=require('vm'),assert=require('assert');
let requests=[],submit,events=[];
const chain={serialize:()=>'',css:()=>{},append:()=>{},prepend:()=>{},on:(_event,handler)=>{submit=handler}};
function jquery(){return chain;}
jquery.ajax=options=>requests.push(options);jquery.isEmptyObject=value=>value==null||Object.keys(value).length===0;
const context={jQuery:jquery,window:{_formLink:'/form',_loadPageLink:'/load',_thankYouURL:'/thanks',location:{}},document:{dispatchEvent:e=>events.push(e.type)},CustomEvent:function(type){this.type=type},csrfName:'csrf',csrfHash:'test',codePage:'page',Swal:{fire:()=>{}},console};
vm.runInNewContext(fs.readFileSync(__dirname+'/../modules/zillapage_off/assets/landingpage/js/main-page.js','utf8'),context);
requests.shift().success({html:'<form></form>'});assert.equal(events.length,0,'Loading page is not a lead');
submit();requests.shift().success({error:'Required fields'});assert.equal(events.length,0,'Rejected submission is not a lead');
submit();requests.shift().error({status:500});assert.equal(events.length,0,'Failed request is not a lead');
submit();requests.shift().success({type_form_submit:'thank_you_page'});assert.deepEqual(events,['sc:landing-lead-created']);assert.equal(context.window.location.href,'/thanks');
console.log('PASS Lead only after successful CRM form submission; load/errors emit no conversion; native redirect preserved');
