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
		if( empty($fonts) ) {
			$fonts = include dirname(__FILE__) . '/fonts.php';
		}
		?>
		<select class="font">
			<!-- Unchanged -->
			<option value="" data-webfont="false"></option>

			<optgroup label="Web Safe">
				<!-- Sans Serif Fonts -->
				<option value="Arial" data-category="sans-serif" data-webfont="false">Arial</option>
				<option value="Helvetica" data-category="sans-serif" data-webfont="false">Helvetica</option>
				<option value="Tahoma" data-category="sans-serif" data-webfont="false">Tahoma</option>
				<option value="Verdana" data-category="sans-serif" data-webfont="false">Verdana</option>

				<!-- Sans Serif Fonts -->
				<option value="Georgia" data-category="serif" data-webfont="false">Georgia</option>
				<option value="Palatino" data-category="serif" data-webfont="false">Palatino</option>
				<option value="Times New Roman" data-category="serif" data-webfont="false">Times New Roman</option>

				<!-- Monospaced -->
				<option value="Courier New" data-category="serif" data-webfont="false">Courier New</option>

			</optgroup>

			<optgroup label="Google Webfonts">
				<?php foreach( $fonts as $name => $attr ) : ?>
					<option value="<?php echo esc_attr($name) ?>" data-variants="<?php echo esc_attr( implode( ',', $attr['variants'] ) ) ?>" data-subsets="<?php echo esc_attr( implode( ',', $attr['subsets'] ) ) ?>" data-category="<?php echo esc_attr($attr['category']) ?>" data-webfont="true"><?php echo esc_html($name) ?></option>
				<?php endforeach; ?>
			</optgroup>
		</select>

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
		// wp_enqueue_script( 'siteorigin-settings-onscreen', get_template_directory_uri() . '/settings/js/jquery.onscreen.js', array('jquery') );
		wp_enqueue_script( 'siteorigin-settings-font-control', get_template_directory_uri() . '/settings/js/font-control.js', array('jquery') );
	}
}