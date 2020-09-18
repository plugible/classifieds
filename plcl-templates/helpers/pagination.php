<?php

/**
 * Hide pagination when there is only one page.
 */
if( 1 == $data[ 'page_count' ] ) {
	return;
}

/**
 * Prepate the page number URL parameter.
 */
$page_number_parameter = ! empty( $data[ 'page_number_parameter' ] )
	? $data[ 'page_number_parameter' ]
	: 'page_number'
;

/**
 * Prepare the base URL.
 */
$base_URL = site_url( remove_query_arg( $page_number_parameter ) );
if  ( ! array_key_exists( 'query', parse_url( $base_URL ) ) ) {
	$base_URL = trailingslashit( $base_URL );
}

/**
 * Get the current page.
 */
$current_page_number = plcl_get_request_parameter( $page_number_parameter, 1 );

/**
 * Output pagination.
 */
?>
<ul role='navigation' class='<?php echo apply_filters( 'plcl_pagination_class', 'plcl-pagination' ); ?>'>
	<?php
	for( $i = 1; $i <= $data[ 'page_count' ]; $i++ ) {

		/**
		 * Prepare the URL.
		 */
		$url = ( 1 === $i )
			? $base_URL
			: add_query_arg( $page_number_parameter, $i, $base_URL )
		;
		if( $data[ 'hash' ] ) {
			$url = explode( '#', $url )[ 0 ] . '#' . $data[ 'hash' ];
		}

		/**
		 * Prepare classes.
		 */
		$classes = [];
		$classes[] = apply_filters( 'plcl_pagination_item_class', 'plcl-pagination-item' );
		if ( $i == $current_page_number ) {
			$classes[] = apply_filters( 'plcl_pagination_active_item_class', 'active' );
		}

		/**
		 * Output.
		 */
		?>
		<li class='<?php echo implode( ' ', $classes ); ?>'>
			<a href="<?php echo $url; ?>"><?php echo $i; ?></a>
		</li>
		<?php
	}
	?>
</ul>
