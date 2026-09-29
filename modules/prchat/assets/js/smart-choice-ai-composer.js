(function(){
'use strict';
var recognition=null;
var voiceToken=0;
var activeTextarea=null;
var sending=false;
var voiceRestartTimer=null;
var voiceStopTimer=null;
var VOICE_SILENCE_GRACE_MS=8000;
var VOICE_MAX_SESSION_MS=180000;

function toast(message,type){
  if(window.alert_float){window.alert_float(type||'info',message);return;}
  if(type==='danger'){console.error(message);}else{console.log(message);}
}
function findTextarea(button){
  var selector=button.getAttribute('data-target');
  var scope=button.closest('.message-input,form,#frame,#clientChat,.pusherChatBox')||document;
  return (selector&&scope.querySelector(selector)) || (selector&&document.querySelector(selector)) || scope.querySelector('textarea');
}
function csrfPayload(){
  var out={};
  if(window.csrfData&&window.csrfData.token_name&&window.csrfData.hash){out[window.csrfData.token_name]=window.csrfData.hash;return out;}
  var token=document.querySelector('input[name^="csrf"],input[name="csrf_token_name"]');
  if(token){out[token.name]=token.value;}
  return out;
}
function contextFor(textarea){
  var scope=textarea.closest('#frame,#clientChat,.pusherChatBox')||document;
  return Array.prototype.slice.call(scope.querySelectorAll('.message-text,.msgTxt,.message-bubble,p.you,p.him'),-8)
    .map(function(node){return(node.innerText||'').trim();}).filter(Boolean).join('\n');
}
function stopRecognition(clear){
  voiceToken++;
  var old=recognition;
  recognition=null;
  if(old){
    old.onstart=old.onresult=old.onerror=old.onend=null;
    try{old.abort();}catch(e){try{old.stop();}catch(ignore){}}
  }
  document.querySelectorAll('.prchat-voice-to-text').forEach(function(btn){btn.classList.remove('is-listening');btn.setAttribute('aria-pressed','false');});
  if(clear!==false&&activeTextarea){
    delete activeTextarea.dataset.prchatVoiceToken;
  }
  activeTextarea=null;
}
function startVoice(button){
  var textarea=findTextarea(button);
  if(!textarea||textarea.disabled){toast('Open a conversation before using voice to text.','warning');return;}
  var SpeechRecognition=window.SpeechRecognition||window.webkitSpeechRecognition;
  if(!SpeechRecognition){toast('Voice to text is not supported in this browser.','warning');return;}
  if(recognition){stopRecognition(false);return;}
  stopRecognition(false);
  var token=++voiceToken;
  var instance=new SpeechRecognition();
  recognition=instance;
  activeTextarea=textarea;
  textarea.dataset.prchatVoiceToken=String(token);
  instance.continuous=true;
  instance.interimResults=true;
  instance.maxAlternatives=1;
  var language=window.prchatSpeechLanguage||'auto';
  instance.lang=language==='es'?'es-US':language==='en'?'en-US':((document.documentElement.lang||navigator.language||'en-US').toLowerCase().indexOf('es')===0?'es-US':'en-US');
  var base=(textarea.value||'').trim();
  var committedText='';
  instance.onstart=function(){if(token!==voiceToken)return;button.classList.add('is-listening');button.setAttribute('aria-pressed','true');clearTimeout(voiceStopTimer);voiceStopTimer=setTimeout(function(){if(token===voiceToken&&recognition===instance){try{instance.stop();}catch(e){}}},VOICE_MAX_SESSION_MS);};
  instance.onresult=function(event){
    if(token!==voiceToken||recognition!==instance||activeTextarea!==textarea||sending)return;
    var newFinalText='',interim='';
    for(var i=event.resultIndex;i<event.results.length;i++){
      var value=(event.results[i][0].transcript||'').trim();
      if(event.results[i].isFinal){newFinalText+=(newFinalText?' ':'')+value;}else{interim+=(interim?' ':'')+value;}
    }
    if(newFinalText){committedText=[committedText,newFinalText].filter(Boolean).join(' ').replace(/\s+/g,' ').trim();}
    textarea.value=[base,committedText,interim].filter(Boolean).join(' ').replace(/\s+/g,' ').trim();
    textarea.dispatchEvent(new Event('input',{bubbles:true}));
    clearTimeout(voiceRestartTimer);
    voiceRestartTimer=setTimeout(function(){
      if(token===voiceToken&&recognition===instance&&!sending){try{instance.stop();}catch(e){}}
    },VOICE_SILENCE_GRACE_MS);
  };
  instance.onerror=function(event){if(token!==voiceToken)return;if(event&&event.error!=='aborted'&&event.error!=='no-speech'){toast('Voice recognition stopped: '+event.error,'warning');}};
  instance.onend=function(){if(token!==voiceToken)return;clearTimeout(voiceStopTimer);if(!sending&&activeTextarea===textarea&&button.classList.contains('is-listening')){voiceRestartTimer=setTimeout(function(){if(token!==voiceToken||sending||activeTextarea!==textarea)return;try{instance.start();}catch(e){recognition=null;activeTextarea=null;button.classList.remove('is-listening');button.setAttribute('aria-pressed','false');delete textarea.dataset.prchatVoiceToken;}},500);return;}recognition=null;activeTextarea=null;button.classList.remove('is-listening');button.setAttribute('aria-pressed','false');delete textarea.dataset.prchatVoiceToken;};
  try{instance.start();}catch(e){stopRecognition();toast('Voice recognition could not start.','danger');}
}
function parseResponse(response){
  return response.text().then(function(raw){
    var data;
    try{data=JSON.parse(raw);}catch(e){
      var clean=raw.replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim();
      throw new Error(clean||'The AI endpoint returned HTML instead of JSON.');
    }
    if(!response.ok||!data.success){throw new Error(data.message||('AI request failed ('+response.status+')'));}
    return data;
  });
}
function improve(button){
  var textarea=findTextarea(button);
  if(!textarea||textarea.disabled){toast('Open a conversation before using AI Improve.','warning');return;}
  var text=(textarea.value||'').trim();
  if(!text){toast('Write or dictate a message first.','warning');return;}
  stopRecognition(false);
  button.disabled=true;button.classList.add('is-loading');
  var payload=csrfPayload();
  payload.text=text;
  payload.context=contextFor(textarea);
  payload.language=(document.documentElement.lang||navigator.language||'en').slice(0,2);
  var url=window.prchatAiImproveUrl||window.PRCHAT_AI_IMPROVE_URL||((window.site_url||'/').replace(/\/?$/,'/')+'prchat/Prchat_Controller/improve_message');
  fetch(url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:new URLSearchParams(payload).toString()})
    .then(parseResponse)
    .then(function(data){textarea.value=data.text||text;textarea.dispatchEvent(new Event('input',{bubbles:true}));textarea.focus();})
    .catch(function(error){toast(error.message||'AI improvement failed.','danger');})
    .finally(function(){button.disabled=false;button.classList.remove('is-loading');});
}
document.addEventListener('click',function(event){
  var voice=event.target.closest('.prchat-voice-to-text');
  if(voice){event.preventDefault();event.stopPropagation();startVoice(voice);return;}
  var ai=event.target.closest('.prchat-improve-ai');
  if(ai){event.preventDefault();event.stopPropagation();improve(ai);return;}
},true);
window.addEventListener('beforeunload',function(){stopRecognition();});
})();
