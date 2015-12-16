
/* globals ajaxurl, wp */

jQuery( function($){

    var api = wp.customize;

    var premiumModal;

    api.SoTeaserControl = api.Control.extend( {
        ready: function () {
            var control = this;
            var container = control.container;

            container.find('.so-premium-upgrade').click( function( e ){
                e.preventDefault();

                // Setup the modal
                if( typeof premiumModal === 'undefined' ) {
                    premiumModal = $( $('#so-premium-modal-template').html().trim() ).hide().appendTo( 'body' ).fadeIn( 'fast' );

                    // Setup the premium modal
                    premiumModal.find( '.so-modal-close').click( function(){
                        premiumModal.fadeOut('fast');
                    } );

                    $.get( ajaxurl + "?action=so_settings_premium_content", function( r ){
                        console.log( r );
                    } );
                }
                else {
                    premiumModal.fadeIn('fast');
                }
            } );
        }
    } );

    api.controlConstructor['siteorigin-teaser'] = api.SoTeaserControl ;
} );