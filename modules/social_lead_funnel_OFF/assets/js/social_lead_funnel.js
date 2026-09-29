(function(){
  document.addEventListener('click', function(e){
    var copyBtn = e.target.closest('[data-slf-copy]');
    if(copyBtn){
      var selector = copyBtn.getAttribute('data-slf-copy');
      var el = document.querySelector(selector);
      if(el){ navigator.clipboard.writeText(el.value || el.textContent || ''); }
    }
  });
})();
