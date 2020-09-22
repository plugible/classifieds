<?php

/**
 * Register.
 */
add_filter( 'manage_edit-comments_columns', function( $columns ){
	return array_merge( $columns, [
		'plcl_links'    => __( 'Links', 'wpmyads' ),
	] );
} );

/**
 * Display.
 */
add_action( 'manage_comments_custom_column', function( $column, $comment_ID ) {
	if ( 'plcl_links' !== $column ) {
		return;
	}

	if ( 'pl_classified' !== get_post_type( get_comment( $comment_id )->comment_post_ID ) ) {
		return;
	}

	$link    = plcl_get_comment_link( $comment_ID );
	$link_op = plcl_get_comment_link( $comment_ID, true );

	?>
		<a href="<?php echo $link; ?>" title="<?php _e( 'Commentor', 'wpmyads' ); ?>"><span class="dashicons dashicons-megaphone"></span></a>
		<a href="<?php echo $link_op; ?>" title="<?php _e( 'OP', 'wpmyads' ); ?>"><span class="dashicons dashicons-admin-comments"></span></a>
	<?php
}, 10, 2 );
