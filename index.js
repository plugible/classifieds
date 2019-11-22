// Require Uppy stuff.

const Uppy = require( '@uppy/core' );
const Dashboard = require( '@uppy/dashboard' );
const XHRUpload = require( '@uppy/xhr-upload' );
require( '@uppy/core/dist/style.css' );
require( '@uppy/dashboard/dist/style.css' );

// Application settings.

const settingsObjectName = 'classifieds';

// App Code.

const appSettings = window[ settingsObjectName ];

const uppy = Uppy( {
	allowMultipleUploads: false,
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
	formData: true,
} );

uppy.setMeta( {
	action: appSettings.ajaxActionForImageUpload,
	salt: document.getElementById( appSettings.saltElementId ).value,
} );

// Debugging.
if ( appSettings.debug)
uppy.on('complete', (result) => {
	console.debug('Upload Complete. Results: ', result.successful );
} )
