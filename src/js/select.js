(async function () {
	const { default: $ } = await import(/* webpackChunkName: "jquery" */ 'jquery');

	await import( /* webpackChunkName: "select2.all" */ 'select2/dist/css/select2.min.css' );
	await import( /* webpackChunkName: "select2.all" */ 'select2-bootstrap-theme/dist/select2-bootstrap.min.css' );
	await import( /* webpackChunkName: "select2.all" */ 'select2');


	$( 'select[data-use-select2]:not([multiple])' ).select2();
	$( 'select[data-use-select2][multiple]' ).select2( {
		closeOnSelect: false,
	} );

	$( 'select[data-use-select2]' ).on( 'select:onUpdate', function() {
		const $select = $( this );
		/**
		 * Refresh to hide disabled items.
		 */
		if ( $select.data( 'select2' ).isOpen() ) {
			$select.select2( 'close' );
			$select.select2( 'open' );
		}
	} );

	$('html > head').append( `<style>
		.select2-container .select2-selection--single {
			height: 45px;
		}
		.select2-container .select2-selection--single {
			line-height: 55px;
		}
		.select2-container .select2-selection--single .select2-selection__rendered {
			line-height: 45px;
		}
		.select2-container .select2-selection--single .select2-selection__arrow {
			height: 45px;
		}
		.select2-container {
			width: 100% !important;
		}
		.select2-container [role=option][aria-disabled=true] {
			display: none;
		}
		.select2-container [aria-multiselectable=true] [role=option][aria-selected=true] {
			display: none;
		}
		body.admin-bar .select2-container--open .select2-dropdown {
			top: 32px;
		}
	` );
} )();
