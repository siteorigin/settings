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

		?><a href="<?php echo esc_url( SiteOrigin_Settings::single()->loc['premium_url'] ) ?>" class="button-primary" target="_blank"><?php echo SiteOrigin_Settings::single()->loc['premium_only'] ?></a><?php
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
							style="font-family: '<?php echo esc_attr($name) ?>', <?php echo esc_attr($attr['category']) ?>, __websafe">
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

		<div class="field-wrapper">
			<label><?php echo esc_html( SiteOrigin_Settings::single()->loc['variant'] ) ?></label>
			<select class="font-variant"></select>
		</div>

		<div class="field-wrapper">
			<label><?php echo esc_html( SiteOrigin_Settings::single()->loc['subset'] ) ?></label>
			<select class="font-subset"></select>
		</div>

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

		// The main font controls
		wp_enqueue_script( 'siteorigin-settings-font-control', get_template_directory_uri() . '/settings/js/font-control' . SITEORIGIN_THEME_JS_PREFIX . '.js', array('jquery') );
		wp_enqueue_style( 'siteorigin-settings-font-control', get_template_directory_uri() . '/settings/css/font-control.css', array() );
	}
}

class SiteOrigin_Image_Select_Control extends WP_Customize_Control {
	public $type = 'siteorigin-image-select';
	public $choices;

	function render_content() {
		if ( ! empty( $this->label ) ) {
			?><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
		}
		if ( ! empty( $this->description ) ) {
			?><span class="description customize-control-description"><?php echo $this->description; ?></span><?php
		}

		?>
		<select <?php $this->link(); ?>>
			<?php foreach( $this->choices as $key => $choice ) : ?>
				<option value="<?php echo esc_attr($key) ?>" data-image="<?php echo esc_url( $choice[1] ) ?>" <?php selected( $key, $this->value() ) ?>>
					<?php echo esc_html($choice[0]) ?>
				</option>
			<?php endforeach ?>
		</select>

		<ul class="image-options">
			<?php foreach( $this->choices as $key => $choice ) : ?>
				<li data-key="<?php echo esc_attr($key) ?>" <?php if( $key == $this->value() ) echo 'class="active"' ?>>
					<label><?php echo esc_html($choice[0]) ?></label>
					<img src="<?php echo esc_url( $choice[1] ) ?>" />
				</li>
			<?php endforeach ?>
		</ul>

		<?php
	}

	public function enqueue() {
		wp_enqueue_script( 'siteorigin-settings-font-control', get_template_directory_uri() . '/settings/js/image-select-control' . SITEORIGIN_THEME_JS_PREFIX . '.js', array('jquery') );
		wp_enqueue_style( 'siteorigin-settings-font-control', get_template_directory_uri() . '/settings/css/image-select-control.css', array() );
	}

}