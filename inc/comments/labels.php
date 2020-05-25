<?php

add_action( 'pl_classified_loaded', function() {
	/**
	 * Comment form title.
	 */
	add_filter( 'comment_form_defaults', function( $defaults ) {
		$defaults[ 'comment_notes_before' ] = '';
		$defaults[ 'title_reply' ] = __( 'Send a Private Message ', 'classifieds-by-plugible' );
		$defaults[ 'label_submit' ] = __( 'Send', 'classifieds-by-plugible' );
		return $defaults;
	}, 20 );

	/**
	 * Comment field label.
	 */
	$comment_label_cb = function( $translation, $text, $context, $domain ) {
		if ( 1
			&& $text === 'Comment'
			&& $context === 'noun'
		) {
			return _x( 'Message', 'noun', 'classifieds-by-plugible' );
		}
		return $translation;
	};
	add_action( 'comment_form_default_fields', function( $unused ) use( $comment_label_cb ) {
		add_filter( 'gettext_with_context', $comment_label_cb, 10, 4 );
		return $unused;
	} );
	add_action( 'comment_form_defaults', function( $unused ) use( $comment_label_cb ) {
		remove_filter( 'gettext_with_context', $comment_label_cb, 10, 4 );
		return $unused;
	}, 20 );

	/**
	 * Comments area title.
	 */
	add_action( 'comments_template', function( $_unused) {
		ob_start();
		return $_unused;
	} );
	add_action( 'wp_list_comments_args', function( $_unused) {
		echo preg_replace( '/<h2 class="comments-title">.*?<\/h2>/s', sprintf( '<h2 class="comments-title">%s</h2>', __( 'Private Discussion', 'classifieds-by-plugible' ) ), ob_get_clean() );
		return $_unused;
	} );
} );
