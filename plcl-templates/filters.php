<?php

global $post;
global $wp_taxonomies;

$category = get_queried_object();
$filters = [
	'locations' => 'pl_classified_location',
	'specifications' => 'pl_classified_specification',
];

?>

<form action="<?php echo get_term_link( $category, 'pl_classified_category' ); ?>">
<?php
	$i = 0;
	foreach ( $filters as $key => $taxonomy ) {

		$terms = get_terms( [
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
		] );
		if ( ! $terms ) {
			continue;
		}
		switch ( $key ) {
			case 'specifications':
				/**
				 * Add options.
				 */
				array_walk( $terms, function( &$s ) use ( $terms ) {
					$s->enabled = in_array( $s->slug, $terms );
					$s->options = get_option( 'taxonomy_term_' . $s->term_id );
				} );
				/**
				 * Filter out other categories.
				 */
				$terms = array_filter( $terms, function( $term ) use ( $category ) {
					return ( $term->options[ 'scope' ] ?? '' ) === $category->slug;
				} );
				/**
				 * Group
				 */
				$groups = [];
				foreach ( $terms as $term ) {
					$groups [ $term->options[ 'specification' ] ][] = $term;
				}
				/**
				 * Display.
				 */
				foreach ( $groups as $group => $group_specs ) {
					$i++;
					?>
					<p class="alignleft" style="width: 30%">
						<?php echo $group; ?>
						<select multiple='multiple' name='filters[<?php echo $key ?>][]' data-use-select2='true'>
							<?php foreach ( $group_specs as $spec ) { ?>
								<option
									value='<?php echo $spec->slug ?>'
									<?php selected( in_array( $spec->slug, $_REQUEST[ 'filters' ][ $key ] ?? [] ) ) ?>
								><?php echo $spec->options[ 'value' ] ?></option>
							<?php } ?>
						</select >
					</p>
					<?php if ( $i % 3 === 0 ) { ?>
						<br style="clear: both;">
					<?php } ?>
					<?php
				}
				break;
			default:
				$i++;
				?>
				<p class="alignleft" style="width: 30%">
					<?php echo $wp_taxonomies[ $taxonomy ]->labels->all_items ?>
					<select multiple='multiple' name='filters[<?php echo $key ?>][]' data-use-select2='true'>
						<?php foreach ( $terms as $term ) { ?>
							<option
								value='<?php echo $term->slug ?>'
								<?php selected( in_array( $term->slug, $_REQUEST[ 'filters' ][ $key ] ?? [] ) ) ?>
							><?php echo $term->name ?></option>
						<?php } ?>
					</select >
				</p>
				<?php if ( $i % 3 === 0 ) { ?>
					<br style="clear: both;">
				<?php } ?>
				<?php
				break;
		}
	}

?>
<br style="clear: both">
<p><input type="submit" value="<?php _e( 'Search'); ?>"></p>
</form>