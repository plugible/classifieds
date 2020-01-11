<?php

$post = $data;
$permalink = get_permalink( $post );

?>

<tr>
	<td><a href="<?php echo $permalink; ?>"><?php echo $post->post_title; ?></a></td>
	<td>
		<?php plcl_classified_gallery( $post->ID, 3, [
			'size' => [ 55, 55 ],
			'enhanced' => false,
			'linked' => true,
		] ); ?>
	</td>
</tr>