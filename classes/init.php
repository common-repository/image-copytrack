<?php

spl_autoload_register(function ( $class ) {
  $necessary = true;
  $file = null;
  if ( strpos( $class, 'Meow_MCT' ) !== false ) {
    $file = MCT_PATH . '/classes/' . str_replace( 'meow_mct_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowCommon_' ) !== false ) {
    $file = MCT_PATH . '/common/' . str_replace( 'meowcommon_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowPro_MCT' ) !== false ) {
    $necessary = false;
    $file = MCT_PATH . '/premium/' . str_replace( 'meowpro_mct_', '', strtolower( $class ) ) . '.php';
  }
  if ( $file ) {
    if ( !$necessary && !file_exists( $file ) ) {
      return;
    }
    require( $file );
  }
});

require_once( MCT_PATH . '/common/helpers.php');

// In admin or Rest API request (REQUEST URI begins with '/wp-json/')
if ( is_admin() || MeowCommon_Helpers::is_rest() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	global $mct_core;
	$mct_core = new Meow_MCT_Core();
}

?>