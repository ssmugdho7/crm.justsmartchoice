'use strict';
const fs=require('fs'),vm=require('vm'),assert=require('assert');
const source=fs.readFileSync(require('path').join(__dirname,'../modules/debug_mode/assets/js/debug_mode_capture.js'),'utf8');
const handlers={}, documentHandlers={},requests=[];
const response={status:200};
function XHR(){this.listeners={};this.status=500;}
XHR.prototype.addEventListener=function(name,callback){this.listeners[name]=callback;};XHR.prototype.open=function(){};
const window={fetch:(url,options)=>{requests.push({url,options});return Promise.resolve(response);},csrfData:{token_name:'csrf',hash:'fixture'},addEventListener:(name,callback)=>handlers[name]=callback};
const document={readyState:'complete',currentScript:{getAttribute:()=>'/admin/debug_mode/browser_error'},addEventListener:(name,callback)=>documentHandlers[name]=callback};
const sandbox={window,document,location:{href:'https://crm.example/admin/appointly',origin:'https://crm.example',pathname:'/admin/appointly'},URL,URLSearchParams,XMLHttpRequest:XHR,Error,console};
vm.runInNewContext(source,sandbox);
(async()=>{
 handlers.error({message:'A fixture exception',filename:'https://crm.example/modules/appointly/app.js?token=secret',lineno:5});
 assert.equal(requests.length,1);let body=new URLSearchParams(requests[0].options.body);assert.equal(body.get('file'),'/modules/appointly/app.js');assert.equal(body.get('csrf'),'fixture');assert.equal(body.get('kind'),'javascript');assert(!requests[0].options.body.includes('secret'));
 handlers.error({message:'A fixture exception',filename:'https://crm.example/modules/appointly/app.js?token=secret',lineno:5});assert.equal(requests.length,1,'Deduplicate the same event');
 handlers.unhandledrejection({reason:{privateCustomerData:'must not be captured'}});assert(!requests.at(-1).options.body.includes('privateCustomerData'));
 handlers.error({target:{tagName:'SCRIPT',src:'https://third-party.example/script?secret=x'}});body=new URLSearchParams(requests.at(-1).options.body);assert.equal(body.get('file'),'[external resource]');
 assert.strictEqual(await window.fetch('/api/working'),response,'Return the original response to application callers');
 const xhr=new XHR();xhr.open('GET','/api/failure?token=hidden');xhr.listeners.loadend.call(xhr);body=new URLSearchParams(requests.at(-1).options.body);assert.equal(body.get('message'),'XHR HTTP 500');assert(!requests.at(-1).options.body.includes('hidden'));
 const before=requests.length;vm.runInNewContext(source,sandbox);assert.equal(requests.length,before,'Install only once');
 window.csrfData=null;handlers.error({message:'Buffered failure',filename:'/modules/other/test.js'});assert.equal(requests.length,before);window.csrfData={token_name:'csrf',hash:'new'};documentHandlers.DOMContentLoaded();assert.equal(requests.length,before+1,'Flush buffered event once CSRF is ready');
 console.log('PASS browser capture: JS, rejected promises, resource/XHR failures, query exclusion, deduplication, CSRF, buffering and fetch response preservation');
})().catch(e=>{console.error(e);process.exitCode=1;});
