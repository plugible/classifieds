<?php

add_action( 'pre_get_posts', function( $query ) {

	if ( empty( $_REQUEST[ 'filters' ] ) ) {
		return $query;
	}

	$filters_taxonomies = [ 
		'locations' => 'pl_classified_location',
		'specifications' => 'pl_classified_specification',
	];

	/**
	 * Check this is the main query.
	 */
	if( ! $query->is_main_query() ) {
		return $query;
	}

	/**
	 * Check this is a `pl_classified_category` taxonomy page.
	 */
	if( ! is_tax( 'pl_classified_category' ) ) {
		return $query;
	}

	$tax_query = [];
	foreach ( $filters_taxonomies as $key => $taxonomy ) {
		/**
		 * Pass `$_REQUEST[ 'filters' ][ $key ]` through:
		 * - array_explode
		 * - array_unique
		 * - trim
		 */
		$terms = array_filter( array_map( 'trim', array_filter( array_unique( $_REQUEST[ 'filters' ][ $key ] ?? [] ) ) ) );
		if ( $terms ) {
			switch ( $key ) {
				case 'specifications':

					/**
					 * Get all.
					 */
					$all_specification_terms = get_terms( [
						'taxonomy' => 'pl_classified_specification',
						'hide_empty' => false,
					] );

					/**
					 * Tag enabled & add options.
					 */
					array_walk( $all_specification_terms, function( &$s ) use ( $terms ) {
						$s->enabled = in_array( $s->slug, $terms );
						$s->options = get_option( 'taxonomy_term_' . $s->term_id );
					} );

					/**
					 * Prepare grouped terms using enabled specs values.
					 */
					$specs_enabled = [];
					foreach ( $all_specification_terms as $s ) {
						if ( $s->enabled ) {
							$specs_enabled[ $s->options[ 'specification' ] ][] = $s->slug;
						}
					}

					/**
					 * Add terms to taxonomy query. 
					 */
					foreach ( $specs_enabled as $s ) {
						$tax_query[] = [
							'taxonomy' => $taxonomy,
							'field' => 'slug',
							'terms' => $s,
						];
					}
					break;
				default:
					$tax_query[] = [
						'taxonomy' => $taxonomy,
						'field' => 'slug',
						'terms' => $terms,
					];
					break;
			}

		}
	}

	$query->set( 'tax_query', $tax_query );

	/**
	 * Done.
	 */
	return $query;
}, PHP_INT_MIN );

/**
 * Inject the filters above the category title.
 */
add_action( 'get_the_archive_title', function() {
	if ( ! is_tax( 'pl_classified_category' ) ) {
		return;
	}

	global $post;
	global $wp_taxonomies;

	$category = get_queried_object();
	$filters_taxonomies = [
		'locations' => 'pl_classified_location',
		'specifications' => 'pl_classified_specification',
	];

	$filters = [];

	foreach ( $filters_taxonomies as $key => $taxonomy ) {

		$terms = get_terms( [
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
		] );
		if ( ! $terms ) {
			continue;
		}
		switch ( $key ) {
			case 'specifications':
				/**
				 * Add options.
				 */
				array_walk( $terms, function( &$s ) use ( $terms ) {
					$s->enabled = in_array( $s->slug, $terms );
					$s->options = get_option( 'taxonomy_term_' . $s->term_id );
				} );
				/**
				 * Filter out other categories.
				 */
				$terms = array_filter( $terms, function( $term ) use ( $category ) {
					return ( $term->options[ 'scope' ] ?? '' ) === $category->slug;
				} );
				/**
				 * Group
				 */
				$groups = [];
				foreach ( $terms as $term ) {
					$groups [ $term->options[ 'specification' ] ][] = $term;
				}
				/**
				 * Add all filter details.
				 */
				foreach ( $groups as $group => $group_specs ) {
					$options = [];
					foreach ( $group_specs as $spec ) {
						$options[ $spec->slug ] = $spec->options[ 'value' ];
					}
					$filters[] = array(
						'key'     => $key,
						'title'   => $group,
						'options' => $options,
					);
				}
				break;
			default:
				$options = [];
				foreach ( $terms as $term ) {
					$options[ $term->slug ] = $term->name;
				}
				$filters[] = array(
					'key'     => $key,
					'title'   => $wp_taxonomies[ $taxonomy ]->labels->all_items,
					'options' => $options,
				);
				break;
		}
	}

	add_action( 'loop_start', function() use ( $category, $filters ) {
		plcl_load_template( 'filters.php', array(
			'category' => $category,
			'filters'  => $filters,
		) );
	} );
} );

/**
 * Add options.
 */
add_filter( sprintf( '%s::enqueue-asset', wpmyads()->plugin_slug ), function( $args, $path ) {
	if ( 'dist/js/main.bundle.js' !== $path ) {
		return $args;
	}

	$args['l10n'] = array_merge_recursive(
		$args['l10n'] ?? array(),
		array(
			'filters' => array(
				'filtersElementId' => wpmyads()->plugin_slug . '-filters',
				'perLine'          => array(
					'(max-width: 575px)'                         => 1,
					'(min-width: 576px) and (max-width: 767px)'  => 2,
					'(min-width: 768px) and (max-width: 991px)'  => 3,
					'(min-width: 992px) and (max-width: 1199px)' => 4,
					'(min-width: 1200px)'                        => 6,
				),
			),
		)
	);
	return $args;
}, 10, 2 );

/**
 * Override 404.
 *
 * @link https://barn2.co.uk/create-fake-wordpress-post-fly/
 */
add_action( 'posts_results', function( $results, $query ) {

	/**
	 * Only when no results were found.
	 */
	if( $results ) {
		return $results;
	}

	/**
	 * Only classified category archives
	 */
	if( ! is_archive( 'pl_classified' ) ) {
		return $results;
	}

	$post_id = 0 - rand( 100000, 999999 );
	$post = new stdClass();
	$post->ID = $post_id;
	$post->post_author = 1;
	$post->post_date = current_time( 'mysql' );
	$post->post_date_gmt = current_time( 'mysql', 1 );
	$post->post_title = '';
	$post->post_content = '';
	$post->post_status = 'publish';
	$post->comment_status = 'closed';
	$post->ping_status = 'closed';
	$post->post_name = 'fake' . $post_id;
	$post->post_type = 'pl_classified';
	$post->filter = 'raw';
	wp_cache_add( $post_id, $post, 'posts' );

	return [ $post ];
}, 10, 2 );
