<?php

add_action( 'admin_init', function() {
	add_settings_section(
		'wpmyads-permalinks',
		__( 'WPMyAds', 'wpmyads' ),
		function() {
			echo wpautop( __( 'These settings control the permalinks used for WPMyAds custom posts and taxonomies.', 'wpmyads' ) );
		},
		'permalink'
	);
} );

foreach ( [
	'plcl_post_type_args',
	'plcl_taxonomy_args',
] as $filter ) {
	add_filter( $filter, function( $args, $id ) {
		if ( 1
			&& ! empty( $args[ 'rewrite' ] )
			&& ! empty( $args[ 'rewrite' ][ 'slug' ] )
		) {
			/**
			 * Option name.
			 * @var string
			 */
			$option = sprintf( '%1$s_%2$s_slug', wpmyads()->plugin_slug, $id );

			/**
			 * Add permalink setting.
			 */
			add_filter( 'admin_init', function() use( $args, $id, $option ) {
				/**
				 * Add.
				 */
				add_settings_field(	
					$id,
					$args[ 'labels' ][ 'name' ],
					function () use ( $args, $id, $option ){
						$slug   = $args[ 'rewrite' ][ 'slug' ];
						$value  = get_option( $option );
						?>
						<fieldset>
							<input
								type        = "text"
								class       = "regular-text code"
								id          = "<?php echo esc_attr( $option ); ?>" 
								name        = "<?php echo esc_attr( $option ); ?>" 
								placeholder = "<?php echo esc_attr( $slug ); ?>"
								value       = "<?php echo esc_attr( $value ); ?>"
							>
						</fieldset>
						<?php
					},
					'permalink',
					'wpmyads-permalinks'
				);
				/**
				 * Save.
				 */
				if ( isset( $_REQUEST[ $option ] ) ) {
					$value = sanitize_text_field( $_REQUEST[ $option ] );
					if ( empty( $value ) ) {
						delete_option( $option );
					} else {
						update_option( $option, $value );
					}
				}
			} );

			/**
			 * Override slug.
			 */
			$args[ 'rewrite' ][ 'slug' ] = get_option( $option, $args[ 'rewrite' ][ 'slug' ] );
		}

		/**
		 * Done.
		 */
		return $args;
	}, 10, 2 );
}
