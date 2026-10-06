'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const jquery = require('jquery');
const source = fs.readFileSync(path.join(__dirname,'../modules/google_meet/assets/js/jitsi_meeting.js'),'utf8');
const tick = ms => new Promise(resolve=>setTimeout(resolve,ms));
let checks = 0;
function check(value,label){assert.ok(value,label);checks++;}
function harness(host=true,video=true){
    const config={domain:'meet.jit.si',roomName:'SC-202610-0123456789abcdef-0123456789abcdef',pin:'123456',subject:'Planning',meetingId:7,isHost:host,userInfo:{displayName:'Test Customer',email:'guest@example.test'},noteKey:'a'.repeat(32),noteUrl:host?'/notes':null,lifecycleUrl:'/lifecycle',csrf:{token_name:'csrf',hash:'initial'}};
    const dom=new JSDOM(`<div id="shareMeetingModal"><input id="gm-share-link" value="https://meet.jit.si/${config.roomName}"><textarea id="gm-share-invitation">Planning invitation</textarea><button data-gm-copy="gm-share-link">Copy</button><button id="gm-device-share" hidden>Device share</button><p id="gm-share-feedback"></p></div><div id="jitsi-meet-viewport"></div><p id="gm-room-feedback"></p><button data-gm-fullscreen>Full screen</button>${host?'<textarea id="gm-room-note"></textarea><button id="gm-save-note">Save</button><p id="gm-note-feedback"></p><div id="gm-note-timeline"></div><button id="gm-finish-meeting">Finish</button>':''}<script type="application/json" id="gm-room-config">${JSON.stringify(config)}</script>`,{runScripts:'outside-only',url:'https://portal.example/room/7'});
    const w=dom.window;w.jQuery=jquery(w);w.confirm=()=>true;w.setTimeout=(fn,ms)=>setTimeout(fn,ms===1200?20:ms);
    const state={requests:[],commands:[],listeners:{},failNext:false,delay:0,active:0,maxActive:0,csrf:'initial'};
    w.jQuery.ajax=options=>{
        const deferred=w.jQuery.Deferred();state.requests.push(options);state.active++;state.maxActive=Math.max(state.active,state.maxActive);
        assert.equal(options.data.csrf,state.csrf,'Latest CSRF token used');
        setTimeout(()=>{state.active--;state.csrf='token-'+state.requests.length;const response={success:true,csrf:{token_name:'csrf',hash:state.csrf},comment_id:12,comment:options.data.comment};
            if(state.failNext){state.failNext=false;deferred.reject({responseJSON:{...response,success:false}});}else deferred.resolve(response);
        },state.delay);
        return deferred.promise();
    };
    class API {
        constructor(domain,options){state.domain=domain;state.options=options;state.iframe=w.document.createElement('iframe');options.parentNode.appendChild(state.iframe);}
        getIFrame(){return state.iframe;}
        addEventListener(name,fn){state.listeners[name]=fn;}
        executeCommand(...args){state.commands.push(args);if(args[0]==='hangup')state.listeners.videoConferenceLeft();}
    }
    if(video)w.JitsiMeetExternalAPI=API;
    state.copied='';Object.defineProperty(w.navigator,'clipboard',{value:{writeText:value=>{state.copied=value;return Promise.resolve();}},configurable:true});
    Object.defineProperty(w.document,'readyState',{value:'complete'});w.eval(source);
    return {w,state,config,close:()=>w.close()};
}
(async()=>{
    const h=harness();const {w,state,config}=h;
    check(state.domain===config.domain&&state.options.roomName===config.roomName&&state.options.userInfo.email===config.userInfo.email,'Saved room and authenticated identity used');
    check(state.iframe.getAttribute('allow').includes('display-capture')&&state.iframe.title==='Video meeting','Accessible iframe with WebRTC permissions');
    state.listeners.videoConferenceLeft();await tick(5);check(state.requests.length===0,'Prejoin exit cannot finish CRM meeting');
    state.listeners.videoConferenceJoined({id:'local'});await tick(5);
    check(state.requests[0].data.event==='joined','Joined event saved');
    state.listeners.participantRoleChanged({id:'other',role:'moderator'});check(!state.commands.some(command=>command[0]==='password'),'Remote moderator does not apply PIN');
    state.listeners.participantRoleChanged({id:'local',role:'moderator'});check(state.commands.some(command=>command[0]==='password'&&command[1]===config.pin),'Host moderator applies PIN');
    state.listeners.passwordRequired();check(state.commands.filter(command=>command[0]==='password').length===2,'Saved PIN used on password prompt');
    const field=w.document.getElementById('gm-room-note');state.delay=40;field.value='<img src=x onerror=alert(1)>';field.dispatchEvent(new w.Event('input'));await tick(25);field.value='Revised note';field.dispatchEvent(new w.Event('input'));await tick(105);
    check(state.requests.filter(request=>request.url==='/notes').length===2&&state.requests.every(request=>!request.data.comment||request.data.note_key===config.noteKey),'Autosave retries retain stable note key');
    check(w.document.querySelectorAll('#gm-note-timeline article').length===1&&w.document.querySelector('#gm-note-timeline p').textContent==='Revised note'&&w.document.querySelector('#gm-note-timeline img')===null,'Timeline updates one escaped note');
    check(state.maxActive===1,'Notes and lifecycle serialize rotating CSRF tokens');
    state.failNext=true;field.value='Keep this unsaved note';w.document.getElementById('gm-save-note').click();await tick(50);
    check(field.value==='Keep this unsaved note'&&w.document.getElementById('gm-note-feedback').textContent.includes('Not saved'),'Network failure preserves draft');
    w.document.getElementById('gm-save-note').click();await tick(50);check(w.document.getElementById('gm-note-feedback').textContent==='Saved to timeline.','Failed draft can retry');
    w.document.querySelector('[data-gm-copy]').click();await tick(5);check(state.copied===w.document.getElementById('gm-share-link').value,'Exact shared provider link copied');
    Object.defineProperty(w.navigator,'clipboard',{value:null,configurable:true});w.document.execCommand=command=>{state.fallback=command;return true;};w.document.querySelector('[data-gm-copy]').click();check(state.fallback==='copy','Non-clipboard browsers fall back to selection copy');
    w.document.getElementById('gm-finish-meeting').click();await tick(50);check(state.requests.filter(request=>request.data.event==='finish').length===1&&!state.requests.some(request=>request.data.event==='left')&&state.commands.some(command=>command[0]==='hangup'),'Explicit finish does not double-complete after hangup');h.close();
    const guest=harness(false);guest.state.listeners.videoConferenceJoined({id:'guest'});await tick(5);guest.state.listeners.participantRoleChanged({id:'guest',role:'moderator'});check(!guest.state.commands.some(command=>command[0]==='password'),'Customer cannot set host PIN');guest.state.listeners.videoConferenceLeft();await tick(5);
    check(guest.state.requests.map(request=>request.data.event).join(',')==='joined,left'&&!guest.state.requests.some(request=>request.data.event==='finish'),'Customer lifecycle never sends finish');guest.close();
    const offline=harness(true,false);const script=offline.w.document.querySelector('script[src]');check(script.src==='https://meet.jit.si/external_api.js','Official HTTPS API dynamically loaded');script.onerror();check(offline.w.document.getElementById('gm-room-feedback').textContent.includes('new window'),'Provider loading failure gives escape hatch');offline.close();
    console.log(`PASS: ${checks} embedded room, autosave, PIN, CSRF, share and lifecycle UI checks`);
})().catch(error=>{console.error(error);process.exitCode=1;});
