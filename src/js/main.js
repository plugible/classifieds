(window.jQuery)( ( $ ) => {

	/**
	 * Form.
	 */
	const $form = $( '#' + window[ require( './settings.js' ).settingsObjectName ].form.formElementId );
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
	const $gallery = $( '.pl_classified_gallery_enhanced' );
	if ( $gallery.length ) {
		( async () => await import(/* webpackChunkName: "gallery" */ './gallery.js' ))();
	}
} );