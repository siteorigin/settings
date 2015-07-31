<?php

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

	private $loc;

	function __construct(){
		$this->add_actions();

		$this->defaults = array();
		$this->settings = array();
		$this->sections = array();
		$this->loc = array(
			'section_title' => 'Theme Settings',
			'section_description' => 'Theme Settings',
		);
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
	 * Get a theme setting value
	 *
	 * @param $setting
	 *
	 * @return string
	 */
	function get( $setting ) {
		$default = isset( $this->defaults[$setting] ) ? $this->defaults[$setting] : false;
		return get_theme_mod( 'theme_settings_' . $setting, $default );
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
		$this->settings[$section][$id] = array(
			'id' => $id,
			'type' => $type,
			'label' => $label,
			'args' => $args,
		);
	}

	/**
	 * Add a teaser field that points to a premium upgrade page
	 *
	 * @param $section
	 * @param $id
	 * @param $name
	 * @param array $args
	 */
	function add_teaser( $section, $id, $name, $args = array() ) {

	}

	/**
	 * Register everything for the customizer
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	function customize_register( $wp_customize ){
		// Let everything setup the settings
		if( !did_action('siteorigin_settings_init') ) {
			do_action('siteorigin_settings_init');
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
				$sanitize_callback = false;
				switch( $setting_args['type'] ) {
					case 'url':
					case 'media':
						$sanitize_callback = 'esc_url_raw';
						break;
					case 'color':
						$sanitize_callback = 'sanitize_hex_color';
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
				if( $setting_args['type'] == 'radio' || $setting_args['type'] == 'select' ) {
					if( !empty($setting_args['options']) ) {
						$control_args['choices'] = $setting_args['options'];
					}
					if( !empty($setting_args['choices']) ) {
						$control_args['choices'] = $setting_args['choices'];
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

		wp_enqueue_script( 'siteorigin-settings-live-preview', get_stylesheet_directory_uri() . '/settings/live.js', array('jquery') );
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
				preg_match_all('/@\{([a-z_]+)\}/', $line, $matches);
				if( empty($matches[0]) ) continue;

				$replaced = 0;

				for( $j = 0; $j < count($matches[0]); $j++ ) {
					$current = $this->get( $matches[1][$j] );
					$default = isset($this->defaults[$matches[1][$j]]) ? $this->defaults[$matches[1][$j]] : false;

					if( $current != $default ) {
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

function siteorigin_settings_add_section( $id, $title ) {
	SiteOrigin_Settings::single()->add_section( $id, $title );
}

function siteorigin_settings_add_field( $section, $id, $type, $label = null, $args = array() ) {
	SiteOrigin_Settings::single()->add_field( $section, $id, $type, $label, $args );
}

function siteorigin_settings_add_teaser( $section, $id, $name, $args = array() ) {
	SiteOrigin_Settings::single()->add_teaser( $section, $id, $name, $args );
}

class SiteOrigin_Settings_Value_Sanitize {
	static function intval( $val ){
		return intval( $val );
	}
}