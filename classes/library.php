<?php

class Meow_MCT_Library {

	public $core = null;

	public function __construct( $core ) {
		$this->core = $core;
		add_filter( 'manage_media_columns', array( $this, 'manage_media_columns' ) );
		add_action( 'manage_media_custom_column', array( $this, 'manage_media_custom_column' ), 10, 2 );
	}

	function manage_media_columns( $cols ) {
		$cols["image-copytrack"] = "Copytrack";
		return $cols;
	}

	function manage_media_custom_column( $column_name, $id ) {
		if ( $column_name !== 'image-copytrack' )
			return;
		echo '<div class="mct_column" data-mct-id="' . $id . '">';
		echo '</div>';
	}

}


?>
