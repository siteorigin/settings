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

		add_action( 'init', array( $this, 'add_page_settings_support' ) );

		add_action( 'add_meta_boxes', array($this, 'add_meta_box'), 10, 2 );
		add_action( 'save_post', array($this, 'save_post') );

		add_action( 'load-post.php', array($this, 'init') );
		add_action( 'load-post-new.php', array($this, 'init') );

		add_action( 'siteorigin_panels_create_home_page', array( $this, 'panels_save_home_page' ) );
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
	 * @param $default
	 *
	 * @return null
	 */
	static function get( $key, $default = null ) {
		$single = self::single();

		global $post;
		if( empty($single->meta[ $post->ID ] ) ) {
			$single->meta[ $post->ID ] = $single->get_post_meta( $post->ID );
		}
		return isset( $single->meta[ $post->ID ][ $key ] ) ? $single->meta[ $post->ID ][ $key ] : $default;
	}

	/**
	 *
	 */
	function init() {
		$screen = get_current_screen();

		if( $screen->base != 'post' ) return;

		// Let everything setup the settings
		if( !did_action( 'siteorigin_page_settings_init' ) ) {
			do_action( 'siteorigin_page_settings_init' );
		}
	}

	function add_page_settings_support(){
		add_post_type_support( 'page', 'so-page-settings' );
		add_post_type_support( 'post', 'so-page-settings' );
	}

	/**
	 * Get the settings post meta and add the default values.
	 *
	 * @param $post_id
	 *
	 * @return array|mixed
	 */
	function get_post_meta( $post_id ){
		$defaults = apply_filters( 'siteorigin_page_settings_defaults', array(), $post_id );
		$values = get_post_meta( $post_id, 'siteorigin_page_settings', true );
		if( empty($values) ) $values = array();

		$values = apply_filters( 'siteorigin_page_settings_values', $values, $post_id );

		return wp_parse_args( $values, $defaults );
	}

	/**
	 * Add the meta box
	 */
	function add_meta_box( $post_type, $post ){

		if( !empty( $post->post_type ) && post_type_supports( $post->post_type, 'so-page-settings' ) ) {
			add_meta_box(
				'siteorigin_page_settings',
				SiteOrigin_Settings::single()->get_localization_term( 'meta_box' ),
				array( $this, 'display_meta_box' ),
				$post->post_type,
				'side'
			);
		}
	}

	/**
	 * Display the Meta Box
	 */
	function display_meta_box( $post ){
		$values = $this->get_post_meta( $post->ID );

		do_action( 'siteorigin_settings_before_page_settings_meta_box', $post );

		foreach( $this->settings as $id => $field ) {
			if( empty($values[$id]) ) $values[$id] = false;
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

		do_action( 'siteorigin_settings_after_page_settings_meta_box', $post );
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

	/**
	 * @param $post_id
	 */
	function panels_save_home_page( $post_id ){
		$settings = $this->get_post_meta( $post_id );
		$settings = apply_filters( 'siteorigin_page_settings_panels_home_defaults', $settings );
		update_post_meta( $post_id, 'siteorigin_page_settings', $settings );
	}

}
SiteOrigin_Settings_Page_Settings::single();
