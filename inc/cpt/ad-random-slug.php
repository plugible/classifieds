<?php

/**
 * Use random string as slug for classifieds.
 */
add_filter( 'save_post_pl_classified', function( $post_id, $post, $update ) {
	if ( ! $update ) {
		wp_update_post( array(
			'ID' => $post_id,
			'post_name' => strtolower( wp_generate_password( 7, false ) ),
		) );
	}
}, 10, 3 );
