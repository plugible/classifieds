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
	const $gallery = $( `.${appSettings.objectName}_gallery` );
	if ( $gallery.length ) {
		( async () => await import(/* webpackChunkName: "gallery" */ './gallery.js' ))();
	}

	/**
	 * Gallery Enhanced.
	 */
	const $galleryEnhanced = $( `.${appSettings.objectName}_gallery_enhanced` );
	if ( $gallery.length ) {
		( async () => await import(/* webpackChunkName: "gallery-enhanced" */ './gallery-enhanced.js' ))();
	}

	/**
	 * Filters.
	 */
	const $filters = $( '#' + appSettings.filters.filtersElementId );
	if ( $filters.length ) {
		(async () => await import(/* webpackChunkName: "filters" */ './filters.js' ))();
	}
} );
