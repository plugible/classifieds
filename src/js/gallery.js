( async function () {

	const $           = window.jQuery;
	const appSettings = window[ require( './settings.js' ).settingsObjectName ];

	const $galleries  = $( `.${appSettings.objectName}_gallery` );

	/**
	 * Converts text to data URL.
	 * @param  string text   Text.
	 * @param  string width  Image width.
	 * @param  string height Image height.
	 * @return string        Image data URL.
	 */
	const text2pngURL = ( text, width, height ) => {
		/*
		 *Create SVG and prepare it for drawing in 2d.
		 */
		var canvas = $( `<canvas width="${width}" height="${height}">` ).get( 0 );
		var context = canvas.getContext('2d');

		/**
		 * Draw Background.
		 */
		context.fillStyle = '#444';
		context.fillRect( 0, 0, width, height );

		/**
		 * Draw Text.
		 */
		context.textAlign = 'center';
		context.fillStyle = '#FFF';
		context.font      = `normal ${ width / 3 }px Sans`;
		context.fillText( text
			, width / 2
			, height * 2 / 3
		);

		/**
		 * Return data URL.
		 */
		return canvas.toDataURL( 'image/png' );
	};

	/**
	 * Add remaining image count.
	 */
	$galleries.each( function() {
		const $this     = $( this );
		const remaining = $this.data( 'remaining-images-count' );
		if ( ! remaining ) {
			return;
		}
		const url    = $( 'a', $this).first().attr( 'href' );
		const width  = $( 'img', $this).first().width()
		const height = $( 'img', $this).first().height()
		const src    = text2pngURL( `+${remaining}`, width, height );

		if ( url ) {
			$this.append( $( `<div class="remaining-images-count"><a href="${url}"><img src="${src}"></a></div>` ) );
		} else {
			$this.append( $( `<div class="remaining-images-count"><img src="${src}"></div>` ) );
		}
	} );

	$( 'html > head').append( `<style>
		.${appSettings.objectName}_gallery > div {
			display: inline-block;
			margin-bottom: .5rem;
		}
	</style>` );
}() );
