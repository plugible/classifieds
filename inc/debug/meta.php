<?php

if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
	return;
}

/**
 * Show Classifieds meta.
 */
add_action( 'manage_pl_classified_posts_custom_column', function( $column_name, $post_id ) {
	if ( 'meta' == $column_name ) {
		$meta = array_filter( get_post_meta( $post_id ), function( $meta_key ) {
			return false
				|| 'classified_hash' === substr( $meta_key, 0, 15 )
				|| 'comment_hash' === substr( $meta_key, 0, 12 )
			;
		}, ARRAY_FILTER_USE_KEY );
		echo '<pre>' . json_encode( $meta,  JSON_PRETTY_PRINT ) . '</pre>';
	}
}, 10, 2 );
add_filter( 'manage_pl_classified_posts_columns', function ( $columns ) {
	$columns['meta'] = __( 'Meta', 'classifieds-by-plugible' );
	return $columns;
} );

/**
 * Show comment meta.
 */
add_filter( 'manage_edit-comments_columns', function( $columns ) {
	return array_merge( $columns, [ 'meta' => __( 'Meta', 'classifieds-by-plugible' ) ] );
} );
add_filter( 'manage_comments_custom_column', function( $column, $comment_id ) {
	if ( 'meta' === $column ) {
		$meta = array_filter( get_comment_meta( $comment_id ), function( $meta_key ) {
			return 'comment_hash' === substr( $meta_key, 0, 12 );
		}, ARRAY_FILTER_USE_KEY );
		echo '<pre>' . json_encode( $meta,  JSON_PRETTY_PRINT ) . '</pre>';
	}
}, 10, 2 );
