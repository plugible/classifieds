<?php

global $post; 

$gallery_args = [
	'enhanced' => false,
	'linked'   => true,
	'size'     => [
		72,
		72,
	],
];

?>

<p>📍 <?php plcl_classified_terms( $post->ID, 'pl_classified_location' ); ?></p>

<?php plcl_classified_gallery( $post->ID, 2, $gallery_args ); ?>

<?php plcl_classified_excerpt(); ?>

<?php plcl_classified_specs( $post->ID, 2 ); ?>
