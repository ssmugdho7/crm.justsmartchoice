(function(){
'use strict';
function scReady(fn){if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',fn);}else{fn();}}
function isEligible(el){
  var n=(el.getAttribute('name')||'').toLowerCase();
  var id=(el.id||'').toLowerCase();
  return n==='clientnote'||n==='terms'||n.indexOf('predefined_clientnote')>-1||n.indexOf('predefined_terms')>-1||n.indexOf('proposal_info_format')>-1||n.indexOf('company_info_format')>-1||id==='clientnote'||id==='terms';
}
function enableEditor(el,index){
  if(!isEligible(el))return;
  if(!el.id)el.id='sc_rich_textarea_'+index;
  el.classList.add('tinymce');
  if(window.tinymce){try{if(tinymce.get(el.id)){return;}}catch(e){}}
  if(typeof window.init_editor==='function'){window.init_editor('#'+el.id,{height:240});}
}
scReady(function(){
  document.querySelectorAll('textarea').forEach(enableEditor);
  var obs=new MutationObserver(function(){document.querySelectorAll('textarea').forEach(enableEditor);});
  obs.observe(document.body,{childList:true,subtree:true});
});
})();

/* Smart Choice Core Upgrade v1.0.4 - table compatibility
 * Global table mutation/conflict banner removed in 4.3.5. DataTables now retains
 * its native Perfex structure; targeted responsive rules are applied in CSS.
 */

// Smart Choice Core v1.0.5: profile image recovery and clean labels.
(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.sidebar-user-profile img, .staff-profile-image, .staff-profile-image-small').forEach(function (img) {
            img.addEventListener('error', function () {
                var original = img.getAttribute('data-original-src');
                if (original && img.src !== original) {
                    img.src = original;
                }
            }, { once: true });
        });
        document.querySelectorAll('[data-language-label], .label, .badge').forEach(function (el) {
            if (el.childElementCount === 0 && el.textContent.indexOf('_') !== -1) {
                el.textContent = el.textContent.replace(/_/g, ' ').replace(/\b\w/g, function (m) { return m.toUpperCase(); });
            }
        });
    });
})();

/* Smart Choice Core v1.0.6 */
(function(){
  'use strict';
  function start($){
  function normalizeSalesRows(){
    $('.accounting-template .table.items tr').each(function(){
      var h=0; $(this).children('td').each(function(){h=Math.max(h,$(this).outerHeight()||0);});
      if(h){$(this).children('td').css('min-height',h+'px');}
    });
  }
  function defaultTasksNotBillable(){
    var $billable=$('input[name="billable"]');
    if($billable.length && !$('input[name="taskid"]').val() && !$billable.data('sc-touched')){
      $billable.prop('checked',false).trigger('change');
    }
    $billable.on('change',function(){$(this).data('sc-touched',true);});
  }
  $(document).ready(function(){normalizeSalesRows();defaultTasksNotBillable();$('.widget-staff_tickets_report,.staff-tickets-report').addClass('sc-ticket-scroll');});
  $(document).ajaxComplete(function(){setTimeout(normalizeSalesRows,150);});
  }
  if(window.jQuery){start(window.jQuery);}else{document.addEventListener('DOMContentLoaded',function(){if(window.jQuery){start(window.jQuery);}});}
})();


/* Smart Choice CRM 4.3.4 non-destructive form/accessibility hardening. */
(function(){
  function scFixFormMetadata(scope){
    scope=scope||document;
    var seq=0;
    scope.querySelectorAll('input:not([id]):not([name]),select:not([id]):not([name]),textarea:not([id]):not([name])').forEach(function(el){
      el.id='sc-auto-field-'+Date.now()+'-'+(++seq);
    });
    scope.querySelectorAll('label[for]').forEach(function(label){
      var target=label.getAttribute('for');
      if(!target||document.getElementById(target)) return;
      try {
        var byName=document.querySelector('[name="'+CSS.escape(target)+'"]');
        if(byName){ if(!byName.id) byName.id='sc-field-'+target.replace(/[^a-zA-Z0-9_-]/g,'-'); label.setAttribute('for',byName.id); }
      } catch(e){}
    });
    scope.querySelectorAll('input[name],textarea[name]').forEach(function(el){
      if(el.hasAttribute('autocomplete')) return;
      var n=(el.getAttribute('name')||'').toLowerCase();
      if(n==='email'||n.indexOf('email')!==-1) el.setAttribute('autocomplete','email');
      else if(n.indexOf('phone')!==-1||n.indexOf('mobile')!==-1) el.setAttribute('autocomplete','tel');
      else if(n==='firstname'||n.indexOf('first_name')!==-1) el.setAttribute('autocomplete','given-name');
      else if(n==='lastname'||n.indexOf('last_name')!==-1) el.setAttribute('autocomplete','family-name');
      else if(n.indexOf('address')!==-1) el.setAttribute('autocomplete','street-address');
    });
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',function(){scFixFormMetadata(document);}); else scFixFormMetadata(document);
  if(window.MutationObserver){ new MutationObserver(function(m){m.forEach(function(x){x.addedNodes.forEach(function(n){if(n.nodeType===1) scFixFormMetadata(n);});});}).observe(document.documentElement,{childList:true,subtree:true}); }
})();
