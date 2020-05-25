<?php

add_action( 'pl_classified_loaded', function() {
	global $post;

	if ( is_user_logged_in() ) {
		/**
		 * Prepare discussion number (commenter user ID).
		 */
		$discussion = get_current_user_id() == $post->post_author
			? plcl_decrypt( get_query_var( plcl_get_param( 'discussion' ) ) )
			: get_current_user_id()
		;
		/**
		 * Members, when user is logged in, should absolutly contain 2 members or '0'.
		 *
		 * - '0' hides all comments.
		 */
		$members = array_values( array_filter( array_unique( [
			get_current_user_id(),
			$post->post_author,
			$discussion,
		] ) ) );
		if ( count( $members ) !== 2 ) {
			$members = [0];
		}

		add_filter( 'comments_template_query_args', function( $comment_args ) use( $members, $discussion ) {
			$comment_args[ 'author__in' ] = $members;
			$comment_args[ 'meta_query' ] = [
				'relation' => 'AND',
				[
					'key' => 'comment_discussion',
					'value' => $discussion,
				],
			];
			return $comment_args;
		} );
	} else {
		$guest_commentor_email = wp_get_unapproved_comment_author_email();
		if ( $guest_commentor_email ) {
			/**
			 * Show messages from current unapproved email address.
			 */
			add_filter( 'comments_template_query_args', function( $comment_args ) use ( $guest_commentor_email ) {
				return array_merge( $comment_args, [
					'author_email'       => $guest_commentor_email,
					'include_unapproved' => $guest_commentor_email,
					'order'              => 'DESC',
					'status'             => 0,
				] );
			} );
		} else {
			/**
			 * Show no messages for normal users.
			 */
			$members = [0];
			add_filter( 'comments_template_query_args', function( $comment_args ) use( $members ) {
				$comment_args[ 'author__in' ] = $members;
				return $comment_args;
			} );
		}
	}
} );
