(function(){
 function ready(fn){if(document.readyState!=='loading'){fn();}else{document.addEventListener('DOMContentLoaded',fn);}}
 ready(function(){
   document.body.classList.add('smart-choice-normalized-module');
   document.querySelectorAll('a[]').forEach(function(a){ if(a.closest('.favorite-links, .favorite_links, [data-smart-choice-same-tab]')) a.removeAttribute('target'); });
   document.querySelectorAll('.btn').forEach(function(b){ b.style.backgroundImage='none'; });
 });
})();
