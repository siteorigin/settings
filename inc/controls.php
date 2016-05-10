<?php

class SiteOrigin_Teaser_Control extends WP_Customize_Control {
	public $type = 'siteorigin-teaser';

	/**
	 * Render the teaser control's content.
	 */
	public function render_content() {
		if ( ! empty( $this->label ) ) {
			?><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
		}
		if ( ! empty( $this->description ) ) {
			?><span class="description customize-control-description"><?php echo $this->description; ?></span><?php
		}

		?>
		<a
			href="<?php echo esc_url( SiteOrigin_Settings::single()->get_localization_term('premium_url') ) ?>"
			class="button-primary so-premium-upgrade"
			target="_blank">
			<?php echo esc_html( SiteOrigin_Settings::single()->get_localization_term('premium_only') ) ?>
		</a>
		<?php
	}

	/**
	 * Enqueue everything we need for this teaser
	 */
	public function enqueue (  ){
		wp_enqueue_script( 'siteorigin-settings-teaser-control', get_template_directory_uri() . '/inc/settings/js/controls/teaser-control' . SITEORIGIN_THEME_JS_PREFIX . '.js', array( 'jquery', 'customize-controls'  ) );
		wp_enqueue_style( 'siteorigin-settings-teaser-control', get_template_directory_uri() . '/inc/settings/css/teaser-control.css', array() );
	}
}

class SiteOrigin_Font_Control extends WP_Customize_Control {
	public $type = 'siteorigin-font';

	/**
	 * Render the font selector
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
			<label><?php echo esc_html( SiteOrigin_Settings::single()->get_localization_term( 'variant') ) ?></label>
			<select class="font-variant"></select>
		</div>

		<div class="field-wrapper">
			<label><?php echo esc_html( SiteOrigin_Settings::single()->get_localization_term( 'subset' ) ) ?></label>
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
		wp_enqueue_script( 'siteorigin-settings-chosen', get_template_directory_uri() . '/inc/settings/chosen/chosen.jquery.min.js', array('jquery'), '1.4.2' );
		wp_enqueue_style( 'siteorigin-settings-chosen', get_template_directory_uri() . '/inc/settings/chosen/chosen.min.css', array(), '1.4.2' );

		// The main font controls
		wp_enqueue_script( 'siteorigin-settings-font-control', get_template_directory_uri() . '/inc/settings/js/controls/font-control' . SITEORIGIN_THEME_JS_PREFIX . '.js', array( 'jquery', 'customize-controls' ) );
		wp_enqueue_style( 'siteorigin-settings-font-control', get_template_directory_uri() . '/inc/settings/css/font-control.css', array() );
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
		wp_enqueue_script( 'siteorigin-settings-font-control', get_template_directory_uri() . '/inc/settings/js/controls/image-select-control' . SITEORIGIN_THEME_JS_PREFIX . '.js', array( 'jquery', 'customize-controls' ) );
		wp_enqueue_style( 'siteorigin-settings-font-control', get_template_directory_uri() . '/inc/settings/css/image-select-control.css', array() );
	}
}

class SiteOrigin_Widget_Setting_Control extends WP_Customize_Control {

	public $type = 'siteorigin-widget-setting';

	public $widget_args;

	function render_content(  ){
		if( empty( $this->widget_args['class'] ) ) return;

		if ( ! empty( $this->label ) ) {
			?><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
		}
		if ( ! empty( $this->description ) ) {
			?><span class="description customize-control-description"><?php echo $this->description; ?></span><?php
		}

		if( !class_exists( $this->widget_args['class'] ) && !empty( $this->widget_args['bundle_widget'] ) && class_exists('SiteOrigin_Widgets_Bundle') ) {
			// If this is a widget bundle widget, and the class isn't available, then try activate it.
			SiteOrigin_Widgets_Bundle::single()->activate_widget( $this->widget_args['bundle_widget'] );
		}

		if( !class_exists( $this->widget_args['class'] ) ) {
			// Display the message prompting the user to install the widget plugin from WordPress.org
			?><div class="so-settings-widget-form"><?php
			_e('This field requires the Widgets Bundle plugin.', 'siteorigin');
			echo ' ';
			printf( __( '<a href="%s">Install</a> the Widgets Bundle now.', 'siteorigin' ), 'https://wordpress.org/plugins/so-widgets-bundle/' );
			?></div>
			<input type="hidden" class="widget-value" value="<?php esc_attr( $this->value()  ) ?>" />
			<?php
		}
		else {
			$widget_values = $this->value();
			if( is_string( $widget_values ) ) {
				if( is_serialized( $widget_values ) ) {
					$widget_values = unserialize( $widget_values );
				}
				else {
					$widget_values = json_decode( $widget_values, true );
				}
			}

			// Render the widget form
			$the_widget = new $this->widget_args['class']();
			$the_widget->id = 1;
			$the_widget->number = 1;
			ob_start();
			$the_widget->form( $widget_values );
			$form = '<p><a href="" class="button-secondary so-widget-close">' . __( 'Close', 'siteorigin' ) . '</a></p>' . ob_get_clean();
			// Convert the widget field naming into ones that Settings will use
			$exp = preg_quote( $the_widget->get_field_name('____') );
			$exp = str_replace('____', '(.*?)', $exp);
			$form = preg_replace( '/'.$exp.'/', 'siteorigin_settings_widget['.preg_quote(1).'][$1]', $form );
			$form .= '<p><a href="" class="button-secondary so-widget-close">' . __( 'Close', 'siteorigin' ) . '</a></p>';

			?>
			<div class="so-settings-widget-form">
				<div class="so-widget-form" data-widget-class="<?php echo esc_attr( $this->widget_args['class'] ) ?>">
					<?php echo $form ?>
				</div>

				<a href="#" class="button-primary so-edit-widget"><?php _e('Edit Widget', 'siteorgin') ?></a>

			</div>
			<?php
		}
	}

	public function enqueue() {
		wp_enqueue_script( 'siteorigin-settings-widget-control', get_template_directory_uri() . '/inc/settings/js/controls/widget-setting-control' . SITEORIGIN_THEME_JS_PREFIX . '.js', array( 'jquery', 'customize-controls' ) );
		wp_enqueue_style( 'siteorigin-settings-widget-control', get_template_directory_uri() . '/inc/settings/css/widget-setting-control.css', array() );
	}
}
