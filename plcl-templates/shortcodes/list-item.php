<?php

$post = $data;
$permalink = get_permalink( $post );

?>

<tr>
	<td><a href="<?php echo $permalink; ?>"><?php echo $post->post_title; ?></a></td>
	<td>
		<?php plcl_classified_gallery( $post->ID, 10, [
			'size' => [ 48, 48 ],
			'enhanced' => false,
			'linked' => true,
		] ); ?>
	</td>
	<td>📍 <?php plcl_classified_terms( $post->ID, 'pl_classified_location' ); ?></td>
	<td>🗓️ <?php printf( __( '%s ago' ), human_time_diff( get_the_time( 'U' ) ) ); ?></td>
</tr>