<?php

/**
 * Register post types and taxonomies.
 */
add_action( 'init', function() {

	/**
	 * Register the "classified" post type.
	 */
	register_post_type( 'pl_classified', [
		'labels' => [
			'name' => 'Classifieds', 'classifieds-by-plugibles',
		],
		'menu_icon' => 'dashicons-megaphone',
		'public' => true,
			'rewrite' => [
			'slug' => 'classified',
		],
		'supports' => [
			'author',
			'custom-fields',
			'editor',
			'title',
		],
	] );

	/**
	 * Register the "Classified/Location" taxonomy.
	 */
	register_taxonomy( 'pl_classified_location', [ 'pl_classified' ], [
		'labels' => [
			'name' => 'Locations', 'classifieds-by-plugibles',
		],
		'show_admin_column' => true,
		'hierarchical' => true,
		'rewrite' => [
			'slug' => 'classifieds-location',
		],
	] );

	/**
	 * Register the "Classified/Category" taxonomy.
	 */
	register_taxonomy( 'pl_classified_category', [ 'pl_classified' ], [
		'labels' => [
			'name' => 'Categories', 'classifieds-by-plugibles',
		],
		'show_admin_column' => true,
		'hierarchical' => true,
		'rewrite' => [
			'slug' => 'classifieds-category',
		],
	] );
} );

/**
 * Classifieds attached images metabox.
 */
add_action( 'add_meta_boxes', function() {
	add_meta_box( 'pl_classified_images_metabox', __( 'Images' ), function( $post ) {
		$w = 150;
		$h = 150;
		$attachments = get_children( [
			'post_mime_type' => 'image',
			'post_parent' => $post->ID,
			'post_type' => 'attachment',
		] );
		foreach ( $attachments as $attachment_id => $attachment ) {
			echo wp_get_attachment_image( $attachment_id, [ $w, $h ] ) . ' ';
		}
	} );
});

/**
 * Classifieds attached images column.
 */
add_action( 'manage_pl_classified_posts_custom_column', function( $column_name, $post_id ) {
	$w = 32;
	$h = 32;
	if ( 'images' == $column_name ) {
		$attachments = get_children( [
			'post_mime_type' => 'image',
			'post_parent' => $post_id,
			'post_type' => 'attachment',
		] );
		foreach ( $attachments as $attachment_id => $attachment ) {
			echo wp_get_attachment_image( $attachment_id, [ $w, $h ] ) . ' ';
		}
	}
}, 10, 2 );
add_filter( 'manage_pl_classified_posts_columns', function ( $cols ) {
	$cols['images'] = __( 'Images' );
	return $cols;
} );



 