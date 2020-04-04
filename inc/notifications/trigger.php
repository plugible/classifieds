<?php

/**
 * Trigger classified emails with new submissin.
 */
add_action( 'plcl_classified_inserted', function( $post_id ) {
	$post = get_post( $post_id );
	do_action( 'plcl_classified_inserted_' . $post->post_status, $post_id );
} );

/**
 * Trigger classified emails with status change.
 */
add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
	if ( 'pl_classified' === $post->post_type
		&& 'publish' === $new_status
		&& 'draft' === $old_status
	) {
		plcl_mail( 'ad_approved', $post->ID );
	}
}, 10, 3 );
