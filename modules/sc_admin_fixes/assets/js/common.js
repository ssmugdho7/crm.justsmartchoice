(function (window, document, $) {
  'use strict';
  if (!$ || !document.body.classList.contains('scaf-enabled')) return;
  var cfg=window.scafConfig||{}, labels=cfg.labels||{}, pending=new WeakMap(), lastAction=null;
  var api=window.SCAF=window.SCAF||{};
  function text(message){var node=document.createElement('div');node.innerHTML=String(message==null?'':message).replace(/<br\s*\/?\s*>/gi,'\n');return node.textContent||'';}
  api.toast=function(type,message,duration){var toast=window.SCAFSonner;if(!toast)return;var key=({danger:'error',success:'success',warning:'warning',info:'info',error:'error'})[type]||'info';return toast[key](text(message),{duration:duration||4500});};
  window.alert_float=function(type,message,timeout){return api.toast(type,message,timeout);};
  // Native informational alerts have no return contract. Confirmation remains explicit and asynchronous.
  window.alert=function(message){api.toast('info',message);};
  api.busy=function(element){
    element=element&&element.jquery?element[0]:element;if(!element)return function(){};
    if(pending.has(element))return pending.get(element);
    var button=element.matches('form')?(element.querySelector('button[type="submit"],input[type="submit"],.customer-form-submiter')):element;
    var previousBusy=element.getAttribute('aria-busy'), previousButton=button&&button.getAttribute('aria-busy');
    element.setAttribute('aria-busy','true');element.classList.add('is-loading');if(button){button.classList.add('is-loading');button.setAttribute('aria-busy','true');}
    var done=false;function release(){if(done)return;done=true;element.classList.remove('is-loading');if(previousBusy===null)element.removeAttribute('aria-busy');else element.setAttribute('aria-busy',previousBusy);if(button){button.classList.remove('is-loading');if(previousButton===null)button.removeAttribute('aria-busy');else button.setAttribute('aria-busy',previousButton);}pending.delete(element);}
    pending.set(element,release);return release;
  };
  var confirmQueue=Promise.resolve();
  api.confirm=function(options){options=options||{};var run=function(){return new Promise(function(resolve){
    var oldFocus=document.activeElement, typed=String(options.typed||''), word=String(labels['word_'+typed.toLowerCase()]||typed), stage=typed?1:2, modal=$('<div class="modal fade scaf-confirm-modal" tabindex="-1" role="dialog" aria-modal="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title"></h4></div><div class="modal-body"><p class="scaf-confirm-message"></p><div class="scaf-confirm-input" hidden><label></label><input class="form-control" autocomplete="off" spellcheck="false"></div></div><div class="modal-footer"><button type="button" class="btn btn-default scaf-confirm-cancel"></button><button type="button" class="btn btn-primary scaf-confirm-accept"></button></div></div></div></div>');
    var titleId='scaf-confirm-'+Date.now()+'-'+Math.random().toString(16).slice(2);modal.find('.modal-title').attr('id',titleId).text(options.title||labels.confirm||'Confirm action');modal.attr('aria-labelledby',titleId);modal.find('.scaf-confirm-message').text(options.message||'');
    var input=modal.find('input'), label=modal.find('label'), inputId=titleId+'-input';
    var instruction=String(labels.type_to_confirm||'Type %s to confirm');
    instruction=instruction.indexOf('%s')!==-1?instruction.replace('%s',word):instruction+' ('+word+')';
    input.attr('id',inputId);label.attr('for',inputId).text(instruction);
    var accept=modal.find('.scaf-confirm-accept'),cancel=modal.find('.scaf-confirm-cancel'), settled=false, answer=false;
    cancel.text(labels.cancel||'Cancel');accept.text(stage===1?(labels.continue||'Continue'):(options.actionLabel||labels.confirm||'Confirm'));
    function finish(value){if(settled)return;answer=value;modal.modal('hide');}
    function matches(){var value=String(input.val()||'').trim();return value===word||value===typed;}
    cancel.on('click',function(){finish(false);});input.on('input',function(){accept.prop('disabled',stage===2&&typed!==''&&!matches());});
    accept.on('click',function(){if(stage===1){stage=2;modal.find('.scaf-confirm-input').prop('hidden',false);accept.text(options.actionLabel||labels.confirm||'Confirm').prop('disabled',true);input.trigger('focus');return;}if(typed&&!matches())return;finish(true);});
    modal.on('shown.bs.modal',function(){cancel.trigger('focus');});modal.on('hidden.bs.modal',function(){if(settled)return;settled=true;modal.remove();if(oldFocus&&document.contains(oldFocus))oldFocus.focus();resolve(answer);});
    $(document.body).append(modal);modal.modal({backdrop:'static',keyboard:true,show:true});
  });};var next=confirmQueue.then(run,run);confirmQueue=next.catch(function(){});return next;};
  api.t=function(english,spanish){return (document.documentElement.lang||'').indexOf('es')===0?spanish:english;};
  api.money=function(value,currency){try{return new Intl.NumberFormat(undefined,{style:'currency',currency:currency||'USD'}).format(Number(value));}catch(_){return String(value)+' '+String(currency||'');}};
  function units(value,places){places=places==null?(cfg.decimals===undefined?2:cfg.decimals):places;var match=String(value).trim().match(/^([+-]?)(\d*)(?:\.(\d*))?$/);if(!match||(!match[2]&&!match[3]))throw Error('Invalid decimal');var frac=match[3]||'',digits=(match[2]||'0')+frac.padEnd(places,'0').slice(0,places),n=BigInt(digits||'0');if(frac.length>places&&Number(frac[places])>=5)n+=1n;return match[1]==='-'?-n:n;}
  function decimal(n,places){places=places==null?(cfg.decimals===undefined?2:cfg.decimals):places;var sign=n<0n?'-':'';if(n<0n)n=-n;var digits=n.toString().padStart(places+1,'0');return sign+(places?digits.slice(0,-places)+'.'+digits.slice(-places):digits);}
  api.decimal={normalize:function(a,p){return decimal(units(a,p),p);},add:function(a,b,p){return decimal(units(a,p)+units(b,p),p);},sub:function(a,b,p){return decimal(units(a,p)-units(b,p),p);},compare:function(a,b,p){a=units(a,p);b=units(b,p);return a<b?-1:a>b?1:0;}};
  // Capture genuine clicks before legacy handlers, blocking repeats without changing submitter values.
  document.addEventListener('click',function(event){var target=event.target.closest('button,a,input[type="submit"]');if(!target)return;if(pending.has(target)||target.closest('form[aria-busy="true"]')){event.preventDefault();event.stopImmediatePropagation();return;}lastAction={element:target,at:Date.now()};
    if(!target.matches('a._delete')||target.hasAttribute('onclick'))return;var href=target.getAttribute('href');if(!href||href==='#')return;var url;try{url=new URL(href,window.location.href);}catch(_){return;}if(url.origin!==window.location.origin||!url.pathname.startsWith(new URL(cfg.adminUrl||'/admin/',window.location.origin).pathname))return;
    event.preventDefault();event.stopImmediatePropagation();var release=api.busy(target);api.confirm({title:labels.delete||'Delete',message:target.getAttribute('data-confirm')||labels.confirm||'Confirm action',typed:'DELETE',actionLabel:labels.delete||'Delete'}).then(function(confirmed){release();if(confirmed)window.location.assign(url.href);},release);
  },true);
  document.addEventListener('submit',function(event){var form=event.target;if(!form.matches('form'))return;if(pending.has(form)){event.preventDefault();event.stopImmediatePropagation();return;}if(form.checkValidity&&!form.checkValidity())return;var release=api.busy(form);window.setTimeout(function(){if(event.defaultPrevented&&!form.scafAjaxPending)release();},0);},true);
  $(document).ajaxSend(function(event,xhr,settings){if(!/^(POST|PUT|PATCH|DELETE)$/i.test(settings.type||'GET'))return;var action=lastAction&&Date.now()-lastAction.at<1500?lastAction.element:null,form=action&&action.closest('form'),release;if(form){form.scafAjaxPending=(form.scafAjaxPending||0)+1;release=api.busy(form);}else if(action)release=api.busy(action);if(!release)return;xhr.always(function(){if(form){form.scafAjaxPending=Math.max(0,(form.scafAjaxPending||1)-1);if(form.scafAjaxPending)return;}release();});});
  // Keep the active Sales submenu open without changing any other navigation group.
  if(['invoices','estimates','proposals','payments','credit_notes'].includes(cfg.page)){
    var sales=document.querySelector('#side-menu .menu-item-sales');if(sales){var submenu=sales.querySelector('ul');sales.classList.add('active');if(submenu){submenu.classList.add('in');submenu.style.height='auto';submenu.setAttribute('aria-expanded','true');}var link=sales.querySelector('a');if(link)link.setAttribute('aria-expanded','true');}
  }
  // Prevent DataTables' blocking error dialog; retain an observable error notification.
  if($.fn.dataTable)$.fn.dataTable.ext.errMode=function(settings,help,message){api.toast('error',message||labels.failed||'Unable to load records');};
})(window,document,window.jQuery);
