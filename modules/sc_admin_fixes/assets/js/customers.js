(function ($) {
 'use strict';
 if (!$ || !document.body.classList.contains('scaf-clients')) return;
 var es=(document.documentElement.lang||'').startsWith('es');function t(a,b){return es?b:a;}
 function toast(type,message) { if (window.SCAF) window.SCAF.toast(type,message); }
 function selected() { return $('.table-clients tbody td:first-child input:checked').map(function(){return this.value;}).get(); }
 function selection() { var count=selected().length; $('.scaf-customer-batch').prop('hidden',!count); $('[data-scaf-selected]').text(count+' '+t('selected','seleccionados')); }
 $(document).on('change','.table-clients input[type=checkbox]',selection).on('draw.dt',selection);
 $(document).on('click','[data-scaf-clear]',function(){ $('.table-clients tbody td:first-child input').prop('checked',false); selection(); });
 $(document).on('preXhr.dt', '.table-clients', function(event, settings, data) { data.scaf_segment=$(this).attr('data-scaf-active-segment') || 'all'; });
 $(document).on('click','button[data-scaf-segment]',function(){
  var key=$(this).attr('data-scaf-segment'),table=$('.table-clients');
  $('button[data-scaf-segment]').attr('aria-pressed','false'); $(this).attr('aria-pressed','true');
  // Use native server filters. Keep pagination, record visibility and saved filter rules.
  if (!window.app || !app.dtFilters || !$.fn.DataTable.isDataTable(table[0])) return;
  table.attr('data-scaf-active-segment',key);
  table.DataTable().ajax.reload(null,false);
 });
 // Replace only this native bulk handler, preserving its endpoint and posted contract.
 window.customers_bulk_action=async function(button){
  if (!window.SCAF || $(button).data('scaf-busy')) return false;
  $(button).data('scaf-busy',true).prop('disabled',true);
  try {
  var ids=selected(); if (!ids.length) return false;
  var deleting=$('#mass_delete').prop('checked');
  if (!await window.SCAF.confirm({title:deleting?t('Delete selected customers?','¿Eliminar clientes seleccionados?'):t('Update selected customers?','¿Actualizar clientes seleccionados?'),message:ids.length+' '+t('selected customers will be affected.','clientes seleccionados se verán afectados.'),actionLabel:t('Continue','Continuar')})) return false;
  if (deleting && !await window.SCAF.confirm({title:t('Permanently delete customers','Eliminar clientes permanentemente'),message:t('This removes selected customer records. Type DELETE to continue.','Se eliminarán los clientes seleccionados. Escriba DELETE para continuar.'),typed:'DELETE',actionLabel:t('Delete customers','Eliminar clientes')})) return false;
  var data={ids:ids};
  if(deleting) data.mass_delete=true;
  else {data.groups=$('select[name="move_to_groups_customers_bulk[]"]').val()||[];if(!data.groups.length){
   if (!await window.SCAF.confirm({title:t('Clear group memberships?','¿Eliminar pertenencia a grupos?'),message:t('No groups are selected. This removes all group memberships from these customers.','No hay grupos seleccionados. Se eliminará la pertenencia a todos los grupos de estos clientes.'),actionLabel:t('Clear groups','Eliminar grupos')})) return false;
   data.groups='remove_all'; data.scaf_clear_groups='1';
  }}
  $(button).data('scaf-busy',true).prop('disabled',true).addClass('is-loading');
  await new Promise(function(resolve){ $.post(admin_url+'clients/bulk_action',data).done(function(){window.location.reload();}).fail(function(){toast('error',t('Customer changes could not be saved.','No se pudieron guardar los cambios.'));}).always(function(){selection();resolve();}); });
  return false;
  } finally { $(button).removeData('scaf-busy').prop('disabled',false).removeClass('is-loading'); }
 };
 $(document).on('click','[data-scaf-new-contact]',function(){var id=$('#scaf-contact-customer').val();if(!/^\d+$/.test(id||''))return;window.location.href=admin_url+'clients/client/'+encodeURIComponent(id)+'?group=contacts&new_contact=true';});
 $(document).on('click','[data-scaf-export]',function(){
  var lines=[];$('.table-clients tbody tr').each(function(){if(!$(this).find('td:first-child input').prop('checked'))return;var fields=[];$(this).find('td').slice(1).each(function(){var value=this.textContent.trim();if(/^[=+@-]/.test(value))value="'"+value;fields.push('"'+value.replace(/"/g,'""')+'"');});lines.push(fields.join(','));});
  if(!lines.length)return;var url=URL.createObjectURL(new Blob(['\ufeff'+lines.join('\r\n')],{type:'text/csv;charset=utf-8'})),a=document.createElement('a');a.href=url;a.download='selected-customers.csv';a.click();setTimeout(function(){URL.revokeObjectURL(url);},1000);
 });
 $(document).on('click','[data-scaf-statements]',function(){
  var ids=selected();if(!ids.length)return;
  var old=document.getElementById('scaf-statement-review');if(old)old.remove();
  var modal=document.createElement('div');modal.id='scaf-statement-review';modal.className='modal fade';modal.tabIndex=-1;modal.setAttribute('role','dialog');
  var content=document.createElement('div');content.className='modal-dialog modal-lg';var body=document.createElement('div');body.className='modal-content';var panel=document.createElement('div');panel.className='modal-body';var title=document.createElement('h3');title.textContent=t('Review customer statements','Revisar estados de cuenta');panel.append(title);
  var notice=document.createElement('p');notice.textContent=t('Review the date range and explicitly select recipients below before sending. No email has been sent.','Revise el período y seleccione destinatarios en el formulario original. No se ha enviado ningún correo.');panel.append(notice);
  var period=document.createElement('div'),from=document.createElement('input'),to=document.createElement('input');from.type=to.type='date';from.value=new Date().getFullYear()+'-01-01';to.value=new Date().toISOString().slice(0,10);from.setAttribute('aria-label',t('From date','Desde'));to.setAttribute('aria-label',t('To date','Hasta'));period.append(from,to);panel.append(period);
  var recipients=document.createElement('div');panel.append(recipients);var token=null;
  var preview=document.createElement('button');preview.type='button';preview.className='btn btn-default';preview.textContent=t('Load recipients for review','Cargar destinatarios para revisar');
  var send=document.createElement('button');send.type='button';send.className='btn btn-primary';send.textContent=t('Send reviewed statements','Enviar estados revisados');send.disabled=true;
  function invalidate(){token=null;send.disabled=true;recipients.replaceChildren();}from.addEventListener('change',invalidate);to.addEventListener('change',invalidate);
  preview.addEventListener('click',function(){preview.disabled=true;$.post(admin_url+'sc_admin_fixes/customer_statements',{action:'preview',ids:ids,from:from.value,to:to.value},null,'json').done(function(response){if(!response.success){toast('error',t('Unable to review this batch.','No se pudo revisar este lote.'));return;}token=response.token;recipients.replaceChildren();Object.entries(response.contacts).forEach(function(entry){entry[1].forEach(function(contact){var label=document.createElement('label'),input=document.createElement('input');input.type='checkbox';input.value=contact.id;input.className='scaf-statement-recipient';label.append(input,document.createTextNode(' #'+entry[0]+' · '+contact.firstname+' '+contact.lastname+' · '+contact.email));recipients.append(label,document.createElement('br'));});});send.disabled=false;}).fail(function(){toast('error',t('Unable to load recipients.','No se pudieron cargar los destinatarios.'));}).always(function(){preview.disabled=false;});});
  send.addEventListener('click',async function(){if(send.disabled||send.dataset.pending)return;send.dataset.pending='1';send.disabled=preview.disabled=true;from.disabled=to.disabled=true;try { var contacts=Array.from(recipients.querySelectorAll('input:checked')).map(function(c){return c.value;});if(!contacts.length||!token)return;var sendToken=token;if(!await window.SCAF.confirm({title:t('Send statements?','¿Enviar estados de cuenta?'),message:from.value+' — '+to.value+' · '+contacts.length+' '+t('recipients','destinatarios'),actionLabel:t('Send','Enviar')}))return;send.disabled=preview.disabled=true;await new Promise(function(resolve){$.post(admin_url+'sc_admin_fixes/customer_statements',{action:'send',token:sendToken,contacts:contacts},null,'json').done(function(response){if(typeof response.sent!=='number'||typeof response.failed!=='number'){toast('error',t('No confirmed send result.','Sin resultado de envío confirmado.'));return;}toast(response.failed?'error':'success',response.sent+' '+t('sent','enviados')+' · '+response.failed+' '+t('failed','fallidos'));token=null;}).fail(function(){toast('error',t('Send result unknown. Review before retrying.','Resultado desconocido. Revise antes de reintentar.'));}).always(function(){resolve();});});}finally{delete send.dataset.pending;send.disabled=!token;preview.disabled=false;from.disabled=to.disabled=false;}});panel.append(preview,send);
  var close=document.createElement('button');close.type='button';close.className='btn btn-default';close.textContent=t('Close','Cerrar');close.setAttribute('data-dismiss','modal');panel.append(close);body.append(panel);content.append(body);modal.append(content);document.body.append(modal);$(modal).modal('show').on('hidden.bs.modal',function(){modal.remove();});
 });
 function enhance(){
  $('.table-contacts,.table-all-contacts').find('a[href^="mailto:"],a[href^="tel:"]').each(function(){
   if(this.dataset.scafEnhanced)return;this.dataset.scafEnhanced='1';
   var link=this,phone=link.getAttribute('href').indexOf('tel:')===0,value=link.textContent.trim(),copy=document.createElement('button');
   copy.type='button';copy.className='scaf-copy';copy.textContent=t('Copy','Copiar');copy.setAttribute('aria-label',phone?t('Copy phone number','Copiar número de teléfono'):t('Copy email address','Copiar correo electrónico'));
   copy.addEventListener('click',async function(){if(copy.disabled)return;copy.disabled=true;copy.classList.add('is-loading');try{await navigator.clipboard.writeText(value);toast('success',t('Copied.','Copiado.'));}catch(error){toast('error',t('Unable to copy. Select and copy the text manually.','No se pudo copiar. Seleccione y copie el texto manualmente.'));}finally{copy.disabled=false;copy.classList.remove('is-loading');}});link.after(copy);
   if(phone){var number=value.replace(/\D/g,'');if(number){var wa=document.createElement('a');wa.href='https://wa.me/'+number;wa.target='_blank';wa.rel='noopener noreferrer';wa.textContent='WhatsApp';wa.className='scaf-whatsapp';copy.after(wa);}}
  });
 }
 $(document).on('draw.dt shown.bs.modal',enhance);$(enhance);
})(window.jQuery);
