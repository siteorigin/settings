
/* globals soSettings, jQuery */

jQuery( function($){

    var $style = $('style[data-siteorigin-settings="true"]');
    if( $style.length === 0 ) {
        $style = $('<style type="text/css" id="siteorigin-settings-css" data-siteorigin-settings="true"></style>').appendTo('head');
    }

    var updateCss = function(){
        // Create a copy of the CSS
        var css = JSON.parse(JSON.stringify(soSettings.css));
        var re;
        for( var k in soSettings.settings ) {
            re = new RegExp('@\{' + k + '\}', 'i');
            css = css.replace( re, soSettings.settings[k] );
        }
        $style.html( css );
    };
    updateCss();

    if( soSettings.settings !== false && soSettings.css !== '' ) {

        $.each( soSettings.settings, function(k, setting){
            wp.customize( 'theme_settings_' + k, function( value ) {
                value.bind( function( newval ) {
                    soSettings.settings[k] = newval;
                    updateCss();
                } );
            } );
        } );
    }
} );