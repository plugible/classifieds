<?php
/**
 * The main form class.
 *
 * @package Plugible\WPMyAds
 */

namespace WPMyAds;

/**
 * The main form class.
 */
class Form {

	/**
	 * Ajax action for ad submission.
	 *
	 * @var string
	 */
	private $ajax_action_for_ad_submission;

	/**
	 * Ajax action for image upload.
	 *
	 * @var string
	 */
	private $ajax_action_for_image_upload;

	/**
	 * Enable debugging.
	 *
	 * @var boolean
	 */
	private $dubug = false;

	private $shortcode;

	private $formElementId;

	private $uploadElementId;

	private $uploadElementSuffix = 'images';

	private $plugin;

	private $saltElementId = 'salt';

	private $settingsObjectName;

	public function __construct( $plugin ) {

		$this->debug                         = (bool) constant( 'WP_DEBUG' );
		$this->plugin                        = $plugin;
		$this->settingsObjectName            = $plugin->plugin_slug;
		$this->ajax_action_for_ad_submission = $plugin->plugin_slug . '-ajax-action-for-ad-submission';
		$this->ajax_action_for_image_upload  = $plugin->plugin_slug . '-ajax-action-for-image-upload';
		$this->formElementId                 = $plugin->plugin_slug . '-form';
		$this->uploadElementId               = sprintf( '%1$s-%2$s', $this->formElementId, $this->uploadElementSuffix );
		$this->shortcode                     = $plugin->plugin_slug . '-form';

		/**
		 * Register shortcode.
		 */
		add_shortcode( $this->shortcode, array( $this, 'output' ) );

		/**
		 * Ajax.
		 */
		add_action( 'wp_ajax_' . $this->ajax_action_for_ad_submission, array( $this, 'ajaxAdSubmission' ) );
		add_action( 'wp_ajax_' . $this->ajax_action_for_image_upload, array( $this, 'ajaxImageUpload' ) );
		add_action( 'wp_ajax_nopriv_' . $this->ajax_action_for_ad_submission, array( $this, 'ajaxAdSubmission' ) );
		add_action( 'wp_ajax_nopriv_' . $this->ajax_action_for_image_upload, array( $this, 'ajaxImageUpload' ) );

		/**
		 * Localize.
		 */
		add_filter( sprintf( '%s::enqueue-asset', wpmyads()->plugin_slug ), array( $this, 'localize' ), 10, 2 );

		/**
		 * Use all children for scopeed selects.
		 */
		add_filter( 'plcl_option_to_data', array( $this, 'optionToData' ), 10, 3 );
	}

	function prepopulate( $field, $default = null ) {
		$value = isset( $_REQUEST[ $this->formElementId . '-' . $field ] )
			? $_REQUEST[ $this->formElementId . '-' . $field ]
			: ( isset( $default ) ? $default : '' )
		;
		return apply_filters( 'pl_prepopulate', $value, $field, $default );
	}

	function prepopulateSelect( $field, $default = [] ) {
		return $this->prepopulate( $field, [] );
	}

	/**
	 * Handle scoped selects.
	 *
	 * - Handle multiple scopes (pipe separated)
	 * - Show all scopes including children as comma separated values.
	 */
	public function optionToData( $data, $option_name, $option_value ) {
		if ( 'scope' !== $option_name ) {
			return $data;
		}

		$scopes = explode( '|', $option_value );

		foreach ( $scopes as $scope ) {
			$children_ids  = get_term_children( get_term_by( 'slug', $scope, 'pl_classified_category' )->term_id ?? 0, 'pl_classified_category' );
			foreach ( $children_ids as $child_id ) {
				$scopes[] = get_term( $child_id, 'pl_classified_category' )->slug;
			}
		}

		$scopes_hashed = array_map( function( $v ) {
			return substr( md5( $v ), 0, 7 );
		}, $scopes );

		return sprintf( 'data-%1$s="%2$s"', $option_name, implode( ',', $scopes_hashed ) );
	}

	public function localize( $args, $path ) {

		if ( 'dist/js/main.bundle.js' !== $path ) {
			return $args;
		}

		$args['l10n'] = array_merge_recursive(
			$args['l10n'] ?? array(),
			array(
				'form' => array(
					'ajaxActionForImageUpload' => $this->ajax_action_for_image_upload,
					'debug'                    => $this->debug,
					'endpoint'                 => admin_url( 'admin-ajax.php' ),
					'formElementId'            => $this->formElementId,
					'saltElementId'            => $this->formElementId . '-' . $this->saltElementId,
					'uploadElementId'          => $this->uploadElementId,
					'text'                     => array(
						'submit'                   => __( 'Submit', 'wpmyads' ),
						'submitting'               => __( 'Submitting... Please wait', 'wpmyads' ),
						'fixErrors'                => __( 'Errors detected. Please fix errors and submit again', 'wpmyads' ),
						'waitForImageUpload'       => __( 'Please wait for the image(s) upload to finish', 'wpmyads' ),
						'submitSuccessTitleHtml'   => '<h2>' . __( 'Submission was completed successfully', 'wpmyads' ) . '</h2>',
						'submitSuccessContentHtml' => '<p>' . __( 'Submission was completed successfully', 'wpmyads' ) . '</p>',
					),
				),
			)
		);

		/**
		 * Done.
		 */
		return $args;
	}

	public function ajaxImageUpload() {

		$nowDateTime = ( new \DateTime() )->format( 'YmdHis-u' );

		$salt = $_REQUEST[ $this->saltElementId ];

		/**
		 * Verify salt.
		 */
		if ( ! preg_match( '/^[0-9a-z]+$/i', $salt ) ) {
			die( '-' . __LINE__ );
		}

		/**
		 * Upload file and check it's an image.
		 */
		$attachment_id = media_handle_upload(
			'files',
			0,
			array(),
			array(
				'test_form' => false,
				'test_type' => true,
				'mimes'     => array(
					'jpg|jpeg|jpe' => 'image/jpeg',
					'gif'          => 'image/gif',
					'png'          => 'image/png',
				),
			)
		);
		if ( is_wp_error( $attachment_id ) ) {
			status_header( 415 ); // So that Uppy treats it as an error.
			die( '-' . __LINE__ );
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
		 * Default status.
		 */
		$status = 'draft';

		/**
		 * Required.
		 */
		$required = array(
			'name',
			'phone',
			'title',
			'location',
			'category',
			'description',
		);
		if ( ! is_user_logged_in() ) {
			$required[] = 'email';
		}

		/**
		 * Verify salt.
		 */
		$salt = $_REQUEST[ $this->formElementId . '-salt' ] ?? '';
		if ( ! $salt ) {
			die( '-' . __LINE__ );
		}
		$saltUsed = (bool) get_posts(
			array(
				'post_type'   => 'pl_classified',
				'meta_key'    => 'salt',
				'meta_value'  => $salt,
				'post_status' => 'all',
			)
		);
		if ( $saltUsed ) {
			die( '-' . __LINE__ );
		}

		/**
		 * Populate variables.
		 */
		$name           = wp_strip_all_tags( $_REQUEST[ $this->formElementId . '-name' ] ) ?? '';
		$phone          = wp_strip_all_tags( $_REQUEST[ $this->formElementId . '-phone' ] ) ?? '';
		$title          = wp_strip_all_tags( $_REQUEST[ $this->formElementId . '-title' ] ) ?? '';
		$description    = wp_strip_all_tags( $_REQUEST[ $this->formElementId . '-description' ] ) ?? '';
		$location       = trim( $_REQUEST[ $this->formElementId . '-location' ] ) ?? '';
		$category       = trim( $_REQUEST[ $this->formElementId . '-category' ] ) ?? '';
		$specifications = $_REQUEST[ $this->formElementId . '-specifications' ] ?? array();

		/**
		 * Prepare email.
		 *
		 * - Get from account if user is logged in
		 * - Otherwhise verify validity
		 */
		if ( ! is_user_logged_in() ) {
			$email = trim( strtolower( $_REQUEST[ $this->formElementId . '-email' ] ) ) ?? '';
		}

		/**
		 * Verify required fields.
		 */
		foreach ( $required as $r ) {
			if ( empty( $$r ) ) {
				die( $$r . '-' . __LINE__ );
			}
		}

		/**
		 * Get or create user.
		 */
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
		} elseif ( email_exists( $email ) ) {
			$user = get_user_by( 'email', $email );
		} else {
			$user = get_user_by( 'id', plcl_create_user( $email ) );
		}
		if ( ! is_a( $user, 'WP_User' ) ) {
			die( '-' . __LINE__ );
		}

		/**
		 * Create ad.
		 */
		$post_id = wp_insert_post(
			array(
				'post_content' => $description,
				'post_status'  => $status,
				'post_title'   => $title,
				'post_type'    => 'pl_classified',
				'post_author'  => $user->ID,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			die( '-' . __LINE__ );
		}

		add_post_meta( $post_id, 'name', $name, true );
		add_post_meta( $post_id, 'phone', $phone, true );
		add_post_meta( $post_id, 'salt', $salt, true );

		wp_set_post_terms( $post_id, $location, 'pl_classified_location' );
		wp_set_post_terms( $post_id, $category, 'pl_classified_category' );
		array_walk(
			$specifications,
			function( $term_id ) use ( $post_id ) {
				wp_set_post_terms( $post_id, get_term( $term_id )->name, 'pl_classified_specification', true );
			}
		);

		/**
		 * Attach images to ad.
		 */
		$attachments_old = get_post_meta( $post_id, 'images' ) ?? array();
		$attachments_new = get_posts(
			array(
				'post_type'  => 'attachment',
				'meta_key'   => 'salt',
				'meta_value' => $salt,
			)
		);
		foreach ( $attachments_new as $attachment_new ) {
			/**
			 * Add image.
			 */
			$attachments_old[ $attachment_new->ID ] = wp_get_attachment_url( $attachment_new->ID );
			/**
			 * Attach to parent.
			 */
			wp_update_post(
				array(
					'ID'          => $attachment_new->ID,
					'post_parent' => $post_id,
					'post_author' => $user->ID,
				)
			);
			/**
			 * Remove salt.
			 */
			delete_post_meta( $attachment_new->ID, 'salt' );
		}
		delete_post_meta( $post_id, 'salt' );

		/**
		 * Save to 'images' meta.
		 */
		delete_post_meta( $post_id, 'images' );
		add_post_meta( $post_id, 'images', $attachments_old );

		/**
		 * Done.
		 */
		do_action( 'plcl_classified_inserted', $post_id );
		do_action( 'plcl_classified_inserted_' . $status, $post_id );
		die( '0' );
	}

	public function output() {

		/**
		 * Verify if login is required.
		 */
		$require_login = apply_filters( 'pl_claassifieds_require_login', false );
		if ( $require_login ) {
			return apply_filters( 'pl_claassifieds_required_login', __( 'Error: Login Required', 'wpmyads' ) );
		}

		/**
		 * Generate form.
		 */
		$locations = array();
		$this->getHierarchicalTerms( 'pl_classified_location', $locations );
		$categories = array();
		$this->getHierarchicalTerms( 'pl_classified_category', $categories );
		$specifications = array();
		$this->getHierarchicalTerms(
			'pl_classified_specification',
			$specifications,
			0,
			function( $term ) {
				$term_options = get_option( 'taxonomy_term_' . $term->term_id );
				if ( array_key_exists( 'specification', $term_options ) && array_key_exists( 'value', $term_options ) ) {
					return sprintf(
						'%1$s %2$s %3$s',
						$term_options['specification'],
						__( '→', 'wpmyads' ),
						$term_options['value']
					);
				}
			}
		);
		return $this->form(
			''
			. $this->separator()
			. $this->heading( __( 'Contact Information', 'wpmyads' ) )
			. $this->text(
				'name',
				__( 'Name*', 'wpmyads' ),
				array(
					'required' => true,
					'value'    => $this->prepopulate( 'name', is_user_logged_in() ? wp_get_current_user()->data->display_name : null ),
				)
			)
			. ( ! is_user_logged_in()
				? $this->email(
					'email',
					__( 'Email*', 'wpmyads' ),
					array(
						'data-disallow-space' => true,
						'email'               => true,
						'required'            => true,
						'value'    => $this->prepopulate( 'email' ),
					)
				)
				: ''
			)
			. $this->text(
				'phone',
				__( 'Phone*', 'wpmyads' ),
				array(
					'data-disallow-non-digit' => true,
					'data-disallow-space'     => true,
					'maxlength'               => 15,
					'minlength'               => 10,
					'required'                => true,
					'value'                   => $this->prepopulate( 'phone' ),
				)
			)
			. $this->separator()
			. $this->heading( __( 'Media', 'wpmyads' ) )
			. $this->uppy( $this->uploadElementId, __( 'Images*', 'wpmyads' ) )
			. $this->separator()
			. $this->heading( __( 'Ad Information', 'wpmyads' ) )
			. $this->text(
				'title',
				__( 'Title*', 'wpmyads' ),
				array(
					'required' => true,
					'value'    => $this->prepopulate( 'title' ),
				)
			)
			. $this->select(
				'location',
				__( 'Location*', 'wpmyads' ),
				$locations,
				$this->prepopulateSelect( 'location' ),
				__( 'Choose', 'wpmyads' ) . '&hellip;',
				array(
					'data-use-select2' => true,
					'required'         => true,
				)
			)
			. $this->select(
				'category',
				__( 'Category*', 'wpmyads' ),
				$categories,
				$this->prepopulateSelect( 'category' ),
				__( 'Choose', 'wpmyads' ) . '&hellip;',
				array(
					'required'         => true,
					'data-controls'    => $this->formElementId . '-specifications',
					'data-use-select2' => true,
				)
			)
			. $this->select(
				'specifications',
				__( 'Specifications*', 'wpmyads' ),
				$specifications,
				$this->prepopulateSelect( 'specifications' ),
				null,
				array(
					'data-use-select2' => true,
					'data-group-by'    => 'specification',
					// 'required'         => true,
					'multiple'         => true,
				)
			)
			. $this->textarea(
				'description',
				__( 'Description*', 'wpmyads' ),
				array(
					'data-disallow-excessive-line-breaks' => true,
					'minlength'                           => 20,
					'maxlength'                           => 20000,
					'required'                            => true,
				)
			)
			. $this->separator()
			. $this->salt( 'salt' )
			. $this->hidden( 'action', $this->ajax_action_for_ad_submission, true )
			. $this->submit( 'submit', __( 'Submit', 'wpmyads' ) )
		);
	}

	private function heading( $content ) {
		$format = apply_filters( 'pl_classifieds_form_heading_format', '<h2>%1$s</h2>' );
		return sprintf( $format, $content );
	}

	private function separator() {
		return apply_filters( 'pl_classifieds_form_separator_format', '<hr>' );
	}

	private function text( $name, $title, $args = array() ) {
		return $this->input( $name, $title, 'text', $args );
	}

	private function email( $name, $title, $args = array() ) {
		return $this->input( $name, $title, 'email', $args );
	}

	private function args2HtmlParameters( $args ) {
		$output = array();
		foreach ( $args as $k => $v ) {
			$output[] = sprintf( '%1$s="%2$s"', $k, esc_html( $v ) );
		}
		return implode( ' ', $output );
	}

	private function input( $name, $title, $type = 'text', $args = array() ) {
		$name   = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$format = apply_filters( 'pl_classifieds_form_input_format', '<p><label for="%1$s">%2$s<br><input type="%3$s" id="%1$s" name="%1$s" %4$s/></label></p>' );
		return sprintf( $format, $name, $title, $type, $this->args2HtmlParameters( $args ) );
	}

	private function hidden( $name, $value, $raw = false ) {
		$name = $raw
			? $name
			: sprintf( '%1$s-%2$s', $this->formElementId, $name );
		return sprintf( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />', $name, $value );
	}

	private function salt( $name, $raw = false ) {
		return $this->hidden( $name, wp_generate_password( 8, false ), $raw );
	}

	private function textarea( $name, $title, $args ) {
		$name   = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$format = apply_filters( 'pl_classifieds_form_textarea_format', '<p><label for="%1$s">%2$s<br><textarea id="%1$s" name="%1$s" cols="40" rows="5" %3$s></textarea></label></p>' );
		return sprintf( $format, $name, $title, $this->args2HtmlParameters( $args ) );
	}

	private function wpEditor( $name, $title ) {
		$name   = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$format = apply_filters( 'pl_classifieds_form_wpeditor_format', '<p><label for="%1$s">%2$s<br>%3$s</label></p>' );
		ob_start();
		wp_editor(
			'',
			$name,
			array(
				'media_buttons' => false,
				'quicktags'     => false,
				'teeny'         => true,
				'textarea_rows' => 6,
			)
		);
		$editor = ob_get_clean();
		return sprintf( $format, $name, $title, $editor );
	}

	private function select( $name, $title, $options, $selected = [], $emptyOptionText = null, $args = array() ) {
		$name = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$id   = $name;
		if ( $args['multiple'] ?? false ) {
			$name .= '[]';
		}

		$format = apply_filters( 'pl_classifieds_form_select_format', '<p><label for="%2$s">%3$s<br><select id="%2$s" name="%1$s" %5$s>%4$s</select></label></p>' );

		$options_html = is_null( $emptyOptionText ) ? '' : '<option value="">' . $emptyOptionText . '</option>';

		array_walk(
			$options,
			function( $value, $index ) use ( &$options_html, $selected ) {
				$name = is_array( $value ) ? $value['name'] : $value;
				$slug = is_array( $value ) ? $value['slug'] : '';
				$data = [];
				if ( is_array( $value ) && array_key_exists( 'options', $value ) && is_array( $value['options'] ) ) {
					foreach ( $value['options'] as $option_name => $option_value ) {
						if ( is_numeric( $option_name ) ) {
							continue;
						}
						$data[] = apply_filters(
							'plcl_option_to_data',
							sprintf( 'data-%1$s="%2$s"', $option_name, substr( md5( (string) $option_value ), 0, 7 ) ),
							$option_name,
							$option_value
						);
					}
				}
				$options_html .= sprintf(
					"\n" . '<option value="%1$s" data-slug="%2$s" %3$s %4$s>%5$s</option>',
					$index,
					substr( md5( urldecode( $slug ) ), 0, 7 ),
					implode( ' ', $data),
					in_array( $index, $selected, false ) ? 'selected="selected"' : '',
					$name
				);
			}
		);

		return sprintf( $format, $name, $id, $title, $options_html, $this->args2HtmlParameters( $args ) );
	}

	private function uppy( $name, $title ) {
		$format = apply_filters( 'pl_classifieds_form_uppy_format', '<div><label for="%1$s">%2$s<br><div id="%1$s"></div></label></div>' );
		return sprintf( $format, $name, $title );
	}

	private function submit( $name, $title ) {
		$name   = sprintf( '%1$s-%2$s', $this->formElementId, $name );
		$format = apply_filters( 'pl_classifieds_form_submit_format', '<p><input type="submit" id="%1$s" value="%2$s" /></p>' );
		return apply_filters( 'pl_classifieds_form_input', sprintf( $format, $name, $title ), $name, $title );
	}

	private function form( $contents ) {
		$format = apply_filters( 'pl_classifieds_form_format', '<form id="%1$s">%2$s</form>' );
		return sprintf( $format, $this->formElementId, $contents );
	}

	private function getHierarchicalTerms( $taxonomy, &$ret, $parent = 0, $name_cb = null ) {

		static $level = 0;

		$terms = get_terms(
			$taxonomy,
			array(
				'hide_empty' => false,
				'order'      => 'ASC',
				'orderby'    => 'name',
				'parent'     => $parent,
			)
		);

		if ( ! $name_cb ) {
			$name_cb = function( $term ) {
				return $term->name;
			};
		}

		foreach ( $terms  as $term ) {
			$ret[ $term->term_id ] = array(
				'name'    => trim( str_repeat( '&mdash;', $level ) . ' ' . $name_cb( $term ) ),
				'slug'    => $term->slug,
				'options' => get_option( 'taxonomy_term_' . $term->term_id, array() ),
			);
			$child_terms           = get_terms(
				$taxonomy,
				array(
					'hide_empty' => false,
					'parent'     => $term->term_id,
				)
			);
			if ( $child_terms ) {
				$level++;
				$this->getHierarchicalTerms( $taxonomy, $ret, $term->term_id, $name_cb );
				$level--;
			}
		}
	}
}
