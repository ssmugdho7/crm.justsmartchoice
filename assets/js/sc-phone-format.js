(function(w,d,$){
'use strict';
function isPhone(el){var n=((el.name||'')+' '+(el.id||'')+' '+(el.getAttribute('data-type')||'')).toLowerCase();return el.hasAttribute('data-sc-phone')||/(^|[_-])(phone|phonenumber|telephone|mobile|cell)([_-]|$)/.test(n)||n==='phone'||n==='phonenumber';}
function digits(v){v=(v||'').replace(/\D/g,'');if(v.length>10&&v.charAt(0)==='1')v=v.slice(1);return v.slice(0,10);}
function fmt(v){var x=digits(v),o='+1 ';if(!x)return '';o+='('+x.slice(0,3);if(x.length>=3)o+=') ';if(x.length>3)o+=x.slice(3,7);if(x.length>7)o+=' '+x.slice(7,10);return o;}
function prep(el){if(!isPhone(el))return;el.setAttribute('inputmode','tel');el.setAttribute('placeholder','+1 (###) #### ###');el.setAttribute('pattern','\\+1 \\(\\d{3}\\) \\d{4} \\d{3}');if(el.value)el.value=fmt(el.value);}
$(function(){$('input').each(function(){prep(this);});});
$(d).on('focus input blur','input',function(){if(!isPhone(this))return;this.value=fmt(this.value);if(this.value&&digits(this.value).length!==10)this.setCustomValidity('Use +1 (###) #### ###');else this.setCustomValidity('');});
$(d).on('submit','form',function(){$(this).find('input').each(function(){if(isPhone(this)&&this.value)this.value=fmt(this.value);});});
})(window,document,jQuery);
