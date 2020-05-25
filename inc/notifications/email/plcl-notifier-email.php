<?php

/**
 * PLCL email Notifications Handler
 */
class PLCLNotifierEmail {
	
	public function __construct() {
		$this->bind();
	}

	private function bind() {
		add_action( 'plcl_classified_created', function( $post_id ) {
			$this->notify( 'classified_created', $post_id );
		} );
		add_action( 'plcl_classified_pending', function( $post_id ) {
			$this->notify( 'classified_pending', $post_id );
		} );
		add_action( 'plcl_classified_approved', function( $post_id ) {
			$this->notify( 'classified_approved', $post_id );
		} );
		add_action( 'plcl_classified_rejected', function( $post_id ) {
			$this->notify( 'classified_rejected', $post_id );
		} );
		add_action( 'plcl_comment_created', function( $comment_id ) {
			$this->notify( 'comment_created', $comment_id );
		} );
		add_action( 'plcl_comment_pending', function( $comment_id ) {
			$this->notify( 'comment_pending', $comment_id );
		} );
		add_action( 'plcl_comment_approved', function( $comment_id ) {
			$this->notify( 'comment_approved', $comment_id );
		} );
		add_action( 'plcl_comment_received', function( $comment_id ) {
			$this->notify( 'comment_received', $comment_id );
		} );
		add_action( 'plcl_comment_rejected', function( $comment_id ) {
			$this->notify( 'comment_rejected', $comment_id );
		} );
	}
	private function notify( $which, $content_id ) {
		/**
		 * Prepare recepient email address.
		 */
		switch ( $which ) {
		case( 'classified_created' ) :
			$to = get_bloginfo( 'admin_email' );
			break;
		case( 'classified_approved' ) :
		case( 'classified_pending' ) :
		case( 'classified_rejected' ) :
			$to = get_the_author_meta( 'email', get_post_field( 'post_author', $content_id ) );
			break;
		case( 'comment_created' ) :
			$to = get_bloginfo( 'admin_email' );
			break;
		case( 'comment_received' ) :
			$to = get_the_author_meta( 'email', get_post_field( 'post_author', get_comment( $content_id )->comment_post_ID ) );
			break;
		case( 'comment_approved' ) :
		case( 'comment_pending' ) :
		case( 'comment_rejected' ) :
			$to = get_comment_author_email( $content_id );
			break;
		default:
			die( $xi);
		}

		/**
		 * Prepare message.
		 */
		$subject = plcl_interpolate( plcl_get_option( 'email_' . $which . '_subject' ), $content_id, $which );
		$message = plcl_interpolate( ''
			. plcl_get_option( 'email_global_header' )
			. "\n\n"
			. plcl_get_option( 'email_' . $which . '_message' )
			. "\n\n"
			. plcl_get_option( 'email_global_footer' )
		, $content_id, $which );

		/**
		 * Send email.
		 */
		wp_mail( $to, $subject, $message );
	}
}

new PLCLNotifierEmail;
