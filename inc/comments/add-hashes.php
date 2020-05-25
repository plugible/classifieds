<?php

/**
 * Update comment hashes.
 *
 * - Add a shared hash to the comment and its post ( `comment_email:post_id` )
 * - Add a unique hash to the comment ( `comment_email:post_id:random` )
 */
add_action( 'plcl_comment_hash_updated', function( $id ) {
	$comment = get_comment( $id );

	$hashes = [
		'shared' => plcl_hash( get_comment_author_email( $id ) . $comment->comment_post_ID ),
		'unique' => plcl_hash( get_comment_author_email( $id ) . $comment->comment_post_ID, true ),
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
	 * Add shared hash to post.
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
