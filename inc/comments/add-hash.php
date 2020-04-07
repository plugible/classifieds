<?php

/**
 * Create hooks.
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

/**
 * Add hashes to new comments.
 *
 * - Add a shared hash to the comment and its post ( `comment_email:post_id` )
 * - Add a unique hash to the comment ( `comment_email:post_id:random` )
 */
add_action( 'plcl_comment_update_hashes', function( $id ) {
	$comment = get_comment( $id );

	$hashes = [
		'shared' => substr( hash( 'sha256', sprintf( '%1$s:%2$s', get_comment_author_email( $id ), $comment->comment_post_ID ) ), 0, 12 ),
		'unique' => substr( hash( 'sha256', sprintf( '%1$s:%2$s:%3$s', get_comment_author_email( $id ), $comment->comment_post_ID, wp_generate_password() ) ), 0, 12 ),
	];
	/**
	 * Add shared hash to comment.
	 */
	delete_comment_meta( $id, 'comment_hash_shared' );
	add_comment_meta( $id, 'comment_hash_shared', $hashes[ 'shared' ], true );

	/**
	 * Add unique hash to comment.
	 */
	delete_comment_meta( $id, 'comment_hash_unique' );
	add_comment_meta( $id, 'comment_hash_unique', $hashes[ 'unique' ], true );

	/**
	 * Add share hash to post.
	 */
	add_post_meta( $comment->comment_post_ID, 'comment_hash_shared', $hashes[ 'shared' ] );

	/**
	 * Re-assign post hashes
	 */
	$post_hashes = get_post_meta( $comment->comment_post_ID, 'comment_hash_shared' );
	$post_hashes = array_filter( array_unique( $post_hashes ), function( $hash ) use( $comment ) {
		return get_comments( [
			'post_id'    => $comment->comment_post_ID,
			'status'     => 'approve',
			'meta_value' => $hash,
			'count'      => true,
		] ) > 0;
	} );
	delete_post_meta( $comment->comment_post_ID, 'comment_hash_shared' );
	foreach( $post_hashes as $hash ) {
		add_post_meta( $comment->comment_post_ID, 'comment_hash_shared', $hash );
	}
} );
