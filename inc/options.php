<?php

add_action( 'cmb2_init', function() {

	$options = new_cmb2_box( [
		'id' => plcl_get_box_id(),
		'menu_title' => __( 'Settings', 'classifieds-by-plugible' ),
		'object_types' => [ 'options-page' ],
		'option_key' => plcl_get_box_id(),
		'parent_slug' => 'edit.php?post_type=pl_classified',
		'save_button' => __( 'Save' ),
		'title' => __( 'Classifieds Settings' ),
	] );

	$options->add_field( [
		'id' => plcl_get_option_id( 'email_global_header' ),
		'name' => plcl_get_translation( __( 'Email Global Header', 'classfieds-by-plugible' ) ),
		'default' => plcl_get_translation( __( 'Hello {name},', 'classifieds-by-plugible' ) ),
		'type' => 'textarea_small',
	] );

	$options->add_field( [
		'id' => plcl_get_option_id( 'email_global_footer' ),
		'name' => plcl_get_translation( __( 'Email Global Footer', 'classfieds-by-plugible' ) ),
		'default' => plcl_get_translation( __( "Thanks,\n{site}", 'classifieds-by-plugible' ) ),
		'type' => 'textarea_small',
	] );

	/**
	 * Email: Ad Pending.
	 */
	$options->add_field( [
		'id' => wp_generate_password( 12, false ),
		'name' => plcl_get_translation( __( 'Email: Ad Pending', 'classfieds-by-plugible' ) ),
		'type' => 'title',
	] );
	$options->add_field( [
		'id' => plcl_get_option_id( 'email_ad_pending_enabled', false ),
		'name' => __( 'Enabled', 'classifieds-by-plugible' ),
		'type' => 'checkbox',
	] );
	$options->add_field( [
		'default' => plcl_get_translation( __( '[{site}] Ad Pending', 'classifieds-by-plugible' ) ),
		'id' => plcl_get_option_id( 'email_ad_pending_subject' ),
		'name' => plcl_get_translation( __( 'Subject', 'classfieds-by-plugible' ) ),
		'type' => 'text',
	] );
	$options->add_field( [
		'default' => plcl_get_translation( __( "We've received your ad \"{title}\". It will become visible once approved.", 'classifieds-by-plugible' ) ),
		'id' => plcl_get_option_id( 'email_ad_pending_message' ),
		'name' => plcl_get_translation( __( 'Body', 'classfieds-by-plugible' ) ),
		'type' => 'textarea_small',
	] );

	/**
	 * Email: Ad Approved.
	 */
	$options->add_field( [
		'id' => wp_generate_password( 12, false ),
		'name' => plcl_get_translation( __( 'Email: Ad Approved', 'classfieds-by-plugible' ) ),
		'type' => 'title',
	] );
	$options->add_field( [
		'id' => plcl_get_option_id( 'email_ad_approved_enabled', false ),
		'name' => __( 'Enabled', 'classifieds-by-plugible' ),
		'type' => 'checkbox',
	] );
	$options->add_field( [
		'default' => plcl_get_translation( __( '[{site}] Ad Approved', 'classifieds-by-plugible' ) ),
		'id' => plcl_get_option_id( 'email_ad_approved_subject' ),
		'name' => plcl_get_translation( __( 'Subject', 'classfieds-by-plugible' ) ),
		'type' => 'text',
	] );
	$options->add_field( [
		'default' => plcl_get_translation( __( "Congratulations! Your ad has been approved and published. You can view it here:\n- {link}", 'classifieds-by-plugible' ) ),
		'id' => plcl_get_option_id( 'email_ad_approved_message' ),
		'name' => plcl_get_translation( __( 'Body', 'classfieds-by-plugible' ) ),
		'type' => 'textarea_small',
	] );

	/**
	 * Email: Ad Approved.
	 */
	$options->add_field( [
		'id' => wp_generate_password( 12, false ),
		'name' => plcl_get_translation( __( 'Email: Ad Rejected', 'classfieds-by-plugible' ) ),
		'type' => 'title',
	] );
	$options->add_field( [
		'id' => plcl_get_option_id( 'email_ad_rejected_enabled', false ),
		'name' => __( 'Enabled', 'classifieds-by-plugible' ),
		'type' => 'checkbox',
	] );
	$options->add_field( [
		'default' => plcl_get_translation( __( '[{site}] Ad Rejected', 'classifieds-by-plugible' ) ),
		'id' => plcl_get_option_id( 'email_ad_rejected_subject' ),
		'name' => plcl_get_translation( __( 'Subject', 'classfieds-by-plugible' ) ),
		'type' => 'text',
	] );
	$options->add_field( [
		'default' => plcl_get_translation( __( 'We apologize! Your ad "{title}" was rejected.', 'classifieds-by-plugible' ) ),
		'id' => plcl_get_option_id( 'email_ad_rejected_message' ),
		'name' => plcl_get_translation( __( 'Body', 'classfieds-by-plugible' ) ),
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

function plcl_get_option( $option_id ) {
	$option_id = plcl_get_option_id( $option_id );
	$default = cmb2_get_metabox( plcl_get_box_id() )->get_field( $option_id )->get_default();
	return cmb2_get_option( plcl_get_box_id(), $option_id, $default );
}

function plcl_get_option_id( $option_id, $translatable = true ) {

	if ( ! $translatable ) {
		return $option_id;
	}

	/**
	 * Polylang Integration.
	 */
	if ( function_exists( 'pll_default_language' ) ) {
		if ( pll_current_language() && pll_default_language() !== pll_current_language() ) {
			$option_id .= '_' . pll_current_language();
		}
	}

	/**
	 * Done.
	 */
	return $option_id;
}

function plcl_get_translation( $text ) {

	/**
	 * Polylang integration.
	 */
	if ( function_exists( 'pll_current_language' ) ) {
		if ( pll_current_language() && pll_default_language() !== pll_current_language() ) {
			$lang_file = sprintf( '%1$s/%2$s/%3$s-%4$s.mo'
				, classifieds_by_plugible()->plugin_dir_path
				, 'lang'
				, classifieds_by_plugible()->plugin_slug
				, pll_current_language( 'locale' )
			);
			$translations = [];
			if ( file_exists( $lang_file ) ) {
				$mo = new MO();
				if ( $mo->import_from_file( $lang_file ) ) {
					$translations = $mo->entries;
					if ( ! empty( $translations[ $text ] ) ) {
						$text = $translations[ $text ]->translations[0];
					}
				}
			}
		}
	}

	/**
	 * Done.
	 */
	return $text;
}

function plcl_get_box_id() {
	$box_id = 'plcl_settings';
	return $box_id;
}