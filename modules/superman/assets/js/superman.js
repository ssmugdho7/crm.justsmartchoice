(function(){
  'use strict';
  function q(selector, root){return (root||document).querySelector(selector);}
  function qa(selector, root){return Array.prototype.slice.call((root||document).querySelectorAll(selector));}
  function ready(fn){if(document.readyState !== 'loading'){fn();}else{document.addEventListener('DOMContentLoaded',fn);}}
  function cleanLabel(value){return String(value||'').replace(/[_-]+/g,' ').replace(/\s+/g,' ').trim().replace(/\w\S*/g,function(txt){return txt.charAt(0).toUpperCase()+txt.substr(1).toLowerCase();});}
  window.supermanCleanLabel = cleanLabel;

  function copyText(text, button){
    text = String(text||'');
    if(!text){return;}
    if(navigator.clipboard && navigator.clipboard.writeText){
      navigator.clipboard.writeText(text).then(function(){ if(button){var old=button.innerHTML;button.innerHTML='Copied';setTimeout(function(){button.innerHTML=old;},900);} });
      return;
    }
    var ta=document.createElement('textarea');ta.value=text;document.body.appendChild(ta);ta.select();document.execCommand('copy');document.body.removeChild(ta);
  }

  function applyRuntimeSettings(){
    var root=document.documentElement;
    qa('input[name^="superman_"]').forEach(function(input){
      if(input.type==='color' && input.value){
        if(input.name==='superman_primary_color')root.style.setProperty('--superman-primary',input.value);
        if(input.name==='superman_secondary_color')root.style.setProperty('--superman-secondary',input.value);
        if(input.name==='superman_accent_color')root.style.setProperty('--superman-accent',input.value);
        if(input.name==='superman_dark_color')root.style.setProperty('--superman-dark',input.value);
        if(input.name==='superman_surface_color')root.style.setProperty('--superman-surface',input.value);
      }
      if(input.type==='number' && input.value){
        if(input.name==='superman_logo_max_height')root.style.setProperty('--superman-logo-height',input.value+'px');
        if(input.name==='superman_menu_font_size')root.style.setProperty('--superman-menu-size',input.value+'px');
        if(input.name==='superman_mobile_menu_font_size')root.style.setProperty('--superman-mobile-menu-size',input.value+'px');
        if(input.name==='superman_topbar_height')root.style.setProperty('--superman-topbar-height',input.value+'px');
        if(input.name==='superman_table_name_width')root.style.setProperty('--superman-name-width',input.value+'px');
        if(input.name==='superman_table_email_width')root.style.setProperty('--superman-email-width',input.value+'px');
      }
    });
  }

  function autofillFromClient(clientId){
    if(!clientId || !window.SUPERMAN_ADMIN_URL){return;}
    fetch(window.SUPERMAN_ADMIN_URL+'/client_bundle/'+encodeURIComponent(clientId),{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(bundle){
      var c=bundle.client||{}, cf=bundle.custom_fields||{};
      var pairs={
        'email':c.email||cf.email,'client_email':c.email||cf.email,'phone':c.phonenumber||cf.mobile_phone,'phonenumber':c.phonenumber||cf.mobile_phone,'client_phone':c.phonenumber||cf.mobile_phone,
        'mobile_phone':cf.mobile_phone,'address':c.address||c.billing_street,'billing_street':c.billing_street||c.address,'shipping_street':c.shipping_street||c.address,
        'city':c.city||c.billing_city,'state':c.state||c.billing_state,'zip':c.zip||c.billing_zip,'project_address':cf.property_address||c.address||c.shipping_street||c.billing_street,
        'company':c.company,'proposal_to':c.company,'client_name':c.company,'name':c.company,
        'property_address':cf.property_address,'property_city':cf.property_city,'property_state':cf.property_state,'property_zip':cf.property_zip,
        'property_owner_name':cf.property_owner_name,'property_apn_or_folio_number':cf.property_apn_or_folio_number,'permit_number':cf.permit_number,
        'scope_of_work':cf.scope_of_work,'warranty':cf.warranty,'project_start_time':cf.project_start_time,'preferred_communication':cf.preferred_communication,
        'sales_agent':cf.sales_agent,'contractor_name':cf.contractor_name,'subcontractor_name':cf.subcontractor_name
      };
      Object.keys(pairs).forEach(function(name){ if(!pairs[name])return; qa('[name="'+name+'"],#'+name).forEach(function(el){ if((el.tagName==='INPUT'||el.tagName==='TEXTAREA'||el.tagName==='SELECT') && (!el.value || String(el.value).trim()==='')){ el.value=pairs[name]; el.dispatchEvent(new Event('change',{bubbles:true})); } }); });
    }).catch(function(){});
  }

  function getChipFromTransfer(e){
    var text=e.dataTransfer ? e.dataTransfer.getData('text/plain') : '';
    if(text){
      try{var parsed=JSON.parse(text); if(parsed.module && parsed.field){return parsed;}}catch(err){}
      if(text.indexOf(':')>-1){var parts=text.split(':');return {module:parts[0]||'', field:parts.slice(1).join(':')||''};}
    }
    return window.supermanDraggedField || null;
  }

  function setupBuilder(){
    var drop=q('#superman-drop-zone'), payload=q('#superman-flow-payload');
    qa('.superman-field-chip,.superman-mini-chip').forEach(function(chip){
      chip.addEventListener('dragstart',function(e){
        var data={module:chip.getAttribute('data-module')||'', field:chip.getAttribute('data-field')||''};
        window.supermanDraggedField=data;
        if(e.dataTransfer){e.dataTransfer.setData('text/plain', JSON.stringify(data));}
      });
    });
    if(drop){
      drop.addEventListener('dragover',function(e){e.preventDefault();drop.classList.add('is-over');});
      drop.addEventListener('dragleave',function(){drop.classList.remove('is-over');});
      drop.addEventListener('drop',function(e){
        e.preventDefault();drop.classList.remove('is-over');
        var data=getChipFromTransfer(e); if(!data || !data.module || !data.field){return;}
        var empty=q('.superman-drop-empty',drop); if(empty){empty.style.display='none';}
        var card=document.createElement('div'); card.className='superman-dropped-card'; card.draggable=true; card.setAttribute('data-module',data.module); card.setAttribute('data-field',data.field);
        card.innerHTML='<strong>'+cleanLabel(data.field)+'</strong><small>'+cleanLabel(data.module)+'</small><button type="button" aria-label="Remove">×</button>';
        drop.appendChild(card); updatePayload();
      });
      drop.addEventListener('click',function(e){ if(e.target.tagName==='BUTTON' && e.target.closest('.superman-dropped-card')){ e.target.closest('.superman-dropped-card').remove(); var empty=q('.superman-drop-empty',drop); if(empty && !q('.superman-dropped-card',drop)){empty.style.display='flex';} updatePayload(); } });
    }
    function updatePayload(){ if(!payload||!drop)return; var items=qa('.superman-dropped-card',drop).map(function(card){return {module:card.getAttribute('data-module')||'',field:card.getAttribute('data-field')||'',label:cleanLabel((card.getAttribute('data-module')||'')+' '+(card.getAttribute('data-field')||''))};}); payload.value=JSON.stringify({items:items,created_at:new Date().toISOString()}); }
    var form=q('#superman-visual-flow-form'); if(form){form.addEventListener('submit',function(e){updatePayload(); var parsed={items:[]}; try{parsed=JSON.parse(payload.value||'{}');}catch(err){} if(!parsed.items || parsed.items.length<2){e.preventDefault(); showPreview({name:'Preview Combination',items:[]});}});}
    var clear=q('#superman-clear-canvas'); if(clear){clear.addEventListener('click',function(){qa('.superman-dropped-card',drop).forEach(function(x){x.remove();}); var empty=q('.superman-drop-empty',drop); if(empty){empty.style.display='flex';} updatePayload();});}
  }

  function setupSearch(){
    qa('.superman-table-search').forEach(function(input){input.addEventListener('input',function(){var panel=this.closest('.panel_s')||document; var term=this.value.toLowerCase(); qa('tbody tr',panel).forEach(function(row){row.style.display=row.textContent.toLowerCase().indexOf(term)>-1?'':'none';});});});
    qa('.superman-bank-search').forEach(function(input){input.addEventListener('input',function(){var bank=this.closest('.superman-field-bank'); var term=this.value.toLowerCase(); qa('.superman-field-chip,.superman-mini-chip',bank).forEach(function(chip){chip.style.display=chip.textContent.toLowerCase().indexOf(term)>-1?'flex':'none';});});});
    qa('.superman-check-all').forEach(function(box){box.addEventListener('change',function(){qa('tbody input[type="checkbox"]',this.closest('table')).forEach(function(cb){cb.checked=box.checked;});});});
  }

  function setupAccordion(){
    document.addEventListener('click',function(e){
      var toggle=e.target.closest('.superman-bank-toggle');
      if(!toggle){return;}
      e.preventDefault();
      var bank=toggle.closest('.superman-collapsible-bank');
      var body=q('.superman-bank-body',bank);
      var isOpen=bank.classList.contains('is-open');
      bank.classList.toggle('is-open',!isOpen);
      toggle.setAttribute('aria-expanded',!isOpen?'true':'false');
      if(body){body.style.display=!isOpen?'block':'none';}
    });
  }

  function repairDropdowns(){qa('.dropdown-menu.open').forEach(function(d){d.style.overflow='hidden';var inner=q('.inner.open',d);if(inner){inner.style.overflowY='auto';inner.style.overflowX='hidden';inner.style.maxHeight=inner.style.maxHeight||'260px';}qa('ul.dropdown-menu.inner',d).forEach(function(u){u.style.overflow='visible';u.style.maxHeight='none';});});}

  function collectCanvas(){
    var items=[];
    qa('#superman-drop-zone .superman-dropped-card').forEach(function(card){items.push({module:card.getAttribute('data-module')||'',field:card.getAttribute('data-field')||''});});
    return {name:(q('input[name="flow_name"]')||{}).value||'Visual Combination',status:'Preview',items:items};
  }

  function flowToItems(payload){
    var items=[];
    if(payload.items && payload.items.length){items=payload.items;}
    else if(payload.fields && payload.fields.length){items=payload.fields.map(function(field){return {module:'Saved Combination',field:field};});}
    return items;
  }

  function showPreview(payload){
    payload=payload||{};
    var items=flowToItems(payload);
    var html='';
    if(!items.length){
      html='<div class="superman-preview-icon"><i class="fa fa-link"></i></div><h4>Preview Combination</h4><p>No Fields Found.</p><p class="text-muted">Select or drag at least two fields to preview what connects with what across the CRM.</p>';
    }else{
      html='<div class="superman-preview-icon"><i class="fa fa-check"></i></div><h4>'+cleanLabel(payload.name||'Field Combination')+'</h4><p class="text-muted">This preview shows what connects with what before it is used in estimates, proposals, contracts, subcontractor agreements, sales records, purchasing records, emails, or templates.</p><ul class="superman-preview-flow-list">';
      items.forEach(function(item,index){
        html+='<li><div><strong>'+cleanLabel(item.field||item)+'</strong><br><small>'+cleanLabel(item.module||'CRM Field')+'</small></div>'+(index<items.length-1?'<span class="superman-preview-arrow">Connects To</span>':'<span class="label label-success">Final Field</span>')+'</li>';
      });
      html+='</ul>';
    }
    var target=q('#superman-preview-modal-content');
    if(target){target.innerHTML=html;}
    if(window.jQuery && jQuery.fn.modal){jQuery('#supermanPreviewModal').modal('show');}
    else{alert((items.length?items.map(function(i){return cleanLabel((i.module||'')+' '+(i.field||i));}).join(' -> '):'Preview Combination: No Fields Found.'));}
  }

  ready(function(){
    applyRuntimeSettings();setupBuilder();setupSearch();setupAccordion();
    document.addEventListener('click',function(e){
      var btn=e.target.closest('.superman-copy'); if(btn){e.preventDefault();copyText(btn.getAttribute('data-copy')||'',btn);}
      var test=e.target.closest('.superman-test-flow'); if(test){e.preventDefault();try{showPreview(JSON.parse(test.getAttribute('data-flow')||'{}'));}catch(err){showPreview({name:'Preview Combination',items:[]});}}
      var canvas=e.target.closest('#superman-preview-canvas'); if(canvas){e.preventDefault();showPreview(collectCanvas());}
      var flow=e.target.closest('.superman-preview-flow'); if(flow){e.preventDefault();try{showPreview(JSON.parse(flow.getAttribute('data-flow')||'{}'));}catch(err){showPreview({name:'Preview Combination',items:[]});}}
      setTimeout(repairDropdowns,80);
    });
    qa('input[name^="superman_"]').forEach(function(input){input.addEventListener('input',applyRuntimeSettings);input.addEventListener('change',applyRuntimeSettings);});
    document.addEventListener('change',function(e){var el=e.target;if(el && (el.name==='clientid'||el.id==='clientid'||el.name==='rel_id')){var val=el.value;if(val){autofillFromClient(val);}}});
    var client=q('[name="clientid"],#clientid'); if(client && client.value){autofillFromClient(client.value);} setInterval(repairDropdowns,1500);
  });
})();
