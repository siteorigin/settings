<?php

class SiteOrigin_Settings_About_Page {

	function __construct(){
		add_action( 'load-themes.php', array( $this, 'activation_admin_notice' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	static function single(){
		static $single;
		if( empty( $single ) ) {
			$single = new self();
		}
		return $single;
	}

	public function activation_admin_notice() {
		global $pagenow;

		if ( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) ) {
			add_action( 'admin_notices', array( $this, 'about_page_notice' ), 99 );
		}
	}

	function about_page_notice(){

		$theme = wp_get_theme( get_template() );

		?>
		<div class="updated notice is-dismissible">
			<p>
				<?php printf( esc_html__( 'Thanks for choosing %s!', 'siteorigin' ), $theme->get( 'Name' ) ); ?>
				<?php
				printf(
					esc_html__( 'You can learn more about it %shere%s, or head straight to the %scustomizer%s to start setting it up.', 'siteorigin' ),
					'<a href="' . admin_url( 'themes.php?page=siteorigin-theme-about' ) . '">',
					'</a>',
					'<a href="' . admin_url( 'customize.php' ) . '">',
					'</a>'
				); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=siteorigin-theme-about' ) ); ?>" class="button-primary">
					<?php printf( esc_html__( 'Learn About %s', 'siteorigin' ), $theme->get( 'Name' ) ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	function add_menu_page( ){
		$theme = wp_get_theme( get_template() );
		$theme_name = $theme->get( 'Name' );

		add_theme_page(
			sprintf( __( 'About %s' ), $theme_name ),
			sprintf( __( 'About %s' ), $theme_name ),
			'edit_theme_options',
			'siteorigin-theme-about',
			array( $this, 'display_about_page' )
		);
	}

	function enqueue_scripts( $prefix ) {
		if( $prefix !== 'appearance_page_siteorigin-theme-about' ) return;

		wp_enqueue_script(
			'siteorigin-settings-about',
			get_template_directory_uri() . '/inc/settings/js/about' . SITEORIGIN_THEME_JS_PREFIX . '.js',
			array( 'jquery' ),
			SITEORIGIN_THEME_VERSION
		);

		wp_enqueue_style(
			'siteorigin-settings-about',
			get_template_directory_uri() . '/inc/settings/css/about.css',
			array( ),
			SITEORIGIN_THEME_VERSION
		);
	}

	function get_share_link( $network ) {
		$theme = wp_get_theme( get_template() );
	}

	function display_about_page(){

		$theme = wp_get_theme( get_template() );

		$about = apply_filters( 'siteorigin_about_page', array(
			'title' => sprintf( __( 'About %s', 'siteorigin' ), $theme->get( 'Name' ) ),
			'sections' => array(),
			'title_image' => false,
			'title_image_2x' => false,
			'version' => $theme->get( 'Version' ),
			'description' => $theme->get( 'Description' ),
			'video_thumbnail' => false,
			'video_url' => false,
			'video_description' => false,
		) );

		?>
		<div class="wrap" id="siteorigin-about-page">
			<div class="about-header">
				<div class="about-container">
					<?php if ( ! empty( $about[ 'title_image' ] ) ) : ?>
						<img
							src="<?php echo esc_url( $about[ 'title_image' ] ) ?>"
							title="<?php echo esc_attr( $about[ 'title' ] ) ?>"
					        <?php if( ! empty( $about[ 'title_image_2x' ] ) ) : ?>
					            srcset="<?php echo esc_url( $about[ 'title_image_2x' ] ) ?> 2x"
					        <?php endif ?>
					        />
						<div class="version"><?php echo esc_html( $about['version'] ) ?></div>
					<?php else : ?>
						<h1>
							<?php echo esc_html( $about[ 'title' ] ) ?>
							<div class="version"><?php echo esc_html( $about['version'] ) ?></div>
						</h1>
					<?php endif; ?>

<!--					<button class="button-primary">-->
<!--						--><?php //_e( 'Share', 'siteorigin' ) ?>
<!--					</button>-->

				</div>
			</div>

			<?php if( ! empty( $about[ 'video_thumbnail' ] ) ) : ?>
				<div class="about-video">
					<div class="about-container">
						<a href="<?php echo esc_url( $about[ 'video_url' ] ) ?>" class="about-play-video" target="_blank">
							<svg version="1.1" id="play" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
							     viewBox="0 0 540 320.6" style="enable-background:new 0 0 540 320.6;" xml:space="preserve">
								<path class="st0" d="M511,0H29C13,0,0,13,0,29v262.6c0,16,13,29,29,29h482c16,0,29-13,29-29V29C540,13,527,0,511,0z"/>
								<path class="st1" d="M326.9,147.3c4.2,2.6,6.9,7.6,6.9,13c0,5.4-2.7,10.3-7.2,13.2l-94.9,69.9c-2.6,2.2-6.1,3.5-9.8,3.5
								c-8.7,0-15.7-7-15.7-15.7V89.4c0-8.6,7-15.7,15.7-15.7c3.7,0,7.3,1.3,10.1,3.7L326.9,147.3z"/>
							</svg>
						</a>

						<div class="about-video-images">
							<?php
							if( is_array( $about[ 'video_thumbnail' ] ) ) {
								$images = $about[ 'video_thumbnail' ];
							}
							else {
								$images = array( $about[ 'video_thumbnail' ] );
							}

							foreach( $images as $image ) {
								?><img src="<?php echo esc_url( $image ) ?>" class="about-video-image" /> <?php
							}
							?>
						</div>

						<?php if( ! empty( $about['video_description'] ) ) ?>
						<div class="about-video-description">
							<?php echo wp_kses_post( $about['video_description'] ) ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if( ! empty( $about['sections'] ) ) : ?>
				<div class="about-sections">
					<?php foreach( $about['sections'] as $section ) : ?>
						<div class="about-section about-container">
							<?php get_template_part( 'admin/about/page', $section['id'] ) ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

}