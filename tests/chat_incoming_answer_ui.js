'use strict';
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
function fixture(toggled=false){
 const source=fs.readFileSync('modules/prchat/views/'+(toggled?'chat_toggled_new.php':'chat_full_view.php'),'utf8');
 const start=source.indexOf(toggled?'(options) => {':'function(options) {',source.indexOf('ChatCallUI.showIncomingCall'));
 const end=source.indexOf(toggled?'}, () => {':'}, function() {',start);
 const callback=('('+source.slice(start,end)+'})').replace(/<\?(?:php|=)[\s\S]*?\?>/g,'Call failed');
 let resolve,reject,shown=0,closed=0,ended=0;
 const pending=new Promise((yes,no)=>{resolve=yes;reject=no;});
 const manager={pc:{},hasEnded:false,answer:()=>pending,hangup(){this.hasEnded=true;ended++;}};
 const ui={unlockAudio(){},showInCall(){shown++;},stopRingtone(){},closeModal(){closed++;}};
 const w={__CHAT_CALL_STATE:'ringingIncoming',__chatCallManager:manager,ChatCallUI:ui,chatConfig:{currentUserId:45}};
 const scope={window:w,ChatCallUI:ui,isVideoCall:false,userSessionId:45,payload:{from_id:18,from_name:'QA',caller_type:'staff'},offer:{},avatarUrlIn:null,avatarUrl:null,saveCallState(){},endCallWithSummary(){},alert_float(){},localStorage:{removeItem(){}},console:{error(){},warn(){}}};
 const accept=vm.runInNewContext(callback,scope);
 return {accept,resolve,reject,w,manager,shown:()=>shown,closed:()=>closed,ended:()=>ended};
}
const tick=()=>new Promise(resolve=>setTimeout(resolve,0));
(async()=>{
 for(const toggled of [false,true]){
 const success=fixture(toggled);success.accept({});assert.equal(success.shown(),0,'Incoming microphone/camera UI waits for peer and answer signaling');success.resolve({success:true});await tick();assert.equal(success.shown(),1);
 const failed=fixture(toggled);failed.accept({});failed.reject(new Error('Signal failed'));await tick();assert.equal(failed.shown(),0);assert.equal(failed.w.__CHAT_CALL_STATE,'idle');assert.ok(failed.closed()>0);
 const cancelled=fixture(toggled);cancelled.accept({});cancelled.w.__CHAT_CALL_STATE='idle';cancelled.manager.hasEnded=true;cancelled.resolve({success:true});await tick();assert.equal(cancelled.shown(),0,'An answer completed after remote hangup cannot reopen media');
 }
 console.log('PASS incoming answer ordering, signaling failure cleanup and cancelled-answer UI isolation');
})().catch(error=>{console.error(error);process.exitCode=1;});
