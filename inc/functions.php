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

add_action( 'shutdown', function() {
	$id = 741;
	do_action( 'plcl_classified_pending', $id );
} );

/**
 * Interpolates replacement tags in email templates.
 */
function plcl_interpolate( $template, $content_id, $context ) {
	$replacements = [
		'site'             => get_bloginfo( 'name' ),
		'post_meta:foo'    => 'foo',
		'comment_meta:bar' => 'bar',
	];
	switch ( $context ) {
	case( 'classified_received' ) :
		$replacements[ 'name' ]  = get_bloginfo();
		$replacements[ 'title' ] = get_the_title( $content_id );
		$replacements[ 'link' ]  = add_query_arg( 's'
			, $replacements[ 'title' ]
			, admin_url( 'edit.php?post_type=pl_classified&post_status=draft' )
		);
		break;
	case( 'classified_approved' ) :
	case( 'classified_pending' ) :
	case( 'classified_rejected' ) :
		$replacements[ 'name' ]  = get_userdata( get_post_field( 'post_author', $content_id ) )->display_name;
		$replacements[ 'link' ]  = plcl_get_classified_link( $content_id );
		$replacements[ 'title' ] = get_the_title( $content_id );
		break;
	case( 'comment_received' ) :
		$replacements[ 'name' ]  = get_userdata( get_post_field( 'post_author', get_comment( $content_id )->comment_post_ID ) )->display_name;
		$replacements[ 'link' ]  = plcl_get_comment_link( $content_id, true );
		$replacements[ 'title' ] = get_the_title( get_comment( $content_id )->comment_post_ID );
		break;
	case( 'comment_approved' ) :
	case( 'comment_rejected' ) :
		$replacements[ 'name' ]  = get_comment_author( $content_id );
		$replacements[ 'link' ]  = plcl_get_comment_link( $content_id );
		$replacements[ 'title' ] = get_the_title( get_comment( $content_id )->comment_post_ID );
		break;
	default:
		break;
	}

	/**
	 * Interpolate.
	 */
	$result = $template;
	preg_replace_callback ( '/{([a-z:_-]+)}/i' , function( $matches ) use ( $replacements, &$result, $content_id, $type ) {
		$tag = $matches[ 0 ];
		$replacement = $replacements[ $matches[1] ];
		if ( 'post_meta:' === substr( $replacement, 0, 10 ) ) {
			$replacement = get_post_meta( $content_id, substr( $replacement, 10 ), true );
		}
		if ( 'comment_meta:' === substr( $replacement, 0, 13 ) ) {
			$replacement = get_comment_meta( $content_id, substr( $replacement, 13 ), true );
		}
		$result = str_replace( $tag, $replacement, $result );
	}, $template );

	/**
	 * Done.
	 */
	return $result;
}

function plcl_get_classified_link( $post_id ) {
	$hashes = [
		'classified_hash_unique' => get_post_meta( $post_id, 'classified_hash_unique', true ),
	];
	return add_query_arg( plcl_get_hash_param(), plcl_encrypt( json_encode( $hashes ) ), get_permalink( $post_id ) );
}

function plcl_get_comment_link( $comment_id, $op = false ) {
	if ( $op ) {
		$hashes = [
			'classified_hash_unique' => get_post_meta( get_comment( $comment_id )->comment_post_ID , 'classified_hash_unique', true ),
			'comment_hash_shared'    => get_comment_meta( $comment_id, 'comment_hash_shared', true ),
		];
	} else {
		$hashes = [
			'comment_hash_shared' => get_comment_meta( $comment_id, 'comment_hash_shared', true ),
			'comment_hash_unique' => get_comment_meta( $comment_id, 'comment_hash_unique', true ),
		];
	}
	return add_query_arg( plcl_get_hash_param(), plcl_encrypt( json_encode( $hashes ) ), get_comment_link( $comment_id ) );
}

/**
 * Encrypts a string.
 *
 * Uses openssl with the AES-256-CBC method with a fallback to `base64_encode`.
 *
 * @param boolean  $secure  Require secure encryption.
 */
function plcl_encrypt( $string, $require_encryption = false ) {
	return $string;
	$encryption_possible = function_exists( 'openssl_get_cipher_methods' ) && in_array( 'AES-256-CBC', openssl_get_cipher_methods() );
	if ( $require_encryption && ! $encryption_possible ) {
		die( ( string ) __LINE__ );
	}
	return $encryption_possible
		? base64_encode( openssl_encrypt( $string, 'AES-256-CBC', 'classifieds-by-plugible', 0, substr( AUTH_KEY, 0, 16 ) ) )
		: base64_encode( $string )
	;
}

/**
 * Decrypts a string.
 *
 * Uses openssl with the AES-256-CBC method with a fallback to `base64_decode`.
 *
 * @param boolean  $secure  Require secure decryption.
 */
function plcl_decrypt( $string, $require_encryption = false ) {
	$encryption_possible = function_exists( 'openssl_get_cipher_methods' ) && in_array( 'AES-256-CBC', openssl_get_cipher_methods() );
	if ( $require_encryption && ! $encryption_possible ) {
		die( ( string ) __LINE__ );
	}
	return $encryption_possible
		? openssl_decrypt( base64_decode( $string ), 'AES-256-CBC', 'classifieds-by-plugible', 0, substr( AUTH_KEY, 0, 16 ) )
		: base64_decode( $string )
	;
}

/**
 * Creates a user.
 *
 * - Username is u{N} where N is a random number from 1 to twice the number or website users
 */
function plcl_create_user( $email ) {
	$users_count = ( new \WP_User_Query( array( 'blog' => 0 ) ) )->get_total();
	do {
		$username = rand( 1, $users_count * 2 );
	} while ( username_exists( $username ) );

	return wp_insert_user( [
		'user_email' => $email,
		'user_login' => $username,
		'user_pass'  => wp_generate_password(),
	] );
}

/**
 * Gets query string key.
 */
function plcl_get_hash_param() {
	return trim( plcl_encrypt( 'hash' ), '=' );
}

/**
 * Hash a string.
 * 
 * - Length is 8
 */
function plcl_hash( $string, $with_salt = false ) {
	if ( $with_salt ) {
		$string .= wp_generate_password();
	}
	return substr( hash( 'sha256', $string ), 0, 8 );
}
