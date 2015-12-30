
/* globals ajaxurl, wp */

jQuery( function($){

    var api = wp.customize;

    var premiumModal;

    api.SoTeaserControl = api.Control.extend( {
        ready: function () {
            var control = this;
            var container = control.container;

            container.find('.so-premium-upgrade').click( function( e ){
                // We can show a modal here at some point, for now we just direct users to SiteOrigin
            } );
        }
    } );

    api.controlConstructor['siteorigin-teaser'] = api.SoTeaserControl ;
} );