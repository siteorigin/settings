
/* globals jQuery, wp */

jQuery( function($){

    var api = wp.customize;

    /**
     * The font control object
     */
    api.SoFontControl = api.Control.extend({
        ready: function(){
            var control = this;

            var $f = control.container.find('select.font'),
                $v = control.container.find('select.font-variant'),
                $s = control.container.find('select.font-subset');

            $f.change( function(){
                var $fs = $(this).find('option:selected');
                $v.empty().val('');
                $s.empty().val('');

                if( $fs.data('variants') !== undefined ) {
                    // Lets populate the variants and subsets
                    $.each( $fs.data('variants').split(','), function(i, v){
                        $v.append( $("<option></option>").html(v) );
                    } );

                    if( $v.find('option').length > 1 ) {
                        $v.show();
                    }
                    else {
                        $v.hide();
                    }
                }
                else {
                    $v.hide();
                }

                if( $fs.data('subsets') !== undefined ) {
                    // Lets populate the variants and subsets
                    $.each( $fs.data('subsets').split(','), function(i, v){
                        $s.append( $("<option></option>").html(v) );
                    } );
                    $s.val('latin');

                    if( $s.find('option').length > 1 ) {
                        $s.show();
                    }
                    else {
                        $s.hide();
                    }
                }
                else {
                    $s.hide();
                }
            } );

            var changeValue = function(){
                var val = {};
                val.font = $f.val();
                val.webfont = $f.find('option:selected').data('webfont');
                val.category = $f.find('option:selected').data('category');
                val.variant = $v.val();
                val.subset = $s.val();

                control.setting.set( JSON.stringify(val) );
            };

            control.container.find('select').change(changeValue);

            // Now, lets set everything up to start
            if( control.setting() !== '' ) {
                var vals = JSON.parse( control.setting() );
                $f.val( vals.font).change();
                $v.val( vals.variant );
                $s.val( vals.subset );
            }
            else {
                $f.change();
            }

            var chosen = null;
            api.section( control.section() ).container
                .on( 'expanded', function() {
                    if( chosen === null ){
                        $f.chosen({
                            allow_single_deselect: true,
                            search_contains: true
                        });
                        chosen = true;
                    }
                });

        }
    });

    // Register this control object
    api.controlConstructor['siteorigin-font'] = api.SoFontControl;

} );