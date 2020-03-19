<?php

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
					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wordpress-importer' ) . '</strong><br />';
					echo esc_html( $file[ 'error' ] ) . '</p>';
					return false;
				} else if ( ! file_exists( $file[ 'file' ] ) ) {
					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wordpress-importer' ) . '</strong><br />';
					printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'wordpress-importer' ), esc_html( $file['file'] ) );
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
				$num_created = 0;
				$num_updated = 0;
				$num_ignored = 0;
				foreach ( $rows as $row ) {

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
					 * Create or import term.
					 */
					add_filter( $taxonomy . '_posted_term_meta', $add_term_meta_func );
					$term = wp_insert_term( $row[ 'name' ], $taxonomy, [
						'slug' => $row[ 'slug' ],
					] );
					if ( ! is_wp_error(  $term ) ) {
						$term_id = $term[ 'term_id' ];
						$num_created++; 
					} else if ( in_array( 'term_exists', $term->get_error_codes() ) ) {
						$term_id = $term->get_error_data( 'term_exists' );
						wp_update_term( $term_id, $taxonomy );
						$num_updated++; 
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
						if ( sanitize_user( $tax, true ) !== $tax ) {
							return;
						}
						$new_term = get_term_by( 'name', $value, $tax );
						if ( ! is_wp_error( $new_term ) ) {
							wp_set_object_terms( $term_id, $new_term->term_id, $tax );
						}
					} );

					/**
					 * Polylang Integration.
					 */
					if ( function_exists( 'pll_set_term_language' ) ) {
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
		$args[ 'description' ],
		function() use ( $args, $header_func, $footer_func, $step_func ) {
			$step = empty( $_GET[ 'step' ] ) ? 0 : ( int ) $_GET[ 'step' ];
			$header_func( $args[ 'header' ] );
			$step_func( $step, $args[ 'taxonomy' ], $args[ 'options' ], $args[ 'success' ] );
			$footer_func();
		}
	);
};
