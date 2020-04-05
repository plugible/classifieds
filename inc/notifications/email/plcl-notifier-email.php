<?php

/**
 * PLCL email Notifications Handler
 */
class PLCLNotifierEmail {
	
	public function __construct() {
		$this->bind();
	}

	private function bind() {
		add_action( 'plcl_classified_pending', function( $post_id ) {
			$this->notify( 'ad_pending', $post_id );
		} );
		add_action( 'plcl_classified_approved', function( $post_id ) {
			$this->notify( 'ad_approved', $post_id );
		} );
		add_action( 'plcl_classified_rejected', function( $post_id ) {
			$this->notify( 'ad_rejected', $post_id );
		} );
	}

	private function notify( $which, $content_id, $type = 'ad' ) {
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
}

new PLCLNotifierEmail;
