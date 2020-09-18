<?php 

/**
 * Prepare ads.
 */
$ads = ! empty( $data[ 'ads' ] ) ? $data[ 'ads' ] : [];

/**
 * Prepare hash.
 */
$hash = ! empty( $data[ 'hash' ] ) ? $data[ 'hash' ] : '';

/**
 * Display Ads.
 */
if( ! $ads ) {
	plcl_load_template( 'shortcodes/list-empty.php' );
	return;
}
?>
<table class="<?php echo apply_filters( 'plcl_table_class', '' ); ?>" id="<?php echo $data[ 'hash' ] ?>">
	<?php
	foreach ( $ads as $ad ) {
		plcl_load_template( 'shortcodes/list-item.php', $ad );
	}
	?>
</table>
