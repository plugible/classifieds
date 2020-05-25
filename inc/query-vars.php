<?php

add_filter( 'query_vars', function ( $vars ) {
	return array_merge( $vars, [
		/**
		 * Classified discussion.
		 */
		plcl_get_param( 'discussion' ),
		/**
		 * Classified of comment hash.
		 */
		plcl_get_param( 'hash' ),
	] );
} );
