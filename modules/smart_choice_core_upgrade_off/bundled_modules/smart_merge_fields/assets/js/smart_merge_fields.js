(function(){
  'use strict';
  function setComboValues(select){
    var value = select.value || '';
    var parts = value.split('||');
    var tableTarget = select.getAttribute('data-table-target');
    var fieldTarget = select.getAttribute('data-field-target');
    if(tableTarget){ var t = document.querySelector('input[name="'+tableTarget+'"]'); if(t){ t.value = parts[0] || ''; } }
    if(fieldTarget){ var f = document.querySelector('input[name="'+fieldTarget+'"]'); if(f){ f.value = parts[1] || ''; } }
  }
  document.addEventListener('change', function(e){ if(e.target.classList.contains('smf-combo')){ setComboValues(e.target); } });
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.smf-combo').forEach(setComboValues);
    var dragged = null;
    document.querySelectorAll('.smf-field-card').forEach(function(card){
      card.addEventListener('dragstart', function(){ dragged = card; });
    });
    document.querySelectorAll('.smf-drop-zone').forEach(function(zone){
      zone.addEventListener('dragover', function(e){ e.preventDefault(); zone.classList.add('smf-active'); });
      zone.addEventListener('dragleave', function(){ zone.classList.remove('smf-active'); });
      zone.addEventListener('drop', function(e){
        e.preventDefault(); zone.classList.remove('smf-active');
        if(!dragged){ return; }
        zone.innerHTML = '<div><strong>'+dragged.querySelector('strong').textContent+'</strong><br>'+dragged.querySelector('span').textContent+'<br><code>'+dragged.querySelector('code').textContent+'</code></div>';
      });
    });
  });
})();
