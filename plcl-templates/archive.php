<?php global $post; ?>

<?php plcl_classified_gallery( $post->ID ); ?>

<?php echo wpautop( $post->post_content ); ?>

<?php plcl_classified_specs( $post->ID ); ?>
