<?php

add_shortcode( '0_plcl_list', function( $atts, $content, $shortcode_tag ) {

	$atts = shortcode_atts( [
		'category' => false,
		'ads_per_page' => PLCL_ADS_PER_PAGE,
	], $atts, 'plcl_list' );

	$posts = get_posts( [
		'paged' => plcl_get_request_parameter( plcl_get_param( 'page_number' ), 1 ),
		'post_type' => 'pl_classified',
		'post_status' => 'publish',
		'posts_per_page' => $atts[ 'ads_per_page' ],
		'tax_query' =>[
			[
				'taxonomy' => 'pl_classified_category',
				'field' => 'slug',
				'terms' => $atts[ 'category' ],
			],
		],
	] );

	/**
	 * Done. Return output.
	 */
	return plcl_load_template( 'shortcodes/list.php', $posts, true );
} );
