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

	$emails_options = [
		'classified_created' => [
			'title' => __( 'Classified Created', 'classfieds-by-plugible' ),
			'body'  => __( "The classified \"{title}\" was created. You can view it here:\n- {link}", 'classifieds-by-plugible' ),
		],
		'classified_pending' => [
			'title' => __( 'Classified Pending', 'classfieds-by-plugible' ),
			'body'  => __( "We've received your classified \"{title}\". It will become visible once approved.", 'classifieds-by-plugible' ),
		],
		'classified_approved' => [
			'title' => __( 'Classified Approved', 'classfieds-by-plugible' ),
			'body'  => __( "Your Classified \"{title}\" was approved. You can view it here:\n- {link}", 'classifieds-by-plugible' ),
		],
		'classified_rejected' => [
			'title' => __( 'Classified Rejected', 'classfieds-by-plugible' ),
			'body'  => __( 'Your classified "{title}" was rejected.', 'classifieds-by-plugible' ),
		],
		'comment_created' => [
			'title' => __( 'Comment Created', 'classfieds-by-plugible' ),
			'body'  => __( "A comment on \"{title}\" was created. You can view it here:\n- {link}", 'classifieds-by-plugible' ),
		],
		'comment_pending' => [
			'title' => __( 'Comment Pending', 'classfieds-by-plugible' ),
			'body'  => __( "We've received your comment on \"{title}\". It will become visible once approved.", 'classifieds-by-plugible' ),
		],
		'comment_received' => [
			'title' => __( 'Comment Received', 'classfieds-by-plugible' ),
			'body'  => __( "You received a comment on \"{title}\". You can view it here:\n- {link}", 'classifieds-by-plugible' ),
		],
		'comment_approved' => [
			'title' => __( 'Comment Approved', 'classfieds-by-plugible' ),
			'body'  => __( "Your comment on \"{title}\" was approved. You can view it here:\n- {link}", 'classifieds-by-plugible' ),
		],
		'comment_rejected' => [
			'title' => __( 'Comment Rejected', 'classfieds-by-plugible' ),
			'body'  => __( "Your comment on \"{title}\" was rejected.", 'classifieds-by-plugible' ),
		],
	];

	foreach ( $emails_options as $email => $o ) {
		$options->add_field( [
			'id' => wp_generate_password( 12, false ),
			'name' => plcl_get_translation( $o[ 'title' ] ),
			'type' => 'title',
		] );
		$options->add_field( [
			'id' => plcl_get_option_id( 'email_' . $email . '_enabled', false ),
			'name' => __( 'Enabled', 'classifieds-by-plugible' ),
			'type' => 'checkbox',
		] );
		$options->add_field( [
			'default' => '[{site}] ' . plcl_get_translation( $o[ 'title' ] ),
			'id' => plcl_get_option_id( 'email_' . $email . '_subject' ),
			'name' => plcl_get_translation( __( 'Subject', 'classfieds-by-plugible' ) ),
			'type' => 'text',
		] );
		$options->add_field( [
			'default' => plcl_get_translation( $o[ 'body' ] ),
			'id' => plcl_get_option_id( 'email_' . $email . '_message' ),
			'name' => plcl_get_translation( __( 'Body', 'classfieds-by-plugible' ) ),
			'type' => 'textarea_small',
		] );
	}

	add_action( 'admin_footer', function() use( $emails_options ) {
		$adds = '';
		foreach ( $emails_options as $email => $o ) {
			$class = sprintf( 'cmb2-id-email-%s-enabled', str_replace( '_' , '-', $email ) );
			$adds .= ".add( '.$class' )";
		}
		?>
		<script>
		jQuery( function( $ ) {
			$('')<?php echo $adds; ?>.change( function() {
				var $this = $( this );
				var $next2 = $this.nextAll( ':lt(2)' );
				var $checked = ! ! $this.find( ':checked' ).length;

				$checked && $next2.fadeIn() || $next2.fadeOut();
			} )
			.change();
		} );
		</script>
		<?php
	}, PHP_INT_MIN );
} );

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
