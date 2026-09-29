(function(){
  'use strict';
  document.addEventListener('DOMContentLoaded', function(){
    var dangerForms = document.querySelectorAll('form ._delete');
    dangerForms.forEach(function(button){
      button.addEventListener('click', function(event){
        if(!confirm('Please confirm this action. Create a backup before deleting records.')){
          event.preventDefault();
        }
      });
    });
  });
})();
