(function(){'use strict';document.addEventListener('DOMContentLoaded',function(){var slugInput=document.querySelector('input[name="slug"]');var titleInput=document.querySelector('input[name="title"]');if(slugInput&&titleInput&&!slugInput.value){titleInput.addEventListener('blur',function(){if(!slugInput.value){slugInput.value=String(titleInput.value||'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');}});}document.querySelectorAll('.usi-check-all').forEach(function(box){box.addEventListener('change',function(){var form=box.closest('form');if(!form){return;}form.querySelectorAll('input[type="checkbox"][name="ids[]"]').forEach(function(row){row.checked=box.checked;});});});document.querySelectorAll('.usi-mass-form').forEach(function(form){form.addEventListener('submit',function(e){if(!form.querySelector('input[name="ids[]"]:checked')){e.preventDefault();alert('Select at least one row.');}});});document.querySelectorAll('.usi-view-report').forEach(function(btn){btn.addEventListener('click',function(){alert(btn.getAttribute('data-summary')||'Report');});});});})();
(function(){document.addEventListener('change',function(e){if(e.target && e.target.classList.contains('sc-check-all')){var table=e.target.closest('table');if(!table){return;}table.querySelectorAll('tbody input[type="checkbox"]').forEach(function(box){box.checked=e.target.checked;});}});})();

(function(){
  'use strict';

  function getCsrfPayload() {
    var payload = {};
    if (typeof csrfData !== 'undefined' && csrfData && csrfData.token_name) {
      payload[csrfData.token_name] = csrfData.hash;
    }
    return payload;
  }

  function postVoice(url, data, done) {
    var payload = getCsrfPayload();
    Object.keys(data || {}).forEach(function(key){ payload[key] = data[key]; });
    if (window.jQuery) {
      jQuery.post(url, payload).done(function(response){ done(response); }).fail(function(){ done({success:false,message:'CRM request failed.'}); });
    }
  }

  function postJson(url, data, done) { postVoice(url, data, done); }

  function voiceInit() {
    var start = document.getElementById('scVoiceStart');
    if (!start || !window.usiSmartChoiceVoiceUrls) { return; }
    var stop = document.getElementById('scVoiceStop');
    var preview = document.getElementById('scVoicePreview');
    var save = document.getElementById('scVoiceSave');
    var run = document.getElementById('scVoiceRun');
    var runEstimate = document.getElementById('scVoiceRunEstimate');
    var text = document.getElementById('scVoiceText');
    var result = document.getElementById('scVoiceResult');
    var status = document.getElementById('scVoiceStatus');
    var commandId = document.getElementById('scVoiceCommandId');
    var Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    var recognition = null;
    var isListening = false;
    var finalTranscript = '';
    var restartTimer = null;
    var settings = window.usiSmartChoiceVoiceSettings || {continuous:'0',language:'en-US',listenSeconds:0};

    function setStatus(value) { if (status) { status.textContent = value; } }
    function setResult(value) { if (result) { result.textContent = value; } }
    function cancelRestart(){ if(restartTimer){ window.clearTimeout(restartTimer); restartTimer=null; } }
    function syncExistingText(){ finalTranscript = (text && text.value ? text.value.trim() : ''); }
    function safeStart(){
      if (!recognition) { setResult('Speech recognition is not available. Type the command manually.'); return; }
      cancelRestart();
      try { recognition.start(); } catch (e) { setStatus('Listening'); }
    }

    if (!Recognition) {
      setStatus('Browser speech recognition is not supported. Use Chrome or Edge, or type the command manually.');
    } else {
      recognition = new Recognition();
      recognition.continuous = false;
      recognition.interimResults = true;
      recognition.maxAlternatives = 1;
      recognition.lang = settings.language || 'en-US';
      recognition.onstart = function(){ setStatus('Listening'); };
      recognition.onerror = function(event){
        var error = event && event.error ? event.error : 'unknown';
        if (error === 'no-speech') { setStatus('Listening - waiting for your voice'); return; }
        setStatus('Microphone error: ' + error);
      };
      recognition.onend = function(){
        if (isListening) {
          setStatus('Listening - microphone stays on');
          restartTimer = window.setTimeout(safeStart, 250);
          return;
        }
        setStatus('Microphone Off');
      };
      recognition.onresult = function(event){
        var interim = '';
        for (var i = event.resultIndex; i < event.results.length; i++) {
          var phrase = event.results[i][0].transcript || '';
          if (event.results[i].isFinal) {
            finalTranscript = (finalTranscript + ' ' + phrase).replace(/\s+/g, ' ').trim();
          } else {
            interim += phrase;
          }
        }
        if (text) { text.value = (finalTranscript + ' ' + interim).replace(/\s+/g, ' ').trim(); }
        setResult('Command captured. Press Preview, Run Command, or Run Estimate.');
      };
    }

    start.addEventListener('click', function(){
      syncExistingText();
      isListening = true;
      safeStart();
    });
    if (stop) { stop.addEventListener('click', function(){ isListening = false; cancelRestart(); if (recognition) { try { recognition.stop(); } catch(e){} } setStatus('Microphone Off'); }); }
    if (preview) { preview.addEventListener('click', function(){
      postVoice(window.usiSmartChoiceVoiceUrls.preview, {command_text:text.value}, function(response){
        setResult((response && (response.preview || response.message)) ? (response.preview || response.message) : 'No preview returned.');
      });
    }); }
    if (save) { save.addEventListener('click', function(){
      postVoice(window.usiSmartChoiceVoiceUrls.save, {command_text:text.value}, function(response){
        if (response && response.command_id) { commandId.value = response.command_id; }
        setResult((response && response.message) ? response.message : 'Command saved.');
      });
    }); }
    if (run) { run.addEventListener('click', function(){
      if (!window.confirm('Run this voice command in the CRM?')) { return; }
      postVoice(window.usiSmartChoiceVoiceUrls.execute, {command_text:text.value, command_id:commandId.value}, function(response){
        setResult((response && response.message) ? response.message : 'Command processed.');
        if (response && response.redirect_url) { window.location.href = response.redirect_url; }
      });
    }); }
    if (runEstimate) { runEstimate.addEventListener('click', function(){
      if (!window.confirm('Run this as an AI estimate draft?')) { return; }
      postVoice(window.usiSmartChoiceVoiceUrls.runEstimate, {command_text:text.value}, function(response){
        setResult((response && response.message) ? response.message : 'Estimate processed.');
        if (response && response.redirect_url) { window.location.href = response.redirect_url; }
      });
    }); }
  }

  function cameraInit() {
    var start = document.getElementById('scCameraStart');
    if (!start) { return; }
    var stop = document.getElementById('scCameraStop');
    var video = document.getElementById('scCameraPreview');
    var status = document.getElementById('scCameraStatus');
    var stream = null;
    function setStatus(value) { if (status) { status.textContent = value; } }
    start.addEventListener('click', function(){
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { setStatus('Camera API is not supported in this browser. Use the photo upload field.'); return; }
      navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false }).then(function(s){
        stream = s;
        if (video) { video.srcObject = s; }
        setStatus('Camera Active');
      }).catch(function(){ setStatus('Camera permission denied or unavailable. Use the photo upload field.'); });
    });
    if (stop) { stop.addEventListener('click', function(){
      if (stream) { stream.getTracks().forEach(function(track){ track.stop(); }); stream = null; }
      if (video) { video.srcObject = null; }
      setStatus('Camera Off');
    }); }
  }

  function settingsInit() {
    var toggle = document.getElementById('scToggleApiKey');
    var key = document.getElementById('usi_smartchoice_ai_api_key');
    var check = document.getElementById('scCheckApiKey');
    var result = document.getElementById('scApiKeyResult');
    if (toggle && key) { toggle.addEventListener('click', function(){ key.type = key.type === 'password' ? 'text' : 'password'; toggle.textContent = key.type === 'password' ? 'Show' : 'Hide'; }); }
    if (check && window.usiSmartChoiceApiKeyCheckUrl) { check.addEventListener('click', function(){
      if (result) { result.textContent = 'Checking API key...'; }
      postJson(window.usiSmartChoiceApiKeyCheckUrl, {}, function(response){ if (result) { result.textContent = (response && response.message) ? response.message : 'No API key response returned.'; } });
    }); }
  }

  document.addEventListener('DOMContentLoaded', function(){ voiceInit(); cameraInit(); settingsInit(); });
})();
