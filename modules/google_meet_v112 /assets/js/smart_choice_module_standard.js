(function(){
  function ready(fn){if(document.readyState!=='loading'){fn();}else{document.addEventListener('DOMContentLoaded',fn);}}
  ready(function(){
    document.querySelectorAll('.gm-table-search').forEach(function(input){
      input.addEventListener('keyup',function(){
        var q=this.value.toLowerCase();
        document.querySelectorAll('.gm-filter-table tbody tr').forEach(function(row){
          row.style.display=row.innerText.toLowerCase().indexOf(q)>-1?'':'none';
        });
      });
    });
    document.querySelectorAll('.gm-check-all').forEach(function(btn){
      btn.addEventListener('click',function(){
        var form=this.closest('form'); if(!form){return;}
        form.querySelectorAll('input[type="checkbox"][name="ids[]"]').forEach(function(box){box.checked=!box.checked;});
      });
    });
    document.querySelectorAll('.gm-mass-delete').forEach(function(btn){
      btn.addEventListener('click',function(e){
        var form=this.closest('form'); if(!form){return;}
        if(!form.querySelector('input[name="ids[]"]:checked')){e.preventDefault(); alert('Please select at least one meeting.'); return false;}
        if(!confirm('Are you sure you want to delete the selected meetings?')){e.preventDefault(); return false;}
      });
    });
  });
})();
