<?php

/**
 * Ad.
 */
add_action( 'single_post_title', function( $title ) {
	if ( is_singular( 'pl_classified' ) ) {
		/**
		 * Add category.
		 */
		if ( plcl_get_the_category() ) {
			$title .= ' ‹ ' . plcl_get_the_category()->name;
		}
	}
	return $title;
} );

/**
 * Category.
 */
add_action( 'single_term_title', function( $title ) {
	if ( is_archive() && is_tax( 'pl_classified_category' ) ) {
		/**
		 * Add category ancestors.
		 */
		$category_ancestors  = get_ancestors( plcl_get_the_category()->term_id, 'pl_classified_category', 'taxonomy' );
		array_walk( $category_ancestors, function( $ancestor_id  ) use ( &$title ) {
			$title .= ' ‹ ' . get_term( $ancestor_id )->name ;
		} );
	}
	return $title;
} );
