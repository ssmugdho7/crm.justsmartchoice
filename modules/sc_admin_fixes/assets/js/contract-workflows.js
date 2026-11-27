(function () {
 'use strict';
 function clean(menu) {
  menu.querySelectorAll('.modal,[role="dialog"]').forEach(function(dialog){document.body.appendChild(dialog);});
  // A dropdown is dismissed by Bootstrap. Close controls belong to their dialogs,
  // not among contract actions. Keep all real actions and any nested dialog intact.
  menu.querySelectorAll('button,a,[role="button"]').forEach(function(control){
   if(control.closest('.modal,[role="dialog"]') || control.matches('.scaf-contract-action-button,._delete'))return;
   var text=(control.textContent||'').trim();
   var dismiss=control.getAttribute('data-dismiss');
   var closeLabel=/^close$/i.test(control.getAttribute('aria-label')||'');
   if(/^[×✕✖]$/.test(text) && (control.classList.contains('close') || closeLabel || dismiss==='dropdown' || !control.hasAttribute('href'))){
    control.remove();
   }
  });
 }
 function init() {
  var menus=new Set();
  document.querySelectorAll('.scaf-contract-action').forEach(function(item){var menu=item.closest('.dropdown-menu');if(menu)menus.add(menu);});
  menus.forEach(function(menu){
   menu.classList.add('scaf-contract-menu');menu.parentElement.classList.add('scaf-contract-menu-owner');clean(menu);
   new MutationObserver(function(){clean(menu);}).observe(menu,{childList:true,subtree:true});
  });
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
