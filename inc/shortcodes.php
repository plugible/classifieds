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
