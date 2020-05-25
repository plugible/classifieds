<?php

add_action( 'pl_classified_loaded', function() {
	/**
	 * Determin type of request or exist function.
	 *
	 * Possible types:
	 * - op        : classified and comment author
	 * - author    : Classified author
	 * - commenter : Comment author
	 */
	$hashes = json_decode( plcl_decrypt( get_query_var( plcl_get_param( 'hash' ) ) ) );
	$type = false;
	if ( $hashes ) {
		if ( isset( $hashes->comment_hash_unique[0], $hashes->comment_hash_shared[0] ) ) {
			$type = 'commenter';
		} else if ( isset( $hashes->classified_hash_unique[0], $hashes->comment_hash_shared[0] ) ) {
			$type = 'op';
		} else if ( isset( $hashes->classified_hash_unique[0] ) ) {
			$type = 'author';
		}
	}

	/**
	 * Handle hash(es).
	 */
	switch ( $type ) {
	case 'commenter':
		/**
		 * Get comment.
		 */
		$comments = get_comments( [
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => 'comment_hash_shared',
					'value' => $hashes->comment_hash_shared ?? wp_generate_password(),
				],
				[
					'key' => 'comment_hash_unique',
					'value' => $hashes->comment_hash_unique ?? wp_generate_password(),
				],
			],
		] );
		$comment = array_pop( $comments ); 
		if( $comment ) {
			/**
			 * Log commentor in.
			 */
			do_action( 'plcl_comment_hash_used', $comment->comment_ID );
			$author = plcl_get_user( $comment->comment_author_email, true );
			plcl_auth( $author->ID, get_comment_link( $comment->comment_ID ) );
		}
		break;
	case 'op':
		$posts = get_posts( [
			'post_type' => 'pl_classified',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => 'comment_hash_shared',
					'value' => $hashes->comment_hash_shared ?? wp_generate_password(),
				],
				[
					'key' => 'classified_hash_unique',
					'value' => $hashes->classified_hash_unique ?? wp_generate_password(),
				],
			],
		] );
		if ( $posts && is_a( $posts[0], 'WP_Post' ) ) {
			/**
			 * Log OP in.
			 */
			plcl_auth( $posts[0]->post_author, get_permalink( $posts[0] ) );
		}
		break;
	default:
		break;
	}

} );
