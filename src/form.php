<?php

namespace Plugible\Classifieds;

use Intervention\Image\ImageManagerStatic as Image;

Class Form {

	private $ajaxActionForAdSubmission = 'classifieds-ad-submission';

	private $ajaxActionForImageUpload = 'classifieds-image-upload';

	private $dubug = false;

	private $shortcode = 'classified-form';

	private $formElementId = 'classified-form';

	private $uploadElementId = 'images';

	private $plugin;

	private $saltElementId = 'salt';

	private $settingsObjectName = 'classifieds';


	public function __construct( $plugin ) {
		$this->debug = ( boolean ) constant( 'WP_DEBUG' );
		$this->plugin = $plugin;
		$this->scripts();
		add_shortcode( $this->shortcode, [ $this, 'output' ] );
		add_action( 'wp_ajax_' . $this->ajaxActionForAdSubmission , [ $this, 'ajaxAdSubmission' ] );
		add_action( 'wp_ajax_' . $this->ajaxActionForImageUpload , [ $this, 'ajaxImageUpload' ] );
		add_action( 'wp_ajax_nopriv_' . $this->ajaxActionForAdSubmission , [ $this, 'ajaxAdSubmission' ] );
		add_action( 'wp_ajax_nopriv_' . $this->ajaxActionForImageUpload , [ $this, 'ajaxImageUpload' ] );
	}

	// public function getUploadDir() {

	// 	$fs = bootswatch_get_filesystem();

	// 	$parts = [
	// 		$this->plugin->plugin_slug,
	// 		date( 'Y' ),
	// 		date( 'm' ),
	// 	];

	// 	$uploadDir = WP_CONTENT_DIR;
	// 	while ( $part = array_shift( $parts ) ) {
	// 		$uploadDir .= DIRECTORY_SEPARATOR . $part; 
	// 		$fs->mkdir( $uploadDir, 0777 );
	// 	}
	
	// 	return $uploadDir;
	// }

	private function scripts() {
		$this->plugin->enqueue_asset( 'public/js/classifieds.js', [
			'in_footer' => true,
			'object_name' => $this->settingsObjectName,
			'l10n' => [
				'ajaxActionForImageUpload' => $this->ajaxActionForImageUpload,
				'debug' => $this->debug,
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'formElementId' => $this->formElementId,
				'saltElementId' => $this->saltElementId,
				'uploadElementId' => $this->uploadElementId,
				'text' => [
					'submit' => __( 'Submit', 'classifieds-by-plugible' ),
					'submitting' => __( 'Submitting in progress', 'classifieds-by-plugible' ),
					'waitForImageUpload' => __( 'Please wait for the image(s) upload to finish', 'classifieds-by-plugible' ),
				],
			],
		] );
	}

	public function ajaxImageUpload() {

		$nowDateTime = (new \DateTime())->format('YmdHis-u');

		// $upload_dir = wp_upload_dir()[ 'path' ];

		// $mime2Extension = [
		// 	'image/png' => 'png',
		// 	'image/jpeg' => 'jpg',
		// ];

		$salt = $_REQUEST[ $this->saltElementId ];

		/**
		 * Verify salt.
		 */
		if ( ! preg_match( '/^[0-9a-z]{5}$/i', $salt ) ) {
			status_header( '400' );
			die( ( string ) __LINE__ );
		}

		// // Create image.
		// $src = $_FILES['files']['tmp_name'][0];
		// $image = Image::make( $src );
		// $dest = sprintf( '%s/%s-%s.%s'
		// 	, $upload_dir
		// 	, $nowDateTime
		// 	, $salt
		// 	, $mime2Extension[ $image->mime ]
		// );

		// /**
		//  * Verify file extension.
		//  */
		// if ( ! array_key_exists( $image->mime, $mime2Extension ) ) {
		// 	status_header( '400' );
		// 	die( ( string ) __LINE__ );
		// }

		$attachement_id = media_handle_upload( 'files', 0 );
		add_post_meta( $attachement_id, 'salt', $salt, true );

		/**
		 * Save.
		 */
		// $image->save( $dest );

		/**
		 * Done
		 */
		die( '{}' );
	}

	public function ajaxAdSubmission() {

		$post_id = wp_insert_post( [
			'post_content' => $_POST[ 'content' ],
			'post_status' => 'draft',
			'post_title' => $_POST[ 'title' ],
			'post_type' => 'pl_classified',
		], true );

		if ( is_wp_error( $post_id ) ) {
			echo '-1';
			exit;
		}

		wp_set_post_terms( $post_id, $_POST[ 'location' ], 'pl_classified_location' );
		wp_set_post_terms( $post_id, $_POST[ 'category' ], 'pl_classified_category' );

		add_post_meta( $post_id, 'phone', $_POST[ 'phone' ], true );
		add_post_meta( $post_id, 'email', $_POST[ 'email' ], true );

		/**
		 * Attach images to ad.
		 */
		$attachments = get_posts( [
			'post_type' => 'attachment',
			'meta_key' => 'salt',
			'meta_value' => $_POST[ 'salt' ],
		] );
		foreach ( $attachments as $attachment ) {
			wp_update_post( [
				'ID' => $attachment->ID,
				'post_parent' => $post_id,
			] );
			delete_post_meta( $attachement_id, 'salt' );
		}

		echo '0';
		exit;

		// $nowDateTime = (new \DateTime())->format('YmdHis-u');

		// $upload_dir = wp_upload_dir()[ 'path' ];

		// $mime2Extension = [
		// 	'image/png' => 'png',
		// 	'image/jpeg' => 'jpg',
		// ];

		// $salt = $_REQUEST[ $this->saltElementId ];

		// /**
		//  * Verify salt.
		//  */
		// if ( ! preg_match( '/^[0-9a-z]{5}$/i', $salt ) ) {
		// 	status_header( '400' );
		// 	die( ( string ) __LINE__ );
		// }

		// // Create image.
		// $src = $_FILES['files']['tmp_name'][0];
		// $image = Image::make( $src );
		// $dest = sprintf( '%s/%s-%s.%s'
		// 	, $upload_dir
		// 	, $nowDateTime
		// 	, $salt
		// 	, $mime2Extension[ $image->mime ]
		// );

		// /**
		//  * Verify file extension.
		//  */
		// if ( ! array_key_exists( $image->mime, $mime2Extension ) ) {
		// 	status_header( '400' );
		// 	die( ( string ) __LINE__ );
		// }

		// /**
		//  * Save.
		//  */
		// $image->save( $dest );

		/**
		 * Done
		 */
		die( '{}' );
	}

	public function output() {

		/**
		 * Verify if login is required.
		 */
		$require_login = apply_filters( 'pl_claassifieds_require_login', false );
		if ( $require_login ) {
			return apply_filters( 'pl_claassifieds_required_login', __( 'Error: Login Required', 'classifieds-by-plugible' ) );
		}

		/**
		 * Generate form.
		 */
		$locations = [];
		$this->getHierarchicalTerms( 'pl_classified_location', $locations );
		$categories = [];
		$this->getHierarchicalTerms( 'pl_classified_category', $categories );
		return $this->form( ''
			. $this->separator()
			. $this->heading( __( 'Contact Info', 'classifieds-by-plugible' ) )
			. $this->text( 'name', __( 'Name*', 'classifieds-by-plugible' ), [
				'required' => true,
			] )
			. $this->email( 'email', __( 'Email*', 'classifieds-by-plugible' ), [
				'data-disallow-space' => true,
				'email' => true,
				'required' => true,
			] )
			. $this->text( 'phone', __( 'Phone*', 'classifieds-by-plugible' ), [
				'maxlength' => 10,
				'minlength' => 10,
				'data-disallow-non-digit' => true,
				'data-disallow-space' => true,
				'required' => true,
			] )
			. $this->separator()
			. $this->heading( __( 'Ad Info', 'classifieds-by-plugible' ) )
			. $this->uppy( $this->uploadElementId, __( 'Images*', 'classifieds-by-plugible' ) )
			. $this->text( 'title', __( 'Title*', 'classifieds-by-plugible' ), [
				'required' => true,
			] )
			. $this->select( 'category', __( 'Category*', 'classifieds-by-plugible' ), $categories, __( 'Choose...', 'classifieds-by-plugible' ), [
				'required' => true,
				'data-use-select2' => true,
			] )
			. $this->select( 'location', __( 'Location*', 'classifieds-by-plugible' ), $locations, __( 'Choose...', 'classifieds-by-plugible' ), [
				'required' => true,
				'data-use-select2' => true,
			] )
			. $this->textarea( 'content', __( 'Description*', 'classifieds-by-plugible' ), [
				'minlength' => 50,
				'required' => true,
			] )
			. $this->separator()
			. $this->salt( 'salt' )
			. $this->hidden( 'action', $this->ajaxActionForAdSubmission )
			. $this->submit( 'submit', __( 'Submit', 'classifieds-by-plugible' ) )
		);
	}

	private function heading( $content ) {
		$format = apply_filters( 'pl_classifieds_form_heading_format', '<h2>%1$s</h2>' );
		return sprintf( $format, $content );
	}

	private function separator() {
		return apply_filters( 'pl_classifieds_form_separator_format', '<hr>' );
	}

	private function text( $name, $title, $args = [] ) {
		return $this->input( $name, $title, 'text', $args );
	}

	private function email( $name, $title, $args = [] ) {
		return $this->input( $name, $title, 'email', $args );
	}

	private function args2HtmlParameters( $args ) {
		return $args
			? str_replace( "=", '="', http_build_query( $args, null, '" ', PHP_QUERY_RFC3986 ) ) . '"'
			: ''
		;
	}

	private function input( $name, $title, $type = 'text', $args = [] ) {
		$format = apply_filters( 'pl_classifieds_form_input_format', '<p><label for="%1$s">%2$s<br><input type="%3$s" id="%1$s" name="%1$s" %3$s/></label></p>' );
		return sprintf( $format, $name, $title, $type, $this->args2HtmlParameters( $args ) );
	}

	private function hidden( $name, $value ) {
		return sprintf( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />', $name, $value );
	}

	private function salt( $name ) {
		return $this->hidden( $name, wp_generate_password( 5, false ) );
	}

	private function textarea( $name, $title, $args ) {
		$format = apply_filters( 'pl_classifieds_form_textarea_format', '<p><label for="%1$s">%2$s<br><textarea id="%1$s" name="%1$s" cols="40" rows="5" %3$s></textarea></label></p>' );
		return sprintf( $format, $name, $title, $this->args2HtmlParameters( $args ) );
	}

	private function wpEditor( $name, $title ) {
		$format = apply_filters( 'pl_classifieds_form_wpeditor_format', '<p><label for="%1$s">%2$s<br>%3$s</label></p>' );
		ob_start();
		wp_editor( '', $name, [
			'media_buttons' => false,
			'quicktags' => false,
			'teeny' => true,
			'textarea_rows' => 6,
		] );
		$editor = ob_get_clean();
		return sprintf( $format, $name, $title, $editor );
	}

	private function select( $name, $title, $options, $emptyOptionText = null, $args = [] ) {

		$format = apply_filters( 'pl_classifieds_form_select_format', '<p><label for="%1$s">%2$s<br><select id="%1$s" name="%1$s" %4$s>%3$s</select></label></p>' );

		$options_html = is_null( $emptyOptionText )
			? ''
			: '<option value="">' . $emptyOptionText . '</option>'
		;
		array_walk( $options, function( $value, $index ) use( &$options_html ) {
			$options_html .= sprintf( '<option value="%1$s">%2$s</option>', $index, $value );
		} );

		return sprintf( $format, $name, $title, $options_html, $this->args2HtmlParameters( $args ) );
	} 

	private function uppy( $name, $title ) {

		$format = apply_filters( 'pl_classifieds_form_uppy_format', '<div><label for="%1$s">%2$s<br><div id="%1$s"></div></label></div>' );
		return sprintf( $format, $name, $title );
	}

	private function submit( $name, $title ) {
		$format = apply_filters( 'pl_classifieds_form_submit_format', '<p><input type="submit" id="%1$s" value="%2$s" /></p>' );
		return apply_filters( 'pl_classifieds_form_input', sprintf( $format, $name, $title ), $name, $title );
	}

	private function form( $contents ) {
		$format = apply_filters( 'pl_classifieds_form_format', '<form id="%1$s">%2$s</form>' );
		return sprintf( $format, $this->formElementId, $contents );
	}

	private function getHierarchicalTerms( $taxonomy, &$ret, $parent = 0 ) {

		static $level = 0;

		$terms = get_terms( $taxonomy, [
			'hide_empty' => false,
			'parent' => $parent,
		] );

		foreach ( $terms  as $term ) {
			$ret[ $term->term_id ] = str_repeat( '&mdash;', $level ) . ' ' . $term->name;
			$child_terms = get_terms( $taxonomy, [
				'hide_empty' => false,
				'parent' => $term->term_id,
			] );
			if ( $child_terms ) {
				$level++;
				$this->getHierarchicalTerms( $taxonomy, $ret, $term->term_id );
				$level--;
			}
		}
	}
}
