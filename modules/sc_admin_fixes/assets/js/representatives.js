(function(w,d,$){'use strict';if(!$||!d.body.classList.contains('scaf-sales_center'))return;
 function selection(){const n=d.querySelectorAll('.scaf-rep-table .sales-center-row-checkbox:checked').length;const bar=d.querySelector('.scaf-rep-batch');if(bar){bar.hidden=!n;bar.querySelector('[data-rep-count]').textContent=n+' '+((d.documentElement.lang||'').startsWith('es')?'seleccionados':'selected');}}
 d.addEventListener('change',selection);
 d.addEventListener('click',e=>{
  if(e.target.closest('[data-rep-clear]')){d.querySelectorAll('.scaf-rep-table .sales-center-row-checkbox').forEach(c=>c.checked=false);selection();}
  if(e.target.closest('[data-rep-review]')){let modal=d.getElementById('scaf-rep-review');if(modal)modal.remove();modal=d.createElement('div');modal.id='scaf-rep-review';modal.className='modal fade';modal.tabIndex=-1;modal.innerHTML='<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">×</button><h4>Selected representatives</h4></div><div class="modal-body"><ul></ul></div></div></div>';d.querySelectorAll('.scaf-rep-table .sales-center-row-checkbox:checked').forEach(c=>{const source=c.closest('tr').querySelector('a[href]'),li=d.createElement('li'),a=d.createElement('a');a.href=source.href;a.textContent=source.textContent;li.append(a);modal.querySelector('ul').append(li);});d.body.append(modal);$(modal).modal('show');}
  const button=e.target.closest('[data-rep-editor]');if(button){const textarea=d.getElementById('content');if(!textarea||textarea.dataset.repEditor)return;textarea.dataset.repEditor='1';if(w.tinymce?.get('content'))return;if(typeof w.init_editor==='function'){w.init_editor('#content');button.hidden=true;}}
 });
})(window,document,window.jQuery);
