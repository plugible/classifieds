<?php

add_action( ' plcl_classified_inserted', function( $post_id ) {
	plcl_mail( 'ad_pending', $post_id );
} );

add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
	if ( 'pl_classified' === $post->post_type
		&& 'publish' === $new_status
		&& 'draft' === $old_status
	) {
		plcl_mail( 'ad_approved', $post->ID );
	}
}, 10, 3 );

function plcl_mail( $which, $post_id ) {
	$to = get_post_meta( $post_id, 'email', true );
	$subject = plcl_interpolate( plcl_get_option( 'email_' . $which . '_subject' ), $post_id );
	$message = plcl_interpolate( ''
		. plcl_get_option( 'email_global_header' )
		. "\n\n"
		. plcl_get_option( 'email_' . $which . '_message' )
		. "\n\n"
		. plcl_get_option( 'email_global_footer' )
	, $post_id );
	wp_mail( $to, $subject, $message );
}

function plcl_interpolate( $template, $post_id ) {

	$post = get_post( $post_id );

	if ( ! $post ) {
		return $template;
	}

	$replacements = [
		'link' => get_permalink( $post_id ),
		'name' => 'meta:name',
		'site' => get_bloginfo( 'name' ),
		'title' => $post->post_title,
	];

	$result = $template;
	preg_replace_callback ( '/{([a-z_-]+)}/i' , function( $matches ) use ( $replacements, &$result, $post ) {
		$tag = $matches[ 0 ];
		$replacement = $replacements[ $matches[1] ];
		if ( 'meta:' === substr( $replacement, 0, 5) ) {
			$replacement = get_post_meta( $post->ID, substr( $replacement, 5), true );
		}
		$result = str_replace( $tag, $replacement, $result );
	} , $template );

	return $result;
}
