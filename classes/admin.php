<?php

class Meow_MCT_Admin extends MeowCommon_Admin {

	public function __construct() {
		parent::__construct( MCT_PREFIX, MCT_ENTRY, MCT_DOMAIN );
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'app_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}
	}
	function admin_enqueue_scripts() {
		$physical_file = MCT_PATH . '/app/index.js';
		$cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : MCT_VERSION;
		wp_register_script( 'mct_image_copytrack-vendor', MCT_URL . 'app/vendor.js',
			['wp-element', 'wp-i18n'], $cache_buster
		);
		wp_register_script( 'mct_image_copytrack', MCT_URL . 'app/index.js',
			['mct_image_copytrack-vendor', 'wp-i18n'], $cache_buster
		);
		wp_set_script_translations( 'mct_image_copytrack', 'media-file-renamer' );
		wp_enqueue_script('mct_image_copytrack' );

		// Load the fonts
		wp_register_style( 'meow-neko-ui-lato-font', 
			'//fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap');
		wp_enqueue_style( 'meow-neko-ui-lato-font' );

		wp_localize_script( 'mct_image_copytrack', 'mct_image_copytrack', [
			'api_nonce' => wp_create_nonce( 'mct_image_copytrack' ),
			'api_url'   => site_url('/wp-json/copytrack/v1/')
		]);

		// Localize and options
		wp_localize_script( 'mct_image_copytrack', 'mct_image_copytrack', [
			'api_url' => get_rest_url(null, '/image-copytrack/v1/'),
			'rest_url' => get_rest_url(),
			'plugin_url' => MCT_URL,
			'prefix' => MCT_PREFIX,
			'domain' => MCT_DOMAIN,
			'rest_nonce' => wp_create_nonce( 'wp_rest' )
		] );
	}

	function app_menu() {
		add_submenu_page( 'meowapps-main-menu', 'Copytrack', 'Copytrack', 'manage_options', 'mct_settings-menu', 
			array( $this, 'admin_settings' )
		);
	}

	function admin_settings() {
		echo '<div id="mct-admin-dashboard"></div>';
	}

}

?>
