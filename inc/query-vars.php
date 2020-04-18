<?php

add_filter( 'query_vars', function ( $vars ) {
	return array_merge( $vars, [
		plcl_get_hash_param(),
	] );
} );
