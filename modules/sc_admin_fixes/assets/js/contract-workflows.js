(function () {
 'use strict';
 function init() {
  document.querySelectorAll('.scaf-contract-action').forEach(function(item){var menu=item.closest('.dropdown-menu');if(menu)menu.classList.add('scaf-contract-menu');});
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
