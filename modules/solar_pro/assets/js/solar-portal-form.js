(function(){
 'use strict';
 var busy=false;
 function replacePage(html,url){
  var next=new DOMParser().parseFromString(html,'text/html'),root=next.querySelector('.sp-public'),current=document.querySelector('.sp-public');
  if(!root||!current)throw new Error('The form could not be loaded.');
  // Move the actual controls, including selected files, without storing contact data in browser storage.
  var old=Array.from(current.querySelectorAll('input,select,textarea'));
  root.querySelectorAll('input,select,textarea').forEach(function(fresh){
   if(fresh.type==='hidden')return;
   var control=old.find(function(x){return x.name===fresh.name&&x.type===fresh.type&&(fresh.type!=='radio'||x.value===fresh.value);});
   if(control)fresh.replaceWith(control);
  });
  current.replaceWith(root);document.title=next.title;document.documentElement.lang=new URL(url,location.href).searchParams.get('lang')||'en';history.replaceState(null,'',url);
 }
 async function load(url,options){
  if(busy)return;busy=true;
  try{
   var response=await fetch(url,options);if(!response.ok)throw new Error('Unable to load the form. Please try again.');
   if(response.redirected&&!new URL(response.url).pathname.endsWith('/estimate')){location.assign(response.url);return;}
   replacePage(await response.text(),response.url);
  }catch(error){window.alert(error.message+' Your entered information is still here.');}
  finally{busy=false;}
 }
 document.addEventListener('click',function(event){var link=event.target.closest('.sp-language-button');if(!link||event.ctrlKey||event.metaKey||event.shiftKey)return;event.preventDefault();load(link.href,{credentials:'same-origin'});});
 document.addEventListener('submit',function(event){var form=event.target;if(!form.closest('.sp-form-card'))return;event.preventDefault();load(form.action,{method:'POST',body:new FormData(form),credentials:'same-origin'});});
})();
