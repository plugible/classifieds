<?php

/**
 * Add field for OPs.
 */
add_action( 'pl_classified_loaded', function() {
	global $post;

	/**
	 * Only logged in user.
	 */
	if ( ! is_user_logged_in() ) {
		return;
	}

	/**
	 * Only OP.
	 */
	if ( get_current_user_id() != $post->post_author ) {
		return;
	}

	/**
	 * Add field.
	 */
	add_action( 'comment_form', function() {
		global $post;
		$name = plcl_get_param( 'discussion' );
		$value = $_REQUEST[ $name ] ?? '';
		printf( '<input name="%1$s" value="%2$s">', $name, $value );
	} );
} );

/**
 * Validate field.
 */
add_filter( 'pre_comment_approved', function( $approved, $commentdata ) {

	/**
	 * Only logged in user.
	 */
	if ( ! is_user_logged_in() ) {
		return $approved;
	}

	/**
	 * Only plcl_classified.
	 */
	if ( 'pl_classified' !== get_post_type( $commentdata[ 'comment_post_ID' ] ) ) {
		return $approved;
	}

	/**
	 * Only OP.
	 */
	$author_id    = get_post_field( 'post_author', $commentdata[ 'comment_post_ID' ] );
	$commenter_id = $commentdata[ 'user_id' ];
	if ( $author_id != $commenter_id ) {
		return $approved;
	}

	/**
	 * Prepare.
	 */
	$discussion = json_decode( plcl_decrypt( $_REQUEST[ plcl_get_param( 'discussion' ) ] ?? '' ) );

	/**
	 * Validate discussion.
	 */
	if ( empty( $discussion ) ) {
		return new WP_Error( 'plcl_error_comment_invalid', __( 'The comment is not valid.', 'wpmyads' ), 429 );
	}

	/**
	 * Add discussion info to the redirect URL.
	 */
	add_filter( 'comment_post_redirect', function( $location ) {
		return add_query_arg( plcl_get_param( 'discussion' ), $_REQUEST[ plcl_get_param( 'discussion' ) ], $location );
	} );

	/**
	 * Done.
	 */
	return $approved;
}, 10, 2 );

/**
 * Set `comment_discussion` meta.
 */
add_action( 'plcl_comment_created', function( $id ) {
	$comment       = get_comment( $id );
	$commenter_id  = $comment->user_id;
	$author_id     = get_post_field( 'post_author', $comment->comment_post_ID );

	$is_op_comment = $author_id == $commenter_id;

	$discussion = $is_op_comment
		? json_decode( plcl_decrypt( $_REQUEST[ plcl_get_param( 'discussion' ) ] ) )
		: $commenter_id
	;

	add_comment_meta( $id, 'comment_discussion', $discussion, true );
} );
