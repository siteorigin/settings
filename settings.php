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
	 * Set a theme setting value. Simple wrapper for set theme mod.
	 *
	 * @param $setting
	 * @param $value
	 */
	function set( $setting, $value ) {
		set_theme_mod( 'theme_settings_' . $setting, $value );
	}

	/**
	 * Add all the necessary actions
	 */
	function add_actions(){
		add_action( 'after_setup_theme', array( $this, 'init' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );

		add_action( 'customize_preview_init', array( $this, 'enqueue_preview' ) );
		add_action( 'wp_head', array( $this, 'display_custom_css' ), 11 );
	}

	/**
	 * Initialize the theme settings
	 */
	function init(){
		$theme = wp_get_theme();
		$this->theme_name = $theme->get_template();
		$this->defaults = apply_filters( 'siteorigin_settings_defaults', $this->defaults );

		$this->loc = apply_filters('siteorigin_settings_localization', array(
			'section_title' => '',          // __('Theme Settings', 'siteorigin'),
			'section_description' =>  '',   // __('Settings for your theme', 'siteorigin'),
			'premium_only' =>  '',          // __('Premium Only', 'siteorigin'),
			'premium_url' => '#',           // The URL where we'll send users for premium information

			// For the controls
			'variant' =>  '',               // __('Variant', 'siteorigin'),
			'subset' =>  '',                // __('Subset', 'siteorigin'),
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
				$this->add_field(
						$section_id,
						$field_id,
						$field['type'],
						!empty($field['label']) ? $field['label'] : '',
						!empty($field['args']) ? $field['args'] : array()
				);
			}
		}
	}

	/**
	 * @param $id
	 * @param $title
	 */
	function add_section( $id, $title ) {
		$this->sections[$id] = array(
			'id' => $id,
			'title' => $title,
		);
	}

	/**
	 * Add a new settings field
	 *
	 * @param $section
	 * @param $id
	 * @param $type
	 * @param null $label
	 * @param array $args
	 */
	function add_field( $section, $id, $type, $label = null, $args = array() ) {
		$current = isset( $this->settings[$section][$id] ) ? $this->settings[$section][$id] : array();

		$this->settings[$section][$id] = wp_parse_args( array(
			'id' => $id,
			'type' => $type,
			'label' => $label,
			'args' => $args,
		), $current);
	}

	/**
	 * Add a teaser field that points to a premium upgrade page
	 *
	 * @param $section
	 * @param $id
	 * @param $type
	 * @param $label
	 * @param array $args
	 */
	function add_teaser( $section, $id, $type, $label, $args = array() ) {
		// Don't add any teasers if the user is already using Premium
		if( apply_filters('siteorigin_display_teaser', true, $section, $id) ) {
			// The theme hasn't implemented this setting yet
			$this->add_field( $section, $id, 'teaser', $label, $args);
		}
		else {
			// Handle this field elsewhere
			do_action( 'siteorigin_settings_add_teaser_field', $this, $section, $id, $type, $label, $args );
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

		wp_enqueue_script( 'siteorigin-settings-live-preview', get_stylesheet_directory_uri() . '/settings/js/live' . SITEORIGIN_THEME_JS_PREFIX . '.js', array('jquery') );
		wp_localize_script( 'siteorigin-settings-live-preview', 'soSettings', array(
			'css' => apply_filters('siteorigin_settings_custom_css', ''),
			'settings' => !empty($values) ? $values : false
		) );
	}

	function display_custom_css(){
		$css = apply_filters('siteorigin_settings_custom_css', '');
		$defaults = $this->defaults;

		if( !empty($css) ) {
			$css_lines = array_map("trim", preg_split("/[\r\n]+/", $css));
			foreach( $css_lines as $i => & $line ) {
				preg_match_all('/$\{([a-z0-9_]+)\}/', $line, $matches);
				if( empty($matches[0]) ) continue;

				$replaced = 0;

				for( $j = 0; $j < count($matches[0]); $j++ ) {
					$current = $this->get( $matches[1][$j] );
					$default = isset($this->defaults[$matches[1][$j]]) ? $this->defaults[$matches[1][$j]] : false;

					if( $current != $default ) {
						// Lets store that we've replaced something in this line
						$replaced++;
					}

					$line = str_replace( $matches[0][$j], esc_attr($current), $line );
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

						$args['subset'][] = !empty($query['subset']) ? $query['subset'] : 'latim';
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

			?>
			<style type="text/css" id="<?php echo esc_attr($this->theme_name) ?>-settings-custom" data-siteorigin-settings="true">
				<?php echo strip_tags($css) ?>
			</style>
			<?php
		}
	}

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

/**
 * Add a settings section
 *
 * @param $id
 * @param $title
 */
function siteorigin_settings_add_section( $id, $title ) {
	SiteOrigin_Settings::single()->add_section( $id, $title );
}

/**
 * Add a settings field
 *
 * @param $section
 * @param $id
 * @param $type
 * @param null $label
 * @param array $args
 */
function siteorigin_settings_add_field( $section, $id, $type, $label = null, $args = array() ) {
	SiteOrigin_Settings::single()->add_field( $section, $id, $type, $label, $args );
}

/**
 * Add a teaser fields which indicates a field that's implemented elsewhere.
 *
 * @param $section
 * @param $id
 * @param $name
 * @param array $args
 */
function siteorigin_settings_add_teaser( $section, $id, $type, $name, $args = array() ) {
	SiteOrigin_Settings::single()->add_teaser( $section, $id, $type, $name, $args );
}

class SiteOrigin_Settings_Value_Sanitize {
	static function intval( $val ){
		return intval( $val );
	}
}