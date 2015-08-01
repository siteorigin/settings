<?php

class SiteOrigin_Teaser_Control extends WP_Customize_Control {
	public $type = 'teaser';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		if ( ! empty( $this->label ) ) {
			?><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
		}
		if ( ! empty( $this->description ) ) {
			?><span class="description customize-control-description"><?php echo $this->description; ?></span><?php
		}

		?><a href="#premium" class="button-primary"><?php echo SiteOrigin_Settings::single()->loc['premium_only'] ?></a><?php
	}
}

class SiteOrigin_Font_Control extends WP_Customize_Control {
	public $type = 'siteorigin-font';

	/**
	 *
	 */
	public function render_content(){
		if ( ! empty( $this->label ) ) {
			?><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
		}
		if ( ! empty( $this->description ) ) {
			?><span class="description customize-control-description"><?php echo $this->description; ?></span><?php
		}

		static $fonts = false;
		static $websafe = false;
		if( empty($fonts) ) {
			$fonts = include dirname(__FILE__) . '/fonts.php';
		}
		if( empty($websafe) ) {
			$websafe = include dirname(__FILE__) . '/websafe.php';
		}

		?>
		<div class="font-wrapper">
			<select class="font">
				<!-- Unchanged -->
				<option value="" data-webfont="false"></option>

				<optgroup label="Web Safe">
					<?php foreach( $websafe as $name => $attr ) : ?>
						<option
							value="<?php echo esc_attr($name) ?>"
							data-variants="<?php echo esc_attr( implode( ',', $attr['variants'] ) ) ?>"
							data-subsets="<?php echo esc_attr( implode( ',', $attr['subsets'] ) ) ?>"
							data-category="<?php echo esc_attr($attr['category']) ?>"
							data-webfont="false"
							style="font-family: '<?php echo esc_attr($name) ?>', <?php echo esc_attr($attr['category']) ?>">
							<?php echo esc_html($name) ?>
						</option>
					<?php endforeach; ?>
				</optgroup>

				<optgroup label="Google Webfonts">
					<?php foreach( $fonts as $name => $attr ) : ?>
						<option
							value="<?php echo esc_attr($name) ?>"
							data-variants="<?php echo esc_attr( implode( ',', $attr['variants'] ) ) ?>"
							data-subsets="<?php echo esc_attr( implode( ',', $attr['subsets'] ) ) ?>"
							data-category="<?php echo esc_attr($attr['category']) ?>"
							data-webfont="true"
							style="font-family: '<?php echo esc_attr($name) ?>', <?php echo esc_attr($attr['category']) ?>">
							<?php echo esc_html($name) ?>
						</option>
					<?php endforeach; ?>
				</optgroup>
			</select>
		</div>

		<select class="font-variant">

		</select>

		<select class="font-subset">

		</select>

		<input type="hidden" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />

		<?php
	}

	/**
	 * Enqueue all the scripts and styles we need
	 */
	public function enqueue() {
		// We'll use chosen for the font selector
		wp_enqueue_script( 'siteorigin-settings-chosen', get_template_directory_uri() . '/settings/chosen/chosen.jquery.min.js', array('jquery'), '1.4.2' );
		wp_enqueue_style( 'siteorigin-settings-chosen', get_template_directory_uri() . '/settings/chosen/chosen.min.css', array(), '1.4.2' );

		// Enqueue onscreen for loading fonts
		wp_enqueue_script( 'siteorigin-settings-onscreen', get_template_directory_uri() . '/settings/js/jquery.onscreen.js', array('jquery') );

		// The main font controls
		wp_enqueue_script( 'siteorigin-settings-font-control', get_template_directory_uri() . '/settings/js/font-control.js', array('jquery') );
		wp_enqueue_style( 'siteorigin-settings-font-control', get_template_directory_uri() . '/settings/css/font-control.css', array() );
	}
}