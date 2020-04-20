<?php

/**
 * Prevent on frontend.
 */
add_action( '0wp', function() {
	global $post;

	if ( 1
		&& 'pl_classified' == $post->post_type
		&& 0 == $post->comment_count
		&& get_current_user_id() == $post->post_author
	) {
		add_action( 'comment_form_before', function() {
			ob_start();
		} );

		add_action( 'comment_form_after', function() {
			ob_end_clean();
		} );
	}
} );

/**
 * Prevent other ways.
 */
add_action( '0pre_comment_on_post', function( $comment_post_ID ) {
	$post = get_post( $comment_post_ID );

	if ( 1
		&& 'pl_classified' == $post->post_type
		&& 0 == $post->comment_count
		&& get_current_user_id() == $post->post_author
	) {
		plcl_die();
	}
} );
