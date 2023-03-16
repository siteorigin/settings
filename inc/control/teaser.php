<?php

class SiteOrigin_Settings_Control_Teaser extends WP_Customize_Control {
	public $type = 'siteorigin-teaser';

	public $featured = false;
	public $teaser = false;
	public $teaser_title = false;

	/**
	 * Render the teaser control's content.
	 */
	public function render_content() {
		// Are we adding a placeholder for a premium setting?
		if ( empty( $this->teaser ) ) {
			if ( ! empty( $this->label ) ) {
				?>
				<span class="customize-control-title">
					<?php echo esc_html( $this->label ); ?>
				</span>
				<?php
			}

			if ( ! empty( $this->description ) ) {
				?>
				<span class="description customize-control-description">
					<?php echo $this->description; ?>
				</span>
				<?php
			}

			?>
			<a
				href="<?php echo esc_url( SiteOrigin_Settings::get_premium_url( $this->featured ) ); ?>"
				class="button-primary so-premium-upgrade"
				target="_blank"
			>
				<?php esc_html_e( 'Available in Premium', 'siteorigin-corp' ); ?>
			</a>
		<?php } else { ?>
			<?php if ( ! empty( $this->teaser['title'] ) ) { ?>
				<span class="customize-control-title siteorigin-teaser-text">
					<?php echo esc_html( $this->teaser['title'] ); ?>
				</span>
			<?php } ?>

			<?php if ( ! empty( $this->teaser['text'] ) ) { ?>
				<div class="siteorigin-teaser-text">
					<?php echo $this->teaser['text']; ?>
				</div>
			<?php } ?>
			<?php
		}
	}

	/**
	 * Enqueue everything we need for this teaser.
	 */
	public function enqueue() {
		wp_enqueue_script( 'siteorigin-settings-teaser-control', get_template_directory_uri() . '/inc/settings/js/control/teaser-control' . SITEORIGIN_THEME_JS_PREFIX . '.js', array( 'jquery', 'customize-controls' ) );
		wp_enqueue_style( 'siteorigin-settings-teaser-control', get_template_directory_uri() . '/inc/settings/css/control/teaser-control.css', array() );
	}
}
