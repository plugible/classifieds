<?php

add_action( 'init', function() {	
	wpmyads()->enqueue_asset( 'dist/js/main.bundle.js', [
		'in_footer' => true,
		'object_name' => wpmyads()->plugin_slug,
		'deps' => [
			'jquery'
		],
		'l10n' => [
			'objectName' => wpmyads()->plugin_slug,
		],
	] );
} );
