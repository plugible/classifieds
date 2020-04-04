<?php

/**
 * Trigger classified emails with new submissin.
 */
add_action( 'plcl_classified_inserted', function( $post_id ) {
	$post = get_post( $post_id );
	do_action( 'plcl_classified_inserted_' . $post->post_status, $post_id );
} );

/**
 * Trigger classified emails with status change.
 */
add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
	if ( 'pl_classified' === $post->post_type
		&& 'publish' === $new_status
		&& 'draft' === $old_status
	) {
		plcl_mail( 'ad_approved', $post->ID );
	}
}, 10, 3 );

/**
 * Send notificatinos.
 */
add_action( 'plcl_classified_inserted_publish', function( $post_id ) {
	plcl_mail( 'ad_approved', $post_id );
} );
add_action( 'plcl_classified_inserted_draft', function( $post_id ) {
	plcl_mail( 'ad_pending', $post_id );
} );

/**
 * Send email.
 */
function plcl_mail( $which, $content_id, $type = 'ad' ) {
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
 * Interpolate replacement tags in email templates.
 */
function plcl_interpolate( $template, $content_id, $type = 'ad' ) {

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
