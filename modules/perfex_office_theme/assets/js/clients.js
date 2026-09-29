(function($){
  "use strict";

  function smartChoiceClientMobileMenuFix(){
    if ($(window).width() <= 768) {
      $('body.customers .navbar-nav li a').each(function(){
        $(this).css({
          'white-space':'normal',
          'word-break':'normal'
        });
      });
    }
  }

  smartChoiceClientMobileMenuFix();
  $(window).on('resize', smartChoiceClientMobileMenuFix);

})(jQuery);
