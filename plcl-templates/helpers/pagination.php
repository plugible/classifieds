<?php


if  ( 1 === $data[ 'total_pages' ] ) {
	return;
}

/**
 * Prepare base URL.
 */
global $wp;
$base = home_url( add_query_arg( [], $wp->request ) );

/**
 * Output navigation.
 */
?>
<ul role='navigation' class='<?php echo apply_filters( 'plcl_pagination_class', 'plcl-navigation' ); ?>'>
	<?php
	for( $page = 1; $page <= $data[ 'total_pages' ]; $page++ ) {
		$url = ( 1 === $page ) ? $base : add_query_arg( $data[ 'query_arg' ], $page, $base );
		$classes = [];
		$classes[] = apply_filters( 'plcl_pagination_item_class', 'plcl-navigation-item' );
		if ( $page === $data[ 'current_page' ] ) {
			$classes[] = apply_filters( 'plcl_pagination_active_item_class', 'active' );
		}
		?>
		<li class='<?php echo implode( ' ', $classes ); ?>'>
			<a href="<?php echo $url; ?>"><?php echo $page; ?></a>
		</li>
		<?php
	}
	?>
</ul>
