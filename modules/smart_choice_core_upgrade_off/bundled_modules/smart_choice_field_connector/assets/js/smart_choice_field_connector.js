(function(){
  function qs(sel, root){return (root||document).querySelector(sel);}
  function qsa(sel, root){return Array.prototype.slice.call((root||document).querySelectorAll(sel));}
  function getCsrf(){var name=(typeof csrfData!=='undefined'&&csrfData.token_name)?csrfData.token_name:'';var hash=(typeof csrfData!=='undefined'&&csrfData.hash)?csrfData.hash:'';return {name:name,hash:hash};}
  function post(url,data,cb){var fd=new FormData();Object.keys(data||{}).forEach(function(k){fd.append(k,data[k]);});var csrf=getCsrf();if(csrf.name&&csrf.hash){fd.append(csrf.name,csrf.hash);}fetch(url,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(cb).catch(function(e){cb({success:false,error:e.message});});}
  function escapeHtml(s){return String(s||'').replace(/[&<>'"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c];});}
  function previewHtml(data){var html='<h4>Test Outcome</h4>';html+='<p><strong>Original:</strong></p><code>'+escapeHtml(data.input||'')+'</code>';html+='<p class="mtop15"><strong>Preview Output:</strong></p><div class="well">'+escapeHtml(data.output||'')+'</div>';html+='<p><strong>Detected Tokens:</strong> '+escapeHtml((data.used_tokens||[]).join(', ')||'None')+'</p>';html+='<p><strong>Missing Sample Values:</strong> '+escapeHtml((data.missing_tokens||[]).join(', ')||'None')+'</p>';return html;}
  function testTemplate(template){post((window.admin_url||'/admin/')+'smart_choice_field_connector/test_json',{template:template},function(res){var box=qs('#scfcPreviewContent');if(box){box.innerHTML=previewHtml(res);}if(window.jQuery){jQuery('#scfcPreviewModal').modal('show');}});}
  document.addEventListener('click',function(e){
    var copy=e.target.closest('.scfc-copy');
    if(copy){e.preventDefault();var text=copy.getAttribute('data-copy')||'';if(navigator.clipboard){navigator.clipboard.writeText(text);}else{var t=document.createElement('textarea');t.value=text;document.body.appendChild(t);t.select();document.execCommand('copy');document.body.removeChild(t);}copy.innerHTML='Copied';setTimeout(function(){copy.innerHTML='Copy';},1200);return;}
    var chip=e.target.closest('.scfc-chip');
    if(chip){e.preventDefault();var ta=qs('#scfcBuilderTemplate');if(ta){ta.value+=(ta.value?' ':'')+(chip.getAttribute('data-token')||'');ta.focus();}return;}
    var test=e.target.closest('.scfc-test-template');
    if(test){e.preventDefault();testTemplate(test.getAttribute('data-template')||'');return;}
    if(e.target.closest('#scfcTestBuilder')){e.preventDefault();testTemplate((qs('#scfcBuilderTemplate')||{}).value||'');return;}
    if(e.target.closest('#scfcTestCombinationModal')){e.preventDefault();testTemplate((qs('#scfc_combination_template')||{}).value||'');return;}
    if(e.target.closest('#scfcClearBuilder')){e.preventDefault();var ta=qs('#scfcBuilderTemplate');if(ta){ta.value='';ta.focus();}return;}
    if(e.target.closest('#scfcOpenCombinationModal')){e.preventDefault();var src=qs('#scfcBuilderTemplate');var dest=qs('#scfc_combination_template');if(src&&dest){dest.value=src.value;}if(window.jQuery){jQuery('#scfcCombinationModal').modal('show');}return;}
    var eg=e.target.closest('.scfc-edit-group');
    if(eg){e.preventDefault();var r=JSON.parse(eg.getAttribute('data-record')||'{}');setVal('scfc_group_id',r.id);setVal('scfc_group_name',r.group_name);setVal('scfc_group_key',r.group_key);setVal('scfc_group_description',r.description);setCheck('scfc_group_active',r.is_active);jQuery('#scfcGroupModal').modal('show');return;}
    var em=e.target.closest('.scfc-edit-mapping');
    if(em){e.preventDefault();var m=JSON.parse(em.getAttribute('data-record')||'{}');setVal('scfc_mapping_id',m.id);setVal('scfc_mapping_group_id',m.group_id);setVal('scfc_mapping_source_module',m.source_module);setVal('scfc_mapping_source_field',m.source_field);setVal('scfc_mapping_destination_module',m.destination_module);setVal('scfc_mapping_destination_field',m.destination_field);setVal('scfc_mapping_merge_tag',m.merge_tag);setVal('scfc_mapping_notes',m.notes);setCheck('scfc_mapping_active',m.is_active);jQuery('#scfcMappingModal').modal('show');return;}
    var et=e.target.closest('.scfc-edit-token');
    if(et){e.preventDefault();var t=JSON.parse(et.getAttribute('data-record')||'{}');setVal('scfc_token_id',t.id);setVal('scfc_token_group_id',t.group_id);setVal('scfc_token_name',t.token_name);setVal('scfc_token_key',t.token_key);setVal('scfc_token_source_module',t.source_module);setVal('scfc_token_source_field',t.source_field);setVal('scfc_token_description',t.description);setCheck('scfc_token_active',t.is_active);jQuery('#scfcTokenModal').modal('show');return;}
    var ec=e.target.closest('.scfc-edit-combination');
    if(ec){e.preventDefault();var c=JSON.parse(ec.getAttribute('data-record')||'{}');setVal('scfc_combination_id',c.id);setVal('scfc_combination_name',c.combination_name);setVal('scfc_combination_key',c.combination_key);setVal('scfc_combination_group_id',c.group_id);setVal('scfc_combination_template',c.template);setVal('scfc_combination_description',c.description);setCheck('scfc_combination_active',c.is_active);jQuery('#scfcCombinationModal').modal('show');return;}
  });
  function setVal(id,val){var el=qs('#'+id);if(el){el.value=(val===null||typeof val==='undefined')?'':val;if(window.jQuery&&jQuery(el).hasClass('selectpicker')){jQuery(el).selectpicker('refresh');}}}
  function setCheck(id,val){var el=qs('#'+id);if(el){el.checked=String(val)==='1'||val===true;}}
  document.addEventListener('dragstart',function(e){var chip=e.target.closest('.scfc-chip');if(chip){e.dataTransfer.setData('text/plain',chip.getAttribute('data-token')||'');}});
  var drop=qs('#scfcBuilderTemplate');
  if(drop){drop.addEventListener('dragover',function(e){e.preventDefault();});drop.addEventListener('drop',function(e){e.preventDefault();var text=e.dataTransfer.getData('text/plain');this.value+=(this.value?' ':'')+text;this.focus();});}
})();
