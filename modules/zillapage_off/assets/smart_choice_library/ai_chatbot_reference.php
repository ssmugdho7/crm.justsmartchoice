<!-- Smart Choice AI Chatbot Library -->
<div id="smart-ai-chat" aria-live="polite">
  <button id="smart-ai-open" type="button" aria-label="Open Smart Choice Assistant">
    <img src="/images/smart-choice-ai-assistant.png" alt="Smart Choice Assistant" class="smart-ai-launch-avatar">
  </button>

  <div id="smart-ai-box" role="dialog" aria-label="Smart Choice Assistant" aria-modal="false">
    <div id="smart-ai-head">
      <div class="smart-ai-avatar"><img src="/images/smart-choice-ai-assistant.png" alt="Smart Choice AI Assistant"></div>
      <div class="smart-ai-title"><strong>Smart Choice Assistant</strong><small>Customer Support, Estimating & Booking</small></div>
      <button id="smart-ai-close" type="button" aria-label="Close chat">×</button>
    </div>

    <div id="smart-ai-mode">
      <button type="button" data-mode="customer_service" class="active">Support</button>
      <button type="button" data-mode="estimating">Estimate</button>
      <button type="button" data-mode="appointment">Book Appointment</button>
    </div>

    <div id="smart-ai-messages">
      <div class="smart-ai-message bot">Hello. I can help with customer support, services, project questions, preliminary estimating, and appointments. How can I help?</div>
    </div>

    <div id="smart-ai-input-area">
      <textarea id="smart-ai-input" placeholder="Type your question..." rows="2"></textarea>
      <button id="smart-ai-send" type="button">Send</button>
    </div>
  </div>
</div>

<style>
#smart-ai-chat{position:fixed;right:22px;bottom:22px;z-index:99999;font-family:Arial,Helvetica,sans-serif}
#smart-ai-open{width:64px;height:64px;border-radius:50%;border:3px solid #ef8c25;background:#169179;color:#fff;font-weight:900;cursor:pointer;box-shadow:0 12px 30px rgba(0,0,0,.28);padding:0;overflow:hidden}
#smart-ai-open img,.smart-ai-launch-avatar{width:100%;height:100%;object-fit:cover;border-radius:50%}
#smart-ai-box{display:none;width:340px;max-width:calc(100vw - 24px);height:545px;max-height:calc(100vh - 95px);background:#fff;border:3px solid #ef8c25;border-radius:18px;box-shadow:0 18px 45px rgba(0,0,0,.30);overflow:hidden;position:relative}
#smart-ai-head{background:linear-gradient(135deg,#169179,#0057b8);color:#fff;padding:12px 46px 12px 12px;display:flex;align-items:center;gap:10px;min-height:76px}
.smart-ai-avatar{width:48px;height:48px;border-radius:50%;border:3px solid #ef8c25;overflow:hidden;background:#fff;flex:0 0 48px}.smart-ai-avatar img{width:100%;height:100%;object-fit:cover}.smart-ai-title strong{display:block;font-size:15px;line-height:1.1}.smart-ai-title small{display:block;font-size:11.5px;opacity:.92;line-height:1.25;margin-top:3px}
#smart-ai-close{position:absolute;right:10px;top:8px;background:transparent;border:0;color:#fff;font-size:28px;line-height:1;cursor:pointer}
#smart-ai-mode{display:flex;gap:5px;padding:9px;background:#f6f7f8}#smart-ai-mode button{flex:1;border:1px solid #ddd;background:#fff;padding:8px 5px;border-radius:999px;cursor:pointer;font-weight:800;font-size:11px;line-height:1.1}#smart-ai-mode button.active{background:#ef8c25;color:#fff;border-color:#ef8c25}
#smart-ai-messages{height:315px;overflow-y:auto;padding:12px;background:#fff}.smart-ai-message{padding:9px 11px;margin-bottom:9px;border-radius:14px;font-size:13.5px;line-height:1.4}.smart-ai-message.bot{background:#f0f7f5;color:#18211f;border-left:4px solid #169179}.smart-ai-message.user{background:#0057b8;color:#fff;margin-left:35px}
#smart-ai-input-area{display:flex;gap:7px;padding:10px;border-top:1px solid #eee;background:#fafafa;position:absolute;left:0;right:0;bottom:0;min-height:78px}#smart-ai-input{flex:1;height:56px;min-height:56px;max-height:56px;resize:none;border:1px solid #ccc;border-radius:12px;padding:10px;font-size:14px;background:#fff;color:#111;outline:none}#smart-ai-input:focus{border-color:#0057b8;box-shadow:0 0 0 2px rgba(0,87,184,.12)}#smart-ai-send{width:62px;min-width:62px;border:0;border-radius:12px;background:#ef8c25;color:#fff;font-weight:900;cursor:pointer;font-size:13px}
@media(max-width:767px){#smart-ai-chat{right:10px;bottom:10px}#smart-ai-open{width:58px;height:58px}#smart-ai-box{position:fixed;right:8px;left:8px;bottom:8px;width:auto;max-width:none;height:78vh;max-height:620px;border-radius:16px}#smart-ai-head{min-height:70px;padding:10px 44px 10px 10px}.smart-ai-avatar{width:44px;height:44px;flex-basis:44px}#smart-ai-messages{height:calc(78vh - 224px);min-height:220px;padding:10px}#smart-ai-input-area{min-height:82px;padding:10px}#smart-ai-input{font-size:16px;height:58px;min-height:58px;max-height:58px}#smart-ai-send{width:60px;min-width:60px}.smart-ai-message{font-size:13.5px}}
</style>

<script>
(function(){
  'use strict';
  var box=document.getElementById('smart-ai-box'),open=document.getElementById('smart-ai-open'),close=document.getElementById('smart-ai-close'),send=document.getElementById('smart-ai-send'),input=document.getElementById('smart-ai-input'),messages=document.getElementById('smart-ai-messages'),mode='customer_service';
  if(!box||!open||!close||!send||!input||!messages){return;}
  function addMessage(text,who){var div=document.createElement('div');div.className='smart-ai-message '+who;div.textContent=text;messages.appendChild(div);messages.scrollTop=messages.scrollHeight;}
  function removeChecking(){var bot=messages.querySelectorAll('.smart-ai-message.bot');if(bot.length&&bot[bot.length-1].textContent==='Checking Smart Choice website information...'){bot[bot.length-1].remove();}}
  function openBox(){box.style.display='block';open.style.display='none';setTimeout(function(){input.focus();},180);}function closeBox(){box.style.display='none';open.style.display='block';}
  open.addEventListener('click',openBox);close.addEventListener('click',closeBox);
  document.querySelectorAll('#smart-ai-mode button').forEach(function(btn){btn.addEventListener('click',function(){document.querySelectorAll('#smart-ai-mode button').forEach(function(b){b.classList.remove('active');});btn.classList.add('active');mode=btn.getAttribute('data-mode')||'customer_service';if(mode==='appointment'){window.open('https://crm.justsmartchoice.com/appointly/appointments_public/book?col=col-md-8+col-md-offset-2','_blank','noopener');addMessage('Appointment link opened. You can also call (727) 755-3786.','bot');}});});
  function sendMessage(){var text=(input.value||'').trim();if(!text){input.focus();return;}addMessage(text,'user');input.value='';if(mode==='appointment'){addMessage('You can book an appointment here: https://crm.justsmartchoice.com/appointly/appointments_public/book?col=col-md-8+col-md-offset-2','bot');window.open('https://crm.justsmartchoice.com/appointly/appointments_public/book?col=col-md-8+col-md-offset-2','_blank','noopener');return;}addMessage('Checking Smart Choice website information...','bot');fetch('/AI/chat-handler.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:text,mode:mode})}).then(function(res){return res.json();}).then(function(data){removeChecking();addMessage((data&&data.reply)?data.reply:'Please call (727) 755-3786 for help.','bot');}).catch(function(){removeChecking();addMessage('The assistant is temporarily unavailable online, but Smart Choice Contractors USA can help by phone at (727) 755-3786 or through the appointment link.','bot');});}
  send.addEventListener('click',sendMessage);input.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMessage();}});
})();
</script>
