<?php

add_filter( 'pre_comment_approved', function( $approved, $commentdata ) {
	/**
	 * Only guests.
	 */
	if ( is_user_logged_in() ) {
		return $approved;
	}

	/**
	 * Only plcl_classified.
	 */
	if ( 'pl_classified' !== get_post_type( $commentdata[ 'comment_post_ID' ] ) ) {
		return $approved;
	}

	/**
	 * Reject guest comments with OP email.
	 */
	$op_email = get_the_author_meta( 'user_email', get_post( $commentdata[ 'comment_post_ID' ] )->post_author );
	if ( $commentdata[ 'comment_author_email' ] === $op_email ) {
		return new WP_Error( 'plcl_error_comment_invalid', __( 'The comment is not valid.', 'wpmyads' ), 429 );
	}

	/**
	 * Done.
	 */
	return $approved;
}, 10, 2 );
