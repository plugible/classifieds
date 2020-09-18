<?php

add_shortcode( 'plcl_ads', function( $atts, $content, $shortcode_tag ) {

	/**
	 * Default attributes.
	 */
	$atts = shortcode_atts( [
		'ads_per_page'   => PLCL_ADS_PER_PAGE,
		'pagination'     => '',
		'user'           => '',
		'categories'     => '',
		'locations'      => '',
		'specifications' => '',
	], $atts, 'plcl_list' );
	ksort( $atts );

	/**
	 * Keep a count of calls.
	 *
	 * The number can be used for pagination hashes part of the link.
	 */
	static $calls = 0;
	$calls++;

	/**
	 * Prepare pagination.
	 */
	$atts[ 'pagination' ] = ! in_array( strtolower( $atts[ 'pagination' ] )
		, [
			'0',
			'false',
		]
		, true
	) ;

	/**
	 * Handle `user=current`.
	 *
	 * Set `user` to the current user ID.
	 */
	if ( 'current' === $atts[ 'user' ] ) {
		$atts[ 'user' ] = get_current_user_id() ? get_current_user_id() : -1;
	}

	/**
	 * Handle taxonomies.
	 *
	 * Handle the categories, locations and specifications taxonomies.
	 */
	$tax_query = [];
	foreach ( [
		'categories'     => 'pl_classified_category',
		'locations'      => 'pl_classified_location',
		'specifications' => 'pl_classified_specification',
	] as $parameter => $taxonomy ) {
		/**
		 * Verify that the shortcode attribute for this taxonomy is used.
		 */
		if ( empty( $atts[ $parameter ] ) ) {
			continue;
		}

		/**
		 * Prepare the terms array.
		 *
		 * - Turn string parameter to array
		 * - Trim values
		 * - Remove duplicates
		 * - Remove empty
		 */
		$terms = array_unique( array_filter( array_map( 'trim', explode( ',', $atts[ $parameter] ) ) ) );

		/**
		 * Change term slugs and name to IDs.
		 */
		foreach ( $terms as $index => $term_name_or_slug ) {
			/**
			 * Skip numeric values.
			 */
			if ( is_numeric( $term_name_or_slug ) ) {
				continue;
			}
			/**
			 * Try slug then name.
			 */
			foreach ( [
				'slug',
				'name',
			] as $by ) {
				$term = get_term_by( $by, $term_name_or_slug, $taxonomy );
				if ( $term ) {
					$terms[ $index ] = $term->term_id;
					continue;
				}
			}
		}

		/**
		 * Remove duplicates again.
		 */
		$terms = array_unique( $terms );

		/**
		 * Add to tax_query.
		 */
		if( $terms ) {
			$tax_query[] = [
				'taxonomy' => $taxonomy,
				'terms'    => $terms,
			];
		}
	}

	/**
	 * Prepare unique ID.
	 *
	 * Used to separate paginations behaviors.
	 */
	$page_number_parameter = plcl_hash( json_encode( ( array_filter( $atts ) ) ) );

	/**
	 * Get ads.
	 */
	$posts = get_posts( [
		'author'         => $atts[ 'user' ],
		'paged'          => plcl_get_request_parameter( $page_number_parameter, 1 ),
		'post_type'      => 'pl_classified',
		'post_status'    => 'publish',
		'posts_per_page' => $atts[ 'ads_per_page' ],
		'tax_query'      => $tax_query,
	] );

	/**
	 * Get pages total.
	 */
	if ( ! count( $posts ) ) {
		$page_count = 1;
	} else if ( -1 == $atts[ 'ads_per_page' ] ) {
		$page_count = 1;
	} else {
		$page_count = ceil( count( get_posts( [
			'author'         => $atts[ 'user' ],
			'post_type'      => 'pl_classified',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		] ) ) / $atts[ 'ads_per_page' ] );
	}

	/**
	 * Prepare output.
	 */
	$output = plcl_load_template( 'shortcodes/list.php', [
		'ads'  => $posts,
		'hash' => $page_number_parameter,
	], true );
	if ( $atts[ 'pagination' ] ) {
		$output .= plcl_load_template( 'helpers/pagination.php', [
			'hash' => $page_number_parameter,
			'page_count'            => $page_count,
			'page_number_parameter' => $page_number_parameter,
		], true );
	}

	/**
	 * Done. Return output.
	 */
	return $output;
} );
