(function () {
 'use strict';
 var panel=document.getElementById('sc-meta-consent');
 if(!panel || window.scMetaPixelInitialized)return;
 var pixel=panel.getAttribute('data-pixel-id');
 if(!/^[0-9]{5,25}$/.test(pixel))return;
 window.scMetaPixelInitialized=true;
 var preferences=document.getElementById('sc-meta-preferences');
 var key='sc_meta_consent_v1', choice=null, started=false;
 try{choice=localStorage.getItem(key);}catch(e){}
 // Do not start advertising tracking for browsers requesting privacy protection.
 var restricted=navigator.globalPrivacyControl===true || navigator.doNotTrack==='1';
 function start(){
  if(restricted || choice!=='allow')return;
  if(started){window.fbq('consent','grant');return;}
  started=true;
  if(!window.fbq){
   var q=window.fbq=function(){q.callMethod?q.callMethod.apply(q,arguments):q.queue.push(arguments);};
   if(!window._fbq)window._fbq=q;
   q.push=q;q.loaded=true;q.version='2.0';q.queue=[];
   var script=document.createElement('script');script.async=true;script.src='https://connect.facebook.net/en_US/fbevents.js';document.head.appendChild(script);
  }
  // Explicit events only; no automatic form-field or advanced matching collection.
  window.fbq('set','autoConfig',false,pixel);
  window.fbq('consent','grant');
  window.fbq('init',pixel);
  window.fbq('trackSingle',pixel,'PageView');
 }
 function choose(value){
  choice=value;
  try{localStorage.setItem(key,value);}catch(e){}
  panel.hidden=true;preferences.hidden=false;
  if(value==='allow')start();
  else if(started && window.fbq)window.fbq('consent','revoke');
 }
 panel.querySelectorAll('[data-consent]').forEach(function(button){button.addEventListener('click',function(){choose(button.getAttribute('data-consent'));});});
 preferences.addEventListener('click',function(){panel.hidden=false;preferences.hidden=true;});
 // Dispatched only after the CRM confirms a successful landing-page submission.
 document.addEventListener('sc:landing-lead-created',function(){
  if(choice==='allow' && !restricted && window.fbq)window.fbq('trackSingle',pixel,'Lead');
 });
 if(restricted){panel.hidden=true;preferences.hidden=true;return;}
 panel.hidden=choice==='allow'||choice==='deny';preferences.hidden=!panel.hidden;
 start();
})();
