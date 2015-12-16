<?php

include dirname( __FILE__ ) . '/inc/webfonts.php';

/**
* Class SiteOrigin_Settings
 *
 * A simple settings framework that works with the customizer in magical ways.
*/
class SiteOrigin_Settings {

	/**
	 * @var array Default setting values
	 */
	private $defaults;

	/**
	 * @var The current theme name
	 */
	private $theme_name;

	/**
	 * @var array The theme settings
	 */
	private $settings;

	/**
	 * @var array The settings sections
	 */
	private $sections;

	/**
	 * @var array The localization strings
	 */
	public $loc;

	function __construct(){
		$this->add_actions();

		$this->defaults = array();
		$this->settings = array();
		$this->sections = array();
		$this->loc = array();
	}

	/**
	 * Create the singleton
	 *
	 * @return SiteOrigin_Settings
	 */
	static function single(){
		static $single;

		if( empty($single) ) {
			$single = new SiteOrigin_Settings();
		}

		return $single;
	}

	/**
	 * @param $loc
	 */
	function set_localization($loc){
		// All these strings must be properly localized by the theme
		$this->loc = wp_parse_args( $loc, $this->loc );
	}

	/**
	 * Get a single localization term.
	 *
	 * @param $id
	 *
	 * @return string
	 */
	function get_localization_term( $id ){
		return !empty($this->loc[$id]) ? $this->loc[$id] : '';
	}

	/**
	 * Get a theme setting value
	 *
	 * @param $setting
	 *
	 * @return string
	 */
	function get( $setting ) {
		static $old_settings = false;
		if( $old_settings === false ) {
			$old_settings = get_option( get_template() . '_theme_settings' );
		}

		if( isset( $old_settings[$setting] ) ) {
			$default = $old_settings[$setting];
		}
		else {
			$default = isset( $this->defaults[$setting] ) ? $this->defaults[$setting] : false;
		}

		// Return a filtered version of the setting
		return apply_filters( 'siteorigin_setting', get_theme_mod( 'theme_settings_' . $setting, $default ), $setting );
	}

	/**
	 * Get all theme settings values currently in the database
	 *
	 * @return array|void
	 */
	function get_all( ){
		$settings = get_theme_mods();
		if( empty($settings) ) return array();

		foreach( array_keys($settings) as $k ) {
			if( strpos( $k, 'theme_settings_' ) !== 0 ) {
				unset($settings[$k]);
			}
		}

		return $settings;
	}

	/**
	 * Set a theme setting value. Simple wrapper for set theme mod.
	 *
	 * @param $setting
	 * @param $value
	 */
	function set( $setting, $value ) {
		set_theme_mod( 'theme_settings_' . $setting, $value );
		set_theme_mod( 'custom_css_key', false );
	}

	/**
	 * Add all the necessary actions
	 */
	function add_actions(){
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );

		add_action( 'customize_preview_init', array( $this, 'enqueue_preview' ) );
		add_action( 'wp_head', array( $this, 'display_custom_css' ), 11 );

		add_action( 'wp_ajax_so_settings_premium_content', array( $this, 'premium_content_action' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'admin_footer' ) );
	}

	/**
	 * Initialize the theme settings
	 */
	function init(){
		$theme = wp_get_theme();
		$this->theme_name = $theme->get_template();
		$this->defaults = apply_filters( 'siteorigin_settings_defaults', $this->defaults );
		$this->loc = apply_filters('siteorigin_settings_localization', array(
			'section_title' => '',          // __( 'Theme Settings', 'siteorigin' ),
			'section_description' =>  '',   // __( 'Settings for your theme', 'siteorigin' ),
			'premium_only' =>  '',          // __( 'Premium Only', 'siteorigin' ),
			'premium_url' => '#',           // The URL where we'll send users for premium information

			// For the controls
			'variant' =>  '',               // __( 'Variant', 'siteorigin'),
			'subset' =>  '',                // __( 'Subset', 'siteorigin'),

			// For the premium upgrade modal
			'modal_title' => '',                  // __( 'Premium Upgrade', 'siteorigin' ),
			'close' => '',                  // __( 'Close', 'siteorigin' ),
		) );
	}

	/**
	 * @param array $settings
	 */
	function configure( $settings ){
		foreach( $settings as $section_id => $section ) {
			$this->add_section( $section_id, !empty($section['title']) ? $section['title'] : '' );
			$fields = !empty($section['fields']) ? $section['fields'] : array();
			foreach( $fields as $field_id => $field ) {
				$args = array_merge(
					!empty($field['args']) ? $field['args'] : array(),
					$field
				);
				unset($args['label']);
				unset($args['type']);

				$this->add_field(
					$section_id,
					$field_id,
					$field['type'],
					!empty($field['label']) ? $field['label'] : '',
					$args
				);
			}
		}
	}

	/**
	 * @param $id
	 * @param $title
	 * @param string|bool $after Add this section after another one
	 */
	function add_section( $id, $title, $after = false ) {

		if( $after === false ) {
			$index = null;
		}
		else if( $after === '' ) {
			$index = 0;
		}
		else if( $after !== false ) {
			$index = array_search( $after, array_keys( $this->sections ) ) + 1;
			if( $index == count( array_keys($this->sections) ) ) {
				$index = null;
			}
		}

		$new_section = array( $id => array(
			'id' => $id,
			'title' => $title,
		) );

		if( $index === null ) {
			// Null means we add this at the end or the current position
			$this->sections = array_merge(
				$this->sections,
				$new_section
			);
		}
		else if( $index === 0 ) {
			$this->sections = array_merge(
				$new_section,
				$this->sections
			);
		}
		else {
			$this->sections = array_merge(
				array_slice( $this->sections, 0, $index, true ),
				$new_section,
				array_slice( $this->sections, $index, count($this->sections), true )
			);
		}

		if( empty($this->settings[$id]) ) {
			$this->settings[$id] = array();
		}
	}

	/**
	 * Add a new settings field
	 *
	 * @param $section
	 * @param $id
	 * @param $type
	 * @param null $label
	 * @param array $args
	 * @param string|bool $after Add this field after another one
	 */
	function add_field( $section, $id, $type, $label = null, $args = array(), $after = false ) {

		if( empty($this->settings[$section]) ) {
			$this->settings[$section] = array();
		}

		$new_field = array(
			'id' => $id,
			'type' => $type,
			'label' => $label,
			'args' => $args,
		);

		if( isset($this->settings[$section][$id]) ) {
			$this->settings[$section][$id] = wp_parse_args(
				$new_field,
				$this->settings[$section][$id]
			);
		}

		if( $after === false ) {
			$index = null;
		}
		else if( $after === '' ) {
			$index = 0;
		}
		else if( $after !== false ) {
			$index = array_search( $after, array_keys( $this->settings[$section] ) ) + 1;
			if( $index == count( $this->settings[$section] ) ) {
				$index = null;
			}
		}

		if( $index === null ) {
			// Null means we add this at the end or the current position
			$this->settings[$section] = array_merge(
				$this->settings[$section],
				array( $id => $new_field )
			);
		}
		else if( $index === 0 ) {
			$this->settings[$section] = array_merge(
				array( $id => $new_field ),
				$this->settings[$section]
			);
		}
		else {
			$this->settings[$section] = array_merge(
				array_slice( $this->settings[$section], 0, $index, true ),
				array( $id => $new_field ),
				array_slice( $this->settings[$section], $index, count( $this->settings[$section] ), true )
			);
		}

	}

	/**
	 * Add a teaser field that points to a premium upgrade page
	 *
	 * @param $section
	 * @param $id
	 * @param $type
	 * @param $label
	 * @param array $args
	 * @param string|bool $after Add this field after another one
	 */
	function add_teaser( $section, $id, $type, $label, $args = array(), $after = false ) {
		// Don't add any teasers if the user is already using Premium
		if( apply_filters('siteorigin_display_teaser', true, $section, $id) ) {
			// The theme hasn't implemented this setting yet
			$this->add_field( $section, $id, 'teaser', $label, $args, $after);
		}
		else {
			// Handle this field elsewhere
			do_action( 'siteorigin_settings_add_teaser_field', $this, $section, $id, $type, $label, $args, $after );
		}
	}

	/**
	 * Register everything for the customizer
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	function customize_register( $wp_customize ){
		// Include the extra control types
		include_once dirname( __FILE__ ) . '/inc/controls.php';

		// Let everything setup the settings
		if( !did_action( 'siteorigin_settings_init' ) ) {
			do_action( 'siteorigin_settings_init' );
		}

		// We'll use a single panel for theme settings
		if( method_exists($wp_customize, 'add_panel') ) {
			$wp_customize->add_panel( 'theme_settings', array(
				'title' => $this->loc['section_title'],
				'description' => $this->loc['section_description'],
				'priority' => 10,
			) );
		}

		// Add sections for what would have been tabs before
		foreach( $this->sections as $id => $args ) {
			$wp_customize->add_section( 'theme_settings_' . $id, array(
				'title' => $args['title'],
				'priority' => ( $id * 5 ) + 10,
				'panel' => 'theme_settings',
			) );
		}

		// Finally, add the settings
		foreach( $this->settings as $section_id => $settings ) {
			foreach( $settings as $setting_id => $setting_args ) {
				switch( $setting_args['type'] ) {
					case 'url':
					case 'media':
						$sanitize_callback = 'esc_url_raw';
						break;
					case 'color':
						$sanitize_callback = 'sanitize_hex_color';
						break;
					case 'font':
						$sanitize_callback = 'sanitize_text_field';
						break;
					case 'checkbox':
						$sanitize_callback = array($this, 'sanitize_bool');
						break;
					default:
						$sanitize_callback = 'sanitize_text_field';
						break;
				}

				if( !empty( $setting_args['args']['sanitize_callback'] ) ) {
					$sanitize_callback = $setting_args['args']['sanitize_callback'];
				}

				// Create the customizer setting
				$wp_customize->add_setting( 'theme_settings_' . $section_id . '_' . $setting_id , array(
					'default' => isset($this->defaults[ $section_id . '_' . $setting_id ]) ? $this->defaults[ $section_id . '_' . $setting_id ] : '',
					'transport' => empty($setting_args['args']['live']) ? 'refresh' : 'postMessage',
					'capability' => 'edit_theme_options',
					'type' => 'theme_mod',
					'sanitize_callback' => $sanitize_callback,
				) );

				// Setup the control arguments for the controller
				$control_args = array(
					'label' => $setting_args['label'],
					'section'  => 'theme_settings_' . $section_id,
					'settings' => 'theme_settings_' . $section_id . '_' . $setting_id,
				);

				if( !empty( $setting_args['args']['description'] ) ) {
					$control_args['description'] = $setting_args['args']['description'];
				}

				// Add different control args for the different field types
				if( $setting_args['type'] == 'radio' || $setting_args['type'] == 'select' || $setting_args['type'] == 'image_select' ) {
					if( !empty($setting_args['args']['options']) ) {
						$control_args['choices'] = $setting_args['args']['options'];
					}
					if( !empty($setting_args['args']['choices']) ) {
						$control_args['choices'] = $setting_args['args']['choices'];
					}
				}

				switch( $setting_args['type'] ) {
					case 'media' :
						$wp_customize->add_control(
							new WP_Customize_Image_Control(
								$wp_customize,
								'theme_settings_' . $section_id . '_' . $setting_id,
								$control_args
							)
						);
						break;

					case 'color' :
						$wp_customize->add_control(
							new WP_Customize_Color_Control(
								$wp_customize,
								'theme_settings_' . $section_id . '_' . $setting_id,
								$control_args
							)
						);
						break;

					case 'teaser' :
						$wp_customize->add_control(
							new SiteOrigin_Teaser_Control(
								$wp_customize,
								'theme_settings_' . $section_id . '_' . $setting_id,
								$control_args
							)
						);
						break;

					case 'image_select':
						$wp_customize->add_control(
							new SiteOrigin_Image_Select_Control(
								$wp_customize,
								'theme_settings_' . $section_id . '_' . $setting_id,
								$control_args
							)
						);
						break;

					case 'font' :
						$wp_customize->add_control(
							new SiteOrigin_Font_Control(
								$wp_customize,
								'theme_settings_' . $section_id . '_' . $setting_id,
								$control_args
							)
						);
						break;

					default:
						$control_args['type'] = $setting_args['type'];
						$wp_customize->add_control(
							'theme_settings_' . $section_id . '_' . $setting_id,
							$control_args
						);
						break;
				}

			}
		}
	}

	function enqueue_preview(){
		if( !did_action('siteorigin_settings_init') ) {
			do_action('siteorigin_settings_init');
		}

		$values = array();
		foreach( $this->settings as $section_id => $section ) {
			foreach( $section as $setting_id => $setting ) {
				$values[$section_id . '_' . $setting_id] = $this->get($section_id . '_' . $setting_id);
			}
		}

		wp_enqueue_script( 'siteorigin-settings-live-preview', get_stylesheet_directory_uri() . '/inc/settings/js/live' . SITEORIGIN_THEME_JS_PREFIX . '.js', array('jquery') );
		wp_localize_script( 'siteorigin-settings-live-preview', 'soSettings', array(
			'css' => apply_filters('siteorigin_settings_custom_css', ''),
			'settings' => !empty($values) ? $values : false
		) );
	}

	function display_custom_css(){
		$css = apply_filters('siteorigin_settings_custom_css', '');

		if( !empty($css) ) {

			$css_key = md5( json_encode( array(
				'css' => $css,
				'settings' => $this->get_all(),
			) ) );

			if( $css_key !== get_theme_mod('custom_css_key') || ( defined('WP_DEBUG') && WP_DEBUG ) ) {
				$css_lines = array_map("trim", preg_split("/[\r\n]+/", $css));
				foreach( $css_lines as $i => & $line ) {
					preg_match_all( '/\$\{([a-zA-Z0-9_]+)\}/', $line, $matches );
					if( empty($matches[0]) ) continue;

					$replaced = 0;

					for( $j = 0; $j < count($matches[0]); $j++ ) {
						$current = $this->get( $matches[1][$j] );
						$default = isset($this->defaults[$matches[1][$j]]) ? $this->defaults[$matches[1][$j]] : false;

						if( $current != $default && str_replace('%', '%%', $current) != $default ) {
							// Lets store that we've replaced something in this line
							$replaced++;
						}

						$line = str_replace( $matches[0][$j], $current, $line );
					}

					if( $replaced == 0 ) {
						// Remove any lines where we haven't done anything
						unset($css_lines[$i]);
					}
				}

				$css = implode(' ', $css_lines);

				// Now, lets handle the custom functions.
				$css = preg_replace_callback('/\.([a-z\-]+) *\(([^\)]*)\) *;/', array($this, 'css_functions'), $css);

				// Finally, we'll combine all imports and put them at the top of the file
				preg_match_all( '/@import url\(([^\)]+)\);/', $css, $matches );
				if( !empty($matches[0]) ) {
					$webfont_imports = array();

					for( $i = 0; $i < count($matches[0]); $i++ ) {
						if( strpos('//fonts.googleapis.com/css', $matches[1][$i]) !== -1 ) {
							$webfont_imports[] = $matches[1][$i];
							$css = str_replace( $matches[0][$i], '', $css );
						}
					}

					if( !empty($webfont_imports) ) {
						$args = array(
							'family' => array(),
							'subset' => array(),
						);

						// Combine all webfont imports into a single argument
						foreach( $webfont_imports as $url ) {
							$url = parse_url($url);
							if( empty($url['query']) ) continue;
							parse_str( $url['query'], $query );

							if( !empty($query['family']) ) {
								$args['family'][] = $query['family'];
							}

							$args['subset'][] = !empty($query['subset']) ? $query['subset'] : 'latin';
						}

						// Clean up the arguments
						$args['subset'] = array_unique($args['subset']);

						$args['family'] = array_map( 'urlencode', $args['family'] );
						$args['subset'] = array_map( 'urlencode', $args['subset'] );
						$args['family'] = implode('|', $args['family']);
						$args['subset'] = implode(',', $args['subset']);

						$import = '@import url(' . add_query_arg( $args, '//fonts.googleapis.com/css' ) . ');';
						$css = $import . "\n" . $css;
					}
				}

				// Now lets remove empty rules
				do {
					$css = preg_replace('/[^\{\}]*?\{ *\}/', ' ', $css, -1, $count);
				} while( $count > 0 );
				$css = trim($css);

				set_theme_mod( 'custom_css', $css );
				set_theme_mod( 'custom_css_key', $css_key );
			}
			else {
				$css = get_theme_mod('custom_css');
			}

			if( !empty($css) ) {
				?>
				<style type="text/css" id="<?php echo esc_attr($this->theme_name) ?>-settings-custom" data-siteorigin-settings="true">
					<?php echo strip_tags($css) ?>
				</style>
				<?php
			}
		}
	}

	/**
	 * LESS style CSS functions
	 *
	 * @param $match
	 *
	 * @return string
	 */
	function css_functions($match) {
		$function = $match[1];
		$return = '';

		switch( $function ) {
			case 'font':
				if( empty($match[2]) ) break;
				$args = json_decode( trim($match[2]), true );
				if( empty($args['font']) ) {
					break;
				}

				if( $args['webfont'] ) {
					// We need to import this too
					$query = add_query_arg(array(
						'family' => rawurlencode( $args['font'] ) . ':' . rawurlencode( $args['variant'] ),
						'subset' => rawurlencode( $args['subset'] )
					), '//fonts.googleapis.com/css');
					$return .= '@import url(' . $query . '); ';
				}

				// Now lets add all the css styling
				$return .= 'font-family: "' . esc_attr( $args['font'] ) . '", ' . $args['category'] . '; ';
				if( strpos( $args['variant'], 'italic' ) !== -1 ) {
					$weight = str_replace('italic', '', $args['variant']);
					$return .= 'font-style: italic; ';
				}
				else {
					$weight = $args['variant'];
				}
				if( empty($args['variant']) ) $args['variant'] = 'regular';
				$return .= 'font-weight: ' . esc_attr( $weight) . '; ';

				break;
		}

		return $return;
	}

	function sanitize_bool($val){
		return (bool) $val;
	}

	static function template_part_names($parts, $part_name){
		$return = array();

		$parent_parts = glob( get_template_directory().'/'.$parts.'*.php' );
		$child_parts = glob( get_stylesheet_directory().'/'.$parts.'*.php' );

		$files = array_unique( array_merge(
			!empty($parent_parts) ? $parent_parts : array(),
			!empty($child_parts) ? $child_parts : array()
		) );

		if( !empty($files) ) {
			foreach( $files as $file ) {
				$p = pathinfo($file);
				$filename = explode('-', $p['filename'], 2);
				$name = isset($filename[1]) ? $filename[1] : '';

				$info = get_file_data($file, array(
					'name' => $part_name,
				) );

				$return[$name] = $info['name'];
			}
		}

		ksort($return);
		return $return;
	}

	/**
	 * Convert an attachment URL to a post ID
	 *
	 * @param $image_url
	 *
	 * @return mixed
	 */
	static function get_image_id( $image_url ){
		global $wpdb;
		$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));
		return $attachment[0];
	}

	/**
	 * Set the callback that generates the premium upgrade information.
	 */
	function premium_content_action( ){
		do_action( 'siteorigin_settings_premium_content' );
		exit();
	}

	/**
	 *
	 */
	function admin_footer(){
		?>
		<script id="so-premium-modal-template" type="text/template">
			<div id="so-premium-modal">
				<div class="so-modal-overlay"></div>
				<div class="so-modal-titlebar">
					<h3 class="so-modal-title"><?php echo esc_html( $this->get_localization_term( 'modal_title' ) ) ?></h3>
					<div class="so-modal-close"><span class="so-modal-close-icon"></span></div>
				</div>
				<div class="so-modal-content">
					<!-- Modal Content -->
				</div>
				<div class="so-modal-toolbar">
					<button class="button-primary so-modal-close"><?php echo esc_html( $this->get_localization_term('close') ) ?></button>
				<div>
			</div>
		</script>
		<?php
	}
}

// Setup the single
SiteOrigin_Settings::single();

/**
 * Access a single setting
 *
 * @param $setting string The name of the setting.
 *
 * @return mixed The setting value
 */
function siteorigin_setting( $setting ){
	return SiteOrigin_Settings::single()->get( $setting );
}

class SiteOrigin_Settings_Value_Sanitize {
	static function intval( $val ){
		return intval( $val );
	}

	static function measurement( $val ){
		$measurements = array_map('preg_quote', array(
			'px',
			'%',
			'in',
			'cm',
			'mm',
			'em',
			'ex',
			'pt',
			'pc',
		) );

		if (preg_match('/([0-9\.,]+).*?(' . implode('|', $measurements) . ')/', $val, $match)) {
			$return = $match[1] . $match[2];
		}
		else {
			$return = '';
		}

		return $return;
	}
}