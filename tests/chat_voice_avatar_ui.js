'use strict';
const fs=require('node:fs'),assert=require('node:assert/strict'),{JSDOM}=require('jsdom'),jquery=require('jquery');
let checks=0;function check(ok,label){assert.ok(ok,label);checks++;}
const dom=new JSDOM('<div id="frame"><div id="sidepanel"><ul class="chat_groups_list"><li class="group_selector"><div class="groups_image"></div></li></ul></div></div><div id="composer"><textarea></textarea><button id="record"></button></div><input type="hidden" name="fixture_csrf" value="fresh-token">',{runScripts:'outside-only',url:'https://fixture.test'});
const w=dom.window,$=jquery(w);w.$=$;w.prchatI18n={chat_group_photo_label:'Group photo',chat_send:'Send',chat_voice_cancel:'Cancel',chat_voice_stop_send:'Send',chat_upload_failed:'Failed',chat_voice_upload_http:'HTTP %s'};
const helper=fs.readFileSync('modules/prchat/assets/module_includes/mutual_and_helper_functions.php','utf8');
const piece=helper.slice(helper.indexOf('  function prchatAppendUploadCsrf'),helper.indexOf('  /**\n   * Main Settings')).replace(/<\?=([\s\S]*?)\?>/g,(_,expr)=>JSON.stringify(expr.includes('get_csrf_token_name')?'fixture_csrf':'fallback'));
w.eval(piece);
const frame=$('.groups_image');w.prchatRenderGroupAvatar(frame,'/wide.png');
check(frame.hasClass('sc-chat-group-avatar-frame')&&frame.find('img.sc-chat-group-avatar').attr('src')==='/wide.png','Wide group photo uses circular frame');
w.prchatRenderGroupAvatar(frame,null);check(frame.find('img').length===0&&frame.find('i').length===1,'Removal retains frame with fallback icon');
w.prchatRenderGroupAvatar(frame,'/replacement.png');check(frame.find('img').length===1&&frame.hasClass('sc-chat-group-avatar-frame'),'Re-add retains circular frame and replaces content');
frame.find('img').trigger('error');check(frame.find('img').length===0&&frame.find('i').length===1,'Missing photo falls back without broken rectangular image');
w.prchatRenderGroupAvatar(frame,'/a" onerror="bad.png');check(frame.find('img').attr('onerror')===undefined,'Photo URL is an attribute, never injected HTML');
const css=fs.readFileSync('modules/prchat/assets/css/chat_full_view.css','utf8');
const style=w.document.createElement('style');style.textContent=css;w.document.head.appendChild(style);
const computed=w.getComputedStyle(frame[0]);check(computed.width==='42px'&&computed.height==='42px'&&computed.borderRadius==='50%'&&computed.flexShrink==='0','Group avatar has equal dimensions, circular clip and no flex compression');
check(w.prchatCsrfPayload({group_id:2}).fixture_csrf==='fresh-token','Photo removal sends current configured CSRF token');
const sent=[],errors=[],requests=[],recorders=[],streams=[];let selected='client_33',permissions=0;
w.XMLHttpRequest=class {open(method,url){this.method=method;this.url=url;}setRequestHeader(){}send(data){this.data=data;requests.push(this);}};
w.MediaRecorder=class {static isTypeSupported(type){return type==='audio/webm';}constructor(stream){this.stream=stream;this.state='inactive';recorders.push(this);}start(){this.state='recording';}stop(){this.state='inactive';if(this.ondataavailable)this.ondataavailable({data:new w.Blob(['voice'])});if(this.onstop)this.onstop();}};
Object.defineProperty(w.navigator,'mediaDevices',{value:{getUserMedia:async()=>{permissions++;const stream={stops:0,getTracks(){return [{stop:()=>stream.stops++}];}};streams.push(stream);return stream;}}});
w.eval(fs.readFileSync('modules/prchat/assets/js/voice-recorder.js','utf8'));
const recorder=w.PrchatVoiceRecorder.init({triggerBtn:w.document.querySelector('#record'),container:w.document.querySelector('#composer'),uploadUrl:'/client-upload',csrfName:'fixture_csrf',csrfValue:'stale-token',extraFormData:()=>({from:'staff_7',send_to:selected}),onSend:(path,context)=>sent.push({path,context}),onError:error=>errors.push(error)});
const tick=()=>new Promise(resolve=>setTimeout(resolve,0));
(async()=>{
$('#record').trigger('click');$('#record').trigger('click');await tick();
check(permissions===1&&recorders.length===1,'Repeated clicks cannot open simultaneous recorders while permission resolves');
selected='client_34';recorder.seconds=2;recorder.stop();
check(streams[0].stops===1&&!w.document.querySelector('.prchat-voice-recorder'),'Stopping releases microphone and restores composer');
check(requests[0].data.get('send_to')==='client_33'&&requests[0].data.get('fixture_csrf')==='fresh-token','Voice upload retains original conversation and uses fresh CSRF');
requests[0].status=200;requests[0].responseText=JSON.stringify({upload_data:{file_name:'recording.webm',subfolder:'clients'}});requests[0].onload();
check(sent[0].path==='clients/recording.webm'&&sent[0].context.send_to==='client_33','Voice send retains original recipient after switching conversations');
recorder._upload(new w.File(['voice'],'recording.webm'));requests[1].status=404;requests[1].onload();check(errors.at(-1)==='HTTP 404','HTTP upload failures remain visible');
recorder._upload(new w.File(['voice'],'recording.webm'));requests[2].status=200;requests[2].responseText='{}';requests[2].onload();check(errors.at(-1)==='Failed'&&sent.length===1,'Malformed upload success cannot silently lose or send a voice note');
recorder._upload(new w.File(['voice'],'recording.webm'));requests[3].status=200;requests[3].responseText='<html>Login</html>';requests[3].onload();check(errors.at(-1)==='Failed'&&sent.length===1,'Expired-login HTML cannot look like a successful upload');
recorder._upload(new w.File(['voice'],'recording.webm'));requests[4].status=200;requests[4].responseText='{"error":"Rejected"}';requests[4].onload();check(errors.at(-1)==='Rejected'&&sent.length===1,'Upload validation error cannot send a message');
$('#record').trigger('click');recorder.cancel();await tick();check(streams[1].stops===1&&requests.length===5,'Cancelling during permission acquisition releases late stream and sends nothing');
// Exercise actual group/staff/client voice persistence acknowledgement logic.
const view=fs.readFileSync('modules/prchat/views/chat_full_view.php','utf8');
const start=view.indexOf('        function markVoiceSent('),end=view.indexOf("        if (target === 'staff')",start);
w.alert_float=()=>{};$.fn.tooltip=function(){return this;};w.eval(view.slice(start,end).replace(/<\?=([\s\S]*?)\?>/g,'null'));
for(const response of ['<html>Login</html>',{success:false,error:'Failure'},{success:true},{success:true,id:42}]){
 const pane=$('<div><div data-message-id="temp" class="modern-message-sending"><i class="modern-status-sending fa-clock"></i></div></div>');
 w.markVoiceSent(pane,'temp',response);
 check(response.id?pane.find('[data-message-id="42"] .modern-status-delivered').length===1:pane.find('.modern-message-failed').length===1,'Only a persisted ID can mark a voice message delivered');
}
console.log(`PASS ${checks} voice recording, cancellation, target isolation, upload failure, acknowledgement and circular-photo UI checks`);dom.window.close();
})().catch(e=>{console.error(e);dom.window.close();process.exitCode=1;});
