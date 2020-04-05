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
			$this->notify( 'classified_pending', $post_id );
		} );
		add_action( 'plcl_classified_approved', function( $post_id ) {
			$this->notify( 'classified_approved', $post_id );
		} );
		add_action( 'plcl_classified_rejected', function( $post_id ) {
			$this->notify( 'classified_rejected', $post_id );
		} );
		add_action( 'plcl_comment_approved', function( $comment_id ) {
			$this->notify( 'comment_approved', $comment_id, 'comment' );
		} );
	}

	private function notify( $which, $content_id, $type = 'classified' ) {
		$to = 'classified' === $type
			? get_post_meta( $content_id, 'email', true )
			: get_comment_author_email( $content_id )
		;
		$subject = plcl_interpolate( plcl_get_option( 'email_' . $which . '_subject' ), $content_id, $type );
		$message = plcl_interpolate( ''
			. plcl_get_option( 'email_global_header' )
			. "\n\n"
			. plcl_get_option( 'email_' . $which . '_message' )
			. "\n\n"
			. plcl_get_option( 'email_global_footer' )
		, $content_id, $type );
		wp_mail( $to, $subject, $message );
	}
}

new PLCLNotifierEmail;
