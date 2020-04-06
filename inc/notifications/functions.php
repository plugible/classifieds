<?php
/**
 * Interpolates replacement tags in email templates.
 */
function plcl_interpolate( $template, $content_id, $type = 'classified' ) {
	$replacements = [
		'link' => plcl_get_link_with_hash( $content_id, $type ),
		'name' => 'meta:name',
		'site' => get_bloginfo( 'name' ),
		'title' => 'classified' === $type
			? get_the_title( $content_id )
			: get_the_title( get_comment( $content_id )->comment_post_ID )
		,
	];

	$result = $template;

	preg_replace_callback ( '/{([a-z_-]+)}/i' , function( $matches ) use ( $replacements, &$result, $content_id, $type ) {
		$tag = $matches[ 0 ];
		$replacement = $replacements[ $matches[1] ];
		if ( 'meta:' === substr( $replacement, 0, 5) ) {
			$replacement = 'classified' === $type
				? get_post_meta( $content_id, substr( $replacement, 5), true )
				: get_comment_meta( $content_id, substr( $replacement, 5), true )
			;
		}
		$result = str_replace( $tag, $replacement, $result );
	}, $template );

	return $result;
}

/**
 * Get link with hash.
 */
function plcl_get_link_with_hash( $content_id, $type = 'classified' ) {
	return 'classified' === $type
		? add_query_arg( 'classified_hash', get_post_meta( $content_id, 'comment_hash_shared', true ), get_permalink( $content_id ) )
		: add_query_arg( 'comment_hash_shared', get_comment_meta( $content_id, 'comment_hash_shared', true ), get_comment_link( $content_id ) )
	;
}
