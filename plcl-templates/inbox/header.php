<?php 

/**
 * Prepare current folder.
 */
$folder = $data[ 'folder' ]; 

/**
 * Prepare folder title.
 */
$folder_title = sprintf( '%1$s%2$s%3$s'
	, _( 'Folder' )
	, _( ': ' )
	, 'sent' === $folder ? _( 'Sent' ) : _( 'Inbox' )
);

/**
 * Show folder title.
 */
?>
	<h2><?php echo $folder_title; ?></h2>
<?php

/**
 * Display navigation.
 */
plcl_load_template( 'helpers/navigation.php', [
	'links' => $data[ 'links' ],
	'current_page_cb' => function( $data ) use ( $folder ) {
		return $folder === $data[ 'folder' ];
	},
] );
