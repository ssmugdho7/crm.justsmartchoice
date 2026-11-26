(function($){'use strict';
var device=null,activeCall=null,ready=false,connecting=false;
function fmt(v){var d=(v||'').replace(/\D/g,'');if(d.length>0&&d[0]==='1')d=d.substring(1);d=d.substring(0,10);var o='+1 ';if(d.length)o+='('+d.substring(0,3);if(d.length>=3)o+=') ';if(d.length>3)o+=d.substring(3,6);if(d.length>=6)o+='-';if(d.length>6)o+=d.substring(6,10);return o;}
function setState(text,type){$('.sccc-dialer-title .label').text(text||'').removeClass('label-success label-warning label-danger label-info').addClass(type||'label-info');}
function idleState(){setState(ready?'READY':'OFFLINE',ready?'label-success':'label-warning');}
function watchCall(call){
 activeCall=call;$('#scccCallButton, .sccc-directory-call').prop('disabled',true);$('#scccHangup').prop('disabled',false);
 function ended(){if(activeCall!==call)return;activeCall=null;$('#scccCallButton, .sccc-directory-call').prop('disabled',false);$('#scccHangup').prop('disabled',true);idleState();}
 call.on('accept',function(){setState('ON CALL','label-info');});
 ['disconnect','cancel','reject'].forEach(function(event){call.on(event,ended);});
 call.on('error',function(e){ended();alert_float('danger',(e&&e.message)||'Call failed');});
}
async function getToken(){var r=await fetch(window.scccTokenUrl,{credentials:'same-origin'});if(!r.ok)throw new Error('Unable to load browser calling credentials.');var j=await r.json();if(!j.success||!j.token)throw new Error('Configure Twilio browser credentials in Call Center settings.');return j.token;}
async function initDevice(){
 setState('CONNECTING','label-warning');$('#scccHangup').prop('disabled',true);
 if(!window.scccBrowserEnabled||!window.Twilio||!Twilio.Device||!window.scccTokenUrl){setState('NOT CONFIGURED','label-warning');return;}
 try{
  var token=await getToken();device=new Twilio.Device(token,{codecPreferences:['opus','pcmu'],closeProtection:true,enableImprovedSignalingErrorPrecision:true});
  device.on('registered',function(){ready=true;if(!activeCall)idleState();});
  device.on('unregistered',function(){ready=false;if(!activeCall)idleState();});
  device.on('error',function(e){ready=false;setState('ERROR','label-danger');if(window.console)console.error('Call Center device error',e);});
  device.on('tokenWillExpire',async function(){try{device.updateToken(await getToken());}catch(e){ready=false;setState('TOKEN EXPIRED','label-danger');}});
  device.on('incoming',function(call){if(activeCall||connecting){call.reject();return;}watchCall(call);setState('RINGING','label-warning');if(window.confirm((window.scccIncomingCall||'Incoming call')+' '+((call.parameters&&call.parameters.From)||''))){call.accept();}else{call.reject();}});
  await device.register();
 }catch(e){ready=false;setState('NOT CONFIGURED','label-warning');if(window.console)console.error('Call Center initialization failed:',e.message);}
}
$(document).on('click','.sccc-key',function(){var $p=$('#scccPhone'),raw=$p.val()+$(this).data('key');if(/[\*#]/.test(raw)){$p.val(raw);return;}$p.val(fmt(raw));});
$(document).on('input','#scccPhone',function(){if(!/[\*#]/.test(this.value))this.value=fmt(this.value);});
$(document).on('click','#scccBackspace',function(){var $p=$('#scccPhone'),d=$p.val().replace(/\D/g,'');if(d.length&&d[0]==='1')d=d.substring(1);d=d.slice(0,-1);$p.val(fmt(d));});
async function startCall(phone){
 if(!phone||activeCall||connecting)return;
 if(!device||!ready){alert_float('warning',window.scccSoftphoneNotReady||'Browser softphone is not ready. Check Twilio settings and microphone permission.');return;}
 connecting=true;$('#scccCallButton, .sccc-directory-call').prop('disabled',true);setState('CALLING','label-warning');
 try{watchCall(await device.connect({params:{To:phone}}));}catch(e){$('#scccCallButton, .sccc-directory-call').prop('disabled',false);idleState();alert_float('danger',(e&&e.message)||'Call failed');}finally{connecting=false;}
}
$(document).on('click','#scccCallButton',function(){return startCall($('#scccPhone').val());});
$(document).on('click','.sccc-directory-call',function(){
 if(activeCall||connecting)return;
 var phone=$(this).attr('data-phone');$('#scccPhone').val(fmt(phone));return startCall(phone);
});
$(document).on('click','#scccHangup',function(){if(activeCall){if(activeCall.status()==='pending')activeCall.reject();else activeCall.disconnect();}});
if(window.scccChartData&&window.Chart&&document.getElementById('scccCallChart')){new Chart(document.getElementById('scccCallChart'),{type:'doughnut',data:{labels:['Answered','Other','Successful'],datasets:[{data:window.scccChartData}]},options:{responsive:true,plugins:{legend:{position:'bottom'}}}});}
if(window.scccOutcome&&window.Chart&&document.getElementById('scccOutcomeChart')){new Chart(document.getElementById('scccOutcomeChart'),{type:'bar',data:{labels:window.scccOutcome.map(x=>x.d),datasets:[{label:'Calls',data:window.scccOutcome.map(x=>x.c)}]},options:{responsive:true,plugins:{legend:{display:false}}}});}
function linkLeadCalls(root) {
 // Lead Manager blocks calls for staff without their own Twilio number; send those calls to the Call Center dialer.
 var blockedCalls = root.querySelectorAll('tr[id^="lead_"] li.fa-stack > a, .table-clients li.fa-stack > a');
 Array.prototype.forEach.call(blockedCalls, function(link) {
  var blockedIcon = link.querySelector('.fa-ban');
  if (!blockedIcon || !link.querySelector('.fa-phone')) return;
  var phoneLink = link.closest('tr').querySelector('a[href^="tel:"]');
  var cell = link.closest('td');
  var raw = phoneLink ? phoneLink.getAttribute('href').slice(4) : (cell ? (cell.textContent || '').split(/[\n,;\/]/)[0] : '');
  var digits = raw.replace(/\D/g, '');
  if (!/^(1)?[2-9]\d{9}$/.test(digits)) return;
  link.href = window.scccLeadDialerUrl + '#phone=' + digits.slice(-10);
  link.title = window.scccLeadDialerLabel;
  link.setAttribute('aria-label', link.title);
  blockedIcon.remove();
  link.querySelector('.fa-phone').classList.remove('fa-stack-1x');
  link.parentElement.classList.remove('fa-stack');
 });
}
if (window.scccLeadDialerUrl) {
 $(function() { linkLeadCalls(document); });
 $(document).on('draw.dt', function(event) { linkLeadCalls(event.target); });
}
$(function() {
 var phone = document.getElementById('scccPhone');
 if (!phone) return;
 var match = /^#phone=([2-9]\d{9})$/.exec(window.location.hash);
 if (!match) return;
 phone.value = fmt(match[1]);
 window.history.replaceState(window.history.state, '', window.location.pathname + window.location.search);
});
// jQuery 3.1.1 does not classify AsyncFunction as a ready callback.
$(function(){return initDevice();});
})(jQuery);
