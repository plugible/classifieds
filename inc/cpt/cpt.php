<?php

/**
 * Register post types and taxonomies.
 */
add_action( 'init', function() {

	$register_taxonomy_fields = function( $taxonomy, $fields ) {

		/**
		 * Add columns.
		 *
		 * Fields with keys starting with '_' will not added.
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
		add_filter( "manage_edit-{$taxonomy}_sortable_columns", function ( $columns ) use ( $fields ) {
			foreach ( $fields as $name => $label ) {
				$columns[ $name ] = $name;
			}
			return $columns;
		} );

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
	$post_type = [
		'id'   => 'pl_classified',
		'args' => [
			'labels' => [
				'name' => __( 'Classifieds', 'wpmyads' ),
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
				'thumbnail',
			],
		],
	];
	register_post_type(
		$post_type[ 'id' ],
		apply_filters(
			'plcl_post_type_args',
			$post_type[ 'args' ],
			$post_type[ 'id' ]
		)
	);

	/**
	 * Register the taxonomies.
	 */
	$taxonomies = [
		[
			'id'         => 'pl_classified_location',
			'taxonomies' => [
				'pl_classified',
			],
			'args'       => [
				'labels' => [
					'name' => __( 'Locations', 'wpmyads' ),
				],
				'show_admin_column' => true,
				'show_in_nav_menus' => false,
				'publicly_queryable' => false,
				'hierarchical' => true,
				'rewrite' => [
					'slug' => 'classifieds-location',
				],
			],
			'fields'     => [
				'_latitude' => __( 'Latitude', 'wpmyads' ),
				'_longitude' => __( 'Longitude', 'wpmyads' ),
			],
		],
		[
			'id'         => 'pl_classified_category',
			'taxonomies' => [
				'pl_classified',
			],
			'args'       => [
				'labels' => [
					'name' => __( 'Categories', 'wpmyads' ),
				],
				'show_admin_column' => true,
				'hierarchical' => true,
				'rewrite' => [
					'slug' => 'classifieds-category',
				],
			],
			'fields'     => [
				'order' => __( 'Order', 'wpmyads' ),
			],
		],
		[
			'id'         => 'pl_classified_specification',
			'taxonomies' => [
				'pl_classified',
			],
			'args'       => [
				'labels' => [
					'name' => __( 'Specifications', 'wpmyads' ),
				],
				'show_admin_column' => true,
				'show_in_nav_menus' => false,
				'publicly_queryable' => false,
			] ,
			'fields'     => [
				'specification' => __( 'Specification', 'wpmyads' ),
				'value' => __( 'Value', 'wpmyads' ),
				'scope' => __( 'Scope', 'wpmyads' ),
			],
		],
	];

	foreach ( $taxonomies as $taxonomy ) {
		register_taxonomy(
			$taxonomy[ 'id' ],
			$taxonomy[ 'taxonomies' ],
			apply_filters(
				'plcl_taxonomy_args',
				$taxonomy[ 'args' ],
				$taxonomy[ 'id' ]
			)
		);
		$register_taxonomy_fields( $taxonomy[ 'id' ], $taxonomy[ 'fields' ] );
	}
} );
