
(async function () {
	const { default: $ } = await import(/* webpackChunkName: "jquery" */ 'jquery');

	const filtersSettings = window[ require( './settings.js' ).settingsObjectName ].filters;
	const filtersSelector = `#${filtersSettings.filtersElementId}`;
	const cssRules = [];

	for( const media in filtersSettings.perLine ) {
		let width    = 100 / filtersSettings.perLine[ media ];
		cssRules.push( `
			@media ${media} {
				${filtersSelector} > div > div {
					max-width: ${width}%;
					min-width: ${width}%;
				}
			}
		` );
	}

	$('html > head').append( `<style>
		${filtersSelector} > div {
			display: flex;
			flex-wrap: wrap;
		}
		${filtersSelector} > div > div {
			flex-grow: 1;
			padding: 1rem;
		}
		${cssRules.join( "\n" )}
	` );
} )();
