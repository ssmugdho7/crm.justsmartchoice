(function(){
  'use strict';
  var paused=false,loading=false;
  var button=document.getElementById('gv-pause');
  if(!button)return;
  button.addEventListener('click',function(){paused=!paused;button.setAttribute('aria-pressed',String(paused));button.textContent=paused?'Resume updates':'Pause updates';});
  setInterval(function(){
    if(paused||loading||document.hidden||document.querySelector('.gv-filters:focus-within'))return;
    loading=true;
    fetch(location.href,{credentials:'same-origin',cache:'no-store',redirect:'error'})
      .then(function(r){if(!r.ok)throw new Error('Report unavailable');return r.text();})
      .then(function(html){var next=new DOMParser().parseFromString(html,'text/html').getElementById('gv-results');var current=document.getElementById('gv-results');if(next&&current)current.replaceWith(next);else{paused=true;button.textContent='Reload to sign in';}})
      .catch(function(){button.textContent='Updates interrupted — resume';paused=true;button.setAttribute('aria-pressed','true');})
      .finally(function(){loading=false;});
  },15000);
})();
