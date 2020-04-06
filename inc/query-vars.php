<?php

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'comment_hash_shared';
	$vars[] = 'comment_hash_unique';
	return $vars;
} );
