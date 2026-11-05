(function (w, d, $) {
 'use strict';
 if (!$ || !d.body.classList.contains('scaf-enabled') || !['proposals','invoices','estimates','payments','credit_notes'].some(x=>d.body.classList.contains('scaf-'+x))) return;
 const api=w.SCAF, es=(d.documentElement.lang||'').startsWith('es'), t=(a,b)=>es?b:a;
 function toast(type,message){if(api?.toast)api.toast(type,message);}
 async function copy(text,source){const release=api?.busy&&source?api.busy(source):()=>{};try{if(!navigator.clipboard?.writeText)throw Error('clipboard');await navigator.clipboard.writeText(text);toast('success',t('Copied to clipboard.','Copiado al portapapeles.'));}catch(_){toast('error',t('Unable to copy. Select and copy the value manually.','No se pudo copiar. Seleccione y copie el valor manualmente.'));}finally{release();}}
 function button(label,cls,fn){const b=d.createElement('button');b.type='button';b.className=cls;b.textContent=label;b.addEventListener('click',fn);return b;}
 function decorate(){
  d.querySelectorAll('#wrapper a[href]').forEach(link=>{
   if(link.dataset.scafSalesLink)return;let url;try{url=new URL(link.href);}catch(_){return;}
   if(url.origin!==location.origin)return;
   if(/^\/proposal\/\d+\/[^/]+\/?$/.test(url.pathname)){
    link.dataset.scafSalesLink='1';if(link.nextElementSibling?.classList.contains('sc-copy-proposal'))link.nextElementSibling.remove();const b=button(t('Copy link','Copiar enlace'),'btn btn-default btn-sm scaf-copy-link sc-copy-proposal',event=>copy(link.href,event.currentTarget));link.after(b);
   }
   if(d.body.classList.contains('scaf-payments')&&/\/admin\/payments\/payment\/\d+\/?$/.test(url.pathname)&&!url.hash){
    link.dataset.scafSalesLink='1';link.addEventListener('click',event=>{if(event.ctrlKey||event.metaKey||event.shiftKey)return;event.preventDefault();receiptDialog(link.href);});
   }
  });
  d.querySelectorAll('.sales-transaction-id').forEach(node=>{
   if(node.dataset.scafCopy||!node.textContent.trim())return;node.dataset.scafCopy='1';
   if(node.nextElementSibling?.classList.contains('sc-copy-transaction'))node.nextElementSibling.remove();
   node.after(button(t('Copy ID','Copiar ID'),'scaf-copy-id sc-copy-transaction',event=>copy(node.textContent.trim(),event.currentTarget)));
  });
  d.querySelectorAll('.table-payments tbody tr').forEach(row=>{
   const cell=row.cells[2];if(!cell||cell.dataset.scafMode)return;
   const text=cell.textContent.trim();if(!text)return;
   cell.dataset.scafMode='1';const badge=d.createElement('span');badge.className='scaf-mode '+(/stripe/i.test(text)?'scaf-mode-stripe':/wire|ach|bank/i.test(text)?'scaf-mode-wire':/check|cheque/i.test(text)?'scaf-mode-check':'scaf-mode-other');badge.textContent=text;cell.replaceChildren(badge);
  });
  d.querySelectorAll('.table-credit-notes tbody tr').forEach(row=>{
   const badge=row.cells[3]?.querySelector('.s-status');if(!badge||badge.dataset.scafStatus)return;
   const entry=Object.entries(w.scafCreditStatuses||{}).find(([,label])=>label===badge.textContent.trim());
   if(entry){badge.dataset.scafStatus=entry[0];badge.classList.add('scaf-credit-status-'+entry[0]);}
  });
  const proposalForm=d.getElementById('proposal-form');
  if(proposalForm&&!proposalForm.dataset.scafSections){
   proposalForm.dataset.scafSections='1';
   function section(container,label){if(!container)return;const heading=d.createElement('h3');heading.className='scaf-form-section-title';heading.textContent=label;container.prepend(heading);}
   section(proposalForm.querySelector('[name=rel_type]')?.closest('.col-md-6'),t('Customer relationship','Relación con el cliente'));
   section(proposalForm.querySelector('[name=date]')?.closest('.row'),t('Proposal dates','Fechas de la propuesta'));
   section(proposalForm.querySelector('.sc-proposal-content-editor'),t('Items & pricing','Artículos y precios'));
   // Cost, margin and draw allowances are not native proposal fields; never invent them from sale totals.
  }
  const form=d.getElementById('record_payment_form');if(form&&!form.closest('#scaf-payment-dialog'))paymentDialog(form);
  d.querySelectorAll('#apply_credits').forEach(allocation);
  batch();
 }
 function paymentDialog(form){
  let modal=d.getElementById('scaf-payment-dialog');
  if(!modal){modal=d.createElement('div');modal.id='scaf-payment-dialog';modal.className='modal fade scaf-sales-dialog';modal.tabIndex=-1;modal.setAttribute('role','dialog');modal.setAttribute('aria-modal','true');modal.setAttribute('aria-label',t('Record payment','Registrar pago'));modal.innerHTML='<div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button><h4></h4></div><div class="modal-body"></div></div></div>';d.body.append(modal);}
  modal.querySelector('h4').textContent=t('Record payment','Registrar pago');modal.querySelector('.modal-body').append(form);
  form.querySelectorAll('a[onclick]').forEach(a=>{if(!/^init_invoice\(/.test(a.getAttribute('onclick').trim()))return;a.addEventListener('click',event=>{event.preventDefault();event.stopImmediatePropagation();$(modal).modal('hide');},true);});
  $(modal).off('hidden.bs.modal.scaf').on('hidden.bs.modal.scaf',()=>{const id=form.querySelector('[name=invoiceid]')?.value;form.remove();if(id&&typeof w.init_invoice==='function')w.init_invoice(id);});
  $(modal).modal('show');
 }
 function receiptDialog(url){
  let modal=d.getElementById('scaf-receipt-dialog');if(modal)modal.remove();
  modal=d.createElement('div');modal.id='scaf-receipt-dialog';modal.className='modal fade scaf-sales-dialog';modal.tabIndex=-1;modal.setAttribute('role','dialog');modal.setAttribute('aria-modal','true');modal.setAttribute('aria-label',t('Payment receipt','Recibo de pago'));
  const box=d.createElement('div');box.className='modal-dialog modal-lg';const body=d.createElement('div');body.className='modal-content';const close=button(t('Close','Cerrar'),'btn btn-default',()=>$(modal).modal('hide'));const frame=d.createElement('iframe');frame.src=url+'?scaf_receipt=1';frame.title=t('Payment receipt','Recibo de pago');frame.className='scaf-receipt-frame';body.append(close,frame);box.append(body);modal.append(box);d.body.append(modal);$(modal).modal('show');$(modal).on('hidden.bs.modal',()=>modal.remove());
 }
 // Integer minor units: no binary floating point allocation authority, no rounding up.
 function units(value,places){if(!/^\d+(\.\d+)?$/.test(String(value)))return null;const [a,b='']=String(value).split('.');if(b.length>places)return null;return BigInt(a)*10n**BigInt(places)+BigInt(b.padEnd(places,'0')||'0');}
 function decimal(value,places){const base=10n**BigInt(places);return String(value/base)+(places?'.'+String(value%base).padStart(places,'0'):'');}
 function allocation(modal){
  if(modal.dataset.scafAllocation)return;
  const fields=Array.from(modal.querySelectorAll('.apply-credits-field'));if(!fields.length)return;
  modal.dataset.scafAllocation='1';
  const places=Math.max(0,Math.min(8,Number(w.scafConfig?.decimals)||0),...fields.map(f=>(f.step.split('.')[1]||'').length),(String(modal.dataset.creditsRemaining).split('.')[1]||'').length);
  const available=units(modal.dataset.creditsRemaining,places);if(available===null)return;
  const limits=fields.map(f=>units(f.max,places));if(limits.some(x=>x===null))return;
  const out=d.createElement('p');out.className='scaf-allocation-remaining';out.setAttribute('role','status');out.setAttribute('aria-live','polite');modal.querySelector('.modal-body').append(out);
  function refresh(){let sum=0n,valid=true;fields.forEach(f=>{const n=units(f.value||'0',places);if(n===null){valid=false;f.setCustomValidity(t('Enter a valid monetary amount.','Introduzca un importe monetario válido.'));}else{sum+=n;f.setCustomValidity('');}});
   fields.forEach((f,i)=>{const own=units(f.value||'0',places)||0n;const left=available-(sum-own);const max=left<limits[i]?left:limits[i];f.max=decimal(max>0n?max:0n,places);if(own>limits[i]||sum>available){valid=false;f.setCustomValidity(t('Allocation exceeds invoice or available credit.','La asignación supera la factura o el crédito disponible.'));}});
   out.textContent=t('Remaining credit after allocation: ','Crédito restante después de asignar: ')+decimal(sum<=available?available-sum:0n,places);
   modal.querySelectorAll('[type=submit]').forEach(b=>{b.disabled=!valid;});
  }
  fields.forEach((field,i)=>{field.after(button(t('Apply max','Aplicar máximo'),'btn btn-default btn-sm scaf-credit-max',()=>{let other=0n;fields.forEach(f=>{if(f!==field)other+=units(f.value||'0',places)||0n;});const left=available-other;field.value=decimal(left<limits[i]?(left>0n?left:0n):limits[i],places);field.dispatchEvent(new Event('input',{bubbles:true}));}));field.addEventListener('input',refresh);});
  modal.querySelector('form')?.addEventListener('submit',event=>{refresh();if(!event.target.checkValidity()){event.preventDefault();event.stopImmediatePropagation();event.target.reportValidity();}},true);refresh();
 }
 function batch(){
  if(!d.body.classList.contains('scaf-invoices'))return;
  const table=d.querySelector('.table-invoices');if(!table)return;
  const bar=d.getElementById('sc-bulk-invoices');if(!bar)return;
  const n=table.querySelectorAll('.sc-bulk-row:checked').length;
  bar.classList.toggle('scaf-floating-batch',n>0);bar.setAttribute('aria-label',t('Selected invoice actions','Acciones de facturas seleccionadas'));
  if(!bar.querySelector('.scaf-batch-pdf'))bar.append(button(t('Review PDFs / print','Revisar PDF / imprimir'),'btn btn-default btn-sm scaf-batch-pdf',()=>{
   let modal=d.getElementById('scaf-batch-pdfs');if(modal)modal.remove();
   modal=d.createElement('div');modal.id='scaf-batch-pdfs';modal.className='modal fade';modal.tabIndex=-1;modal.setAttribute('role','dialog');modal.setAttribute('aria-label',t('Selected invoice documents','Documentos de facturas seleccionadas'));
   modal.innerHTML='<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button><h4></h4></div><div class="modal-body"><ul></ul></div></div></div>';
   modal.querySelector('h4').textContent=t('Review selected PDFs and print','Revisar PDF seleccionados e imprimir');
   table.querySelectorAll('.sc-bulk-row:checked').forEach(cb=>{if(!/^\d+$/.test(cb.value))return;const li=d.createElement('li');const row=cb.closest('tr');const name=row?.querySelector('td:first-child a')?.textContent.trim()||'#'+cb.value;li.append(d.createTextNode(name+' · '));
    [[t('PDF','PDF'),'?output_type=I'],[t('Print','Imprimir'),'?print=true']].forEach(([label,query])=>{const a=d.createElement('a');a.href=(w.scafConfig?.adminUrl||'/admin/')+'invoices/pdf/'+cb.value+query;a.target='_blank';a.rel='noopener';a.className='btn btn-default btn-sm';a.textContent=label;li.append(a);});modal.querySelector('ul').append(li);
   });d.body.append(modal);$(modal).modal('show');$(modal).on('hidden.bs.modal',()=>modal.remove());
  }));
  const count=bar.querySelector('.sc-bulk-count');if(count){const text=n+' '+t('invoices selected','facturas seleccionadas');if(count.textContent!==text)count.textContent=text;}
 }
 // Keep Sales expanded without altering another parent section or authorizing extra links.
 function sidebar(){d.querySelectorAll('#side-menu a[href]').forEach(a=>{if(!/\/admin\/(proposals|invoices|payments|credit_notes|estimates)(\/|$)/.test(a.href))return;const submenu=a.closest('ul.nav-second-level');if(!submenu)return;submenu.classList.add('in','scaf-sales-submenu');submenu.setAttribute('aria-expanded','true');const li=submenu.parentElement;li.classList.add('active');const toggle=li.querySelector(':scope > a');if(toggle)toggle.setAttribute('aria-expanded','true');try{sessionStorage.setItem('scaf-sales-open','1');}catch(_){};});}
 const embeddedReceipt=new URLSearchParams(location.search).get('scaf_receipt')==='1'&&d.body.classList.contains('scaf-payments')&&/\/payments\/payment\/\d+\/?$/.test(location.pathname);
 if(embeddedReceipt){
  const receipt=d.getElementById('tab_receipt');
  if(receipt){
   d.body.classList.add('scaf-receipt-embedded');
   const root=d.createElement('main');root.id='scaf-receipt-root';root.setAttribute('aria-label',t('Payment receipt','Recibo de pago'));
   const actions=d.querySelector('.scaf-receipt-actions');if(actions)root.append(actions);
   root.append(receipt);d.body.append(root);
   // Keep native email dialog outside hidden legacy layout ancestors.
   const email=d.getElementById('payment_send_to_client');if(email)d.body.append(email);
  }
 }
 // Capture before the legacy delegated native confirm handler; keep its exact authorized endpoint.
 d.addEventListener('click',async event=>{
  const source=event.target.closest('.sc-bulk-delete');if(!source)return;
  const bar=source.closest('.sc-bulk-actions'),wrap=bar?.closest('.dataTables_wrapper'),table=wrap?.querySelector('table');
  if(!table)return;
  const checked=Array.from(table.querySelectorAll('.sc-bulk-row:checked'));
  const feature=checked[0]?.dataset.feature;
  if(!['invoices','proposals','estimates','credit_notes','payments'].includes(feature))return;
  event.preventDefault();event.stopImmediatePropagation();
  const ids=checked.map(cb=>cb.value).filter(id=>/^\d+$/.test(id));if(!ids.length)return;
  const release=api.busy(source);
  try{
   if(!await api.confirm({title:t('Delete selected records?','¿Eliminar registros seleccionados?'),message:ids.length+' '+t('selected records. Existing server safeguards still apply.','registros seleccionados. Se aplican las protecciones del servidor.'),typed:'DELETE',actionLabel:t('Delete','Eliminar')}))return;
   const result=await $.ajax({url:(w.scafConfig?.adminUrl||'/admin/')+'smart_choice_bulk/delete/'+feature,type:'POST',data:{ids:ids},dataType:'json'});
   if(!result||result.success!==true||!Number.isInteger(result.deleted)||result.deleted<1){toast('error',t('No deletion was confirmed.','No se confirmó ninguna eliminación.'));return;}
   const failed=Array.isArray(result.failed)?result.failed.length:Math.max(0,ids.length-result.deleted);
   toast(failed?'warning':'success',result.deleted+' '+t('deleted','eliminados')+(failed?' · '+failed+' '+t('not deleted','sin eliminar'):''));
   if($.fn.DataTable?.isDataTable(table))$(table).DataTable().ajax.reload(null,false);
  }catch(_){toast('error',t('Unable to confirm deletion. Refresh before retrying.','No se pudo confirmar la eliminación. Actualice antes de reintentar.'));}finally{release();}
 },true);
 $(d).on('change.scafSales draw.dt init.dt',()=>{decorate();});
 decorate();sidebar();new MutationObserver(decorate).observe(d.getElementById('wrapper')||d.body,{childList:true,subtree:true});
})(window,document,window.jQuery);
