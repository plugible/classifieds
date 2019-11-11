<?php

/**
 * Title: {{ Featured Image(s) }}
 * Post Type: pl_classified
 */
	
piklist( 'field', [
	'type' => 'file',
	'field' => '_thumbnail_id',
	'scope' => 'post_meta',
	'options' => [
		'title' => __( 'Set featured image(s)', 'classifieds-by-plugible' ),
		'button' => __( 'Set featured image(s)', 'classifieds-by-plugible' ),
	],
] );

/**
 * Localize Title
 */
add_action('piklist_pre_render_meta_box', function() {
	ob_start();
} );
add_action('piklist_post_render_meta_box', function() use( $output ) {
	echo str_replace( '{{ Featured Image(s) }}', __( 'Featured Image(s)', 'classifieds-by-plugible' ), ob_get_clean() );
} );
