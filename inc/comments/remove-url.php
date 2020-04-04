<?php

add_action( 'all', function( $f ) {
	// if ( strstr( $f, 'comment_form' ) ) echo "$f ";
} );

add_filter( 'comment_form_fields', function( $fields ) {
	global $post;

	if ( 'pl_classified' !== $post->post_type ) {
		return $fields;
	}

	unset( $fields[ 'url' ] );

	return $fields;
} );