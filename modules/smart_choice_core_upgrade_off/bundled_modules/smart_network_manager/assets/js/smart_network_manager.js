(function(){
  document.addEventListener('click', function(e){
    var el = e.target.closest('.snm-action-buttons .btn-warning');
    if(el && !confirm('This may block a device from the internet. Continue only if you are sure.')){
      e.preventDefault();
    }
  });
})();
