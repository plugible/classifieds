<?php

add_action( 'pl_classified_loaded', function() {
	global $post;

	/**
	 * Only for OP.
	 */
	if ( get_current_user_id() != $post->post_author ) {
		return;
	}

	/**
	 * Prepare commenters and links.
	 */
	$comments = get_comments( [
		'status'  => 'approve',
		'post_id' => $post->ID,
	] );
	$current_discussion = plcl_decrypt( plcl_get_request_parameter( plcl_get_param( 'discussion' ) ) );
	$commenters = [];
	foreach ( $comments as $comment ) {
		/**
		 * Exclude OP.
		 */
		if ( $comment->user_id == $post->post_author ) {
			continue;
		}
		$commenters[ $comment->user_id ] = [
			'name'   => $comment->comment_author,
			'link'   => add_query_arg( plcl_get_param( 'discussion' ), plcl_encrypt( $comment->user_id ), get_comment_link( $comment ) ),
			'active' => plcl_decrypt( plcl_get_request_parameter( plcl_get_param( 'discussion' ) ) ) == $comment->user_id,
		];
	}
	if ( $commenters ) {
		$links = [];
		foreach ( $commenters as $commenter ) {
			$links[] = $commenter[ 'active' ]
				? $commenter[ 'name' ]
				: sprintf( '<a href="%1$s">%2$s</a>', $commenter[ 'link' ], $commenter[ 'name' ] )
			;
		};
		$links = sprintf( '<p>%1$s: %2$s</p>', __( 'Discussions', 'classifieds-by-plugible' ), implode( ' | ', $links ) );
	} else {
		return;
	}

	/**
	 * Add before comments form.
	 */
	add_action( 'comment_form_before', function() use( $links ) {
		echo $links;
	} );

	/**
	 * Add before comments.
	 */
	add_action( 'comment_form_before', function( $_unused ) {
		ob_start();
		return $_unused;
	} );
	add_action( 'wp_list_comments_args', function( $_unused ) use( $links )  {
		echo preg_replace( '/(<h2 class="comments-title">.*?<\/h2>)/s', "\$1 $links", ob_get_clean() );
		return $_unused;
	} );
} );
