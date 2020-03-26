<?php

function plcl_mail( $which, $post_id ) {
	$to = get_post_meta( $post_id, 'email', true );
	$subject = plcl_get_option( 'email_' . $which . '_subject' );
	$message = plcl_get_option( 'email_' . $which . '_message' );
	wp_mail( $to, $subject, $message );
}

add_action( ' plcl_classified_inserted', function( $post_id ) {
	plcl_send_email( 'ad_pending', $post_id );
} );

add_action( 'cmb2_init', function() {
	plcl_mail( 'ad_pending', 705 );
}, PHP_INT_MAX );
