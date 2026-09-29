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
    if (!start) { return; }
    var config = document.getElementById('scVoiceConfig');
    var urls = window.usiSmartChoiceVoiceUrls || {
      preview: config ? config.getAttribute('data-preview-url') : '',
      save: config ? config.getAttribute('data-save-url') : '',
      execute: config ? config.getAttribute('data-execute-url') : '',
      runEstimate: config ? config.getAttribute('data-run-estimate-url') : ''
    };
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
    var settings = window.usiSmartChoiceVoiceSettings || {continuous:(config ? config.getAttribute('data-continuous') : '1'),language:(config ? config.getAttribute('data-language') : 'en-US'),listenSeconds:(config ? parseInt(config.getAttribute('data-listen-seconds')||'0',10) : 0)};

    function setStatus(value) { if (status) { status.textContent = value; } }
    function setResult(value) { if (result) { result.textContent = value; } }
    function cancelRestart(){ if(restartTimer){ window.clearTimeout(restartTimer); restartTimer=null; } }
    function normalizeVoiceCommand(value){
      value = String(value || '').replace(/\s+/g, ' ').trim();
      if (!value) { return ''; }
      var replacements = [
        [/\bcoma\b/gi, ','], [/\bcomma\b/gi, ','], [/\bperiod\b/gi, '.'], [/\bfull stop\b/gi, '.'], [/\bquestion mark\b/gi, '?'], [/\bexclamation mark\b/gi, '!'],
        [/\bat sign\b/gi, '@'], [/\bdot com\b/gi, '.com'], [/\bdot net\b/gi, '.net'], [/\bdot org\b/gi, '.org'],
        [/\bjust smart choice\b/gi, 'justsmartchoice'], [/\bsammy a i\b/gi, 'Sammy AI'], [/\bsami a i\b/gi, 'Sammy AI'], [/\bsammy ai\b/gi, 'Sammy AI'],
        [/\bestimated\b/gi, 'estimate'], [/\bextimate\b/gi, 'estimate'], [/\bestimado\b/gi, 'estimate'], [/\bcostumer\b/gi, 'customer'],
        [/\bpluming\b/gi, 'plumbing'], [/\bdry wall\b/gi, 'drywall'], [/\bsheet rock\b/gi, 'sheetrock'], [/\bremodle\b/gi, 'remodel'],
        [/\bwindow\s+seal\b/gi, 'window sill'], [/\bpaint and key\b/gi, 'pin and key'], [/\bpencil and key\b/gi, 'pin and key']
      ];
      replacements.forEach(function(pair){ value = value.replace(pair[0], pair[1]); });
      value = value.replace(/\s+([,.?!])/g, '$1').replace(/([,.?!])(?=\S)/g, '$1 ');
      value = value.replace(/\bi\b/g, 'I');
      value = value.replace(/(^\s*[a-z])|([.!?]\s+[a-z])/g, function(match){ return match.toUpperCase(); });
      return value.trim();
    }
    function syncExistingText(){ finalTranscript = (text && text.value ? normalizeVoiceCommand(text.value) : ''); }
    function safeStart(){
      if (!recognition) { setResult('Speech recognition is not available. Type the command manually.'); return; }
      cancelRestart();
      try { recognition.start(); } catch (e) { setStatus('Listening'); }
    }

    if (text) {
      text.addEventListener('input', function(){
        finalTranscript = normalizeVoiceCommand(text.value || '');
      });
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
        if (text) { text.value = normalizeVoiceCommand((finalTranscript + ' ' + interim).replace(/\s+/g, ' ').trim()); }
        setResult('Command captured. Press Preview, Run Command, or Run Estimate.');
      };
    }

    start.addEventListener('click', function(){
      syncExistingText();
      isListening = true;
      safeStart();
    });
    if (stop) { stop.addEventListener('click', function(){ isListening = false; cancelRestart(); syncExistingText(); if (recognition) { try { recognition.stop(); } catch(e){} } setStatus('Microphone Off'); }); }
    if (preview) { preview.addEventListener('click', function(){
      postVoice(urls.preview, {command_text:text.value}, function(response){
        setResult((response && (response.preview || response.message)) ? (response.preview || response.message) : 'No preview returned.');
      });
    }); }
    if (save) { save.addEventListener('click', function(){
      postVoice(urls.save, {command_text:text.value}, function(response){
        if (response && response.command_id) { commandId.value = response.command_id; }
        setResult((response && response.message) ? response.message : 'Command saved.');
      });
    }); }
    if (run) { run.addEventListener('click', function(){
      if (!window.confirm('Run this voice command in the CRM?')) { return; }
      postVoice(urls.execute, {command_text:text.value, command_id:commandId.value}, function(response){
        setResult((response && response.message) ? response.message : 'Command processed.');
        if (response && response.redirect_url) { window.location.href = response.redirect_url; }
      });
    }); }
    if (runEstimate) { runEstimate.addEventListener('click', function(){
      if (!window.confirm('Run this as an AI estimate draft?')) { return; }
      postVoice(urls.runEstimate, {command_text:text.value}, function(response){
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
    if (check) { check.addEventListener('click', function(){
      if (result) { result.textContent = 'Checking API key...'; }
      var checkUrl = window.usiSmartChoiceApiKeyCheckUrl || (result ? result.getAttribute('data-check-url') : ''); if (!checkUrl) { if (result) { result.textContent = 'API key check URL is not configured.'; } return; } postJson(checkUrl, {}, function(response){ if (result) { result.textContent = (response && response.message) ? response.message : 'No API key response returned.'; } });
    }); }
  }

  document.addEventListener('DOMContentLoaded', function(){ voiceInit(); cameraInit(); settingsInit(); });
})();


(function(){
  'use strict';
  function speakPreview(text, lang) {
    if (!('speechSynthesis' in window)) { alert('This browser does not support voice preview.'); return; }
    window.speechSynthesis.cancel();
    var utterance = new SpeechSynthesisUtterance(String(text || 'Smart Choice Contractors USA sample voice preview.').substring(0, 260));
    utterance.lang = lang || 'en-US';
    window.speechSynthesis.speak(utterance);
    window.setTimeout(function(){ window.speechSynthesis.cancel(); }, 10000);
  }
  document.addEventListener('click', function(e){
    var btn = e.target && e.target.closest ? e.target.closest('.sc-preview-voice') : null;
    if (!btn) { return; }
    speakPreview(btn.getAttribute('data-text'), btn.getAttribute('data-lang'));
  });
})();

(function(){
  'use strict';
  document.addEventListener('DOMContentLoaded', function(){
    var cfg = document.getElementById('scVoiceOrchestratorConfig');
    if (!cfg || !window.jQuery) { return; }
    var $ = window.jQuery;
    var recognition = null;
    var manuallyStopped = false;
    var csrfName = cfg.getAttribute('data-csrf-name') || '';
    var csrfHash = cfg.getAttribute('data-csrf-hash') || '';
    function post(url, data, cb) {
      data = data || {};
      if (csrfName) { data[csrfName] = csrfHash; }
      $.post(url, data, function(resp){
        if (resp && resp.csrfHash) { csrfHash = resp.csrfHash; }
        cb(resp || {});
      }, 'json').fail(function(){ cb({success:false,message:'Request failed.'}); });
    }
    function status(msg, type) {
      $('#sammy-voice-status').removeClass('alert-info alert-success alert-warning alert-danger').addClass('alert-' + type).text(msg);
    }
    function ensureRecognition() {
      var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRecognition) { status('This browser does not support speech recognition. Use Chrome or Edge on desktop/mobile.', 'danger'); return null; }
      recognition = new SpeechRecognition();
      recognition.lang = $('#sammy-language-code').val() || 'en-US';
      recognition.continuous = true;
      recognition.interimResults = true;
      recognition.maxAlternatives = 1;
      recognition.onstart = function(){ status('Listening. The microphone will stay on until you press Stop.', 'success'); };
      recognition.onerror = function(e){ status('Microphone error: ' + (e.error || 'unknown'), 'danger'); };
      recognition.onend = function(){ if (!manuallyStopped) { try { recognition.start(); } catch(e) {} } else { status('Microphone stopped.', 'warning'); } };
      recognition.onresult = function(event){
        var finalText = '';
        var interimText = '';
        for (var i = event.resultIndex; i < event.results.length; ++i) {
          var part = event.results[i][0].transcript;
          if (event.results[i].isFinal) { finalText += part; } else { interimText += part; }
        }
        if (finalText !== '') {
          var existing = $('#sammy-live-transcript').val();
          $('#sammy-live-transcript').val((existing ? existing + ' ' : '') + finalText.trim());
        } else if (interimText !== '') {
          status('Listening: ' + interimText, 'info');
        }
      };
      return recognition;
    }
    $('#sammy-start-listening').on('click', function(){
      manuallyStopped = false;
      post(cfg.getAttribute('data-start-url'), {session_title: $('#sammy-session-title').val(), language_code: $('#sammy-language-code').val(), session_mode: 'continuous'}, function(resp){
        if (resp.success) { $('#sammy-voice-session-id').val(resp.session_id); var r = ensureRecognition(); if (r) { try { r.start(); } catch(e) {} } }
        else { status(resp.message || 'Could not start session.', 'danger'); }
      });
    });
    $('#sammy-stop-listening').on('click', function(){
      manuallyStopped = true;
      if (recognition) { recognition.stop(); }
      post(cfg.getAttribute('data-stop-url'), {session_id: $('#sammy-voice-session-id').val()}, function(resp){ status(resp.message || 'Stopped.', 'warning'); });
    });
    $('#sammy-save-transcript').on('click', function(){
      post(cfg.getAttribute('data-transcript-url'), {session_id: $('#sammy-voice-session-id').val(), transcript_text: $('#sammy-live-transcript').val(), confidence_score: 0}, function(resp){
        if (resp.success) { $('#sammy-last-transcript-id').val(resp.transcript_id); status(resp.message + ' Intent: ' + resp.intent, 'success'); }
        else { status(resp.message || 'Could not save command.', 'danger'); }
      });
    });
    $('#sammy-run-route').on('click', function(){
      var transcriptId = $('#sammy-last-transcript-id').val();
      if (!transcriptId || transcriptId === '0') { $('#sammy-save-transcript').trigger('click'); status('Save the command first, then press Run Route again.', 'warning'); return; }
      post(cfg.getAttribute('data-execute-url'), {transcript_id: transcriptId}, function(resp){
        if (resp.success && resp.redirect_url) { window.location.href = resp.redirect_url; }
        else { status(resp.message || 'Could not run route.', 'danger'); }
      });
    });
    $('#sammy-clear-transcript').on('click', function(){ $('#sammy-live-transcript').val(''); $('#sammy-last-transcript-id').val('0'); status('Transcript cleared.', 'info'); });
  });
})();

(function(){
  'use strict';
  document.addEventListener('DOMContentLoaded', function(){
    var address = document.getElementById('scJobLocation');
    if (address && window.google && google.maps && google.maps.places) {
      try { new google.maps.places.Autocomplete(address, {types:['geocode']}); } catch(e) {}
    }
  });
})();

(function(){
  'use strict';
  document.addEventListener('DOMContentLoaded', function(){
    var nav = document.getElementById('scSammyNav');
    if (!nav) { return; }
    function setMobileNavState(){
      var links = nav.querySelectorAll('.sc-nav-links');
      for (var i = 0; i < links.length; i++) {
        if (window.innerWidth <= 767) { links[i].classList.remove('in'); }
        else { links[i].classList.add('in'); }
      }
    }
    setMobileNavState();
    window.addEventListener('resize', function(){ window.clearTimeout(window.scSammyNavTimer); window.scSammyNavTimer = window.setTimeout(setMobileNavState, 180); });
  });
})();


(function(){
  'use strict';
  function initFloatingAssistant(){
    var root=document.getElementById('sammyAiFloatingAssistant'); if(!root){return;}
    var toggle=root.querySelector('.sammy-ai-float-toggle'), close=root.querySelector('.sammy-ai-float-close'), send=root.querySelector('.sammy-ai-send'), mic=root.querySelector('.sammy-ai-mic'), input=root.querySelector('textarea'), messages=root.querySelector('.sammy-ai-float-messages');
    function setOpen(open){root.classList.toggle('open',open);var panel=root.querySelector('.sammy-ai-float-panel');if(panel){panel.setAttribute('aria-hidden',open?'false':'true');}if(open&&input){setTimeout(function(){input.focus();},100);}}
    function add(text,role){var d=document.createElement('div');d.className='sammy-ai-message '+role;d.textContent=text;messages.appendChild(d);messages.scrollTop=messages.scrollHeight;}
    function run(command){command=String(command||'').trim();if(!command){return;}add(command,'user');input.value='';var data=new FormData();data.append('command',command);var csrfName=root.getAttribute('data-csrf-name'),csrfHash=root.getAttribute('data-csrf-hash');if(csrfName){data.append(csrfName,csrfHash);}send.disabled=true;fetch(root.getAttribute('data-command-url'),{method:'POST',body:data,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(resp){if(resp.csrfHash){root.setAttribute('data-csrf-hash',resp.csrfHash);}add(resp.message||'Command received.','assistant');}).catch(function(){add('The assistant could not complete the request. Open the Message Center for details.','assistant');}).finally(function(){send.disabled=false;});}
    toggle.addEventListener('click',function(){setOpen(!root.classList.contains('open'));});close.addEventListener('click',function(){setOpen(false);});send.addEventListener('click',function(){run(input.value);});input.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();run(input.value);}});root.querySelectorAll('.sammy-ai-quick-actions button').forEach(function(b){b.addEventListener('click',function(){run(b.getAttribute('data-command'));});});
    if(mic){mic.addEventListener('click',function(){var R=window.SpeechRecognition||window.webkitSpeechRecognition;if(!R){add('Voice input is not supported in this browser. Use Chrome or Edge, or type the command.','assistant');return;}var rec=new R();rec.lang='en-US';rec.interimResults=false;rec.onresult=function(e){input.value=e.results[0][0].transcript;run(input.value);};rec.onerror=function(){add('Microphone permission was denied or unavailable.','assistant');};rec.start();});}
  }
  document.addEventListener('DOMContentLoaded',initFloatingAssistant);
})();
