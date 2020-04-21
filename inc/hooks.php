<?php

/**
 * plcl_classified_created
 *
 * - Classified created.
 */
add_action( 'plcl_classified_inserted', function( $post_id ) {
	do_action( 'plcl_classified_created', $post_id );
} );

/**
 * plcl_classified_pending.
 *
 * - Classified created with status `draft`
 */
add_action( 'plcl_classified_inserted_draft', function( $post_id ) {
	do_action( 'plcl_classified_pending', $post_id );
} );

/**
 * plcl_classified_approved.
 *
 * - Classified published
 * - Classified created with status `publish`
 */
add_action( 'init', function() {
	static $once = [];
	add_action( 'plcl_classified_inserted_publish', function( $post_id ) use ( &$once ) {
		if ( ! in_array( $post_id, $once ) ) {
			$once[] = $post_id;
			do_action( 'plcl_classified_approved', $post_id );
		}
	} );
	add_action( 'transition_post_status', function( $new_status, $old_status, $post ) use ( &$once ) {
		if ( ! in_array( $post->ID, $once ) ) {
			$once[] = $post->ID;
			if ( $old_status !== $new_status
				&& 'pl_classified' === $post->post_type
				&& 'publish' === $new_status
			) {
				do_action( 'plcl_classified_approved', $post->ID );
			}
		}
	}, 10, 3 );
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
 * plcl_comment_created
 *
 * - Classified comment created
 */
add_action( 'wp_insert_comment', function( $id, $comment ) {
	if ( 'pl_classified' === get_post_type( $comment->comment_post_ID ) ) {
		do_action( 'plcl_comment_created', $id );
	}
}, 10, 2 );

/**
 * plcl_comment_pending
 *
 * - Classified comment inserted with status 'hold' or 0.
 */
add_action( 'wp_insert_comment', function( $id, $comment ) {
	if ( 'pl_classified' === get_post_type( $comment->comment_post_ID ) && ! $comment->comment_approved ) {
		do_action( 'plcl_comment_pending', $id );
	}
}, 10, 2 );

/**
 * plcl_comment_approved
 *
 * - Classified comment approved
 * - Classified comment inserted with status `approve`
 */
add_action( 'init', function() {
	static $once = [];	
	add_action( 'transition_comment_status', function( $new_status, $old_status, $comment ) use( &$once ) {
		if ( ! in_array( $comment->comment_ID, $once ) ) {
			$once[] = $comment->comment_ID;
			if ( $old_status !== $new_status
				&& 'pl_classified' === get_post_type( $comment->comment_post_ID )
				&& 'approved' === $new_status
			) {
				do_action( 'plcl_comment_approved', $comment->comment_ID );
			}
		}
	}, 10, 3 );
	add_action( 'wp_insert_comment', function( $id, $comment ) use( &$once ) {
		if ( ! in_array( $comment->comment_ID, $once ) ) {
			$once[] = $comment->comment_ID;
			if ( 'pl_classified' === get_post_type( $comment->comment_post_ID ) && $comment->comment_approved) {
				do_action( 'plcl_comment_approved', $id );
			}
		}
	}, 10, 2 );
} );

/**
 * plcl_comment_received
 *
 * - Classified comment approved
 */
add_action( 'plcl_comment_approved', function( $comment_ID ) {
	do_action( 'plcl_comment_received', $comment_ID );
} );

/**
 * plcl_comment_rejected
 *
 * - Classified comment rejected
 * - Classified comment inserted with status `approve`
 */
add_action( 'comment_unapproved_to_trash', function( $comment ) {
	if ( 'pl_classified' === get_post_type( $comment->comment_post_ID ) ) {
		do_action( 'plcl_comment_rejected', $comment->comment_ID );
	}
} );

/**
 * plcl_comment_update_hashes
 *
 * - Comment created
 * - Comment updated
 * - Comment status updated
 */
add_action( 'init', function() {
	$hook_update_hashes = function( $comment_id ) {
		static $once = [];
		if ( ! in_array( $comment_id, $once ) ) {
			$once[] = $comment_id;
			if ( 'pl_classified' === get_post_type( get_comment( $comment_id )->comment_post_ID ) ) {
				do_action( 'plcl_comment_hash_updated', $comment_id );
			}
		}
	};
	add_action( 'edit_comment', $hook_update_hashes );
	add_action( 'wp_insert_comment', $hook_update_hashes );
	add_action( 'wp_set_comment_status', $hook_update_hashes );
	add_action( 'plcl_comment_hash_used', $hook_update_hashes );
} );

/**
 * plcl_classified_update_hashes
 *
 * - Classified created
 * - Classified updated
 * - Classified status updated
 */
add_action( 'init', function() {
	$once = [];
	add_action( 'transition_post_status', function( $_1, $_2, $post ) use( &$once ) {
		if ( 'pl_classified' === $post->post_type ) {
			if ( ! in_array( $post->ID, $once ) ) {
				$once[] = $post->ID;
				do_action( 'plcl_classified_update_hashes', $post->ID );
			}
		};
	}, 9, 3 );
	add_action( 'save_post_pl_classified', function( $post_id ) use( &$once ) {
		if ( ! in_array( $post_id, $once ) ) {
			$once[] = $post_id;
			do_action( 'plcl_classified_update_hashes', $post_id );
		}
	}, 9 );
} );
