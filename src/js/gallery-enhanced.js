(async function () {
	const $ = window.jQuery;
	const appSettings = window[ require( './settings.js' ).settingsObjectName ];

	await import( /* webpackChunkName: "lightgallery.all" */ 'lightgallery.js/dist/css/lightgallery.min.css' );
	await import( /* webpackChunkName: "lightgallery.all" */ 'lightgallery.js' );
	await import( /* webpackChunkName: "lightgallery.all" */ 'lg-fullscreen.js' );
	await import( /* webpackChunkName: "lightgallery.all" */ 'lg-thumbnail.js' );
	await import( /* webpackChunkName: "lightgallery.all" */ 'lg-zoom.js' );

	const $gallery = $( `.${appSettings.objectName}_gallery_enhanced` );

	$gallery.each( function() {
		lightGallery( this, {
			download: false,
			hideBarsDelay: 3000,
			mode: 'lg-fade',
			selector: `.${appSettings.objectName}_gallery_enhanced > div:not(.remaining-images-count)`,
			thumbnail: true,
		} );
	} );

	$( 'html > head').append( `<style>
		.${appSettings.objectName}_gallery_enhanced > div {
			cursor: pointer;
		}
		.lg  {
			background: black;
			border-width: 10px;
			border-color: rgba( 255, 255, 255, 0.25);
			border-style: solid;
		}
		.lg-outer {
			z-index: 100000;
		}
		html[dir=rtl] .lg-icon {
			float: left;
		}
		html[dir=rtl] .lg-toggle-thumb {
			left: 20px;
			right: initial;
		}
		html[dir=rtl] .lg-thumb-item {
			float: right;
		}
		html[dir=rtl] #lg-counter {
			padding-left: 0;
			padding-right: 20px;
		}
		html[dir=rtl] .lg-actions .lg-prev {
			right: 20px;
			left: initial;
			transform: rotateY( 180deg );
		}
		html[dir=rtl] .lg-actions .lg-next {
			right: initial;
			left: 20px;
			transform: rotateY( 180deg );
		}
	</style>` );
} )();
