( function( $ ) {

  'use strict';

  $(document).ready(function($){

    $('.tmv-info-notice .btn-dismiss').on('click',function(e) {

      e.preventDefault();

      var $this = $(this);

      var userid = $(this).data('userid');
      var nonce = $(this).data('nonce');

      $.ajax({
        type     : 'GET',
        dataType : 'json',
        url      : ajaxurl,
        data     : {
          'action'   : 'medias_dismiss',
          'userid'   : userid,
          '_wpnonce' : nonce
        },
        success  : function (response) {
          if ( true === response.status ) {
            $this.parents('.tmv-info-notice').fadeOut('slow');
          }
        }
      });
    });
  });

} )( jQuery );
