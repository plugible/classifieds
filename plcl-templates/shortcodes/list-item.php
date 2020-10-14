<?php

$gallery_number_of_items = $data[ 'gallery_number_of_items' ];
$ad                      = $data[ 'ad' ];
$permalink               = get_permalink( $ad );


?>

<tr>
	<td><a href="<?php echo $permalink; ?>"><?php echo $ad->post_title; ?></a></td>
	<td>
		<?php plcl_classified_gallery( $ad->ID, $gallery_number_of_items, [
			'size' => [
				48,
				48,
			],
			'enhanced' => false,
			'linked' => true,
		] ); ?>
	</td>
	<td>📍 <?php plcl_classified_terms( $ad->ID, 'pl_classified_location' ); ?></td>
	<td>🗓️ <?php printf( __( '%s ago' ), human_time_diff( get_the_time( 'U' ) ) ); ?></td>
</tr>