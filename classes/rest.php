<?php

require MCT_PATH . '/vendor/autoload.php';

class Meow_MCT_Rest
{
	private $core = null;
	private $namespace = 'image-copytrack/v1';

	public function __construct( $core ) {
		$this->core = $core;
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	function rest_api_init() {
		register_rest_route( $this->namespace, '/update_option/', [
			'methods' => 'POST',
			'callback' => [ $this, 'rest_update_option' ]
		]);
		register_rest_route( $this->namespace, '/status/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => array( $this, 'rest_status' ),
			'args' => array( 'id' => array( 'required' => true ) )
		));
		register_rest_route( $this->namespace, '/upload/(?P<id>\d+)', array(
			'methods' => 'POST',
			'callback' => array( $this, 'rest_upload' ),
			'args' => array( 'id' => array( 'required' => true ) )
		));
		register_rest_route( $this->namespace, '/pending/', [
			'methods' => 'GET',
			'callback' => [ $this, 'rest_get_pending' ],
		]);
		register_rest_route( $this->namespace, '/account/', [
			'methods' => 'GET',
			'callback' => [ $this, 'rest_account' ],
		]);
	}

	function rest_status( $request ) {
		$params = $request->get_params();
		if ( empty( $params['id'] ) )
			return new WP_REST_Error( array( 'success' => false, 'message' => 'Missing ID.' ), 200 );
		$id = $params['id'];
		$data = $this->core->get_media_info( $id );
		return new WP_REST_Response( array( 
			'success' => true,
			'data' => $data ), 
		200 );
	}

	function rest_upload( $request ) {
		$params = $request->get_params();
		if ( empty( $params['id'] ) )
			return new WP_REST_Error( array( 
				'success' => false, 
				'error' => __( 'Missing ID.', 'image-copytrack' )
			), 200 );
		$id = $params['id'];
		$file = wp_get_attachment_url( $id );
		if ( empty( $file ) )
			return new WP_REST_Error( array( 
				'success' => false,
				'error' => __( 'Missing file.', 'image-copytrack' )
			), 200 );
		try {
			// For test.
			//$file = 'https://haikyo.org/wp-content/uploads/2015/01/haikyo-0915010047.jpg';
			$this->core->add_url( $file, $id );
		}
		catch (GuzzleHttp\Exception\ClientException $ex) {
			if ( $ex->getCode() == 400 ) {
				if ( str_contains( $ex->getMessage(), 'SSL certificate error' ) ) {
					return new WP_REST_Response( array( 
						'success' => false,
						'error' => __( 'There is a SSL Certificate Error with the CopyTrack server. Please try again later. If it doesn\'t get fixed, please contact CopyTrack.', 'image-copytrack' )
					), 200 );
				}
				return new WP_REST_Response( array( 
					'success' => false, 
					'error' =>  sprintf( 
						__( "There is a (probably temporary) error with the CopyTrack server. Please try again later. If it happens again, please visit the WordPress Support Forums and let us now. The exact error is: %s", 'image-copytrack' ),
						$ex->getMessage()
					)
				), 200 );
			}
			if ( $ex->getCode() == 401 ) {
				return new WP_REST_Response( array( 
					'success' => false, 
					'error' => __( 'Not authorized. Please check or renew your Copytrack key and token.', 'image-copytrack' )
				), 200 );
			}
			if ( $ex->getCode() == 422 ) {
				return new WP_REST_Response( array( 
					'success' => false, 
					'error' => __( 'Cannot access the file remotely. Is your file accessible via Internet?', 'image-copytrack' )
				), 200 );
			}
			return new WP_REST_Response( array( 
				'success' => false, 
				'error' => __( 'An error occurred. Please check your PHP Error Logs', 'image-copytrack' )
			), 200 );
		}
		return new WP_REST_Response( array( 
			'success' => true,
			'data' => $this->core->get_media_info( $id )
		), 200 );
	}

	function rest_get_pending() {
		global $wpdb;
		$ids = $wpdb->get_col("SELECT p.ID FROM $wpdb->posts p
			LEFT OUTER JOIN $wpdb->postmeta pm
			ON p.ID = pm.post_id AND pm.meta_key = '_copytrack'
			WHERE p.post_status = 'inherit'
			AND p.post_type = 'attachment'
			AND p.post_mime_type IN ( 'image/jpeg' )
			AND meta_value IS NULL");
		return new WP_REST_Response([
			'success' => true,
			'message' => __( 'OK', 'image-copytrack' ),
			'data' => $ids
		], 200 );
	}

	function rest_account() {
		$key = get_option( 'mct_key' );
		$token = get_option( 'mct_token' );
		return new WP_REST_Response( [
			'success' => true,
			'data' => [
				'key' => $key,
				'token' => $token
			]
		], 200 );
	}

	function rest_update_option( $request ) {
		$params = $request->get_json_params();
		try {
			update_option( 'mct_' . $params['name'], $params['value'] );
			return new WP_REST_Response([
				'success' => true,
				'message' => __( 'OK', 'image-copytrack' ),
				'data' => $params['value']
			], 200 );
		} catch (Exception $e) {
			return new WP_REST_Ressponse([
				'success' => false,
				'message' => $e->getMessage(),
			], 500 );
		}
	}
}

?>