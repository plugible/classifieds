<?php

/**
 * Reset classified hash.
 *
 * - Add a unique hash to the post ( `user_id:post_id:random` )
 */
add_action( 'plcl_classified_update_hashes', function( $post_id ) {
	$hashes = [
		'unique' => plcl_hash( get_post_field( 'post_author', $post_id ) . $post_id, true ),
	];
	delete_post_meta( $post_id, 'classified_hash_unique' );
	add_post_meta( $post_id, 'classified_hash_unique', $hashes[ 'unique' ], true );
} );

