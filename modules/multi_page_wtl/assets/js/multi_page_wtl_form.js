"use strict";
(function($){
  var animating=false;
  function duration($form){var d=parseInt($form.data('duration'),10);return isNaN(d)?450:Math.max(100,Math.min(2000,d));}
  function effect($form){return $form.data('transition')||'slide_fade';}
  function validateCurrent($form){return $form.valid();}
  function transition($from,$to,forward){
    if(animating)return false;animating=true;
    var $form=$from.closest('form'), d=duration($form), e=effect($form);
    $to.prop('disabled',false).show();
    var done=function(){$from.hide().prop('disabled',true).css({opacity:'',left:'',transform:''});$to.css({opacity:'',left:'',transform:''});animating=false;$('html,body').animate({scrollTop:Math.max(0,$form.offset().top-20)},180);};
    if(e==='none'){done();return true;}
    if(e==='fade'){$to.css({opacity:0}).animate({opacity:1},d);$from.animate({opacity:0},d,done);return true;}
    if(e==='scale'){$to.css({opacity:0,transform:'scale(.94)'});$from.animate({opacity:0},d/2,function(){$to.css({transition:'all '+d+'ms ease',opacity:1,transform:'scale(1)'});setTimeout(done,d);});return true;}
    if(e==='flip'){$to.css({opacity:0,transform:'rotateY('+(forward?'18deg':'-18deg')+')'});$from.css({transition:'all '+d+'ms ease',transform:'rotateY('+(forward?'-18deg':'18deg')+')',opacity:0});$to.css({transition:'all '+d+'ms ease',transform:'rotateY(0)',opacity:1});setTimeout(done,d);return true;}
    $to.css({opacity:0,left:(forward?'35%':'-35%')});$from.animate({opacity:0,left:(forward?'-20%':'20%')},d);$to.animate({opacity:1,left:'0%'},d,done);return true;
  }
  $(function(){
    var $forms=$('.steps');
    $forms.each(function(){
      var $f=$(this);
      $f.validate({errorClass:'text-danger',errorElement:'p',errorPlacement:function(error,element){error.insertAfter(element);}});
    });
    $('body').on('click','.steps .next[type="button"], .steps input.next[type="button"]',function(e){
      e.preventDefault();var $form=$(this).closest('form');if(!validateCurrent($form))return false;
      var $current=$(this).closest('fieldset'),$next=$current.next('fieldset');if(!$next.length)return false;
      var idx=$('fieldset',$form).index($next);$('#progressbar li',$form).eq(idx).addClass('active');$('.wizard li',$form).eq(idx).addClass('completed active').siblings().removeClass('active');
      return transition($current,$next,true);
    });
    $('body').on('click','.steps .previous',function(e){
      e.preventDefault();var $form=$(this).closest('form'),$current=$(this).closest('fieldset'),$prev=$current.prev('fieldset');if(!$prev.length)return false;
      var idx=$('fieldset',$form).index($current);$('#progressbar li',$form).eq(idx).removeClass('active');$('.wizard li',$form).eq(idx).removeClass('completed active');$('.wizard li',$form).eq(idx-1).addClass('active');
      return transition($current,$prev,false);
    });
  });
})(jQuery);
