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

		// All the meta box stuff
		add_action( 'add_meta_boxes', array($this, 'add_meta_box'), 10, 2 );
		add_action( 'save_post', array($this, 'save_post') );

		// Page Builder integration
		add_action( 'siteorigin_panels_create_home_page', array( $this, 'panels_save_home_page' ) );

		// Customizer integration
		add_action( 'customize_register', array( $this, 'customize_register' ) );
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
	static function get( $key = false, $default = null ) {
		$single = self::single();

		static $type = false;
		static $id = false;

		if( $type === false && $id === false ) {
			global $wp_query;

			if( $wp_query->is_home() ) {
				$type = 'template';
				$id = 'home';
			}
			else if( $wp_query->is_search() ) {
				$type = 'template';
				$id = 'search';
			}
			else if( $wp_query->is_404() ) {
				$type = 'template';
				$id = '404';
			}
			else {
				$object = get_queried_object();
				switch( get_class( $object ) ) {
					case 'WP_Term':
						$type = 'taxonomy';
						$id = $object->taxonomy;
						break;

					case 'WP_Post':
						$type = 'post';
						$id = $object->ID;
						break;

					case 'WP_User':
						$type = 'template';
						$id = 'author';
						break;
				}
			}
		}

		if( empty( $type ) || empty( $id ) ) {
			$type = 'template';
			$id = 'default';
		}

		if( empty( $single->meta[ $type . '_' . $id ] ) ) {
			$single->meta[ $type . '_' . $id ] = $single->get_settings_values( $type, $id );
		}

		// Return the value
		if( empty( $key ) ) {
			return $single->meta[ $type . '_' . $id ];
		}
		else {
			return isset( $single->meta[ $type . '_' . $id ][ $key ] ) ? $single->meta[ $type . '_' . $id ][ $key ] : $default;
		}
	}

	function get_settings( $type, $id ) {
		return apply_filters( 'siteorigin_page_settings', array(), $type, $id );
	}

	function add_page_settings_support(){
		add_post_type_support( 'page', 'so-page-settings' );
		add_post_type_support( 'post', 'so-page-settings' );
	}

	function get_settings_defaults( $type, $id ){
		return apply_filters( 'siteorigin_page_settings_defaults', array(), $type, $id );
	}

	/**
	 * Get the settings post meta and add the default values.
	 *
	 * @param $type
	 * @param $id
	 *
	 * @return array|mixed
	 */
	function get_settings_values( $type, $id ){
		$defaults = $this->get_settings_defaults( $type, $id );

		switch( $type ) {
			case 'post':
				$values = get_post_meta( $id, 'siteorigin_page_settings', true );
				break;

			default:
				$values = get_theme_mod( 'page_settings_' . $type . '_' . $id );
				break;
		}

		if( empty($values) ) $values = array();
		$values = apply_filters( 'siteorigin_page_settings_values', $values, $type, $id );

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
				array( $this, 'display_post_meta_box' ),
				$post->post_type,
				'side'
			);
		}
	}

	/**
	 * Display the Meta Box
	 */
	function display_post_meta_box( $post ){
		$settings = $this->get_settings( 'post', $post->ID );
		$values = $this->get_settings_values( 'post', $post->ID );

		do_action( 'siteorigin_settings_before_page_settings_meta_box', $post );

		foreach( $settings as $id => $field ) {
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

	/**
	 * Register all the archives in the customizer
	 *
	 * @param $wp_customize
	 */
	function customize_register( $wp_customize ){
		if( !current_theme_supports( 'siteorigin-archive-settings' ) ) return;
		$loc = SiteOrigin_Settings::single()->get_localization();

		// We'll use a single panel for theme settings
		if( method_exists($wp_customize, 'add_panel') ) {
			$wp_customize->add_panel( 'page_settings', array(
				'title' => $loc[ 'page_section_title' ],
				'description' => $loc[ 'page_section_description' ],
				'priority' => 11,
			) );
		}

		// Add general page templates
		$types = array(
			array(
				'group' => 'template',
				'id' => 'home',
				'title' => $loc['template_home']
			),
			array(
				'group' => 'template',
				'id' => 'search',
				'title' => $loc['template_search']
			),
			array(
				'group' => 'template',
				'id' => '404',
				'title' => $loc['template_404']
			),
			array(
				'group' => 'template',
				'id' => 'author',
				'title' => $loc['template_author']
			)
		);


		// Add public post types
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach( $post_types as $post_type => $post_type_data ) {
			if( empty( $post_type_data->label ) ) continue;
			if( empty( $post_type_data->has_archive ) ) continue;

			$types[] = array(
				'group' => 'archive',
				'id' => $post_type,
				'title' => $loc['templates_post_type'] . ': ' . $post_type_data->label
			);
		}

		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		foreach( $taxonomies as $tax_slug => $taxonomy ) {
			if( empty( $taxonomy->label ) ) continue;
			if( empty( $taxonomy->publicly_queryable ) ) continue;

			$types[] = array(
				'group' => 'taxonomy',
				'id' => $tax_slug,
				'title' => $loc['templates_taxonomy'] . ': ' . $taxonomy->label
			);
		}

		// Now add controls for all the sections
		foreach( $types as $i => $type ) {
			$wp_customize->add_section( 'page_settings_' . $type['group'] . '_' . $type['id'], array(
				'title' => $type['title'],
				'priority' => ( $i * 5 ) + 10,
				'panel' => 'page_settings',
			) );

			// Now add the settings
			$settings = $this->get_settings( $type['group'], $type['id'] );
			$defaults = $this->get_settings_defaults( $type['group'], $type['id'] );

			foreach( $settings as $id => $setting ) {
				$sanitize_callback = 'sanitize_text_field';
				switch( $setting['type'] ) {
					case 'checkbox':
						$sanitize_callback = array( 'SiteOrigin_Settings', 'sanitize_bool' );
						break;
				}

				$wp_customize->add_setting( 'page_settings_' . $type['group'] . '_' . $type['id'] . '[' . $id . ']' , array(
					'default' => isset( $defaults[ $id ] ) ? $defaults[ $id ] : false,
					'transport' => 'refresh',
					'capability' => 'edit_theme_options',
					'type' => 'theme_mod',
					'sanitize_callback' => $sanitize_callback,
				) );

				// Setup the control arguments for the controller
				$control_args = array(
					'label' => $setting['label'],
					'type' => $setting['type'],
					'description' => !empty( $setting['description'] ) ? $setting['description'] : false,
					'section'  => 'page_settings_' . $type['group'] . '_' . $type['id'],
					'settings' => 'page_settings_' . $type['group'] . '_' . $type['id'] . '[' . $id . ']',
				);

				if( $setting['type'] == 'select' ) {
					$control_args['choices'] = $setting['options'];
				}

				$wp_customize->add_control(
					'page_settings_' . $type['group'] . '_' . $type['id'] . '_' . $id,
					$control_args
				);
			}
		}
	}

}
SiteOrigin_Settings_Page_Settings::single();

function siteorigin_page_setting( $setting = false, $default = false ) {
	return SiteOrigin_Settings_Page_Settings::single()->get( $setting, $default );
}
