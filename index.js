const Uppy = require( '@uppy/core' );
const Dashboard = require( '@uppy/dashboard' );
const XHRUpload = require( '@uppy/xhr-upload' );
require( '@uppy/core/dist/style.min.css' );
require( '@uppy/dashboard/dist/style.min.css' );

const $ = require( 'jquery' );
const validate = require( 'jquery-validation' );
const select2 = require( 'select2' );
require( 'select2/dist/css/select2.css' );
require( 'select2-bootstrap-theme/dist/select2-bootstrap.min.css' );

const settingsObjectName = 'classifieds';
const appSettings = window[ settingsObjectName ];
const appText = appSettings.text;
const $form = $( '#' + appSettings.formElementId );

// Not in a form page.
if ( $form.length ) {

	const $submit = $( `#${appSettings.formElementId}-submit` );

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
		target: `#${appSettings.uploadElementId}`,
		height: 300,
		width: '100%',
	} );

	uppy.use( XHRUpload, {
		endpoint: appSettings.endpoint,
		fieldName: 'files',
		formData: true,
	} );

	uppy.setMeta( {
		action: appSettings.ajaxActionForImageUpload,
		salt: document.getElementById( appSettings.saltElementId ).value,
	} );

	uppy.on( 'upload', ( result ) => {
		$form.attr( 'data-image-upload-status', 'uploading' );
		$submit
			.data( 'data-previous-value', $submit.val() )
			.val( appText.waitForImageUpload )
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
				$submit.val( appText.waitForImageUpload );
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
				.val( appText.submitting )
				.addClass( 'btn-info' )
				.removeClass( 'btn-primary' )
				.removeClass( 'btn-danger' )
			;
			// Trick to submit disabled inputs.
			var disabled = $form.find( ':disabled' ).prop( 'disabled', false );
			var serialized = $form.serialize();
			disabled.prop( 'disabled', true );
			// The actual submission.
			$.post( appSettings.endpoint, serialized )
				.done( function( response ) {
					if( response < 0 ) {
						$form.attr( 'data-ad-submission-status', 'failed' );
						$form.animate( { opacity : 1 } );
						$submit
							.val( appText.fixErrors )
							.addClass( 'btn-danger' )
							.removeClass( 'btn-primary' )
						;
					} else {
						$form.attr( 'data-ad-submission-status', 'complete' );
						$form.replaceWith( `<div>${appText.submitSuccessTitleHtml}${appText.submitSuccessContentHtml}</div>` );
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
				.val( appText.submit )
				.addClass( 'btn-primary' )
				.removeClass( 'btn-danger' )
				.removeClass( 'btn-info' )
			;
		}
	} );

	// Remove spaces.
	$( '[data-disallow-space]', $form ).keyup( function() {
		let $this = $( this );
		$this.val( $this.val().replace( /\s/g, '' ) );
	} );

	// Remove non-digits.
	$( '[data-disallow-non-digit]', $form ).keyup( function() {
		let $this = $( this );
		$this.val( $this.val().replace( /[^0-9]/g, '' ) );
	} );

	// Select 2.
	$( 'select[data-use-select2]:not([multiple])', $form ).select2();
	$( 'select[data-use-select2][multiple]', $form ).select2( {
		closeOnSelect: false,
	} );
	$('html > head').append( '<style>\
		.select2-container .select2-selection--single { height: 35px; }\
		.select2-container .select2-selection--single { line-height: 55px; }\
		.select2-container .select2-selection--single .select2-selection__arrow { height: 35px; }\
		.select2-container .select2-selection--single { border: 1px solid #ced4da; }\
		.select2-container { width: 100% !important; }\
		.select2-container [role=option][aria-disabled=true] { display: none; }\
		.select2-container [aria-multiselectable=true] [role=option][aria-selected=true] { display: none; }\
		body.admin-bar .select2-container--open .select2-dropdown { top: 32px; } \
	' );
	var updateSelect = function( $select ) {
		// Disable/Enable.
		$( 'option', $select ).prop( 'disabled', false );
		$( 'option' ).filter( function() { return $( this ).data( 'disabled-by-group' ); } ).prop( 'disabled', true );
		$( 'option' ).filter( function() { return $( this ).data( 'disabled-by-scope' ); } ).prop( 'disabled', true );
		// Refresh to hide removed.
		if ( $select.data( 'select2' ).isOpen() ) {
			$select.select2( 'close' );
			$select.select2( 'open' );
		}
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
	$('html > head').append( '<style>\
		.uppy-DashboardAddFiles { border-width: 5px !important; }\
		.uppy-Dashboard-dropFilesHereHint { border-width: 5px !important; }\
	' );
}

// lightGallery
require( 'lightgallery.js' );
require( 'lg-fullscreen.js' );
require( 'lg-thumbnail.js' );
require( 'lg-video.js' );
require( 'lg-zoom.js' );
require( 'lightgallery.js/dist/css/lightgallery.min.css' );

$( '.pl_classified_gallery_enhanced' ).each( function() {
	lightGallery( this, {
		download: false,
		thumbnail: true,
		hideBarsDelay: 3000,
	} );
} );

$( 'html > head').append( '<style>\
	.lg  { border-width: 10px}\
	.lg  { border-color: rgba( 255, 255, 255, 0.25); }\
	.lg  { border-style: solid; }\
	.lg  { background: black; }\
</style>' );	