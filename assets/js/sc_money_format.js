(function(window,document,$){'use strict';
function node(root){if(root&&root.originalEvent)root=root.target||root.currentTarget;if(root&&root.jquery)root=root[0];if(root&&root.nodeType===9)return root;if(root&&typeof root.querySelectorAll==='function')return root;return document}
function isProtectedInput(el){var n=(el.name||'').toLowerCase(),id=(el.id||'').toLowerCase(),c=(el.className||'').toString().toLowerCase();if(el.dataset&&el.dataset.scMoneyIgnore==='1')return true;if(n.indexOf('settings[')===0||n.indexOf('paymentmethod_')!==-1||id.indexOf('paymentmethod_')!==-1)return true;if(/api|key|secret|token|webhook|password|username|credential|client_id|client_secret|publishable|identifier|gateway/.test(n+' '+id+' '+c))return true;if(el.closest&&el.closest('#settings-form'))return true;return false}
function isMoneyInput(el){if(!el||isProtectedInput(el))return false;var n=(el.name||'').toLowerCase(),id=(el.id||'').toLowerCase(),c=(el.className||'').toString().toLowerCase();return /amount|rate|price|total|balance|subtotal|discount|adjustment|deposit|cost/.test(n+' '+id+' '+c)&&!/percent|percentage|qty|quantity|tax/.test(n+' '+id)}
function clean(v){return String(v==null?'':v).replace(/[^0-9.\-]/g,'')}
function format(v){var raw=clean(v);if(raw===''||raw==='-'||raw==='.')return raw;var neg=raw.charAt(0)==='-';raw=raw.replace(/-/g,'');var ps=raw.split('.'),whole=(ps.shift().replace(/^0+(?=\d)/,'')||'0').replace(/\B(?=(\d{3})+(?!\d))/g,',');return(neg?'-':'')+whole+(ps.length?'.'+ps.join('').slice(0,2):'')}
function prepareValidation(el){
  if(!$||!$.validator||!el.form||!$(el.form).data('validator'))return;
  var input=$(el),rules=input.rules(),previous=rules.normalizer||$(el.form).data('validator').settings.normalizer;
  if(previous&&previous.scMoneyNormalizer)return;
  var normalizer=function(value){
    if(previous)value=previous.call(this,value);
    // Group separators are presentation only. Keep max/min/number rules intact.
    return String(value).replace(/,/g,'');
  };
  normalizer.scMoneyNormalizer=true;
  input.rules('add',{normalizer:normalizer});
}
function prepare(el){if(!el||!isMoneyInput(el))return;if(el.dataset.scMoneyReady!=='1'){el.dataset.scMoneyReady='1';if(el.type==='number')el.type='text';el.inputMode='decimal';if(el.value)el.value=format(el.value)}prepareValidation(el)}
function scan(root){Array.prototype.forEach.call(node(root).querySelectorAll('input'),prepare)}
document.addEventListener('focusin',function(e){prepare(e.target)});document.addEventListener('input',function(e){var el=e.target;if(!el||el.dataset.scMoneyReady!=='1')return;var p=el.selectionStart,b=el.value.length;el.value=format(el.value);try{var x=Math.max(0,p+el.value.length-b);el.setSelectionRange(x,x)}catch(z){}},true);document.addEventListener('submit',function(e){Array.prototype.forEach.call(node(e.target).querySelectorAll('input[data-sc-money-ready="1"]'),function(el){el.value=clean(el.value)})},true);if($){$(document).ajaxComplete(function(){scan(document)});$(function(){scan(document)})}else document.addEventListener('DOMContentLoaded',function(){scan(document)})})(window,document,window.jQuery);
