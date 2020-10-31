<?php

global $post;
$phone = apply_filters( 'plcl_phone', get_post_meta( $post->ID, 'phone', true ) );

?>

<?php plcl_classified_gallery( $post->ID ); ?>

<?php echo wpautop( $post->post_content ); ?>

<h2><?php _e( 'Specifications' ); ?></h2>

<?php plcl_classified_specs( $post->ID ); ?>

<h2><?php _e( 'Contact Information' ); ?></h2>

<p>
	📍 <?php plcl_classified_terms( $post->ID, 'pl_classified_location' ); ?>
	<?php printf( '☎ %s', $phone ); ?>
</p>

<p><?php plcl_breadcrumbs(); ?></p>
