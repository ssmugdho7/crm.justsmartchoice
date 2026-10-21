'use strict';
const fs=require('node:fs'),assert=require('node:assert/strict'),{JSDOM}=require('jsdom');
const dom=new JSDOM('',{runScripts:'outside-only',url:'https://fixture.test/'}),w=dom.window;
w.MediaStream=class {constructor(tracks=[]){this.tracks=tracks;}getTracks(){return this.tracks;}getAudioTracks(){return this.tracks.filter(t=>t.kind==='audio');}getVideoTracks(){return this.tracks.filter(t=>t.kind==='video');}addTrack(t){this.tracks.push(t);}removeTrack(t){this.tracks=this.tracks.filter(track=>track!==t);}};
function track(kind='audio'){return {kind,enabled:true,stops:0,stop(){this.stops++;}};}
function peer(existing=null,reject=false){const sender={track:existing,replacements:0,async replaceTrack(t){if(reject)throw new Error('Device disconnected');this.replacements++;this.track=t;}};return {sender,adds:0,getSenders(){return [sender];},getTransceivers(){return [{sender,receiver:{track:{kind:'audio'}}}];},addTrack(t){this.adds++;sender.track=t;},close(){}};}
let getMedia;
Object.defineProperty(w.navigator,'mediaDevices',{value:{getUserMedia:constraints=>getMedia(constraints)}});
w.eval(fs.readFileSync('modules/prchat/assets/js/calls/call-manager.js','utf8'));
(async()=>{
 const manager=new w.ChatCallManager(),pc=peer(),audio=track();manager.pc=pc;manager.muted=true;
 getMedia=async()=>new w.MediaStream([audio]);await manager.setAudioInput('qa-microphone');
 assert.equal(pc.adds,0,'An existing negotiated audio transceiver is reused without renegotiation');
 assert.equal(pc.sender.replacements,1);assert.equal(audio.enabled,false,'Connecting microphone respects mute');
 let grant;const late=new w.ChatCallManager();late.pc=peer();getMedia=()=>new Promise(resolve=>{grant=resolve;});
 const pending=late.setAudioInput('qa-late');late.endFromRemote();const lateTrack=track();grant(new w.MediaStream([lateTrack]));await pending;
 assert.equal(lateTrack.stops,1,'Late microphone permission cannot leave a live track after hangup');assert.equal(late.localStream,null);
 const failed=new w.ChatCallManager(),old=track(),replacement=track();failed.pc=peer(old,true);failed.localStream=new w.MediaStream([old]);failed.audioInputId='previous';
 getMedia=async()=>new w.MediaStream([replacement]);await assert.rejects(()=>failed.setAudioInput('failed-device'),/disconnected/);
 assert.equal(replacement.stops,1,'Failed track replacement releases the newly acquired microphone');assert.equal(old.stops,0);assert.equal(failed.audioInputId,'previous');
 const reused=new w.ChatCallManager();reused.pc=peer();getMedia=()=>new Promise(resolve=>{grant=resolve;});const stale=reused.setAudioInput('old-call-device');const freshPeer=peer();reused.pc=freshPeer;const staleTrack=track();grant(new w.MediaStream([staleTrack]));await stale;
 assert.equal(staleTrack.stops,1,'An old call permission result is discarded when a newer peer exists');assert.equal(freshPeer.adds,0);assert.equal(freshPeer.sender.replacements,0);
 w.HTMLMediaElement.prototype.play=async()=>{};
 w.eval(fs.readFileSync('modules/prchat/assets/js/calls/call-ui.js','utf8'));
 for(const isVideo of [false,true]){
  const original=new w.ChatCallManager();original.pc=peer();w.__chatCallManager=original;
  let permissionRequests=0;
  getMedia=()=>{permissionRequests++;return new Promise(resolve=>{grant=resolve;});};
  w.ChatCallUI.showInCall('Private QA',()=>{}, {isVideo});
  if(isVideo)w.document.querySelector('#chat-call-media-prompt').click();
  assert.equal(permissionRequests,1,'Repeated media prompt clicks cannot queue another camera request');
  original.endFromRemote();w.ChatCallUI.closeModal();
  const next=new w.ChatCallManager();next.pc=peer();w.__chatCallManager=next;
  const delayed=track(),delayedCamera=track('video');grant(new w.MediaStream(isVideo?[delayed,delayedCamera]:[delayed]));await new Promise(resolve=>setTimeout(resolve,0));
  assert.equal(delayed.stops,1,'Late '+(isVideo?'video':'voice')+' UI permission cannot activate media in the next call');
  if(isVideo)assert.equal(delayedCamera.stops,1,'Late camera track is stopped too');
  assert.equal(next.localStream,null);next.endFromRemote();
 }
 for(const isVideo of [false,true]){
  const active=new w.ChatCallManager();active.pc=peer();w.__chatCallManager=active;
  getMedia=()=>new Promise(resolve=>{grant=resolve;});w.ChatCallUI.showInCall('Mute QA',()=>{}, {isVideo});
  active.setMuted(true);const pendingAudio=track();grant(new w.MediaStream([pendingAudio]));await new Promise(resolve=>setTimeout(resolve,0));
  assert.equal(pendingAudio.enabled,false,'Muting before '+(isVideo?'camera':'microphone')+' access resolves keeps newly attached audio muted');
  active.endFromRemote();w.ChatCallUI.closeModal();
 }
 const timed=new w.ChatCallManager();timed.pc=peer();timed.localStream=new w.MediaStream([track()]);w.__chatCallManager=timed;
 let timeout;const nativeTimeout=w.setTimeout;w.setTimeout=(fn,ms)=>ms===8000?(timeout=fn,0):nativeTimeout(fn,ms);
 getMedia=()=>new Promise(resolve=>{grant=resolve;});w.ChatCallUI.showInCall('Timeout QA',()=>{}, {isVideo:true});
 w.document.querySelector('#chat-video-call-controls button[title="Turn off camera"]').click();
 assert.ok(timeout,'Camera request has a permission deadline');timeout();await new Promise(resolve=>setTimeout(resolve,0));
 const expiredCamera=track('video');grant(new w.MediaStream([expiredCamera]));await new Promise(resolve=>setTimeout(resolve,0));
 assert.equal(expiredCamera.stops,1,'A camera permission grant after timeout releases its track');
 assert.equal(timed.localStream.getVideoTracks().length,0);w.setTimeout=nativeTimeout;timed.endFromRemote();w.ChatCallUI.closeModal();
 manager.endFromRemote();failed.endFromRemote();reused.endFromRemote();dom.window.close();
 console.log('PASS negotiated microphone reuse, mute, late permission, failed replacement and new-call isolation');
})().catch(error=>{dom.window.close();console.error(error);process.exitCode=1;});
