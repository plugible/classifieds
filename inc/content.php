<?php

add_filter( 'the_content', function( $content ) {

	global $post;

	if ( 'pl_classified' !== $post->post_type ) {
		return $content;
	}

	if ( is_singular( 'pl_classified' ) ) {
		return plcl_load_template( 'single.php', true );
	} else if ( is_archive( 'pl_classified' ) ) {
		return plcl_load_template( $post->ID > 0 ? 'archive.php' : '404.php', true );
	}
} );
