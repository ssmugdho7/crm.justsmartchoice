const fs=require('fs'),vm=require('vm'),assert=require('assert');
let source=fs.readFileSync(__dirname+'/../modules/sc_admin_fixes/assets/js/projects.js','utf8');
source=source.slice(source.indexOf('function approvedNative'),source.indexOf('$(function(){'));
let answer,called=0,nativePrompts=0;
let previous=()=>{nativePrompts++;return false;};
let context={window:{confirm_delete:previous,delete_project_discussion(){assert.equal(context.window.confirm_delete(),true);called++;}},SCAF:{confirm:()=>new Promise(resolve=>answer=resolve)}};
vm.createContext(context);vm.runInContext(source+"guardNative('delete_project_discussion');",context);
(async()=>{context.window.delete_project_discussion(2);assert.equal(called,0);assert.equal(context.window.confirm_delete,previous);answer(false);await Promise.resolve();assert.equal(called,0);context.window.delete_project_discussion(2);answer(true);await Promise.resolve();assert.equal(called,1);assert.equal(nativePrompts,0);assert.equal(context.window.confirm_delete,previous);assert.throws(()=>context.window.SCAFProjectConfirm.resume(()=>{throw Error('native failure');}));assert.equal(context.window.confirm_delete,previous);console.log('PASS cancel/approval gating and synchronous confirmation restoration on success/failure');})().catch(e=>{console.error(e);process.exit(1);});
