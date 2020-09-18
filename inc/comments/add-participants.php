<?php

add_action( 'plcl_comment_created', function( $comment_id ) {

	$comment        = get_comment( $comment_id );
	$post_author_id = get_post( $comment->comment_post_ID )->post_author;
	$discussion_id  = get_comment_meta( $comment_id, 'comment_discussion', true );
	$participants   = plcl_get_discussion_participants( $discussion_id, [
		'excludes' => [ $comment->user_id ],
		'includes' => [ $post_author_id ],
	] );

	add_comment_meta( $comment_id, 'comment_to', $participants[0], true );
	add_comment_meta( $comment_id, 'comment_from', $comment->user_id, true );
	add_comment_meta( $comment_id, 'comment_read', -1, true );
}, 20 );
