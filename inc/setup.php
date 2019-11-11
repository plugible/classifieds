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
