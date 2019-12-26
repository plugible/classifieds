<?php

function plcl_load_template( $template ) {
	static $include_paths = [];
	if ( ! $include_paths ) {
		$include_paths = [
			'stylesheet' => trailingslashit( get_stylesheet_directory() ) . 'plcl-templates' . DIRECTORY_SEPARATOR,
			'template' => trailingslashit( get_template_directory() ) . 'plcl-templates' . DIRECTORY_SEPARATOR,
			'local' => classifieds_by_plugible()->plugin_dir_path . 'plcl-templates' . DIRECTORY_SEPARATOR,
		];
	}
	foreach ( $include_paths as $include_path ) {
		$file = $include_path . $template;
		if  ( file_exists( $include_path . $template ) ) {
			include $file;
			return;
		}
	}
}

add_filter( 'the_content', function( $content ) {

	global $post;

	if ( 'pl_classified' !== $post->post_type ) {
		return $content;
	}

	if ( is_singular( 'pl_classified' ) ) {
		plcl_load_template( 'single.php' );
	} else if ( is_archive( 'pl_classified' ) ) {
		plcl_load_template( 'archive.php' );
	}
} );
