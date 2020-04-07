<?php

/**
 * plcl_classified_approved
 *
 * - Classified published
 * - Classified created with status `publish`
 */
add_action( 'plcl_classified_inserted_publish', function( $post_id ) {
	do_action( 'plcl_classified_approved', $post_id );
} );
add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
	if ( $old_status !== $new_status
		&& 'pl_classified' === $post->post_type
		&& 'publish' === $new_status
	) {
		do_action( 'plcl_classified_approved', $post->ID );
	}
}, 10, 3 );

/**
 * plcl_classified_pending
 *
 * - Classified created with status `draft`
 */
add_action( 'plcl_classified_inserted_draft', function( $post_id ) {
	do_action( 'plcl_classified_pending', $post_id );
} );

/**
 * plcl_classified_rejected
 *
 * - Unpublished classified deleted.
 */
add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
	if ( $old_status !== $new_status
		&& 'pl_classified' === $post->post_type
		&& 'trash' === $new_status
		&& 'publish' !== $old_status
	) {
		do_action( 'plcl_classified_rejected', $post->ID );
	}
}, 10, 3 );

/**
 * plcl_comment_approved
 *
 * - pl_classified comment approved
 * - pl_classified comment inserted with status `approve`
 */
add_action( 'transition_comment_status', function( $new_status, $old_status, $comment ) {
	if ( $old_status !== $new_status
		&& 'pl_classified' === get_post_type( $comment->comment_post_ID )
		&& 'approved' === $new_status
	) {
		do_action( 'plcl_comment_approved', $comment );
	}
}, 10, 3 );
add_action( 'wp_insert_comment', function( $id, $comment ) {
	if ( 'pl_classified' === get_post_type( $comment->comment_post_ID ) && $comment->comment_approved) {
		do_action( 'plcl_comment_approved', $comment );
	}
}, 10, 2 );

/**
 * plcl_comment_update_hashes
 *
 * - Comment created
 * - Comment updated
 * - Comment status updated
 */
add_action( 'init', function() {
	$hook_update_hashes = function( $comment_id ) {
		if ( 'pl_classified' === get_post_type( get_comment( $comment_id )->comment_post_ID ) ) {
			do_action( 'plcl_comment_update_hashes', $comment_id );
		}
	};

	add_action( 'edit_comment', $hook_update_hashes );
	add_action( 'wp_insert_comment', $hook_update_hashes );
	add_action( 'wp_set_comment_status', $hook_update_hashes );
} );
