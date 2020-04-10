<?php

if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
	return;
}

/**
 * Show comment meta.
 */
add_filter( 'manage_edit-comments_columns', function( $columns ) {
	return array_merge( $columns, [ 'meta' => __( 'Meta', 'classifieds-by-plugible' ) ] );
} );

add_filter( 'manage_comments_custom_column', function( $column, $comment_id ) {
	if ( 'meta' === $column ) {
		! d( get_comment_meta( $comment_id ) );
	}
}, 10, 2 );