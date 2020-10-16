<?php

/**
 * Add attachment to CMB2's `images` field. 
 */
add_action("add_attachment", function( $attachment_id ) {

	$attachment_post = get_post( $attachment_id );

	if ( ! $attachment_post->post_parent ) {
		return;
	}

	$parent_post = get_post( $attachment_post->post_parent );
	if ( 'pl_classified' !== $parent_post->post_type ) {
		return;
	}

	$attachments = get_posts( [
		'post_type'   => 'attachment',
		'post_parent' => $parent_post->ID,
	] );

	delete_post_meta( $parent_post->ID, 'images' );
	add_post_meta( $parent_post->ID, 'images', wp_list_pluck( $attachments, 'guid', 'ID' ) );
} );
