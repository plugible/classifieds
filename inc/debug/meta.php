<?php

if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
	return;
}

/**
 * Show Classifieds meta in admin table.
 */
add_action( 'manage_pl_classified_posts_custom_column', function( $column_name, $post_id ) {
	if ( 'meta' == $column_name ) {
		$meta = array_filter( get_post_meta( $post_id ), function( $meta_key ) {
			return false
				|| 'classified_hash' === substr( $meta_key, 0, 15 )
				|| 'comment_hash' === substr( $meta_key, 0, 12 )
			;
		}, ARRAY_FILTER_USE_KEY );
		foreach ( $meta as $k => $v) {
			echo "$k:<br>";
			foreach ( $v as $vv) {
				echo "- $vv<br>";
			}
		}
	}
}, 10, 2 );
add_filter( 'manage_pl_classified_posts_columns', function ( $columns ) {
	$columns['meta'] = __( 'Meta', 'classifieds-by-plugible' );
	return $columns;
} );

/**
 * Show comment meta in admin table.
 */
add_filter( 'manage_edit-comments_columns', function( $columns ) {
	return array_merge( $columns, [ 'meta' => __( 'Meta', 'classifieds-by-plugible' ) ] );
} );
add_filter( 'manage_comments_custom_column', function( $column, $comment_id ) {
	if ( 'meta' === $column ) {
		$meta = array_filter( get_comment_meta( $comment_id ), function( $k ) {
			return in_array( $k, [
				'comment_discussion',
				'comment_hash_shared',
				'comment_hash_unique',
			] );
		}, ARRAY_FILTER_USE_KEY );
		foreach ( $meta as $k => $v) {
			echo "$k:<br>";
			foreach ( $v as $vv) {
				echo "- $vv<br>";
			}
		}
	}
}, 10, 2 );

/**
 * Show comment meta in frontend.
 */
add_filter( 'comment_text', function( $comment_text, $comment, $args ) {
	if ( 1
		&& current_user_can( 'manage_options')
		&& ! is_admin()
		&& 'pl_classified' === get_post_type( $comment->comment_post_ID )
	) {
		$meta = array_filter( get_comment_meta( $comment->comment_ID ), function( $k ) {
			return in_array( $k, [
				'comment_discussion',
				'comment_hash_shared',
				'comment_hash_unique',
			] );
		}, ARRAY_FILTER_USE_KEY );
		$add_text = '';
		foreach ( $meta as $k => $v) {
			$add_text .= "$k:\n";
			foreach ( $v as $vv) {
				$add_text .= "- $vv\n";
			}
		}
		$comment_text .= "<pre>$add_text</pre>";
	}
	return $comment_text;
}, 10, 3 );