(function(window, document, $){
'use strict';
function isMoneyInput(el){
  var n=(el.name||'').toLowerCase(), id=(el.id||'').toLowerCase(), cls=(el.className||'').toString().toLowerCase();
  return /amount|rate|price|total|value|balance|payment|subtotal|discount|adjustment|deposit|cost/.test(n+' '+id+' '+cls) && !/percent|percentage|qty|quantity|tax/.test(n+' '+id);
}
function clean(v){ return String(v||'').replace(/[^0-9.\-]/g,''); }
function format(v){
  var raw=clean(v); if(raw===''||raw==='-'||raw==='.') return raw;
  var neg=raw.charAt(0)==='-'; raw=raw.replace(/-/g,'');
  var parts=raw.split('.'); var whole=parts.shift().replace(/^0+(?=\d)/,'')||'0';
  whole=whole.replace(/\B(?=(\d{3})+(?!\d))/g,',');
  return (neg?'-':'')+whole+(parts.length?'.'+parts.join('').slice(0,2):'');
}
function prepare(el){
  if(!isMoneyInput(el)||el.dataset.scMoneyReady==='1') return;
  el.dataset.scMoneyReady='1';
  if(el.type==='number') el.type='text';
  el.inputMode='decimal'; el.autocomplete='off';
  if(el.value) el.value=format(el.value);
}
function scan(root){
  if(root && root.jquery) root=root[0];
  if(root && root.target && root.target.nodeType) root=root.target;
  if(!root || typeof root.querySelectorAll!=='function') root=document;
  root.querySelectorAll('input').forEach(prepare);
}
document.addEventListener('focusin',function(e){ if(e.target&&e.target.tagName==='INPUT') prepare(e.target); });
document.addEventListener('input',function(e){ var el=e.target; if(!el||el.dataset.scMoneyReady!=='1') return; var p=el.selectionStart, before=el.value.length; el.value=format(el.value); var diff=el.value.length-before; try{el.setSelectionRange(Math.max(0,p+diff),Math.max(0,p+diff));}catch(x){} });
document.addEventListener('submit',function(e){ e.target.querySelectorAll('input[data-sc-money-ready="1"]').forEach(function(el){el.value=clean(el.value);}); },true);
if($){ $(document).ajaxComplete(function(){scan(document);}); $(function(){scan(document);}); } else { document.addEventListener('DOMContentLoaded',function(){scan(document);}); }
})(window,document,window.jQuery);