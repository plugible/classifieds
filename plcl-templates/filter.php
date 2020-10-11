<?php

$filter = $data[ 'filter' ];

?>

<?php echo $filter[ 'title' ]; ?>

<select multiple="multiple" name="filters[<?php echo $filter[ 'key' ]; ?>][]" data-use-select2="true" class="<?php echo apply_filters( 'plcl_filters_dropdown_class', wpmyads()->plugin_slug .'-filters-dropdown' ) ?>">
	<?php foreach ( $filter[ 'options' ] as $value => $name ) { ?>
	<?php $selected = in_array( $value, $_REQUEST[ 'filters' ][ $filter[ 'key' ] ] ?? [], true ); ?>
	<option value="<?php echo $value; ?>" <?php echo $selected ? 'selected' : ''; ?>><?php echo $name; ?></option>
	<?php } ?>
</select>
