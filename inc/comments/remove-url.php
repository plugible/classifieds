<?php

add_filter( 'comment_form_fields', function( $fields ) {
	global $post;

	if ( 'pl_classified' !== $post->post_type ) {
		return $fields;
	}

	unset( $fields[ 'url' ] );

	return $fields;
} );