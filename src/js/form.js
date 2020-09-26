
(async function () {
	const { default: $ } = await import(/* webpackChunkName: "jquery" */ 'jquery');

	await import( /* webpackChunkName: "uppy.all" */ '@uppy/core/dist/style.min.css' );
	await import( /* webpackChunkName: "uppy.all" */ '@uppy/dashboard/dist/style.min.css' );
	const { default: Uppy }      = await import(/* webpackChunkName: "uppy.all" */ '@uppy/core');
	const { default: Dashboard } = await import(/* webpackChunkName: "uppy.all" */ '@uppy/dashboard');
	const { default: XHRUpload } = await import(/* webpackChunkName: "uppy.all" */ '@uppy/xhr-upload');
	const { default: validate }  = await import(/* webpackChunkName: "jquery-validation" */ 'jquery-validation' );

	const formSettings = window[ require( './settings.js' ).settingsObjectName ].form;
	const formText     = formSettings.text;
	const $form        = $( `#${formSettings.formElementId}` );
	const $submit      = $( `#${formSettings.formElementId}-submit` );

	/**
	 * General form fields enhancements.
	 *
	 * Enhancements:
	 * - `[data-disallow-space]` to remove spaces
	 * - `[data-disallow-non-digit]` to remove non digits
	 */
	$( '[data-disallow-space]' ).keyup( function() {
		let $this = $( this );
		$this.val( $this.val().replace( /\s/g, '' ) );
	} );
	$( '[data-disallow-excessive-line-breaks]' ).keyup( function() {
		let $this = $( this );
		$this.val( $this.val().replace( /([\r]?\n){3,}/g, "\n\n" ) );
	} );
	$( '[data-disallow-non-digit]' ).keyup( function() {
		let $this = $( this );
		$this.val( $this.val().replace( /[^0-9]/g, '' ) );
	} );

	const uppy = Uppy( {
		allowMultipleUploads: true,
		autoProceed: true,
		locale: {
			strings: {
				dropPaste: "%{browse}",
				browse: "Browse",
			},
		}
	} );

	uppy.use( Dashboard, {
		disableStatusBar: true,
		inline: true,
		proudlyDisplayPoweredByUppy: false,
		target: `#${formSettings.uploadElementId}`,
		height: 300,
		width: '100%',
	} );

	uppy.use( XHRUpload, {
		endpoint: formSettings.endpoint,
		fieldName: 'files',
		formData: true,
	} );

	uppy.setMeta( {
		action: formSettings.ajaxActionForImageUpload,
		salt: document.getElementById( formSettings.saltElementId ).value,
	} );

	uppy.on( 'upload', ( result ) => {
		$form.attr( 'data-image-upload-status', 'uploading' );
		$submit
			.data( 'data-previous-value', $submit.val() )
			.val( formText.waitForImageUpload )
		;
	} );

	uppy.on( 'complete', ( result ) => {
		$form.attr( 'data-image-upload-status', 'uploaded' );
		$submit
			.val( $submit.data( 'data-previous-value' ) )
			.removeAttr( 'data-previous-value' )
		;
	} );

	const validator = $form.validate( {
		submitHandler: () => {
			// Do nothing if images upload not completed.
			if ( 'uploading' === $form.attr( 'data-image-upload-status' ) ) {
				$submit.val( formText.waitForImageUpload );
				return;
			}

			// De nothing is form submission is currently processing
			if ( 'processing' === $form.attr( 'data-ad-submission-status' ) ) {
				return;
			}

			// De nothing is form is submission was completed
			if ( 'complete' === $form.attr( 'data-ad-submission-status' ) ) {
				return;
			}

			$form.attr( 'data-ad-submission-status', 'processing' );

			// Submit form.
			$form.animate( { opacity : .5 } );
			$submit
				.val( formText.submitting )
				.addClass( 'btn-info' )
				.removeClass( 'btn-primary' )
				.removeClass( 'btn-danger' )
			;
			// Trick to submit disabled inputs.
			var disabled = $form.find( ':disabled' ).prop( 'disabled', false );
			var serialized = $form.serialize();
			disabled.prop( 'disabled', true );
			// The actual submission.
			$.post( formSettings.endpoint, serialized )
				.done( function( response ) {
					if( response < 0 ) {
						$form.attr( 'data-ad-submission-status', 'failed' );
						$form.animate( { opacity : 1 } );
						$submit
							.val( formText.fixErrors )
							.addClass( 'btn-danger' )
							.removeClass( 'btn-primary' )
						;
					} else {
						$form.attr( 'data-ad-submission-status', 'complete' );
						$form.replaceWith( `<div>${formText.submitSuccessTitleHtml}${formText.submitSuccessContentHtml}</div>` );
					}
				} )
				.fail( function() {
					$form.animate( { opacity : 1 } );
				} )
			;
		},
		errorElement: "em",
		errorClass: 'invalid-feedback',
		validClass: 'valid-feedback',
		errorPlacement: function ( error, element ) {
			$( element ).parent().append(
			error.addClass( "feedback" )
				);
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).parents( '.form-group' )
				.addClass( 'has-feedback' )
				.addClass( 'has-error' )
			;
		},
		unhighlight: function (element, errorClass, validClass) {
			$( element ).parents( '.form-group' )
				.removeClass( 'has-feedback' )
				.removeClass( 'has-error' )
			;
		},
	} );

	$( 'input,select,textarea', $form ).filter( ':visible' ).on( 'blur change keyup', function() {
		$( this ).valid();
		if ( ! validator.numberOfInvalids() ) {
			$submit
				.val( formText.submit )
				.addClass( 'btn-primary' )
				.removeClass( 'btn-danger' )
				.removeClass( 'btn-info' )
			;
		}
	} );

	var updateSelect = function( $select ) {
		// Disable/Enable.
		$( 'option', $select ).prop( 'disabled', false );
		$( 'option' ).filter( function() { return $( this ).data( 'disabled-by-group' ); } ).prop( 'disabled', true );
		$( 'option' ).filter( function() { return $( this ).data( 'disabled-by-scope' ); } ).prop( 'disabled', true );
		$( $select ).trigger( 'select:onUpdate' )
	}

	// Handle scoped selects.
	$('select[data-controls]').each( function( index, element ) {

		var $this = $( element );
		var $controlled = $this.data( 'controls' ) ? $( '#' + $this.data( 'controls' ) ) : false;

		// Handle change.
		$this.change( function() {
			var slug = $( ':selected', $this ).data( 'slug' );

			// Enable all options then disable invalid.
			$( 'option', $controlled )
				.data( 'disabled-by-scope', false )
				.filter( `[value!=""][data-scope!=${slug}]` )
				.data( 'disabled-by-scope', true )
			;

			if ( $( ':selected', $controlled ).val() ) {
				$controlled.val( null ).change();
			}

			// Show/hide contolled.
			$controlled.prop( 'disabled', ! slug );

			updateSelect( $controlled );
		} );

		// Disable controlled at first.
		$controlled.prop( 'disabled', true );

		// First verification.
		if ( $this.val() ) {
			$this.change();
		}
	} );

	// Disable options with same group.
	$( 'select[data-group-by]' ).change( function() {
		var $this = $( this );
		var groupBy = $this.data( 'group-by' ); // e.g.: "specification".
		var groupsUsed = [];
		// Fill groups.
		$( 'option:selected', $this ).each( function() {
			groupsUsed.push( $( this ).data( groupBy ) );
		} );
		// Show all.
		$( 'option', $this ).data( 'disabled-by-group', false )
		// Hide invalid.
		$( 'option:not(:selected)', $this ).filter( function() {
			var $this = $( this );
			var $select = $this.parent( 'select' );
			var group = $this.data( groupBy ); // e.g.: "a1fa277" (for "Type").
			return groupsUsed.indexOf( group ) > -1;
		} ).data( 'disabled-by-group', true );
		updateSelect( $this );
	} );

	// Uppy.
	$('html > head').append( `<style>
		.uppy-DashboardAddFiles {
			border-width: 5px !important;
		}
		.uppy-Dashboard-dropFilesHereHint {
			border-width: 5px !important;
		}
	` );
} )();