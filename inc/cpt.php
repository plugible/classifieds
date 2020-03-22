<?php

/**
 * Register post types and taxonomies.
 */
add_action( 'init', function() {

	$register_taxonomy_fields = function( $taxonomy, $fields ) {

		/**
		 * Add columns.
		 */
		add_filter("manage_edit-{$taxonomy}_columns", function( $columns ) use ( $fields ) {
			return array_merge( $columns, array_filter( $fields, function( $key ) {
				return '_' !== $key[0]; 
			}, ARRAY_FILTER_USE_KEY ) );
		} );
		add_filter("manage_{$taxonomy}_custom_column", function( $string, $column_name, $term_id ) {
			static $terms_options = [];
			if ( ! array_key_exists( $term_id, $terms_options ) ) {
				$terms_options[ $term_id ] = get_option( 'taxonomy_term_' . $term_id );
			}
			return $terms_options[ $term_id ][ $column_name ] ?? '';
		}, 10, 3 );

		/**
		 * Add fields.
		 */
		add_action( $taxonomy . '_add_form_fields', function( $taxonomy ) use( $fields ) {
			foreach ( $fields as $name => $label ) {
				?>
				<div class="form-field term-slug-wrap">
					<label for="<?php echo $name; ?>"><?php echo $label; ?></label>
					<input name="term_meta[<?php echo $name; ?>]" id="term_meta[<?php echo $name; ?>]" type="text" value="" size="40">
				</div>
				<?php
			}
		} );
		add_action( $taxonomy . '_edit_form_fields', function( $tag, $taxonomy ) use( $fields ) {
			$term_meta =  get_option( 'taxonomy_term_' . $tag->term_id );
			foreach ( $fields as $name => $label ) {
				$value = $term_meta[ $name ] ?? '';
				?>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="<?php echo $name; ?>"><?php echo $label; ?></label></th>
					<td><input id="term_meta[<?php echo $name; ?>]" name="term_meta[<?php echo $name; ?>]" type="text" value="<?php echo $value; ?>" /></td>
				</tr>
				<?php
			}
		} , 10, 2 );

		/**
		 * Save fields.
		 */
		$save_term_cb = function( $term_id, $tt_id ) use( $fields, $taxonomy ) {
			$term_meta = ( array ) get_option( 'taxonomy_term_' . $term_id );
			$posted_term_meta = apply_filters( $taxonomy . '_posted_term_meta', $_POST[ 'term_meta' ] ?? [] );
			foreach ( $posted_term_meta as $key => $value ) {
				if ( array_key_exists( $key, $fields ) ) {
					$term_meta[ $key ] = $value;
				}
			}
			update_option( 'taxonomy_term_' . $term_id, $term_meta );
			return;
		};
		add_action( 'created_' . $taxonomy, $save_term_cb, 10, 2 );
		add_action( 'edited_' . $taxonomy, $save_term_cb, 10, 2 );
	};

	/**
	 * Register the "classified" post type.
	 */
	register_post_type( 'pl_classified', [
		'labels' => [
			'name' => 'Classifieds', 'classifieds-by-plugibles',
		],
		'menu_icon' => 'dashicons-megaphone',
		'public' => true,
			'rewrite' => [
			'slug' => 'classified',
		],
		'supports' => [
			'author',
			'custom-fields',
			'editor',
			'title',
			'comments',
		],
	] );

	/**
	 * Register the "Classified/Location" taxonomy.
	 */
	register_taxonomy( 'pl_classified_location', [ 'pl_classified' ], [
		'labels' => [
			'name' => 'Locations', 'classifieds-by-plugibles',
		],
		'show_admin_column' => true,
		'hierarchical' => true,
		'rewrite' => [
			'slug' => 'classifieds-location',
		],
	] );
	$register_taxonomy_fields( 'pl_classified_location', [
		'_latitude' => __( 'Latitude', 'classifieds-by-plugibles' ),
		'_longitude' => __( 'Longitude', 'classifieds-by-plugibles' ),
	] );
 
	/**
	 * Register the "Classified/Category" taxonomy.
	 */
	register_taxonomy( 'pl_classified_category', [ 'pl_classified' ], [
		'labels' => [
			'name' => 'Categories', 'classifieds-by-plugibles',
		],
		'show_admin_column' => true,
		'hierarchical' => true,
		'rewrite' => [
			'slug' => 'classifieds-category',
		],
	] );

	/**
	 * Register the "Classified/Specification" taxonomy.
	 */
	register_taxonomy( 'pl_classified_specification', [ 'pl_classified' ], [
		'labels' => [
			'name' => 'Specifications', 'classifieds-by-plugibles',
		],
		'show_admin_column' => true,
		// 'hierarchical' => true,
		'rewrite' => [
			'slug' => 'classifieds-specification',
		],
	] );
	$register_taxonomy_fields( 'pl_classified_specification', [
		'specification' => __( 'Specification', 'classifieds-by-plugibles' ),
		'value' => __( 'Value', 'classifieds-by-plugibles' ),
		'scope' => __( 'Scope', 'classifieds-by-plugibles' ),
	] );

} );


/**
 * Register taxonomy importers.
 */
add_action( 'admin_init', function() {

	/**
	 * Register categories importer.
	 */
	plcl_register_taxonomy_importer( [
		'taxonomy'    => 'pl_classified_category',
		'name'        => __( 'Classifieds Categories' ),
		'description' => __( 'Import classifieds categories.' ),
		'header'      => __( 'Import Classifieds Categories' ),
		'options'     => [],
		'success'     => _( 'All done. Go to <a href="%s">categories</a>.' ),
	] );

	/**
	 * Register specifications importer.
	 */
	plcl_register_taxonomy_importer( [
		'taxonomy'    => 'pl_classified_specification',
		'name'        => __( 'Classifieds Specifications' ),
		'description' => __( 'Import classifieds specifications.' ),
		'header'      => __( 'Import Classifieds Specifications' ),
		'options'     => [
			'scope',
			'specification',
			'value',
		],
		'success'     => _( 'All done. Go to <a href="%s">specifications</a>.' ),
	] );

	/**
	 * Register locations importer.
	 */
	plcl_register_taxonomy_importer( [
		'taxonomy'    => 'pl_classified_location',
		'name'        => __( 'Classifieds Locations' ),
		'description' => __( 'Import classifieds locations.' ),
		'header'      => __( 'Import Classifieds Locations' ),
		'options'     => [],
		'success'     => _( 'All done. Go to <a href="%s">locations</a>.' ),
	] );
} );


/**
 * Classifieds attached images metabox.
 */
add_action( 'add_meta_boxes', function( $post_type ) {

	if ( 'pl_classified' !== $post_type ) {
		return;
	}

	add_meta_box( 'pl_classified_images_metabox', __( 'Images' ), function( $post ) {
		$w = 150;
		$h = 150;
		$attachments = get_children( [
			'post_mime_type' => 'image',
			'post_parent' => $post->ID,
			'post_type' => 'attachment',
		] );
		foreach ( $attachments as $attachment_id => $attachment ) {
			echo wp_get_attachment_image( $attachment_id, [ $w, $h ] ) . ' ';
		}
	} );
} );

/**
 * Classifieds attached images column.
 */
add_action( 'manage_pl_classified_posts_custom_column', function( $column_name, $post_id ) {
	$w = 32;
	$h = 32;
	if ( 'images' == $column_name ) {
		$attachments = get_children( [
			'post_mime_type' => 'image',
			'post_parent' => $post_id,
			'post_type' => 'attachment',
		] );
		foreach ( $attachments as $attachment_id => $attachment ) {
			echo wp_get_attachment_image( $attachment_id, [ $w, $h ] ) . ' ';
		}
	}
}, 10, 2 );
add_filter( 'manage_pl_classified_posts_columns', function ( $cols ) {
	$cols['images'] = __( 'Images' );
	return $cols;
} );



 