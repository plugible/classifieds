<?php

/**
 * Ad attached images column.
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

/**
 * Ad images metabox.
 */
add_action( 'cmb2_admin_init', function() {

	$cmb = new_cmb2_box( [
		'id' => 'plcl_metabox_pl_classified',
		'title' => __( 'Images' ),
		'object_types' => [ 'pl_classified' ],
		'show_names' => false,
	] )->add_field( [
		'id' => 'images',
		'name' => esc_html__( 'Images' ),
		'type' => 'file_list',
	] );
} );

/**
 * Delete images attached to the deleted ad.
 */
add_action( 'before_delete_post', function( $post_id ) {

    global $post_type;
	if ( 'pl_classified' !== $post_type ) {
		return;
	}
	foreach ( get_attached_media( '', $post_id ) as $attachment ) {
		wp_delete_attachment( $attachment->ID );
	}
} );

/**
 * Add attachment to CMB2's `images` field. 
 */
add_action("add_attachment", function( $attachment_id ) {

	$attachment_post = get_post( $attachment_id );

	if ( ! $attachment_post->post_parent ) {
		return;
	}

	$parent_post = get_post( $attachment_post->post_parent );
	if ( 'pl_classified' !== $parent_post->post_type ) {
		return;
	}

	$attachments = get_posts( [
		'post_type'   => 'attachment',
		'post_parent' => $parent_post->ID,
	] );

	delete_post_meta( $parent_post->ID, 'images' );
	add_post_meta( $parent_post->ID, 'images', wp_list_pluck( $attachments, 'guid', 'ID' ) );
} );

/**
 * Sync attachments to 'images' meta.
 */
add_action( 'cmb2_save_field_' . 'images', function( $updated, $action, $field ) {

	$post_id = $field->object_id;

	$attachments_new = $field->get_data();
	$attachments_old = get_posts( [
		'post_type'   => 'attachment',
		'post_parent' => $post_id,
		'fields'      => 'ids',
	] );

	/**
	 * Remove removed.
	 */
	foreach ( $attachments_old as $attachment_old_id ) {
		if ( ! array_key_exists( ( string ) $attachment_old_id, $attachments_new ) ) {
			wp_delete_attachment( $attachment_old_id );
		}
	}

	/**
	 * Add added.
	 */
	if ( $attachments_new ) {
		foreach ( $attachments_new as $attachment_new_id => $url ) {
			if ( ! array_key_exists( ( int ) $attachment_new_id, $attachments_old ) ) {
				wp_update_post( [
					'ID'          => $attachment_new_id,
					'post_parent' => $post_id,
				] );
			}
		}
	}
}, 10, 4 );
