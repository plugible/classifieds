<?php

add_filter( 'bootswatch_variables_overrides', function( $overrides, $theme ) {

	$font_size_base = '18px';
	$font_size_small = '15px';

	$overrides[ '@font-size-base' ] = $font_size_base;
	$overrides[ '@font-size-small' ] = $font_size_small;
	$overrides[ '@line-height-base' ] = '1.7';

	return $overrides;
}, 10, 2 );
