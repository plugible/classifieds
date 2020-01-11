<?php

add_filter( 'the_content', function( $content ) {

	global $post;

	if ( 'pl_classified' !== $post->post_type ) {
		return $content;
	}

	if ( is_singular( 'pl_classified' ) ) {
		plcl_load_template( 'single.php' );
	} else if ( is_archive( 'pl_classified' ) ) {
		plcl_load_template( 'archive.php' );
	}
} );
