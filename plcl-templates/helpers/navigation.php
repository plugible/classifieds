<?php
$current_url = site_url( remove_query_arg( 'lol then rofl' ) );
?>

<ul role='navigation' class='<?php echo apply_filters( 'plcl_navigation_class', 'plcl-navigation' ); ?>'>
	<?php
	foreach( $data[ 'links' ] as $link ) {

		/**
		 * Detect if this link is the current one.
		 */
		$is_current = ! empty( $data[ 'current_page_cb' ] )
			? $data[ 'current_page_cb' ]( $link[ 'data' ] )
			: $current_url === $link[ 'url' ]
		;

		/**
		 * Add classes.
		 */
		$classes = [];
		$classes[] = apply_filters( 'plcl_navigation_item_class', 'plcl-navigation-item' );
		if ( $is_current ) {
			$classes[] = apply_filters( 'plcl_navigation_active_item_class', 'active' );
		}

		/**
		 * Output.
		 */
		?>
		<li class='<?php echo implode( ' ', $classes ); ?>'>
			<a href="<?php echo $link[ 'url' ]; ?>"><?php echo $link[ 'title' ]; ?></a>
		</li>
		<?php
	}
	?>
</ul>
