<?php

add_action( 'pl_classified_loaded', function() {
	/**
	 * Determine type of request or exist function.
	 *
	 * Possible types:
	 * - op        : classified and comment author
	 * - author    : Classified author
	 * - commenter : Comment author
	 */
	$hashes = json_decode( plcl_decrypt( plcl_get_request_parameter( plcl_get_param( 'hash' ) ) ) );
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
	 * Flashes an error message.
	 */
	$flash_error = function( $content ) {
		if ( is_main_query() ) {
			global $wp;
			$login_url = wp_login_url( home_url( $wp->request ) );
			$content =  ''
				. '<p class="plcl_flash">'
				. sprintf(
					__( 'The link you used has expired. <strong><a href="%s">Log in</a></strong> to see any private discussion you started.', 'classifieds-by-plugible' )
					, $login_url
				)
				. '</p>'
				. $content
			;
		}
		return $content;
	};

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
		add_action( 'the_content', $flash_error );
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
			 * Locate last comment with shared hash so we can get the author.
			 */
			$comments = get_comments( [
				'meta_key' => 'comment_hash_shared',
				'meta_value' => $hashes->comment_hash_shared ?? wp_generate_password(),
			] );
			$comment = array_pop( $comments );
			$link = $comment
				? add_query_arg( plcl_get_param( 'discussion' ), plcl_encrypt( plcl_get_user( $comment->comment_author_email, true )->ID ), get_comment_link( $comment ) )
				: get_permalink( $posts[0] )
			;
			/**
			 * Log OP in.
			 */
			do_action( 'plcl_classified_hash_used', $posts[0]->ID );
			plcl_auth( $posts[0]->post_author, $link );
		}
		add_action( 'the_content', $flash_error );
		break;
	default:
		break;
	}

} );
