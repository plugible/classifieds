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
		if  ( file_exists( $file ) ) {
			include $file;
			break;
		}
	}

	return $return ? ob_get_clean() : print( ob_get_clean() );
}

function plcl_get_the_category() {
	if ( is_single() ) {
		$categories = get_the_terms( $GLOBALS[ 'post' ], 'pl_classified_category' );
		return is_array( $categories )
			? $categories[0]
			: false
		;
	} else {
		return get_queried_object();
	}
}

function plcl_get_the_category_link( $page = 1, $text = '', $include_filters = false ) {
	$category = plcl_get_the_category();
	if ( ! $category ) {
		return '';
	}

	$text = $text ? $text : $category->name;
	$link = plcl_get_the_category_url( $page, $include_filters );

	ob_start();
	?><a href="<?php echo $link ?>"><?php echo $text ?></a><?php
	return ob_get_clean();
}

function plcl_get_the_category_url( $page = 1, $include_filters =false ) {
	$category = plcl_get_the_category();
	if ( ! $category ) {
		return '';
	}

	$url = get_term_link( $category->slug, 'pl_classified_category' );
	$filters = $_REQUEST[ 'filters' ] ?? [];
	if ( $page > 1 ) {
		$url = trailingslashit( $url ) . sprintf( 'page/%d', $page );
	}
	if ( $include_filters ) {
		$url = $url . '?' . http_build_query( [ 'filters' => $filters ] );
	}
	return $url;
}

function plcl_the_category_link( $page = 1, $text = '', $include_filters = false ) {
	echo plcl_get_the_category_link( $page, $text, $include_filters );
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
					<a href="<?php echo $permalink; ?>"><?php wp_get_attachment_image( $image->ID, $args[ 'size' ] ) ?></a>
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
	$paths = [];

	/**
	 * Add homepage.
	 */
	$paths[] = [
		'text' => '⌂',
		'url' => home_url(),
	];

	/**
	 * Add category.
	 */
	if ( is_archive( 'pl_classified' ) ) {

		$paged   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$filters = $_REQUEST[ 'filters' ] ?? [];

		/**
		 * Page 1.
		 */
		$paths[] = [
			'text' => plcl_get_the_category()->name,
			'url'  => plcl_get_the_category_url(),
		];

		/**
		 * Search.
		 */
		if ( $filters ) {
			$paths[] = [
				'text' => __( 'Search' ),
				'url'  => plcl_get_the_category_url()
			];
		}

		/**
		 * Current page.
		 */
		if ( $paged > 1 ) {
			$paths[] = [
				'text' => sprintf( __( 'Page %s', 'classifieds-theme-by-plugible' ), number_format_i18n( $paged ) ),
				'url'  => plcl_get_the_category_url( $paged),
			];
		}
	}

	/**
	 * Add classified.
	 */
	if ( is_singular( 'pl_classified' ) ) {
		if ( plcl_get_the_category() ) {
			$paths[] = [
				'text' => plcl_get_the_category()->name,
				'url'  => plcl_get_the_category_url(),
			];
			$paths[] = [
				'text' => get_the_title(),
				'url'  => get_the_permalink(),
			];
		}
	}

	/**
	 * Display.
	 */
	echo $open;
	echo '<span class="text-muted">';
	for ( $i = 0; $i < count( $paths ) - 1; $i++ ) { 
		printf( '<a href="%1$s">%2$s</a> › ', $paths[ $i ][ 'url' ], $paths[ $i ][ 'text' ] );
	}
	echo $paths[ $i ][ 'text' ];
	echo '</span>';
	echo $close;
}

function plcl_get_breadcrumbs( $open, $close ) {
	ob_start();
	plcl_breadcrumbs( $open, $close );
	return ob_get_clean();
}

/**
 * Interpolates replacement tags in email templates.
 */
function plcl_interpolate( $template, $content_id, $type = 'classified' ) {
	$replacements = [
		'link' => plcl_get_link_with_hash( $content_id, $type ),
		'name' => 'meta:name',
		'site' => get_bloginfo( 'name' ),
		'title' => 'classified' === $type
			? get_the_title( $content_id )
			: get_the_title( get_comment( $content_id )->comment_post_ID )
		,
	];

	$result = $template;

	preg_replace_callback ( '/{([a-z_-]+)}/i' , function( $matches ) use ( $replacements, &$result, $content_id, $type ) {
		$tag = $matches[ 0 ];
		$replacement = $replacements[ $matches[1] ];
		if ( 'meta:' === substr( $replacement, 0, 5) ) {
			$replacement = 'classified' === $type
				? get_post_meta( $content_id, substr( $replacement, 5), true )
				: get_comment_meta( $content_id, substr( $replacement, 5), true )
			;
		}
		$result = str_replace( $tag, $replacement, $result );
	}, $template );

	return $result;
}

/**
 * Get link with hash.
 */
function plcl_get_link_with_hash( $content_id, $type = 'classified' ) {
	return 'classified' === $type
		? add_query_arg( 'classified_hash_shared', get_post_meta( $content_id, 'classified_hash_shared', true ), get_permalink( $content_id ) )
		: add_query_arg( 'comment_hash_shared', get_comment_meta( $content_id, 'comment_hash_shared', true ), get_comment_link( $content_id ) )
	;
}
