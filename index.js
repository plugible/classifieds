const Uppy = require( '@uppy/core' );
const Dashboard = require( '@uppy/dashboard' );
const XHRUpload = require( '@uppy/xhr-upload' );
require( '@uppy/core/dist/style.css' );
require( '@uppy/dashboard/dist/style.css' );

import $ from 'jquery';
import validate from 'jquery-validation';

const settingsObjectName = 'classifieds';
const appSettings = window[ settingsObjectName ];
const form = $( '#' + appSettings.formElementId );

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
	$( '#submit', form ).val( appSettings.text.waitForImageUpload );
} );

uppy.on( 'complete', ( result ) => {
	form.attr( 'data-image-upload-status', 'uploaded' );
	$( '#submit', form ).val( appSettings.text.submit );
} );


form.validate( {
	submitHandler: () => {
		form.attr( 'data-form-status', 'submitted' );

		// Do nothing if images upload not completed.
		if ( 'uploading' === form.attr( 'data-image-upload-status' ) ) {
			$( '#submit', form ).val( appSettings.text.waitForImageUpload );
			return;
		}
		// Submit form.
		$( '#submit', form ).val( appSettings.text.submitting );

		form.animate( { opacity : .5 } );
		$.post( appSettings.endpoint, form.serialize(), function() {
			form.fadeOut( 'fast' );
		} );
	},
	errorElement: "em",
	errorPlacement: function ( error, element ) {
		error.addClass( "help-block" ).insertAfter( element );
	},
	highlight: function ( element, errorClass, validClass ) {
		$( element ).parents( ".form-group" ).addClass( "has-error" ).removeClass( "has-success" );
	},
	unhighlight: function (element, errorClass, validClass) {
		$( element ).parents( ".form-group" ).addClass( "has-success" ).removeClass( "has-error" );
	},
} );

$( 'input,select,textarea', form ).on( 'blur change keyup', function() {
	$( this ).valid();
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

// Debugging.
if ( appSettings.debug ) {	
	uppy.on( 'complete', ( result ) => {
		console.debug( 'Upload Complete. Results: ', result.successful );
	} );
}
