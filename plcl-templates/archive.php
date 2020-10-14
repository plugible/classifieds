<?php global $post; ?>

<?php plcl_classified_gallery( $post->ID, 2, [
	'enhanced' => false,
	'linked'   => true,
] ); ?>

<?php

echo wpautop( 
	$post->post_excerpt 
		? $post->post_excerpt
		: wp_trim_words( $post->post_content, 11, false )
	. sprintf( '<a href="%1$s">&hellip;%2$s</a>'
		, get_permalink( $post )
		, __( 'Continue Reading' )
	)
);	

?>

<?php plcl_classified_specs( $post->ID, 2 ); ?>
