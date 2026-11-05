(function(){'use strict';
window.showAppointmentToast=function(title,message,type){SCAF.toast(type||'success',String(title||'')+(message?': '+message:''));};
// Keep the native booking validator and isSubmitting guard responsible for creating records.
// Common busy guards add visual feedback without removing submit button values from serialization.
})();
// Hold an action from the first click through confirmation and all native write requests.
(function($){'use strict';var pending=new Set(),sequence=0;
window.scafAppointmentBegin=function(key){
 if(pending.has(key))return null;pending.add(key);var writes=[],token='.scafAppointment'+(++sequence);
 var element=document.activeElement;if(element&&!element.matches('button,a,input'))element=null;
 var release=SCAF.busy(element);
 $(document).on('ajaxSend'+token,function(event,xhr,settings){if(/^(POST|PUT|PATCH|DELETE)$/i.test(settings.type||'GET')&&String(settings.url).indexOf('appointly/')!==-1)writes.push(new Promise(function(resolve){xhr.always(resolve);}));});
 return async function(){ $(document).off(token);try{await Promise.allSettled(writes);}finally{pending.delete(key);release();} };
};
})(jQuery);
