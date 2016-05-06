( function( $ ){
	var api = wp.customize;

	$(function(){
		api.previewer.bind( 'page-settings', function( message ) {
			// accordion-section-page_settings_template_home
			var $section = $( '#accordion-section-page_settings_' + message[0] + '_' + message[1] );
			var $all_sections = $('[id^="accordion-section-page_settings_"]');

			console.log( $section.length );
			console.log( $all_sections.length );

			$all_sections.not( $section ).hide();
			$section.show();
		} );
	});
} )( jQuery );
