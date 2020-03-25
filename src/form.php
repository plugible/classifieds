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

	private function scripts() {
		$this->plugin->enqueue_asset( 'dist/classifieds.js', [
			'in_footer' => true,
			'object_name' => $this->settingsObjectName,
			'l10n' => [
				'ajaxActionForImageUpload' => $this->ajaxActionForImageUpload,
				'debug' => $this->debug,
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'formElementId' => $this->formElementId,
				'saltElementId' => $this->formElementId . '-' .$this->saltElementId,
				'uploadElementId' => $this->formElementId . '-' .$this->uploadElementId,
				'text' => [
					'submit' => __( 'Submit', 'classifieds-by-plugible' ),
					'submitting' => __( 'Submitting... Please wait', 'classifieds-by-plugible' ),
					'fixErrors' => __( 'Errors detected. Please fix errors and submit again', 'classifieds-by-plugible' ),
					'waitForImageUpload' => __( 'Please wait for the image(s) upload to finish', 'classifieds-by-plugible' ),
					'submitSuccessTitleHtml' => '<h2>' . __( 'Submission was completed successfully', 'classifieds-by-plugible' ) . '</h2>',
					'submitSuccessContentHtml' => '<p>'. __( 'Submission was completed successfully', 'classifieds-by-plugible' ) . '</p>',
				],
			],
		] );
	}

	public function ajaxImageUpload() {

		$nowDateTime = (new \DateTime())->format('YmdHis-u');

		$salt = $_REQUEST[ $this->saltElementId ];

		/**
		 * Verify salt.
		 */
		if ( ! preg_match( '/^[0-9a-z]{12}$/i', $salt ) ) {
			die( -1 );
		}

		/**
		 * Upload file and check it's an image.
		 */
		$attachment_id = media_handle_upload( 'files', 0, [], [
			'test_form' => false,
			'test_type' => true,
			'mimes' => [
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
			],
		] );
		if ( is_wp_error( $attachment_id ) ) {
			status_header( 415 ); // So that Uppy treat it as an error.
			die( '-' . __LINE__);
		}

		/**
		 * Add salt to attachment.
		 */
		add_post_meta( $attachment_id, 'salt', $salt, true );

		/**
		 * Done
		 */
		die( '0' );
	}

	public function ajaxAdSubmission() {

		/**
		 * Verify salt.
		 */
		$salt = $_REQUEST[ $this->formElementId . '-salt' ] ?? '';
		if ( ! $salt ) {
			die( '-' . __LINE__ );
		}
		$saltUsed = ( bool ) get_posts( [
			'post_type' => 'pl_classified',
			'meta_key' => 'salt',
			'meta_value' => $salt,
			'post_status' => 'all',
		] );
		if ( $saltUsed ) {
			die( '-' . __LINE__ );
		}

		$name =           $_REQUEST[  $this->formElementId . '-name' ]           ?? '';
		$email =          $_REQUEST[  $this->formElementId . '-email' ]          ?? '';
		$phone =          $_REQUEST[  $this->formElementId . '-phone' ]          ?? '';
		$title =          $_REQUEST[  $this->formElementId . '-title' ]          ?? '';
		$location =       $_REQUEST[  $this->formElementId . '-location' ]       ?? '';
		$category =       $_REQUEST[  $this->formElementId . '-category' ]       ?? '';
		$specifications = $_REQUEST[  $this->formElementId . '-specifications' ] ?? [];
		$description =    $_REQUEST[  $this->formElementId . '-description' ]    ?? '';

		/**
		 * Validation.
		 */
		$required = [
			'name',
			'email',
			'phone',
			'title',
			'location',
			'category',
			'description',
		];

		foreach ( $required as $r ) {
			if ( false
				|| ! array_key_exists( $this->formElementId . '-' . $r, $_REQUEST )
				|| empty( trim( $_REQUEST[ $this->formElementId . '-' .$r ] ) )
			) {
				die( '-' . __LINE__ );
			}
		}

		/**
		 * Create ad.
		 */
		$post_id = wp_insert_post( [
			'post_content' => $description,
			'post_status' => 'draft',
			'post_title' => $title,
			'post_type' => 'pl_classified',
		], true );

		if ( is_wp_error( $post_id ) ) {
			die( '-' . __LINE__ );
		}

		add_post_meta( $post_id, 'name', $name, true );
		add_post_meta( $post_id, 'phone', $phone, true );
		add_post_meta( $post_id, 'email', $email, true );
		add_post_meta( $post_id, 'salt', $salt, true );

		wp_set_post_terms( $post_id, $location, 'pl_classified_location' );
		wp_set_post_terms( $post_id, $category, 'pl_classified_category' );
		array_walk( $specifications, function( $term_id ) use ( $post_id ) {
			wp_set_post_terms( $post_id, get_term( $term_id )->name, 'pl_classified_specification', true );
		} );

		/**
		 * Attach images to ad.
		 */
		$attachments_old = get_post_meta( $post_id, 'plcl_image' ) ?? [];
		$attachments_new = get_posts( [
			'post_type' => 'attachment',
			'meta_key' => 'salt',
			'meta_value' => $salt,
		] );
		foreach ( $attachments_new as $attachment_new ) {
			/**
			 * Add image.
			 */
			$attachments_old[ $attachment_new->ID ] = wp_get_attachment_url( $attachment_new->ID );
			/**
			 * Attach to parent.
			 */
			wp_update_post( [
				'ID' => $attachment_new->ID,
				'post_parent' => $post_id,
			] );
			/**
			 * Remove salt.
			 */
			delete_post_meta( $attachment_new->ID, 'salt' );
		}
		/**
		 * Save to 'plcl_image' meta.
		 */
		delete_post_meta( $post_id, 'plcl_image' );
		add_post_meta( $post_id, 'plcl_image', $attachments_old );

		/**
		 * Done.
		 */
		do_action( 'plcl_classified_inserted', $post_id );
		die( '0' );
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
		$specifications = [];
		$this->getHierarchicalTerms( 'pl_classified_specification', $specifications, 0, function( $term ) {
			$term_options = get_option( 'taxonomy_term_' . $term->term_id );
			if ( array_key_exists( 'specification', $term_options ) && array_key_exists( 'value', $term_options ) ) {
				return sprintf( '%1$s %2$s %3$s'
					, $term_options[ 'specification' ]
					, __( '→', 'classifieds-by-plugible' )
					, $term_options[ 'value' ]
				);
			}
		} );
		return $this->form( ''
			. $this->separator()
			. $this->heading( __( 'Contact Information', 'classifieds-by-plugible' ) )
			. $this->text( 'name', __( 'Name*', 'classifieds-by-plugible' ), [
				'required' => true,
			] )
			. $this->email( 'email', __( 'Email*', 'classifieds-by-plugible' ), [
				'data-disallow-space' => true,
				'email' => true,
				'required' => true,
			] )
			. $this->text( 'phone', __( 'Phone*', 'classifieds-by-plugible' ), [
				'data-disallow-non-digit' => true,
				'data-disallow-space' => true,
				'maxlength' => 10,
				'minlength' => 10,
				'required' => true,
			] )
			. $this->separator()
			. $this->heading( __( 'Images', 'classifieds-by-plugible' ) )
			. $this->uppy( $this->uploadElementId, __( 'Images*', 'classifieds-by-plugible' ) )
			. $this->separator()
			. $this->heading( __( 'Ad Information', 'classifieds-by-plugible' ) )
			. $this->text( 'title', __( 'Title*', 'classifieds-by-plugible' ), [
				'required' => true,
			] )
			. $this->select( 'location', __( 'Location*', 'classifieds-by-plugible' ), $locations, __( 'Choose...', 'classifieds-by-plugible' ), [
				'data-use-select2' => true,
				'required' => true,
			] )
			. $this->select( 'category', __( 'Category*', 'classifieds-by-plugible' ), $categories, __( 'Choose...', 'classifieds-by-plugible' ), [
				'required' => true,
				'data-controls' => $this->formElementId . '-specifications',
				'data-use-select2' => true,
			] )
			. $this->select( 'specifications', __( 'Specifications*', 'classifieds-by-plugible' ), $specifications, null, [
				'data-use-select2' => true,
				'data-group-by' => 'specification',
				// 'required' => true,
				'multiple' => true,
			] )
			. $this->textarea( 'description', __( 'Description*', 'classifieds-by-plugible' ), [
				'minlength' => 50,
				'required' => true,
			] )
			. $this->separator()
			. $this->salt( 'salt' )
			. $this->hidden( 'action', $this->ajaxActionForAdSubmission, true )
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
		$name = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$format = apply_filters( 'pl_classifieds_form_input_format', '<p><label for="%1$s">%2$s<br><input type="%3$s" id="%1$s" name="%1$s" %3$s/></label></p>' );
		return sprintf( $format, $name, $title, $type, $this->args2HtmlParameters( $args ) );
	}

	private function hidden( $name, $value, $raw = false ) {
		$name = $raw
			? $name
			: sprintf( '%1$s-%2$s', $this->formElementId, $name )
		;
		return sprintf( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />', $name, $value );
	}

	private function salt( $name, $raw = false ) {
		return $this->hidden( $name, wp_generate_password( 12, false ), $raw );
	}

	private function textarea( $name, $title, $args ) {
		$name = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$format = apply_filters( 'pl_classifieds_form_textarea_format', '<p><label for="%1$s">%2$s<br><textarea id="%1$s" name="%1$s" cols="40" rows="5" %3$s></textarea></label></p>' );
		return sprintf( $format, $name, $title, $this->args2HtmlParameters( $args ) );
	}

	private function wpEditor( $name, $title ) {
		$name = sprintf( '%1$s-%2$s', $this->formElementId, $name );
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
		$name = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$id = $name;
		if ( $args[ 'multiple' ] ?? false ) {
			$name .= '[]';
		}

		$format = apply_filters( 'pl_classifieds_form_select_format', '<p><label for="%2$s">%3$s<br><select id="%2$s" name="%1$s" %5$s>%4$s</select></label></p>' );

		$options_html = is_null( $emptyOptionText ) ? '' : '<option value="">' . $emptyOptionText . '</option>';

		array_walk( $options, function( $value, $index ) use( &$options_html ) {
			$name = is_array( $value ) ? $value[ 'name' ] : $value;
			$slug = is_array( $value ) ? $value[ 'slug' ] : '';
			$data = '';
			if ( is_array( $value ) && array_key_exists( 'options', $value ) && is_array( $value[ 'options' ] ) ) {
				foreach ( $value[ 'options' ] as $option_name => $option_value ) {
					if ( is_numeric( $option_name ) ) {
						continue;
					}
					$data .= sprintf( ' data-%1$s="%2$s"', $option_name, substr( md5( ( string ) $option_value ), 0, 7 ) );
				}
			}
			$options_html .= sprintf( "\n" . '<option value="%1$s" data-slug="%2$s"%3$s>%4$s</option>'
				, $index
				, substr( md5( urldecode( $slug ) ), 0, 7 )
				, $data
				, $name
			);
		} );

		return sprintf( $format, $name, $id, $title, $options_html, $this->args2HtmlParameters( $args ) );
	} 

	private function uppy( $name, $title ) {
		$name = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$format = apply_filters( 'pl_classifieds_form_uppy_format', '<div><label for="%1$s">%2$s<br><div id="%1$s"></div></label></div>' );
		return sprintf( $format, $name, $title );
	}

	private function submit( $name, $title ) {
		$name = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$format = apply_filters( 'pl_classifieds_form_submit_format', '<p><input type="submit" id="%1$s" value="%2$s" /></p>' );
		return apply_filters( 'pl_classifieds_form_input', sprintf( $format, $name, $title ), $name, $title );
	}

	private function form( $contents ) {
		$format = apply_filters( 'pl_classifieds_form_format', '<form id="%1$s">%2$s</form>' );
		return sprintf( $format, $this->formElementId, $contents );
	}

	private function getHierarchicalTerms( $taxonomy, &$ret, $parent = 0, $name_cb = null ) {

		static $level = 0;

		$terms = get_terms( $taxonomy, [
			'hide_empty' => false,
			'order' => 'ASC',
			'orderby' => 'name',
			'parent' => $parent,
		] );

		if ( ! $name_cb ) {
			$name_cb = function( $term ) {
				return $term->name;
			};
		}

		foreach ( $terms  as $term ) {
			$ret[ $term->term_id ] = [
				'name' => trim( str_repeat( '&mdash;', $level ) . ' ' . $name_cb( $term ) ),
				'slug' => $term->slug,
				'options' => get_option( 'taxonomy_term_' . $term->term_id ) ?? [],
			];
			$child_terms = get_terms( $taxonomy, [
				'hide_empty' => false,
				'parent' => $term->term_id,
			] );
			if ( $child_terms ) {
				$level++;
				$this->getHierarchicalTerms( $taxonomy, $ret, $term->term_id, $name_cb );
				$level--;
			}
		}
	}
}
