(async function () {
	const $ = window.jQuery;
	const appSettings = window[ require( './settings.js' ).settingsObjectName ];

	$( 'html > head').append( `<style>
		.${appSettings.objectName}_gallery{
			display: flex;
			flex-wrap: wrap;
		}
		.${appSettings.objectName}_gallery > div {
			flex-grow: 1;
			max-width: calc( 25% - 1rem );
			min-width: calc( 25% - 1rem );
			margin: .25rem;
		}
	</style>` );
} )();
