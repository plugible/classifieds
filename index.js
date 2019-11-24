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
const form = $( '#' + appSettings.formElementId );

// Not in a form page.
if ( form.length ) {
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
		target: '#' + appSettings.uploadElementId,
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
		form.attr( 'data-image-upload-status', 'uploading' );
		$( '#submit', form )
			.data( 'data-previous-value', $( '#submit', form ).val() )
			.val( appText.waitForImageUpload )
		;
	} );

	uppy.on( 'complete', ( result ) => {
		form.attr( 'data-image-upload-status', 'uploaded' );
		$( '#submit', form )
			.val( $( '#submit', form ).data( 'data-previous-value' ) )
			.removeAttr( 'data-previous-value' )
		;
	} );

	const validator = form.validate( {
		submitHandler: () => {
			// Do nothing if images upload not completed.
			if ( 'uploading' === form.attr( 'data-image-upload-status' ) ) {
				$( '#submit', form ).val( appText.waitForImageUpload );
				return;
			}

			// De nothing is form submission is currently processing
			if ( 'processing' === form.attr( 'data-ad-submission-status' ) ) {
				return;
			}

			// De nothing is form is submission was completed
			if ( 'complete' === form.attr( 'data-ad-submission-status' ) ) {
				return;
			}

			form.attr( 'data-ad-submission-status', 'processing' );

			// Submit form.
			form.animate( { opacity : .5 } );
			$( '#submit', form )
				.val( appText.submitting )
				.addClass( 'btn-info' )
				.removeClass( 'btn-primary' )
				.removeClass( 'btn-danger' )
			;
			$.post( appSettings.endpoint, form.serialize() )
				.done( function( response ) {
					switch( response ) {
						case '-1':
							form.attr( 'data-ad-submission-status', 'failed' );
							form.animate( { opacity : 1 } );
							$( '#submit', form )
								.val( appText.fixErrors )
								.addClass( 'btn-danger' )
								.removeClass( 'btn-primary' )
							;
							break;
						default:
							form.attr( 'data-ad-submission-status', 'complete' );
							form.replaceWith( `<div>${appText.submitSuccessTitleHtml}${appText.submitSuccessContentHtml}</div>` );
					}
				} )
				.fail( function() {
					form.animate( { opacity : 1 } );
				} )
			;
		},
		errorElement: "em",
		errorPlacement: function ( error, element ) {
			error.addClass( "help-block" ).appendTo( element.parent() );
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".form-group" ).addClass( "has-error" ).removeClass( "has-success" );
		},
		unhighlight: function (element, errorClass, validClass) {
			$( element ).parents( ".form-group" ).addClass( "has-success" ).removeClass( "has-error" );
		},
	} );

	$( 'input,select,textarea', form ).filter( ':visible' ).on( 'blur change keyup', function() {
		$( this ).valid();
		if ( ! validator.numberOfInvalids() ) {
			$( '#submit', form )
				.val( appText.submit )
				.addClass( 'btn-primary' )
				.removeClass( 'btn-danger' )
				.removeClass( 'btn-info' )
			;
		}
	} );

	// Remove spaces.
	$( '[data-disallow-space]', form ).keyup( function() {
		let $this = $( this );
		$this.val( $this.val().replace( /\s/g, '' ) );
	} );

	// Remove non-digits.
	$( '[data-disallow-non-digit]', form ).keyup( function() {
		let $this = $( this );
		$this.val( $this.val().replace( /[^0-9]/g, '' ) );
	} );

	// Select 2
	$( 'select[data-use-select2]', form ).select2();
	$('html > head').append( '<style>\
		.select2-container { width: 100% !important; }\
		.select2-container .select2-selection--single { height: 48px !important; }\
		.select2-selection__arrow { height: 48px !important; }\
		.select2-selection__rendered { line-height: 48px !important; }\
		.uppy-DashboardAddFiles { border-width: 5px !important; }\
	' );
}

// lightGallery
require( 'lightgallery.js' );
require( 'lg-fullscreen.js' );
require( 'lg-thumbnail.js' );
require( 'lg-video.js' );
require( 'lg-zoom.js' );
require( 'lightgallery.js/dist/css/lightgallery.min.css' );

$( '.pl_classified_gallery' ).each( function() {
	lightGallery( this, {
		thumbnail: true,
		width: function() {
			return ( $(window).width() - 50 ) + 'px';
		},
		height: function() {
			return ( $(window).height() - 50 ) + 'px';
		},
		download: false,
	} );
} );

$( 'html > head').append( '<style>\
	.lg  { border-width: 10px}\
	.lg  { border-color: rgba( 255, 255, 255, 0.25); }\
	.lg  { border-style: solid; }\
	.lg  { background: black; }\
	.lg  { padding: 10px; }\
	.lg-backdrop.in { opacity: 0.85; }\
</style>' );	