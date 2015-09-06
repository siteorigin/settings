<?php

/**
 * A basic settings class used to add settings metaboxes to pages.
 *
 * Class SiteOrigin_Settings_Page_Settings
 */
class SiteOrigin_Settings_Page_Settings {

	private $settings;
	private $meta;

	function __construct(){
		$this->meta = array();
		$this->settings = array();

		add_action( 'add_meta_boxes', array($this, 'add_meta_box') );
		add_action( 'save_post', array($this, 'save_post') );
	}

	/**
	 * Get the singular instance
	 *
	 * @return SiteOrigin_Settings_Page_Settings
	 */
	static function single(){
		static $single;
		if( empty($single) ) {
			$single = new SiteOrigin_Settings_Page_Settings();
		}

		return $single;
	}

	/**
	 * Get a settings value
	 *
	 * @param $key
	 *
	 * @return null
	 */
	static function get( $key ) {
		$single = self::single();

		global $post;
		if( empty($single->meta[ $post->ID ] ) ) {
			$single->meta[ $post->ID ] = $single->get_post_meta( $post->ID );
		}
		return isset( $single->meta[ $post->ID ][ $key ] ) ? $single->meta[ $post->ID ][ $key ] : null;
	}

	/**
	 * Get the settings post meta and add the default values.
	 *
	 * @param $post_id
	 *
	 * @return array|mixed
	 */
	function get_post_meta( $post_id ){
		$values = get_post_meta( $post_id, 'siteorigin_page_settings', true );
		if( empty($values) ) $values = array();

		foreach( $this->settings as $id => $field ) {
			if( !isset($values[$id]) && isset($field['default']) ) {
				$values[$id] = $field['default'];
			}
		}

		return $values;
	}

	/**
	 * Add the metabox
	 */
	function add_meta_box(){
		add_meta_box(
			'siteorigin_page_settings',
			SiteOrigin_Settings::single()->get_localization_term( 'meta_box' ),
			array( $this, 'display_meta_box' ),
			'page'
		);
	}

	/**
	 * Display the Meta Box
	 */
	function display_meta_box( $post ){

		$values = $this->get_post_meta( $post->ID );

		foreach( $this->settings as $id => $field ) {
			?><p><label for="so-page-settings-<?php echo esc_attr( $id ) ?>"><strong><?php echo esc_html( $field['label'] ) ?></strong></label></p><?php

			switch( $field['type'] ) {

				case 'select' :
					?>
					<select name="so_page_settings[<?php echo esc_attr( $id ) ?>]" id="so-page-settings-<?php echo esc_attr( $id ) ?>">
						<?php foreach( $field['options'] as $v => $n ) : ?>
							<option value="<?php echo esc_attr( $v ) ?>" <?php selected( $values[$id], $v ) ?>><?php echo esc_html( $n ) ?></option>
						<?php endforeach; ?>
					</select>
					<?php

					break;

				case 'checkbox' :
					?>
					<label><input type="checkbox" name="so_page_settings[<?php echo esc_attr( $id ) ?>]" <?php checked( $values[$id] ) ?> /><?php echo esc_html($field['checkbox_label']) ?></label>
					<?php
					break;

				case 'text' :
				default :
					?><input type="text" name="so_page_settings[<?php echo esc_attr( $id ) ?>]" id="so-page-settings-<?php echo esc_attr( $id ) ?>" value="<?php echo esc_attr( $values[$id] ) ?>" /><?php
					break;

			}

			if( !empty($field['description']) ) {
				?><p class="description"><?php echo esc_html( $field['description'] ) ?></p><?php
			}
		}

		wp_nonce_field( 'save_page_settings', '_so_page_settings_nonce' );
	}

	/**
	 * Save settings
	 *
	 * @param $post_id
	 */
	function save_post( $post_id ){
		if( !current_user_can( 'edit_post', $post_id ) ) return;
		if( empty($_POST['_so_page_settings_nonce']) || !wp_verify_nonce( $_POST['_so_page_settings_nonce'], 'save_page_settings' ) ) return;
		if( empty($_POST['so_page_settings']) ) return;

		$settings = stripslashes_deep( $_POST['so_page_settings'] );

		foreach( $this->settings as $id => $field ) {
			switch( $field['type'] ) {
				case 'select' :
					if( !in_array( $settings[$id], array_keys( $field['options'] ) ) ) {
						$settings[$id] = isset($field['default']) ? $field['default'] : null;
					}
					break;

				case 'checkbox' :
					$settings[$id] = !empty( $settings[$id] );
					break;

				case 'text' :
				default :
					$settings[$id] = sanitize_text_field( $settings[$id] );
					break;
			}
		}

		update_post_meta( $post_id, 'siteorigin_page_settings', $settings );
	}

	function configure( $settings ){
		$this->settings = $settings;
	}

}
SiteOrigin_Settings_Page_Settings::single();