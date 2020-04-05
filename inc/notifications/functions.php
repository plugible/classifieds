<?php

/**
 * Sends email.
 */
function plcl_mail( $which, $content_id, $type = 'classified' ) {
	$to = get_post_meta( $content_id, 'email', true );
	$subject = plcl_interpolate( plcl_get_option( 'email_' . $which . '_subject' ), $content_id );
	$message = plcl_interpolate( ''
		. plcl_get_option( 'email_global_header' )
		. "\n\n"
		. plcl_get_option( 'email_' . $which . '_message' )
		. "\n\n"
		. plcl_get_option( 'email_global_footer' )
	, $content_id );
	wp_mail( $to, $subject, $message );
}

/**
 * Interpolates replacement tags in email templates.
 */
function plcl_interpolate( $template, $content_id, $type = 'classified' ) {

	$content = get_post( $content_id );

	if ( ! $content ) {
		return $template;
	}

	$replacements = [
		'link' => get_permalink( $content_id ),
		'name' => 'meta:name',
		'site' => get_bloginfo( 'name' ),
		'title' => $content->post_title,
	];

	$result = $template;
	preg_replace_callback ( '/{([a-z_-]+)}/i' , function( $matches ) use ( $replacements, &$result, $content ) {
		$tag = $matches[ 0 ];
		$replacement = $replacements[ $matches[1] ];
		if ( 'meta:' === substr( $replacement, 0, 5) ) {
			$replacement = get_post_meta( $content->ID, substr( $replacement, 5), true );
		}
		$result = str_replace( $tag, $replacement, $result );
	}, $template );

	return $result;
}
