<?php

/**
 * Filter comments by comment meta, in the (main) comments template
 */
add_filter( 'comments_template_query_args', function( $comment_args ) {
	global $post;

	if ( 'pl_classified' !== $post->post_type ) {
		return $comment_args;
	}

	$comment_args['meta_query'] = [ [
		'key' => 'comment_hash_shared',
		'value' => get_query_var( 'comment_hash_shared' ),
	], ];

	return $comment_args;
} );
