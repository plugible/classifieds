<?php 

if  ( ! $data ) {
	return;
}

$posts = $data;

?>

<table>
	<?php
	foreach ( $posts as $post ) {
		plcl_load_template( 'shortcodes/list-item.php', $post );
	}
	?>
</table>
