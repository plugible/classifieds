<?php

namespace Plugible\Classifieds;

use Intervention\Image\ImageManagerStatic as Image;

Class Form {

	private $ajaxActionForAdSubmission = 'classifieds-ad-submission';

	private $ajaxActionForImageUpload = 'classifieds-image-upload';

	private $dubug = false;

	private $shortcode = 'classified-form';

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

	private function scripts() {
		$this->plugin->enqueue_asset( 'public/js/classifieds.js', [
			'in_footer' => true,
			'object_name' => $this->settingsObjectName,
			'l10n' => [
				'ajaxActionForImageUpload' => $this->ajaxActionForImageUpload,
				'debug' => $this->debug,
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'saltElementId' => $this->saltElementId,
				'uploadElementId' => $this->uploadElementId,
			],
		] );
	}

	public function ajaxImageUpload() {

		$nowDateTime = (new \DateTime())->format('YmdHis-u');

		$upload_dir = wp_upload_dir()[ 'path' ];

		$mime2Extension = [
			'image/png' => 'png',
			'image/jpeg' => 'jpg',
		];

		$salt = $_REQUEST[ $this->saltElementId ];

		/**
		 * Verify salt.
		 */
		if ( ! preg_match( '/^[0-9a-z]{5}$/i', $salt ) ) {
			status_header( '400' );
			die( ( string ) __LINE__ );
		}

		// Create image.
		$src = $_FILES['files']['tmp_name'][0];
		$image = Image::make( $src );
		$dest = sprintf( '%s/%s-%s.%s'
			, $upload_dir
			, $nowDateTime
			, $salt
			, $mime2Extension[ $image->mime ]
		);

		/**
		 * Verify file extension.
		 */
		if ( ! array_key_exists( $image->mime, $mime2Extension ) ) {
			status_header( '400' );
			die( ( string ) __LINE__ );
		}

		/**
		 * Save.
		 */
		$image->save( $dest );

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
			. $this->input( 'title', __( 'Classified Title', 'classifieds-by-plugible' ) )
			. $this->select( 'category', __( 'Category', 'classifieds-by-plugible' ), $categories )
			. $this->select( 'location', __( 'Location', 'classifieds-by-plugible' ), $locations )
			. $this->uppy( $this->uploadElementId, __( 'Classified Images', 'classifieds-by-plugible' ) )
			. $this->wpEditor( 'description', __( 'Classified Description', 'classifieds-by-plugible' ) )
			. $this->salt( 'salt' )
			. $this->submit( 'submit', __( 'Submit', 'classifieds-by-plugible' ) )
		);
	}

	private function input( $name, $title, $args = [] ) {
		$format = apply_filters( 'pl_classifieds_form_text_format', '<p><label for="%1$s">%2$s<br><input type="text" id="%1$s" name="%1$s" /></label></p>' );
		return apply_filters( 'pl_classifieds_form_text', sprintf( $format, $name, $title ), $name, $title, $args );
	}

	private function salt( $name ) {
		$format = '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />';
		$value = wp_generate_password( 5, false );
		return sprintf( $format, $name, $value );
	}

	private function textArea( $name, $title ) {
		$format = apply_filters( 'pl_classifieds_form_textarea_format', '<p><label for="%1$s">%2$s<br><textarea id="%1$s" name="%1$s" cols="40" rows="5"></textarea></label></p>' );
		return sprintf( $format, $name, $title );
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

	private function select( $name, $title, $options ) {

		$format = apply_filters( 'pl_classifieds_form_select_format', '<p><label for="%1$s">%2$s<br><select id="%1$s" name="%1$s">%3$s</select></label></p>' );

		$options_html = '';
		array_walk( $options, function( $value, $index ) use( &$options_html ) {
			$options_html .= sprintf( '<option value="%1$s">%2$s</option>', $index, $value );
		} );

		return sprintf( $format, $name, $title, $options_html );
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
		$format = apply_filters( 'pl_classifieds_form_format', '<form>%1$s</form>' );
		return sprintf( $format,  $contents );
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
