<?php global $post; ?>

<h2><?php _e( 'Media Gallery' ); ?></h2>

<?php
$args = array( 
	'post_type' => 'attachment', 
	'post_mime_type' => 'image',
	'numberposts' => -1, 
	'post_status' => null, 
	'post_parent' => $post->ID 
); 
$images = get_posts( $args );
?>

<div class='pl_classified_gallery'>
	<?php foreach ( $images as $image ) { ?>
		<a href="<?php echo wp_get_attachment_url( $image->ID ) ?>">
			<?php echo wp_get_attachment_image( $image->ID ) ?>
		</a>
	<?php } ?>
</div>

<style>
	.pl_classified_gallery a {
		display: block;
		margin: 5px;
	}
	html:not([dir=rtl]) .pl_classified_gallery a {
		float:left;
	}
	html[dir=rtl] .pl_classified_gallery a {
		float:right;
	}
</style>

<h2><?php _e( 'Details' ); ?></h2>

<?php echo $content; ?>

<h2><?php _e( 'Specs' ); ?></h2>

<?php $specifications = wp_get_post_terms( $post->ID, 'pl_classified_specification' ); ?>
<table class="table table-dark">
	<tbody>
		<?php foreach ( $specifications as $specification ) { ?>
			<?php $meta = get_option( 'taxonomy_term_' . $specification->term_id ); ?>
			<tr>
				<th scope="row"><?php echo $meta[ 'specification' ]; ?></th>
				<td><?php echo $meta[ 'value' ]; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
