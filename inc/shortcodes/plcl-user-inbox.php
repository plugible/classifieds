<?php

add_shortcode( 'plcl_user_inbox', function( $atts, $content, $shortcode_tag ) {

	/**
	 * Prepare navigation links.
	 */
	$base  = site_url( remove_query_arg( [
		'folder',
		plcl_get_param( 'page_number' ),
	] ) );
	$links = [
		[
			'title' => __( 'Inbox' ),
			'url'   => $base,
			'data'  => [
				'folder' => 'inbox',
			],
		],
		[
			'title' => __( 'Sent' ),
			'url'   => add_query_arg( 'folder', 'sent', $base ),
			'data'  => [
				'folder' => 'sent',
			],
		],
	];

	/**
	 * Prepare current folder.
	 */
	$folder = ! empty( $_GET[ 'folder' ] ) && 'sent' === $_GET[ 'folder' ] ? 'sent' : 'inbox';

	/**
	 * Prepare page number.
	 */
	$page_number = plcl_get_request_parameter( plcl_get_param( 'page_number' ), 1 );

	/**
	 * Prepare comments and comments count.
	 */
	if ( 'inbox' === $folder ) {
		$comments = get_comments( [
			'paged' =>$page_number,
			'number' => PLCL_INBOX_MESSAGES_PER_PAGE,
			'meta_query' => [
				[
					'key' => 'comment_to',
					'value' => get_current_user_id(),
				],
			],
		] );
		$comments_count = count( get_comments( [
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => 'comment_to',
					'value' => get_current_user_id(),
				],
			],
		] ) );
	} else if ( 'sent' === $folder ) {
		$comments = get_comments( [
			'paged' =>$page_number,
			'number' => PLCL_INBOX_MESSAGES_PER_PAGE,
			'meta_query' => [
				[
					'key' => 'comment_from',
					'value' => get_current_user_id(),
				],
			],
		] );
		$comments_count = count( get_comments( [
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => 'comment_from',
					'value' => get_current_user_id(),
				],
			],
		] ) );
	}

	/**
	 * Prepate page count.
	 */
	$page_count = ceil( $comments_count / PLCL_INBOX_MESSAGES_PER_PAGE );

	/**
	 * Build output.
	 */
	$output = ''
		. plcl_load_template( 'inbox/header.php', [
			'links' => $links,
			'folder' => $folder,
		], true )
		. plcl_load_template( 'inbox/content.php', [
			'comments' => $comments,
		], true )
		. plcl_load_template( 'inbox/footer.php', [
			'page_count' => $page_count,
		], true )
	;

	/**
	 * Done. Return output.
	 */
	return $output;
} );
