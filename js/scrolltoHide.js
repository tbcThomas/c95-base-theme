var $ =jQuery.noConflict();
$(document).ready(function() {

   $(function() {
      var header = $('.headerDiv-move');
      $(window).scroll(function() {
            var scroll = $(window).scrollTop();

            if (scroll >= 150) {
                  header.removeClass('headerDiv-move').addClass('headerDiv-move-up');
            } else {
                  header.removeClass('headerDiv-move-up').addClass('headerDiv-move');
            }
      });
   });

});
