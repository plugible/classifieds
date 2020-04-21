<?php

add_filter( 'preprocess_comment', function( $commentdata ) {

	/**
	 * Vefirfy post.
	 */
	$post = get_post( $commentdata[ 'comment_post_ID' ] );
	if ( ! $post || 'pl_classified' !== $post->post_type ) {
		return $commentdata;
	}

	/**
	 * Verify `user_id` is not set already.
	 */
	if ( $commentdata[ 'user_id' ] ) {
		return;
	}

	/**
	 * Get/create user.
	 */
	$user = plcl_get_user( $commentdata[ 'comment_author_email' ], true, [
		'first_name' => $commentdata[ 'comment_author' ],
	] );

	/**
	 * Set.
	 */
	$commentdata[ 'user_id' ] = $user->ID;
	$commentdata[ 'user_ID' ] = $user->ID;

	/**
	 * Done.
	 */
	return $commentdata;
} );
