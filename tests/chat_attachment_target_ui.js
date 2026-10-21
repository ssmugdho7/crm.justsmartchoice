'use strict';
const fs=require('node:fs'),assert=require('node:assert/strict'),{JSDOM}=require('jsdom'),jquery=require('jquery'),moment=require('moment');
const dom=new JSDOM('<div id="frame"><textarea class="chatbox">Unsaved staff draft</textarea><textarea class="client_chatbox">Unsaved client draft</textarea><textarea class="group_chatbox">Unsaved group draft</textarea><ul id="contacts"><li class="contact active" id="3"></li></ul><ul class="chat_clients_list"><li class="contact_name selected" id="34"></li></ul></div><textarea class="clients_textarea">Unsaved portal draft</textarea><input type="hidden" name="fixture_csrf" value="fresh">',{runScripts:'outside-only',url:'https://fixture.test'});
const w=dom.window,$=jquery(w);w.$=$;w.moment=moment;w.userSessionId='7';w.contact_name_id='client_33';w.site_url='/';w.alert_float=()=>{};w.getSharedFiles=()=>{};
w.prchatSettings={serverPath:'/staff-send',clientsMessagesPath:'/client-send',groupMessagePath:'/group-send',uploadMethod:'/staff-upload',groupUploadMethod:'/group-upload'};w.customerSettings={clientsMessagesPath:'/portal-send',uploadMethod:'/client-upload'};
const source=f=>fs.readFileSync('modules/prchat/'+f,'utf8');
const render=s=>s.replace(/<\?=([\s\S]*?)\?>/g,(_,e)=>JSON.stringify(e.includes('get_csrf_token_name')?'fixture_csrf':e.includes('base_url')?'/uploads/':e.includes('get_csrf_hash')?'fallback':'Translated')).replace(/<\?php\s+echo[\s\S]*?\?>/g,'translated').replace(/<\?php[\s\S]*?\?>/g,'');
const helper=source('assets/module_includes/mutual_and_helper_functions.php');w.eval(render(helper.slice(helper.indexOf('  function prchatAppendUploadCsrf'),helper.indexOf('  // Build an image node'))));
const calls=[];$.ajax=cfg=>{const d=$.Deferred();calls.push({cfg,d});return d;};let events=[];$(w.document).on('prchat:attachment-saved',(e,file)=>events.push(file));
let checks=0;function check(ok,label){assert.ok(ok,label);checks++;}const tick=()=>new Promise(r=>setTimeout(r,15));
function extract(file,start,end){const s=source(file),a=s.indexOf(start);return render(s.slice(a,s.indexOf(end,a)));}
(async()=>{
for(const [file,start,end,args,kind,peer,field,expected,url] of [
 ['views/chat_full_view.php','    function uploadFilesSequentially(','    /*---------------* Check for messages history',[[new w.File(['pdf'],'fixture.pdf')],'2','token',0],'staff','2','to','2','/staff-send'],
 ['assets/module_includes/crm_clients.php','  function uploadClientFilesSequentially(','  /*!',[[new w.File(['pdf'],'fixture.pdf')],'33','token','form',0],'client','33','to','client_33','/client-send'],
 ['assets/module_includes/groups.php','  function uploadGroupFilesSequentially(','  function appendGroupOptions(',[[new w.File(['pdf'],'fixture.pdf')],'10','token',0],'group','10','group_id','10','/group-send'],
 ['views/chat_clients_view.php','  function uploadClientFilesSequentially(','  clientMessageChannel.bind("chat-ticket-event"',[[new w.File(['pdf'],'fixture.pdf')],'staff_7','token',0],'portal','staff_7','to','staff_7','/portal-send']]){
 w.eval(extract(file,start,end));
 const name=start.match(/function (\w+)/)[1],before=calls.length;w[name](...args);
 const request=calls[before];check(request.cfg.data.get('fixture_csrf')==='fresh',kind+' picker upload uses current CSRF');
 request.cfg.success({upload_data:{file_name:'fixture.pdf'}});
 const persist=calls.at(-1);check(persist.cfg.url===url&&String(persist.cfg.data[field])===expected,kind+' attachment persists to captured recipient, despite another active conversation');
 persist.d.resolve({id:50,success:true});await tick();check(events.at(-1).kind===kind&&events.at(-1).recipient===peer,kind+' saved event is scoped to original conversation');
 check($('.chatbox').val()==='Unsaved staff draft'&&$('.client_chatbox').val()==='Unsaved client draft'&&$('.group_chatbox').val()==='Unsaved group draft'&&$('.clients_textarea').val()==='Unsaved portal draft',kind+' upload never overwrites or submits a composer draft');
}
for(const result of [{success:true},{success:false,id:3},{id:-1}]){
 let saved=false;const p=w.prchatPersistAttachment('/send',{msg:'file'},'staff','2').done(()=>saved=true);calls.at(-1).d.resolve(result);await tick();check(!saved&&p.state()==='rejected','Invalid send acknowledgement cannot produce a saved attachment event');
}
// Exercise the actual admin voice initialization and frozen client-recipient send path.
$('body').append('<form name="clientMessagesForm" id="clientMessagesForm"><input class="to" value="client_33"><input class="from" value="staff_7"><button class="chat-action-voice-client"></button></form>');
w.PrchatSafeRenderer={renderFromText:raw=>({display:raw,sidebar:raw})};w.PrchatVoiceRecorder={isSupported:()=>true,init:cfg=>{w.voiceConfig=cfg;}};
const view=source('views/chat_full_view.php'),a=view.indexOf('    // Voice recorder initialization'),b=view.indexOf('</script>',a);
w.eval(render(view.slice(a,b)));const context=w.voiceConfig.extraFormData();check(context.send_to==='client_33','Admin voice uploader uses exactly one client namespace');
$('form[name=clientMessagesForm] input.to').val('client_34');let post;$.post=(url,data)=>{post={url,data};return $.Deferred();};
w.voiceConfig.onSend('clients/user_voice_fixture.webm',context);check(post.data.to==='client_33'&&post.data.from==='staff_7'&&post.data.typing==='false','Voice persistence keeps original client after switching to another client');
console.log(`PASS ${checks} attachment picker target isolation, draft retention, acknowledgements and real voice-initialization checks`);dom.window.close();
})().catch(e=>{console.error(e);dom.window.close();process.exitCode=1;});
