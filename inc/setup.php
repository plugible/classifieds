<?php

/**
 * Require dependencies.
 */
classifieds_by_plugible()->require_plugin( 'piklist' );

/**
 * Register post types.
 */
add_filter( 'piklist_post_types', function ( $post_types ) {

	/**
	 * Register the "classified" post type.
	 */
	$post_types[ 'pl_classified' ] = [
		'labels' => piklist( 'post_type_labels', __( 'Classifieds', 'classifieds-by-plugibles' ) ),
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
	];

	return $post_types;
} );

/**
 * Register taxonomies. 
 */
add_filter('piklist_taxonomies', function ($taxonomies) {

	/**
	 * Register the "Classified/Location" taxonomy.
	 */
	$taxonomies[] = [
		'post_type' => 'pl_classified',
		'name' => 'pl_classified_location',
		'show_admin_column' => true,
		'configuration' => [
			'hierarchical' => true,
			'labels' => piklist( 'taxonomy_labels', __( 'Location', 'classifieds-by-plugibles' ) ),
			'hide_meta_box' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => [
				'slug' => 'classifieds-location',
			],
		],
	];

	/**
	 * Register the "Classified/Category" taxonomy.
	 */
	$taxonomies[] = [
		'post_type' => 'pl_classified',
		'name' => 'pl_classified_category',
		'show_admin_column' => true,
		'configuration' => [
			'hierarchical' => true,
			'labels' => piklist( 'taxonomy_labels', __( 'Category', 'classifieds-by-plugibles' ) ),
			'hide_meta_box' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => [
				'slug' => 'classifieds-category',
			],
		],
	];

	return $taxonomies;
} );