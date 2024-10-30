<?php

// Authorize: https://app.copytrack.com/application/authorize?name=WP%20CopyTrack&redirect_uri=

require MCT_PATH . '/vendor/autoload.php';

class Meow_MCT_Core
{
	public $is_rest = false;
	public $is_cli = false;
	public $site_url = null;

	private $http = null;
	public $key = null;
	public $token = null;
	public $hasCredentials = false;

	public function __construct() {
		$this->site_url = get_site_url();
		$this->is_rest = MeowCommon_Helpers::is_rest();
		$this->is_cli = defined( 'WP_CLI' ) && WP_CLI;
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	function init() {
		load_plugin_textdomain( MCT_DOMAIN, false, basename( MCT_PATH ) . '/languages' );

		// Part of the core, settings and stuff
		$this->admin = new Meow_MCT_Admin();

		// Only for REST
		if ( $this->is_rest ) {
			new Meow_MCT_Rest( $this );
		}

		// Credentials for requests
		$this->hasCredentials = $this->get_credentials_info();
		if ( $this->hasCredentials ) {
			$this->http = new \GuzzleHttp\Client([ 'headers' => [
				'User-Agent' => 'WordPress/1.0',
				'x-api-key' => $this->key,
				'x-auth-token' => $this->token
			]]);
		}

		// Admin screens
		if ( is_admin() ) {
			new Meow_MCT_Library( $this );
		}
	}

	public function get_credentials_info() {
		$this->key = get_option( 'mct_key' );
		$this->token = get_option( 'mct_token' );
		return !empty( $this->token ) && !empty( $this->key );
	}

	public function get_media_info( $id ) {
		$data = get_post_meta( $id, '_copytrack', true );
		return empty( $data ) ? null : $data;
	}

	function add_url( $url, $id, $name = null ) {
		if ( empty( $name ) )
			$name = basename( $url );
		try {
			$res = $this->http->request( 'POST', 'https://api.copytrack.com/v1/images/add-url', [
				'json' => [
					'collection' => $this->get_collection(),
					'image' => $url,
					'name' => $name,
					'status' => 'active'
				]
			]);
		}
		catch (GuzzleHttp\Exception\ClientException $ex) {
			if ( $ex->getCode() == 422 ) {
				$body = $ex->getResponse()->getBody( true );
				if ( strpos( $body, 'already' ) !== false) {
    			// The file has already been uploaded
					$meta = get_post_meta( $id, '_copytrack', true );
					if ( empty( $meta ) )
						$data = update_post_meta( $id, '_copytrack', array( 'already' => true ) );
						clean_post_cache( $id );
					return true;
				}
			}
			throw $ex;
		}
		$data = json_decode( $res->getBody() );
		$data = update_post_meta( $id, '_copytrack', array( 'id' => $data->id ) );
		clean_post_cache( $id );
		return true;
	}

	function get_collections() {
		$res = $this->http->request( 'GET', 'https://api.copytrack.com/v1/collections' );
		$data = json_decode( $res->getBody() );
		return $data;
	}

	function get_collection( $name = 'WordPress' ) {
		$cols = $this->get_collections();
		foreach ( $cols as $col ) {
			if ( $col->name === $name )
				return $col->id;
		}
		$website_name = get_bloginfo( 'name' );
		$website_url = get_site_url();
		$res = $this->http->request( 'POST', 'https://api.copytrack.com/v1/collections', [
			'json' => [
				'description' => sprintf( "Collection created by %s (%s) via Image Copytrack.", $website_name, $website_url ),
				'name' => $name
			]
		]);
		$data = json_decode( $res->getBody() );
		return $data->id;
	}

}

// $core = new Meow_MCT_Core();
// print_r( $core->add_url( 'http://offbeatjapan.org/wp-content/uploads/2016/11/jordy-meow-0930071152.jpg' ) );
// $core->add_url( 'http://offbeatjapan.org/wp-content/uploads/2012/02/north-korea-village-1280x640.jpg' );

?>