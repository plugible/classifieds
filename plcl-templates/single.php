<?php global $post; ?>

<?php plcl_classified_gallery( $post->ID ); ?>

<h2><?php _e( 'Description' ); ?></h2>
<?php echo wpautop( $post->post_content ); ?>

<h2><?php _e( 'Specs' ); ?></h2>
<?php plcl_classified_specs( $post->ID ); ?>
