<?php

function plcl_load_template( $template, $data = [], $return = false ) {

	static $include_paths = [];

	if ( ! $include_paths ) {
		$include_paths = [
			'stylesheet' => trailingslashit( get_stylesheet_directory() ) . 'plcl-templates' . DIRECTORY_SEPARATOR,
			'template' => trailingslashit( get_template_directory() ) . 'plcl-templates' . DIRECTORY_SEPARATOR,
			'local' => wpmyads()->plugin_dir_path . 'plcl-templates' . DIRECTORY_SEPARATOR,
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
	$output = ob_get_clean();

	/**
	 * Filter.
	 */
	$output = apply_filters( 'plcl_template_output', $output, $template, $data );

	/**
	 * Done.
	 */
	if ( $return ) {
		return $output;
	} else {
		echo $output;
	}
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

function plcl_classified_gallery( int $post_id, int $number = -1, array $args = [] ) {

	if ( $number < 1 ) {
		$number = -1;
	}

	/**
	 * Merge $args with the defaults.
	 */
	$args = array_merge( [
		'enhanced' => true,
		'linked' => false,
		'size' => [
			150,
			150,
		],
	], $args );

	$images                 = [];
	$all_images             = ( array ) get_post_meta( $post_id, 'images', true );
	$first_images           = ( $number >= 1 ) ? array_slice( ( array ) $all_images, 0, $number, true ) : $all_images;
	$remaining_images_count = 0;
	foreach ( $first_images as $id => $url ) {
		$images[] = get_post( $id );
	}
	if ( ! $images ) {
		return;
	}
	if ( $number >= 1 && count( $all_images ) > $number ) {
		$remaining_images_count = count( $all_images ) - $number;
	}

	$classes = [ sprintf( '%s_gallery', wpmyads()->plugin_slug ) ];
	if ( $args[ 'enhanced' ] ) {
		$classes[] = sprintf( '%s_gallery_enhanced', wpmyads()->plugin_slug );
	}

	?>
	<div class="<?php echo implode( ' ', $classes ); ?>" data-remaining-images-count="<?php echo $remaining_images_count; ?>">
		<?php foreach ( $images as $image ) { ?>
			<div data-src="<?php echo wp_get_attachment_url( $image->ID ) ?>">

				<?php if ( $args[ 'linked' ] ) { ?>
					<a href="<?php echo get_permalink( $post_id ); ?>"><?php wp_get_attachment_image( $image->ID, $args[ 'size' ] ) ?>
				<?php } ?>

				<?php echo wp_get_attachment_image( $image->ID, $args[ 'size' ] ) ?>

				<?php if ( $args[ 'linked' ] ) { ?>
					</a>
				<?php } ?>

			</div>
		<?php } ?>
	</div>
	<?php
}

/**
 * Show classified excerpt.
 *
 * Show excerpt of first `$length words`.
 *
 * @param  int  $post_id  Post ID. If null, the global $post object will be used.
 * @param  int  $length   The excerpt length.
 */
function plcl_classified_excerpt( $post_id = null , $length = 32 ) {

	/**
	 * Get the post.
	 */
	if ( ! $post_id ) {
		global $post;
	} else {
		$post = get_post( $post_id );
	}
	if ( ! $post ) {
		return;
	}

	/**
	 * Prepare excerpt.
	 */
	$excerpt = wpautop( 
		$post->post_excerpt 
			? $post->post_excerpt
			: wp_trim_words( $post->post_content, $length, false )
		. sprintf( '<a href="%1$s">&hellip;%2$s</a>'
			, get_permalink( $post )
			, __( 'Continue Reading' )
		)
	);

	/**
	 * Done!
	 */
	echo $excerpt;
}

/**
 * Show ad specifications.
 *
 * @param int $post_id The ad post id.
 * @param int $number  The number of rows to show.
 */
function plcl_classified_specs( $post_id, $number = PHP_INT_MAX ) {


	if ( $number < 1 ) {
		$number = PHP_INT_MAX;
	}

	$specifications = wp_get_post_terms( $post_id, 'pl_classified_specification', [ 'orderby' => 'name'] );
	if ( ! $specifications ) {
		return;
	}

	?>
	<table class="table">
		<tbody>
			<?php
			$i         = 0;
			$remaining = 0;
			foreach ( $specifications as $specification ) {

				$i++;

				/**
				 * Only show `$number` first rows.
				 */
				if ( $number < $i ) {
					$remaining++;
					continue;
				}

				/**
				 * Get specification
				 */
				$meta = get_option( 'taxonomy_term_' . $specification->term_id );
				if ( false
					|| ! array_key_exists( 'specification', $meta  )
					|| ! array_key_exists( 'value', $meta  )
				) {
					continue;
				}

				/**
				 * Display specification.
				 */
				?>
				<tr>
					<th scope="row"><?php echo $meta[ 'specification' ]; ?></th>
					<td><?php echo $meta[ 'value' ]; ?></td>
				</tr>
				<?php
			}

			/**
			 * Display "Show more".
			 */
			if ( $remaining ) {
				?>
				<tr>
					<td colspan="2"><a href="<?php the_permalink( $post_id ) ?>"><?php echo _nx(
						sprintf( '+%d more specification', $remaining ),
						sprintf( '+%d more specifications', $remaining ),
						$remaining,
						'x'
					);?></a></td>
				</tr>
				<?php
			}

			?>
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
			$first_images = [];
			foreach ( $terms as $term ) {
				$first_images[] = $term->name;
			}
			echo implode( __( ', ', 'classifieds-theme-by-plugible' ),  $first_images );
			break;
	}
}

function plcl_breadcrumbs( $open = '', $close = '' ) {
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
		 * Ancestors.
		 */
		$category_ancestors  = get_ancestors( plcl_get_the_category()->term_id, 'pl_classified_category', 'taxonomy' );
		array_walk( $category_ancestors, function( $ancestor_id  ) use ( &$paths ) {
			$paths[] = [
				'text' => get_term( $ancestor_id )->name,
				'url'  => get_term_link( $ancestor_id, 'pl_classified_category' ),
			];
		} );

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

		/**
		 * Categories.
		 */
		if ( plcl_get_the_category() ) {
			/**
			 * Category Ancestors.
			 */
			$category_ancestors  = get_ancestors( plcl_get_the_category()->term_id, 'pl_classified_category', 'taxonomy' );
			array_walk( $category_ancestors, function( $ancestor_id  ) use ( &$paths ) {
				$paths[] = [
					'text' => get_term( $ancestor_id )->name,
					'url'  => get_term_link( $ancestor_id, 'pl_classified_category' ),
				];
			} );

			/**
			 * Category.
			 */
			$paths[] = [
				'text' => plcl_get_the_category()->name,
				'url'  => plcl_get_the_category_url(),
			];
		}

		/**
		 * Ad.
		 */
		$paths[] = [
			'text' => get_the_title(),
			'url'  => get_the_permalink(),
		];
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

function wpmyads_get_breadcrumbs( $open = '', $close = '' ) {
	ob_start();
	plcl_breadcrumbs( $open, $close );
	return ob_get_clean();
}

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
	case( 'classified_created' ) :
		$replacements[ 'name' ]  = get_bloginfo();
		$replacements[ 'link' ]  = add_query_arg( 's'
			, $replacements[ 'title' ]
			, admin_url( 'edit.php?post_type=pl_classified&post_status=draft' )
		);
		$replacements[ 'title' ] = get_the_title( $content_id );
		break;
	case( 'classified_approved' ) :
	case( 'classified_pending' ) :
	case( 'classified_rejected' ) :
		$replacements[ 'name' ]  = get_userdata( get_post_field( 'post_author', $content_id ) )->display_name;
		$replacements[ 'link' ]  = plcl_get_classified_link( $content_id );
		$replacements[ 'title' ] = get_the_title( $content_id );
		break;
	case( 'comment_created' ) :
		$replacements[ 'name' ]  = get_bloginfo();
		$replacements[ 'link' ]  = admin_url( 'edit-comments.php?post_type=pl_classified' );
		$replacements[ 'title' ] = get_the_title( get_comment( $content_id )->comment_post_ID );
		break;
	case( 'comment_pending' ) :
		$replacements[ 'name' ]  = get_comment_author( $content_id );
		$replacements[ 'link' ]  = '';
		$replacements[ 'title' ] = get_the_title( get_comment( $content_id )->comment_post_ID );
		break;
	case( 'comment_approved' ) :
	case( 'comment_rejected' ) :
		$replacements[ 'name' ]  = get_comment_author( $content_id );
		$replacements[ 'link' ]  = plcl_get_comment_link( $content_id );
		$replacements[ 'title' ] = get_the_title( get_comment( $content_id )->comment_post_ID );
		break;
	case( 'comment_received' ) :
		$replacements[ 'name' ]  = get_userdata( get_post_field( 'post_author', get_comment( $content_id )->comment_post_ID ) )->display_name;
		$replacements[ 'link' ]  = plcl_get_comment_link( $content_id, true );
		$replacements[ 'title' ] = get_the_title( get_comment( $content_id )->comment_post_ID );
		break;
	default:
		break;
	}

	/**
	 * Interpolate.
	 */
	$result = $template;
	preg_replace_callback ( '/{([a-z:_-]+)}/i' , function( $matches ) use ( $replacements, &$result, $content_id ) {
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
	return add_query_arg( plcl_get_param( 'hash' ), plcl_encrypt( json_encode( $hashes ) ), get_permalink( $post_id ) );
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
	return add_query_arg( plcl_get_param( 'hash' ), plcl_encrypt( json_encode( $hashes ) ), get_comment_link( $comment_id ) );
}

/**
 * Encrypts a string.
 *
 * Uses openssl with the AES-256-CBC method with a fallback to `base64_encode`.
 *
 * @param boolean  $secure  Require secure encryption.
 */
function plcl_encrypt( $string, $require_encryption = false ) {
	$encryption_possible = function_exists( 'openssl_get_cipher_methods' ) && in_array( 'aes-256-cbc', openssl_get_cipher_methods() );
	if ( $require_encryption && ! $encryption_possible ) {
		die( ( string ) __LINE__ );
	}
	return $encryption_possible
		? base64_encode( openssl_encrypt( $string, 'aes-256-cbc', SECURE_AUTH_KEY, 0, substr( AUTH_KEY, 0, 16 ) ) )
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
	$encryption_possible = function_exists( 'openssl_get_cipher_methods' ) && in_array( 'aes-256-cbc', openssl_get_cipher_methods() );
	if ( $require_encryption && ! $encryption_possible ) {
		die( ( string ) __LINE__ );
	}
	return $encryption_possible
		? openssl_decrypt( base64_decode( $string ), 'aes-256-cbc', SECURE_AUTH_KEY, 0, substr( AUTH_KEY, 0, 16 ) )
		: base64_decode( $string )
	;
}

/**
 * Creates a user.
 *
 * - Username is u{N} where N is a random number from 1 to twice the number or website users
 */
function plcl_create_user( $email, $args = [] ) {
	$defaults = [
		'first_name' => __( 'Unnamed', 'wpmyads' ),
		'username_mode' => 'string',
	];

	$args = array_merge( $defaults, $args );

	switch ( $args[ 'username_mode' ] ) {
	case 'numeric':
		$users_count = ( new \WP_User_Query( array( 'blog' => 0 ) ) )->get_total();
		do {
			$username = rand( 1, $users_count * 2 );
		} while ( username_exists( $username ) );
		break;		
	default:
		do {
			$username = 'u-' . strtolower( wp_generate_password( 6, false ) );
		} while ( username_exists( $username ) );
		break;
	}

	$user_id = wp_insert_user( [
		'user_email' => $email,
		'user_login' => $username,
		'user_pass'  => wp_generate_password(),
		'first_name' => $args[ 'first_name' ],
	] );

	/**
	 * Attach execting comments to user. 
	 */
	if ( ! is_wp_error( $user_id ) ) {
		$comments = get_comments( [
			'author_email' => $email,
			'include_unapproved' => 1,
		] );
		foreach ( $comments as $comment ) {
			wp_update_comment( [
				'comment_ID' => $comment->comment_ID,
				'user_id' => $user_id,
			] );
		}
	}

	return $user_id;
}

/**
 * Get user by email. Create if request.
 */
function plcl_get_user( $email, $create_if_not_exists = true, $args = [] ) {
	if ( email_exists( $email ) ) {
		return get_user_by( 'email', $email );
	} else if ( $create_if_not_exists ) {
		return get_user_by( 'id', plcl_create_user( $email, $args ) );
	} else {
		return false;
	}
}

/**
 * Gets query string key.
 */
function plcl_get_param( $param ) {
	return trim( plcl_encrypt( $param ), '=' );
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

function plcl_auth( $user_id, $destination = null ) {
	if ( ! $destination ) {
		$destination = site_url();
	}
	if ( $user_id !== get_current_user_id() ) {
		wp_set_auth_cookie( $user_id );
		wp_safe_redirect( $destination );
		exit;
	}
}

function plcl_die() {
	die( __( 'Application Error: ', 'wpmyads' ) . plcl_encrypt( json_encode( [
		'file' => debug_backtrace()[0][ 'file' ],
		'line' => debug_backtrace()[0][ 'line' ],
	] ) ) );
}

/**
 * Gets discussion participants user IDs.
 * @param   integer  $discussion_id  Discussion ID (usually matches the first comment author ID
 * @param   array    $args           Arguments:
 *                                   - `( array ) excludes`: ids to exclude. This takes precedence over includes. 
 *                                   - `( array ) includes`: ids to include.
 * @return  array                    Discussion participants user IDs.
 */
function plcl_get_discussion_participants( $discussion_id, $args = [] ) {
	$participants = $args[ 'includes' ] ?? [];
	$comments = get_comments( [
		'meta_key' => 'comment_discussion',
		'meta_value' => $discussion_id,
	] );
	foreach ( $comments as $comment ) {
		$participants[] = $comment->user_id;
	}

	/**
	 * Remove duplicates, excludes and reset indexes.
	 */
	$participants = array_values( array_diff( array_unique( $participants ), $args[ 'excludes' ] ?? [] ) );

	/**
	 * Done!
	 */
	return $participants;
}

/**
 * Gets a parameter from $_REQUEST
 *
 * @param  string $parameter The parameter name.
 * @param  string $default   The default value.
 * @return string            The value.
 */
function plcl_get_request_parameter( $parameter, $default = '' ) {
	return ! empty( $_REQUEST[ $parameter ] )
		? $_REQUEST[ $parameter ]
		: $default
	;
}
