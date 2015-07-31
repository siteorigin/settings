
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

        // Now we also need to handle the CSS functions.
        // This should mirror what's in PHP - SiteOrigin_Settings::css_functions
        var match, replace, prepend, fargs;
        do {
            match =  css.match(/\.([a-z\-]+) *\(([^\)]+)\) *;/);
            if( match === null ) {
                break;
            }
            fargs = JSON.parse( match[2] );
            replace = '';
            prepend = '';

            switch( match[1] ) {
                case 'font':
                    if( fargs.webfont ) {
                        prepend = '@import url(//fonts.googleapis.com/css?';
                        prepend += 'family=' + encodeURIComponent( fargs.font ) + '|' + encodeURIComponent( fargs.variant );
                        prepend += '&subset=' + encodeURIComponent( fargs.subset );
                        prepend += '); ';
                    }

                    replace += 'font-family: "' + fargs.font + '", ' + fargs.category + '; ';

                    var weight;
                    if( fargs.variant.indexOf('italic' ) !== -1 ) {
                        weight = fargs.variant.replace('italic', '');
                        replace += 'font-style: italic; ';
                    }
                    else {
                        weight = fargs.variant;
                    }

                    if( fargs.variant == '' ) fargs.variant = 'regular';
                    replace += 'font-weight: ' + weight + '; ';


                    break;
            }

            css = css.replace( match[0], replace );
            css = prepend + css;
            console.log(css);
        } while( match !== null );

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