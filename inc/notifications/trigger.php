<?php

/**
 * Classified submitted.
 */
add_action( 'plcl_classified_inserted_publish', function( $post_id ) {
	do_action( 'plcl_classified_approved', $post_id );
} );
add_action( 'plcl_classified_inserted_draft', function( $post_id ) {
	do_action( 'plcl_classified_pending', $post_id );
} );

/**
 * Classified status changed.
 */
add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
	if ( $old_status !== $new_status && 'pl_classified' === $post->post_type ) {
		switch ( $new_status ) {
		case 'publish':
			do_action( 'plcl_classified_approved', $post->ID );
			break;
		case 'trash':
			do_action( 'plcl_classified_rejected', $post->ID );
			break;
		default:
			break;
		}
	}
}, 10, 3 );

/**
 * Classified comment approved.
 */
add_action( 'transition_comment_status', function( $new_status, $old_status, $comment ) {
	if ( $old_status !== $new_status && 'pl_classified' === get_post_type( $comment->comment_post_ID ) ) {
		if ( 'approved' === $new_status ) {
			do_action( 'plcl_comment_approved', $comment );
		}
	}
}, 10, 3 );
add_action( 'wp_insert_comment', function( $id, $comment ) {
	if ( 'pl_classified' === get_post_type( $comment->comment_post_ID ) ) {
		do_action( 'plcl_comment_approved', $comment );
	}
}, 10, 2 );
