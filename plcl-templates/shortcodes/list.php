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
 * Prepare gallery's number of items.
 */
$gallery_number_of_items = $data[ 'gallery_number_of_items' ] ?? 2;

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
		plcl_load_template( 'shortcodes/list-item.php', [
			'ad'                      => $ad,
			'gallery_number_of_items' => $gallery_number_of_items,
		] );
	}
	?>
</table>
