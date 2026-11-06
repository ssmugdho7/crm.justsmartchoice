(function(w,d,$){
 'use strict';
 if(!$||!d.body.classList.contains('scaf-workspace-recruitment'))return;
 const es=(d.documentElement.lang||'').startsWith('es'),t=(a,b)=>es?b:a;
 if(new URLSearchParams(location.search).get('scaf_candidate_drawer')==='1'){d.body.classList.add('scaf-candidate-embedded');return;}
 function close(){const drawer=d.getElementById('scaf-candidate-drawer');if(drawer){drawer.remove();d.body.classList.remove('scaf-candidate-drawer-open');}}
 function open(url,name){
  close();const drawer=d.createElement('aside');drawer.id='scaf-candidate-drawer';drawer.setAttribute('role','dialog');drawer.setAttribute('aria-modal','false');drawer.setAttribute('aria-label',t('Candidate workspace','Espacio de candidato'));
  const head=d.createElement('header'),title=d.createElement('h2');title.textContent=name||t('Candidate details','Detalles del candidato');head.append(title);
  const full=d.createElement('a');full.href=url.href;full.className='btn btn-default';full.textContent=t('Open full profile','Abrir perfil completo');head.append(full);
  const button=d.createElement('button');button.type='button';button.className='btn btn-default';button.textContent=t('Close','Cerrar');button.addEventListener('click',close);head.append(button);drawer.append(head);
  const loading=d.createElement('p');loading.setAttribute('role','status');loading.textContent=t('Loading candidate…','Cargando candidato…');drawer.append(loading);
  const frame=d.createElement('iframe');url.searchParams.set('scaf_candidate_drawer','1');frame.src=url.href;frame.title=t('Candidate profile and evaluations','Perfil y evaluaciones');frame.addEventListener('load',()=>{loading.remove();});drawer.append(frame);d.body.append(drawer);d.body.classList.add('scaf-candidate-drawer-open');button.focus();
 }
 $(d).on('click','a[href]',function(event){
  if(event.ctrlKey||event.metaKey||event.shiftKey||event.altKey||event.button)return;
  let url;try{url=new URL(this.href);}catch(_){return;}
  if(url.origin!==location.origin||!/^\/admin\/recruitment\/candidate\/\d+\/?$/.test(url.pathname))return;
  event.preventDefault();open(url,this.textContent.trim());
 });
 d.addEventListener('keydown',event=>{if(event.key==='Escape')close();});
})(window,document,window.jQuery);
