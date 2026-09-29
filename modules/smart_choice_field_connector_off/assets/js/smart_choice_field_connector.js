(function(){
  document.addEventListener('click',function(e){
    var btn=e.target.closest('.scfc-copy');
    if(!btn){return;}
    e.preventDefault();
    var text=btn.getAttribute('data-copy')||'';
    if(navigator.clipboard){navigator.clipboard.writeText(text);}else{var t=document.createElement('textarea');t.value=text;document.body.appendChild(t);t.select();document.execCommand('copy');document.body.removeChild(t);}
    btn.innerHTML='Copied';
    setTimeout(function(){btn.innerHTML='Copy';},1200);
  });
})();
