(function(){
  'use strict';
  function copyPrevious(button){
    var target = button.previousElementSibling;
    if (!target) return;
    target.select();
    document.execCommand('copy');
    if (typeof alert_float === 'function') alert_float('success','Copied.');
  }
  document.addEventListener('click', function(event){
    var button = event.target.closest ? event.target.closest('.sc-copy-prev') : null;
    if (button) copyPrevious(button);
  });
})();
