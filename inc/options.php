<?php

add_action( 'cmb2_init', function() {

	$options = new_cmb2_box( [
		'id' => 'plcl_settings',
		'menu_title' => __( 'Settings' ),
		'object_types' => [ 'options-page' ],
		'option_key' => 'plcl_settings',
		'parent_slug' => 'edit.php?post_type=pl_classified',
		'save_button' => __( 'Save' ),
		'title' => __( 'Classifieds Settings' ),
	] );

	$options->add_field( [
		'id' => 'email_global_header',
		'name' => __( 'Email Global Header' ),
		'default' => __( 'Hello {name},' ),
		'type' => 'textarea_small',
	] );

	$options->add_field( [
		'id' => 'email_global_footer',
		'name' => __( 'Email Global Footer' ),
		'default' => __( "Thanks,\n{site}" ),
		'type' => 'textarea_small',
	] );

	/**
	 * Email: Ad Pending.
	 */
	$options->add_field( array(
		'name' => __( 'Email: Ad Pending' ),
		'type' => 'title',
		'id' => wp_generate_password( 12, false ),
	) );
	$options->add_field( array(
		'name' => 'Enabled',
		'id'   => 'email_ad_pending_enabled',
		'type' => 'checkbox',
	) );
	$options->add_field( [
		'id' => 'email_ad_pending_subject',
		'default' => __( '[{site}] Ad Pending' ),
		'name' => __( 'Subject' ),
		'type' => 'text',
	] );
	$options->add_field( [
		'id' => 'email_ad_pending_message',
		'name' => __( 'Subject' ),
		'default' => __( "We've received your ad \"{title}\". It will become visible once approved." ),
		'type' => 'textarea_small',
	] );

	/**
	 * Email: Ad Approved.
	 */
	$options->add_field( array(
		'name' => __( 'Email: Ad Approved' ),
		'type' => 'title',
		'id' => wp_generate_password( 12, false ),
	) );
	$options->add_field( array(
		'name' => 'Enabled',
		'id'   => 'email_ad_approved_enabled',
		'type' => 'checkbox',
	) );
	$options->add_field( [
		'id' => 'email_ad_approved_subject',
		'default' => __( '[{site}] Ad Approved' ),
		'name' => __( 'Subject' ),
		'type' => 'text',
	] );
	$options->add_field( [
		'id' => 'email_ad_approved_message',
		'name' => __( 'Subject' ),
		'default' => __( 'Congratulations! Your ad has been approved and published, you can view it here "{link}".' ),
		'type' => 'textarea_small',
	] );

	/**
	 * Email: Ad Approved.
	 */
	$options->add_field( array(
		'name' => __( 'Email: Ad Rejected' ),
		'type' => 'title',
		'id' => wp_generate_password( 12, false ),
	) );
	$options->add_field( array(
		'name' => 'Enabled',
		'id'   => 'email_ad_rejected_enabled',
		'type' => 'checkbox',
	) );
	$options->add_field( [
		'id' => 'email_ad_rejected_subject',
		'default' => __( '[{site}] Ad Rejected' ),
		'name' => __( 'Subject' ),
		'type' => 'text',
	] );
	$options->add_field( [
		'id' => 'email_ad_rejected_message',
		'name' => __( 'Subject' ),
		'default' => __( 'We apologize! Your ad "{title}" was rejected, .' ),
		'type' => 'textarea_small',
	] );

	add_action( 'admin_footer', function() {
		?><script>
			jQuery( function( $ ) {
				$('')
					.add( '.cmb2-id-email-ad-pending-enabled' )
					.add( '.cmb2-id-email-ad-approved-enabled' )
					.add( '.cmb2-id-email-ad-rejected-enabled' )
				.change( function() {
					var $this = $( this );
					var $next2 = $this.nextAll( ':lt(2)' );
					var $checked = ! ! $this.find( ':checked' ).length;
					if ( $checked ) {
						$next2.fadeIn();
					} else {
						$next2.fadeOut();
					}
				} ).change();
			} );
		</script><?php
	} );

}, PHP_INT_MIN );

function plcl_get_option( $option ) {
	$default = cmb2_get_metabox( 'plcl_settings' )->get_field( $option )->get_default();
	return cmb2_get_option( 'plcl_settings', $option, $default );
}