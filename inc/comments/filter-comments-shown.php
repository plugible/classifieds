<?php

add_action( 'wp', function() {
	global $post;

	if ( ! $post || 'pl_classified' !== $post->post_type ) {
		return;
	}

	if ( is_user_logged_in() ) {
		$members = array_values( array_filter( array_unique( [
			( int ) get_current_user_id(),
			( int ) $post->post_author,
			( int ) plcl_decrypt( get_query_var( plcl_get_param( 'cid' ) ) ),
		] ) ) );
		/**
		 * Members, when user is logged in, should absolutly contain 2 members or '0'.
		 *
		 * - '0' hides all comments.
		 */
		if ( count( $members ) !== 2 ) {
			$members = [0];
		}
		add_filter( 'comments_template_query_args', function( $comment_args ) use( $members ) {
			$comment_args[ 'author__in' ] = $members;
			return $comment_args;
		} );
	} else {
		/**
		 * Show last message from current unapproved email address.
		 */
		add_filter( 'comments_template_query_args', function( $comment_args ) use( $members ) {
			$comment_args['include_unapproved'] = wp_get_unapproved_comment_author_email();
			$comment_args['author_email'] = wp_get_unapproved_comment_author_email();
			$comment_args['number'] = 1;
			return $comment_args;
		} );
	}
} );
