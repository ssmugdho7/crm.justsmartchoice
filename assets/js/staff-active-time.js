(function(w,d,$){
 'use strict';
 var lastInput=Date.now(),pending=false;
 function input(){lastInput=Date.now();}
 ['pointerdown','pointermove','keydown','scroll','touchstart'].forEach(function(event){d.addEventListener(event,input,{passive:true});});
 function tick(){
  if(pending)return;
  var active=!d.hidden&&Date.now()-lastInput<60000;
  var data={active:active?'1':'0'};
  if(typeof csrfData!=='undefined')data[csrfData.token_name]=csrfData.hash;
  pending=true;
  $.post(admin_url+'smart_choice_ui/active_time_heartbeat',data).fail(function(){if(w.console)console.warn('CRM active time could not be saved.');}).always(function(){pending=false;});
 }
 d.addEventListener('visibilitychange',tick);
 setInterval(tick,15000);tick();
})(window,document,jQuery);
