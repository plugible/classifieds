const path = require( 'path' );

module.exports = {
	mode: 'development',
	entry: "./",
	output: {
		filename: "classifieds.js",
		path: path.resolve( __dirname, 'public/js/' ),
	},
	module: {
		rules: [
			{
				test: /\.css$/i,
				use: ['style-loader', 'css-loader'],
			},
		],
	},
};
