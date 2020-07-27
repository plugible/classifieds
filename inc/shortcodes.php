<?php

add_shortcode( 'plcl_list', function( $atts, $content, $shortcode_tag ) {

	$atts = shortcode_atts( [
		'category' => false,
		'number' => 10,
	] , $atts, 'plcl_list' );

	$posts = get_posts( [
		'post_type' => 'pl_classified',
		'post_status' => 'publish',
		'posts_per_page' => $atts[ 'number' ],
		'tax_query' =>[
			[
				'taxonomy' => 'pl_classified_category',
				'field' => 'slug',
				'terms' => $atts[ 'category' ],
			],
		],
	] );

	return plcl_load_template( 'shortcodes/list.php', $posts, true );
} );


add_shortcode( 'plcl_user_classifieds', function( $atts, $content, $shortcode_tag ) {

	global $wp;

	$atts = shortcode_atts( [
		'number' => 1,
		'user' => 'current',
	] , $atts, 'plcl_list' );

	/**
	 * Handle user=current
	 */
	if ( 'current' === $atts[ 'user' ] ) {
		$atts[ 'user' ] = get_current_user_id() ? get_current_user_id() : -1;
	}

	/**
	 * Get page and prepare offset.
	 */
	$query_arg = apply_filters( 'plcl_query_arg_page', '_' . plcl_hash( 'page' ) );
	$page = ! empty( $_GET[ $query_arg ] )
		? ( int ) $_GET[ $query_arg ]
		: 1
	;
	$offset = ( $page - 1 ) * $atts[ 'number' ];

	/**
	 * Get ads.
	 */
	$posts = get_posts( [
		'author' => $atts[ 'user' ],
		'offset' => $offset,
		'post_type' => 'pl_classified',
		'post_status' => 'publish',
		'posts_per_page' => $atts[ 'number' ],
	] );

	/**
	 * Get pages total.
	 */
	if ( ! count( $posts ) ) {
		$total_pages = 1;
	} else if ( ! $offset && -1 == $atts[ 'number' ] ) {
		$total_pages = 1;
	} else {
		$total_posts = count( get_posts( [
			'author' => $atts[ 'user' ],
			'post_type' => 'pl_classified',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
		] ) );
		$total_pages = ceil( $total_posts / $atts[ 'number' ] );
	}

	/**
	 * Prepare output.
	 */
	$output = ''
		. '<div class="plcl-user-classifieds">'
		. plcl_load_template( 'shortcodes/list.php', $posts, true )
		. plcl_load_template( 'helpers/pagination.php', [
			'current_page' => $page,
			'query_arg' => $query_arg,
			'total_pages' => $total_pages,
		], true )
		. '</div>'
	;

	/**
	 * Done.
	 */
	return $output;
} );
