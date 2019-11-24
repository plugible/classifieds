<?php

add_filter( 'the_content', function( $content ) {

	global $post;

	if ( 'pl_classified' !== $post->post_type ) {
		return $content;
	}

	$templates_path = classifieds_by_plugible()->plugin_dir_path . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
	if ( is_single() ) {
		include  $templates_path . 'single.php';
	}

} );