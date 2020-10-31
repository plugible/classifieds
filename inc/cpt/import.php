<?php

/**
 * Register taxonomy importers.
 */
add_action( 'admin_init', function() {

	/**
	 * Register categories importer.
	 */
	plcl_register_taxonomy_importer( [
		'taxonomy'    => 'pl_classified_category',
		'name'        => __( 'Classifieds Categories', 'wpmyads' ),
		'options'     => [],
		'success'     => __( 'All done.', 'wpmyads' ). ' ' . __( 'Go to <a href="%s">categories</a>.', 'wpmyads' ),
	] );

	/**
	 * Register specifications importer.
	 */
	plcl_register_taxonomy_importer( [
		'taxonomy'    => 'pl_classified_specification',
		'name'        => __( 'Classifieds Specifications', 'wpmyads' ),
		'options'     => [
			'scope',
			'specification',
			'value',
		],
		'success'     => __( 'All done.', 'wpmyads' ). ' ' . __( 'Go to <a href="%s">specifications</a>.', 'wpmyads' ),
	] );

	/**
	 * Register locations importer.
	 */
	plcl_register_taxonomy_importer( [
		'taxonomy'    => 'pl_classified_location',
		'name'        => __( 'Classifieds Locations', 'wpmyads' ),
		'options'     => [],
		'success'     => __( 'All done.', 'wpmyads' ). ' ' . __( 'Go to <a href="%s">locations</a>.', 'wpmyads' ),
	] );
} );


function plcl_register_taxonomy_importer( $args ) {

	/**
	 * Open importer wrapper.
	 */
	$header_func = function( $title ) {
		echo '<div class="wrap">';
		echo '<h2>' . $title . '</h2>';
	};

	/**
	 * Closes importer wrapper.
	 */
	$footer_func = function() {
		echo '</div>';
	};

	/**
	 * Outputs different importer steps.
	 */
	$step_func = function( $step, $taxonomy, $options, $success ) {

		switch ( $step ) {

			case 0:
				/**
				 * Show form.
				 */
				wp_import_upload_form( 'admin.php?import=' . $taxonomy .'&amp;step=1' );
				break;

			case 1:

				/**
				  *  Security.
				  */
				if ( ! check_admin_referer( 'import-upload' ) ) {
					return false;
				}

				/**
				 * Validate file import.
				 */
				$file = wp_import_handle_upload();
				if ( isset( $file['error'] ) ) {
					echo '<p><strong>' . __( 'An error happened.', 'wpmyads' ) . '</strong><br />';
					echo esc_html( $file[ 'error' ] ) . '</p>';
					return false;
				} else if ( ! file_exists( $file[ 'file' ] ) ) {
					echo '<p><strong>' . __( 'An error happened.', 'wpmyads' ) . '</strong><br />';
					printf( __( 'The export file could not be found at <code>%s</code>.', 'wpmyads' ), esc_html( $file['file'] ) );
					echo '</p>';
					return false;
				}

				/**
				 * Get rows.
				 */
				$rows = ( new \EasyCSV\Reader( $file[ 'file' ] ) )->getAll();
				if ( ! $rows ) {
					return false;
				}

				/**
				 * Import terms.
				 */
				set_time_limit(0);
				$num_created     = 0;
				$num_updated     = 0;
				$num_ignored     = 0;
				$previous        = [];
				$locked_previous = [];
				foreach ( $rows as $row ) {
					/**
					 * Trim.
					 */
					$row = array_map( 'trim', $row );

					/**
					 * Adds options to term
					 */
					$add_term_meta_func = function( $posted_term_meta ) use ( $row, $options ) {
						foreach ( $options as $option ) {
							if ( array_key_exists( $option, $row ) ) {
								$posted_term_meta[ $option ] = $row[ $option ];
							}
						}
						return $posted_term_meta;
					};

					/**
					 * Populate from previous.
					 *
					 * - `{{{name}}}` for previous.
					 * - `{{key}}` for locked previous.
					 */
					$lock_previous = false;
					foreach ( $row as $column => $value ) {
						if ( preg_match( '/^{{{.+}}}$/', $value ) ) {
							$key_from_value = substr( $value, 3, -3 );
							if ( ! empty( $previous[ $key_from_value ] ) ) {
								$row[ $column ] = $previous[ $key_from_value ];
							}
						} else if ( preg_match( '/^{{.+}}$/', $value ) ) {
							$key_from_value = substr( $value, 2, -2 );
							if ( ! empty( $locked_previous[ $key_from_value ] ) ) {
								$row[ $column ] = $locked_previous[ $key_from_value ];
								$lock_previous  = true;
							}
						}
					}

					/**
					 * Verify required columns, name or slug, with fallback to name.
					 */
					if ( ! empty( $row[ 'name' ] ) ) {
						if ( empty( $row[ 'slug' ] ) ) {
							$row[ 'slug' ] = $row[ 'name' ];
						}
					} else {
						$num_ignored++;
						continue;
					}

					/**
					 * Prepare the term parent.
					 */
					if ( ! empty( $row[ 'parent' ] ) ) {
						foreach ( explode( '|', 'slug|name' ) as $by ) {
							if ( $parent_term = get_term_by( $by, strtolower( sanitize_title( $row[ 'parent' ] ) ), $taxonomy ) ) {
								$row[ 'parent' ] = $parent_term->term_id;
								break;
							}
						}
					} else {
						$row[ 'parent' ] = 0;
					}

					/**
					 * Update or create term.
					 */
					$args = [
						'parent' => $row[ 'parent' ],
						'slug'   => strtolower( sanitize_title( $row[ 'slug' ] ) ),
					];
					/**
					 * Existing term with same slug.
					 */
					$slug_matches = get_terms( array(
						'taxonomy'   => $taxonomy,
						'slug'       => $row[ 'slug' ],
						'hide_empty' => false,
					) );
					/**
					 * Existing term with same name and parent.
					 */
					$name_matches = get_terms( array(
						'taxonomy'   => $taxonomy,
						'name'       => $row[ 'name' ],
						'hide_empty' => false,
						'parent'     => $row[ 'parent' ],
					) );
					add_filter( $taxonomy . '_posted_term_meta', $add_term_meta_func );
					if ( $slug_matches || $name_matches ) {
						/**
						 * Update existing.
						 */
						$term_id = array_merge( $name_matches, $slug_matches )[0]->term_id;
						$term = wp_update_term( $term_id, $taxonomy, $args );
						$num_updated++; 
					} else {
						/**
						 * Create new.
						 */
						$term = wp_insert_term( $row[ 'name' ], $taxonomy, $args );
						if ( ! is_wp_error(  $term ) ) {
							$term_id = $term[ 'term_id' ];
							$num_created++; 
						}
					}
					remove_filter( $taxonomy . '_posted_term_meta', $add_term_meta_func );

					/**
					 * Link terms.
					 */
					array_walk( $row, function( $value, $column ) use ( $term_id ) {
						if ( 't:' !== substr( $column, 0, 2 ) ) {
							return;
						}
						$tax = substr( $column, 2 );
						if ( sanitize_title( $tax, true ) !== $tax ) {
							return;
						}
						$new_term = get_term_by( 'name', $value, $tax );
						if ( ! $new_term ) {
							$new_term = get_term_by( 'slug', $value, $tax );
						}
						if ( $new_term ) {
							wp_set_object_terms( $term_id, $new_term->term_id, $tax );
						}
					} );

					/**
					 * Polylang Integration.
					 */
					if ( function_exists( 'pll_default_language' ) ) {
						if ( ! empty( $row[ 'language' ] ) ) {
							/**
							 * Set language.
							 */
							pll_set_term_language( $term_id, $row[ 'language' ] );

							/**
							 * Relate translations to each other.
							 */
							if ( ! empty( $row[ 'original' ] ) ) {
								$original = get_term_by( 'slug', $row[ 'original' ], $taxonomy );
								if ( is_a( $original, 'WP_Term' ) ) {							
									if ( pll_default_language() === pll_get_term_language( $original->term_id ) ) {
										$translations = array_merge( pll_get_term_translations( $original->term_id ), [ $row[ 'language' ] => $term_id ] );
										pll_save_term_translations( $translations );
									}
								}
							}
						}
					}

					/**
					 * Done importing row.
					 */
					do_action( 'plcl_imported_specification', $term_id, $row );

					/**
					 * Fill previous.
					 */
					$previous = $row;
					if ( ! $lock_previous ) {
						$locked_previous = $row;
					}
				}

				/**
				 * Done importing all rows. Clean-up.
				 */
				wp_import_cleanup( $file[ 'id' ] );
				echo '<p>'
					. sprintf( _( '<strong>%s</strong></a> created' ), number_format_i18n( $num_created ) )
					. ' | '
					. sprintf( _( '<strong>%s</strong></a> updated' ), number_format_i18n( $num_updated ) )
					. ' | '
					. sprintf( _( '<strong>%s</strong></a> ignored' ), number_format_i18n( $num_ignored ) )
					. '</p>'
				;
				echo '<p>'
					. sprintf( $success, admin_url( 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=pl_classified' ) )
					. '</p>'
				;
				break;
			default:
				break;
		}
	};

	register_importer(
		$args[ 'taxonomy' ],
		$args[ 'name' ],
		$args[ 'name' ],
		function() use ( $args, $header_func, $footer_func, $step_func ) {
			$step = empty( $_GET[ 'step' ] ) ? 0 : ( int ) $_GET[ 'step' ];
			$header_func( $args[ 'name' ] );
			$step_func( $step, $args[ 'taxonomy' ], $args[ 'options' ], $args[ 'success' ] );
			$footer_func();
		}
	);
};
