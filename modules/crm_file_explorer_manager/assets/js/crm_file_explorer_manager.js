(function(){
  document.addEventListener('click', function(e){
    var btn = e.target.closest('[data-fullscreen-target]');
    if(!btn){return;}
    var sel = btn.getAttribute('data-fullscreen-target');
    var el = document.querySelector(sel);
    if(el && el.requestFullscreen){el.requestFullscreen();}
  });
})();
