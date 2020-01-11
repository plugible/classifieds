<?php

function plcl_load_template( $template, $data = [], $return = false ) {

	static $include_paths = [];

	if ( ! $include_paths ) {
		$include_paths = [
			'stylesheet' => trailingslashit( get_stylesheet_directory() ) . 'plcl-templates' . DIRECTORY_SEPARATOR,
			'template' => trailingslashit( get_template_directory() ) . 'plcl-templates' . DIRECTORY_SEPARATOR,
			'local' => classifieds_by_plugible()->plugin_dir_path . 'plcl-templates' . DIRECTORY_SEPARATOR,
		];
	}

	ob_start();
	foreach ( $include_paths as $include_path ) {
		$file = $include_path . $template;
		if  ( file_exists( $include_path . $template ) ) {
			include $file;
			break;
		}
	}

	return $return ? ob_get_clean() : print( ob_get_clean() );
}

function plcl_get_the_category_link() {
	global $post;
	$category = get_the_terms( $post, 'pl_classified_category' )[0];
	ob_start();
	?><a href="<?php echo get_term_link( $category->slug, 'pl_classified_category' ); ?>"><?php echo $category->name; ?></a><?php
	return ob_get_clean();
}

function plcl_the_category_link() {
	echo plcl_get_the_category_link();
}

function plcl_classified_image_count( $post_id ) {
	return count( get_posts( [ 
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'numberposts' => -1,
		'post_status' => null,
		'post_parent' => $post_id,
	] ) );
}

function plcl_classified_gallery( $post_id, $number = -1, $args = [] ) {

	$args = array_merge( [
		'enhanced' => true,
		'linked' => false,
		'size' => [
			120,
			120,
		],
	], $args );

	$cssonce = false;

	$images = get_posts( [ 
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'numberposts' => $number,
		'post_status' => null,
		'post_parent' => $post_id,
	] );

	if ( ! $images ) {
		return;
	}

	$permalink = get_permalink( $post_id );

	?>
	<div class="pl_classified_gallery <?php echo $args[ 'enhanced' ] ? 'pl_classified_gallery_enhanced' : ''; ?>">
		<?php foreach ( $images as $image ) { ?>
			<div data-src="<?php echo wp_get_attachment_url( $image->ID ) ?>">

				<?php if ( $args[ 'linked' ] ) { ?>
					<a href="<?php echo $permalink; ?>"><?php echo wp_get_attachment_image( $image->ID, $args[ 'size' ] ) ?></a>
				<?php } ?>

				<?php echo wp_get_attachment_image( $image->ID, $args[ 'size' ] ) ?>

				<?php if ( $args[ 'linked' ] ) { ?>
					</a>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
	<?php

	/**
	 * Add CSS once. 
	 */
	if ( ! $cssonce ) {
		?><style>
			.pl_classified_gallery > div {
				margin: 5px;
			}
			html:not([dir=rtl]) .pl_classified_gallery > div {
				float:left;
			}
			html[dir=rtl] .pl_classified_gallery > div {
				float:right;
			}
			.pl_classified_gallery_enhanced > div {
				cursor: pointer;
			}
		</style><?php
	}
	$cssonce = true;
}

function plcl_classified_specs( $post_id ) {
	$specifications = wp_get_post_terms( $post_id, 'pl_classified_specification' );
	if ( ! $specifications ) {
		return;
	}

	?>
	<table class="table">
		<tbody>
			<?php foreach ( $specifications as $specification ) {
				$meta = get_option( 'taxonomy_term_' . $specification->term_id );
				if ( false
					|| ! array_key_exists( 'specification', $meta  )
					|| ! array_key_exists( 'value', $meta  )
				){
					continue;
				}
				?>
				<tr>
					<th scope="row"><?php echo $meta[ 'specification' ]; ?></th>
					<td><?php echo $meta[ 'value' ]; ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php
}

function plcl_classified_terms( $post_id, $taxonomy, $format = 'linear' ) {
	$terms = wp_get_post_terms( $post_id, $taxonomy );
	if ( ! $terms ) {
		return;
	}

	switch ( $format ) {
		case 'table':
			// Nothing.
			break;
		default:
			$tmp = [];
			foreach ( $terms as $term ) {
				$tmp[] = $term->name;
			}
			echo implode( __( ', ', 'classifieds-theme-by-plugible' ),  $tmp );
			break;
	}
}

function plcl_breadcrumbs( $open, $close ) {
	if ( is_archive( 'pl_classified' ) ) {
		echo $open;
		?>
		<span class="text-muted">
			<a href="<?php echo home_url(); ?>" class="text-muted">⌂</a> ›
		</span>
		<?php
		echo $close;
	} else if ( is_singular( 'pl_classified' ) ) {
		echo $open;
		?>
		<span class="text-muted">
			<a href="<?php echo home_url(); ?>" class="text-muted">⌂</a> › 
			<a href="#" class="text-muted"><?php plcl_the_category_link() ?></a> › 
			<a href="#" class="text-muted">Page 1</a> › 
		</span>
		<?php
		echo $close;
	}
}

function plcl_get_breadcrumbs( $open, $close ) {
	ob_start();
	plcl_breadcrumbs( $open, $close );
	return ob_get_clean();
}
