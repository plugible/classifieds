(window.jQuery)( ( $ ) => {

	const appSettings = window[ require( './settings.js' ).settingsObjectName ];

	/**
	 * Form.
	 */
	const $form = $( '#' + appSettings.form.formElementId );
	if ( $form.length ) {
		(async () => await import(/* webpackChunkName: "form" */ './form.js' ))();
	}

	/**
	 * Selects.
	 */
	const $select = $( 'select[data-use-select2]' );
	if ( $select.length ) {
		(async () => await import(/* webpackChunkName: "select" */ './select.js'))();
	}

	/**
	 * Gallery.
	 */
	const $gallery = $( `.${appSettings.objectName}_gallery_enhanced` );
	if ( $gallery.length ) {
		( async () => await import(/* webpackChunkName: "gallery" */ './gallery.js' ))();
	}
} );
