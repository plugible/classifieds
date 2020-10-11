<?php

$category = $data[ 'category' ];
$filters  = $data[ 'filters' ];

$filters_action = get_term_link( $category, 'pl_classified_category' );
$filters_class  = apply_filters( 'plcl_filters_class', wpmyads()->plugin_slug .'-filters' );
$filters_id     = wpmyads()->plugin_slug . '-filters';

$filters_button_class = apply_filters( 'plcl_filters_button_class', wpmyads()->plugin_slug .'-filters-button' );

?>

<form class="<?php echo $filters_class; ?>" id="<?php echo $filters_id; ?>" action="<?php echo $filters_action; ?>">
	<div>
		<?php foreach ( $filters as $filter ) { ?>
		<div>
			<?php plcl_load_template( 'filter.php', array( 'filter' => $filter ) ); ?>
		</div>
		<?php } ?>
		<div>
			&nbsp;
			<input class="<?php echo $filters_button_class; ?>" type="submit" value="<?php _e( 'Search'); ?>">
		</div>
	</div>
</form>
