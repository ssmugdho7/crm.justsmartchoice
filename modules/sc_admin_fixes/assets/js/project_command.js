(function($){'use strict';$(function(){
 var command=document.querySelector('[data-scaf-command]'),content=document.querySelector('#wrapper>.content');if(!command||!content)return;content.prepend(command);
 var old=document.querySelector('.project-due-notice');if(old)old.hidden=true;
 $(document).on('click','[data-scaf-quick-task]',function(){new_task_from_relation(undefined,'project',Number(this.dataset.scafQuickTask));});
 $(document).on('click','[data-scaf-open-task]',function(){init_task_modal(Number(this.dataset.scafOpenTask));});
});})(jQuery);
