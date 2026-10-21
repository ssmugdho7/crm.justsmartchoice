'use strict';
const fs = require('node:fs');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const source = fs.readFileSync('modules/prchat/assets/js/calls/call-manager.js','utf8');
const dom = new JSDOM('', {runScripts:'outside-only',url:'https://fixture.test/'}), w=dom.window;
let requests=[], response={success:true}, rejectNetwork=false, closed=0, stopped=0;
w.PRCHAT_CALLS={start:'/start',answer:'/answer',ice:'/ice',hangup:'/hangup',mute_status:'/mute'};
w.PRCHAT_CSRF={name:'csrf',hash:'fixture'};
w.fetch=async(url,options)=>{requests.push({url,body:new URLSearchParams(options.body)});if(rejectNetwork)throw new Error('Offline');return {ok:true,status:200,text:async()=>JSON.stringify(response)};};
class Peer {
  constructor(){this.transceivers=[];this.senders=[];this.signalingState='stable';this.candidates=[];}
  addTransceiver(kind){const sender={track:null};this.transceivers.push({direction:'sendrecv',sender,receiver:{track:{kind}}});this.senders.push(sender);}
  createOffer(){return Promise.resolve({type:'offer',sdp:this.transceivers.map((t)=>'m='+t.receiver.track.kind).join('\n')});}
  setLocalDescription(value){this.localDescription=value;this.signalingState=value.type==='offer'?'have-local-offer':'stable';return Promise.resolve();}
  setRemoteDescription(value){this.remoteDescription=value;return Promise.resolve();}
  createAnswer(){return Promise.resolve({type:'answer',sdp:'m=audio'});}
  getTransceivers(){return this.transceivers;}
  getSenders(){return this.senders;}
  addIceCandidate(value){this.candidates.push(value);return Promise.resolve();}
  close(){closed++;}
}
w.RTCPeerConnection=Peer;w.RTCSessionDescription=class {constructor(value){Object.assign(this,value);}};w.RTCIceCandidate=w.RTCSessionDescription;
w.eval(source);
let checks=0;function check(ok,label){assert.ok(ok,label);checks++;}
(async()=>{
  const caller=new w.ChatCallManager();
  await caller.call(7,8,false);
  check(requests[0].url==='/start' && requests[0].body.get('from_id')==='7' && requests[0].body.get('csrf')==='fixture','Voice offer uses configured URL, identity and CSRF');
  check(caller.pc.transceivers.length===1,'Voice offers audio only');
  caller.pc.onicecandidate({candidate:{candidate:'fixture'}});await Promise.resolve();
  check(requests.some(r=>r.url==='/ice'&&r.body.get('to_id')==='8'),'ICE candidate relays to selected peer');
  caller.setMuted(true);check(caller.isMuted(),'Mute state changes');
  const peer=caller.pc;
  await caller.addIceCandidate({candidate:'remote'});check(caller.pendingCandidates.length===1,'Early remote ICE is buffered');
  await caller.handleRemoteAnswer({type:'answer',sdp:'m=audio'});
  check(peer.candidates.length===1&&caller.pendingCandidates.length===0,'Remote answer drains buffered ICE');
  caller.localStream={getTracks:()=>[{stop:()=>stopped++}]};
  caller.hangup();caller.hangup();
  check(closed===1&&stopped===1&&caller.pc===null&&requests.filter(r=>r.url==='/hangup').length===1,'Hangup releases tracks/peer exactly once');
  const video=new w.ChatCallManager();await video.call(7,9,true);
  check(video.pc.transceivers.length===2,'Video offers both audio and video');video.endFromRemote();
  const crossIdentity=new w.ChatCallManager();await crossIdentity.call(7,7,false,{callerType:'staff',recipientType:'client'});
  check(requests.at(-1).body.get('recipient_type')==='client','Equal numeric staff/contact IDs are different callers');crossIdentity.endFromRemote();
  await assert.rejects(()=>new w.ChatCallManager().call(7,7,false),/yourself/);
  await assert.rejects(()=>new w.ChatCallManager().call(0,8,false),/Invalid self/);checks+=2;
  const callee=new w.ChatCallManager();await callee.answer(8,7,{type:'offer',sdp:'m=audio\nm=video'});
  check(callee.isVideo&&requests.at(-1).url==='/answer','Answer detects video and uses answer signaling');callee.endFromRemote();
  for(const payload of [{success:false,error:'Unavailable'},null]){
    response=payload;const failed=new w.ChatCallManager();await assert.rejects(()=>failed.call(7,8,false));
    check(failed.pc===null&&failed.hasEnded,'Rejected signaling closes peer instead of reporting a successful call');
  }
  response={success:true};rejectNetwork=true;
  const offline=new w.ChatCallManager();await assert.rejects(()=>offline.call(7,8,false),/Offline/);
  check(offline.pc===null&&offline.hasEnded,'Network error also releases the peer');
  const traces=[];w.console.log=(...args)=>traces.push(args);w.localStorage.setItem('prchat_debug_calls','1');
  w.PRCHAT_GET_CALL_TOKEN_URL='/ice-config';rejectNetwork=false;
  const normalFetch=w.fetch;w.fetch=async(url,options)=>url==='/ice-config'?{text:async()=>JSON.stringify({iceServers:[{urls:['turn:fixture.invalid'],username:'qa',credential:'qa-turn-secret'}]})}:normalFetch(url,options);
  w.eval(source);const configured=new w.ChatCallManager();await configured.call(7,8,false);configured.endFromRemote();
  check(!JSON.stringify(traces).includes('qa-turn-secret'),'Call diagnostics do not log TURN credentials');
  dom.window.close();console.log(`PASS ${checks} isolated voice/video, answer, ICE, mute, hangup and failure checks (no devices or live recipients)`);
})().catch(error=>{dom.window.close();console.error(error);process.exitCode=1;});
