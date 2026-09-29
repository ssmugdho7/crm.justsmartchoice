(function(w,d,$){'use strict';
function initSelects(root){root=root&&root.querySelectorAll?root:d;$(root).find('select.selectpicker').each(function(){try{$(this).selectpicker('refresh')}catch(e){}})}
function addUnits(root){var units=['Each','Linear Foot','Foot','Inch','Square Foot','Square Yard','Cubic Foot','Cubic Yard','Pound','Ton','Gallon','Hour','Day','Week','Month','Lot','Assembly','Sheet','Bundle','Square'];if(!d.getElementById('sc-unit-options')){var dl=d.createElement('datalist');dl.id='sc-unit-options';dl.innerHTML=units.map(function(x){return '<option value="'+x+'">'}).join('');d.body.appendChild(dl)}$(root||d).find('input[name*="[unit]"]').attr('list','sc-unit-options').attr('autocomplete','off').on('focus',function(){this.select();});}
function defaultDates(root){var months=parseInt((w.scDefaultValidityMonths||3),10)||3;$(root||d).find('input[name="open_till"],input[name="expirydate"],input[name="duedate"]').each(function(){if(this.value)return;var dt=new Date();dt.setMonth(dt.getMonth()+months);this.value=('0'+(dt.getMonth()+1)).slice(-2)+'/'+('0'+dt.getDate()).slice(-2)+'/'+dt.getFullYear();$(this).trigger('change')})}
function statusPlus(root){$(root||d).find('select[name="status"]').each(function(){var $s=$(this);if($s.closest('.sc-native-status-wrap').length)return;var type=$('form.proposal-form').length?'proposal':($('form.estimate-form').length?'estimate':($('form.invoice-form').length?'invoice':($('.credit-note-form').length?'credit_note':($('.payment-form').length?'payment':''))));if(!type)return;var $g=$s.closest('.form-group');$g.wrap('<div class="sc-native-status-wrap"></div>');$g.after('<button type="button" class="btn btn-success sc-native-status-add" data-type="'+type+'" title="'+(w.scStatusAddLabel||'Add Status')+'"><i class="fa fa-plus"></i></button>');});}
$(d).on('click','.sc-native-status-add',function(){$('#sc_status_type').val($(this).data('type'));$('#sc_status_name').val('');$('#sc-sales-status-modal').modal('show');});
$(d).on('click','#sc-save-sales-status',function(){var type=$('#sc_status_type').val(),name=$.trim($('#sc_status_name').val()),color=$('#sc_status_color').val();if(!name)return;var data={document_type:type,name:name,color:color};if(typeof csrfData!=='undefined')data[csrfData.token_name]=csrfData.hash;$.post((w.admin_url||'/admin/')+'smart_choice_sales/add_status',data).done(function(r){try{r=typeof r==='string'?JSON.parse(r):r}catch(e){r={success:false}}if(!r.success){alert_float('danger',r.message||(w.scStatusAddFailedLabel||'Unable to add status'));return;}var $s=$('select[name="status"]:visible').first();var $form=$('form.proposal-form,form.estimate-form,form.invoice-form,form.credit-note-form,form.payment-form').filter(':visible').first();var $hidden=$form.find('input[name="sc_custom_status_value"]').first();if(!$hidden.length&&$form.length){$hidden=$('<input>',{type:'hidden',name:'sc_custom_status_value'}).appendTo($form);}if($hidden.length){$hidden.val(name).trigger('change');}if($s.length){var current=$s.val();if(!$s.find('option[data-custom-status="'+name.replace(/"/g,'&quot;')+'"]').length){$s.append($('<option>',{value:current,text:name,'data-custom-status':name}));}if($.fn.selectpicker){$s.selectpicker('refresh').selectpicker('val',current);}}$('#sc-sales-status-modal').modal('hide');alert_float('success',w.scStatusAddedLabel||'Status added');});});
$(d).on('shown.bs.select','.bootstrap-select',function(){var $menu=$(this).find('.dropdown-menu.inner'),$search=$(this).find('.bs-searchbox input');if($search.length&&!$search.val()){$search.trigger('input');}$menu.scrollTop(0);});
$(d).on('click','.mobile-menu-toggle,.navbar-toggle',function(){d.body.classList.toggle('mobile-menu-open')});
function normalizeProposalRegister(){ /* CRM 4.2.6: native DataTable row layout; no DOM height mutation. */ }

function initMobileSearchClose(){
  if(d.getElementById('sc-mobile-search-close'))return;
  var top=d.getElementById('top_search'); if(!top)return;
  var b=d.createElement('button'); b.type='button'; b.id='sc-mobile-search-close'; b.className='sc-mobile-search-close'; b.setAttribute('aria-label','Close search'); b.innerHTML='<i class="fa fa-times"></i>';
  b.addEventListener('click',function(e){e.preventDefault();var input=d.getElementById('search_input');if(input){input.value='';input.blur();}var r=d.getElementById('search_results');if(r)r.innerHTML='';var h=d.getElementById('search-history');if(h)h.classList.remove('show');});
  top.appendChild(b);
}

function ensurePaymentsIcon(){var $a=$('#side-menu a[href*="/payments"],#side-menu a[href$="payments"]');$a.each(function(){if(!$(this).children('i').length)$(this).prepend('<i class="fa fa-credit-card menu-icon" aria-hidden="true"></i>');});}
$(function(){initSelects(d);addUnits(d);defaultDates(d);statusPlus(d);normalizeProposalRegister();ensurePaymentsIcon();initMobileSearchClose();refreshCustomerSummary();});$(d).ajaxComplete(function(){initSelects(d);addUnits(d);statusPlus(d);ensurePaymentsIcon()});$(d).on('draw.dt','.table-proposals',function(){normalizeProposalRegister();});

function refreshCustomerSummary(forceEditable){
  var $box=$('[data-sc-sales-customer-summary]').first();
  var $editable=$('[data-sc-invoice-customer-edit]').first();
  if(!$box.length&&!$editable.length)return;
  var type='customer', id=0, project=parseInt($('#project_id').val()||0,10)||0;
  if($('#rel_type').length){type=$('#rel_type').val()||'customer';id=parseInt($('#rel_id').val()||0,10)||0;}else{id=parseInt($('#clientid').val()||0,10)||0;}
  if(!id){if($box.length)$box.find('[data-sc-summary]').text('—');return;}
  $.getJSON((w.admin_url||'/admin/')+'smart_choice_sales/customer_summary',{rel_type:type,rel_id:id,project_id:project}).done(function(r){
    if(!r||!r.success)return;
    if($box.length){$box.find('[data-sc-summary="name"]').text(r.name||'—');$box.find('[data-sc-summary="email"]').text(r.email||'—');$box.find('[data-sc-summary="phone"]').text(r.phone||'—');$box.find('[data-sc-summary="address"]').text(r.address||'—');}
    if($editable.length){
      [['name',r.name],['email',r.email],['phone',r.phone],['address',r.address]].forEach(function(pair){
        var $f=$editable.find('[data-sc-customer-field="'+pair[0]+'"]');
        if($f.length && (forceEditable===true || $.trim($f.val())==='')){$f.val(pair[1]||'').trigger('change');}
      });
    }
  });
}
$(d).on('change','#clientid,#rel_id,#rel_type,#project_id',function(){refreshCustomerSummary(true);});
})(window,document,jQuery);
