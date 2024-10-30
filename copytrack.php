<?php
/*
Plugin Name: Image Copytrack
Plugin URI: https://meowapps.com
Description: Copytrack detects where your images has been used on the web and assist you in the legal process, for free.
Version: 1.2.4
Author: Jordy Meow
Text Domain: copytrack
Domain Path: /languages

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html
*/

if ( !( is_admin() || strpos( trailingslashit( $_SERVER['REQUEST_URI'] ), '/' . 
	trailingslashit( rest_get_url_prefix() ) ) === 0 ) ) {
	return;
}

define( 'MCT_VERSION', '1.2.4' );
define( 'MCT_PREFIX', 'mct' );
define( 'MCT_DOMAIN', 'copytrack' );
define( 'MCT_ENTRY', __FILE__ );
define( 'MCT_PATH', dirname( __FILE__ ) );
define( 'MCT_URL', plugin_dir_url( __FILE__ ) );

require_once( 'classes/init.php');

?>
