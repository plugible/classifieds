<?php global $post; ?>

<?php plcl_classified_gallery( $post->ID, 1 ); ?>

<?php echo $post->post_content; ?>

<?php plcl_classified_specs( $post->ID ); ?>