( function( $ ){
	var api = wp.customize;

	var hideSections = false;
	var showSections = false;
	api.bind( 'pane-contents-reflowed', function(){
		if( hideSections ) {
			hideSections.hide();
		}
		if( showSections ) {
			showSections.show();
		}
	} );

	$(function(){
		api.previewer.bind( 'page-settings', function( message ) {
			// accordion-section-page_settings_template_home
			var $section = $( '#accordion-section-page_settings_' + message[0] + '_' + message[1] ),
				$all_sections = $( '[id^="accordion-section-page_settings_"]' ),
				$open = $( '[id^="accordion-section-page_settings_"].open' );

			if( $open.length ) {
				// We'll return to the Page Settings section
				$( '#accordion-panel-page_settings .accordion-section-title' ).trigger( 'click' );
			}

			hideSections = $all_sections.not( $section ).hide();
			showSections = $section.show();
		} );
	});
} )( jQuery );
