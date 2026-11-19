(function () {
 'use strict';
 function init() {
  document.querySelectorAll('.scaf-contract-action').forEach(function(item){
   var menu=item.closest('.dropdown-menu');
   if(!menu)return;
   menu.classList.add('scaf-contract-menu');
   // The positioned dropdown owner must outrank AJAX field clear controls.
   menu.parentElement.classList.add('scaf-contract-menu-owner');
  });
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
