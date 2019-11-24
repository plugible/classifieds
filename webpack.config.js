const path = require( 'path' );

module.exports = {
	mode: 'development',
	entry: "./",
	output: {
		filename: "classifieds.js",
		path: path.resolve( __dirname, 'dist' ),
		publicPath: '/wp-content/plugins/classifieds-by-plugible/dist/',
	},
	module: {
		rules: [
			{
				test: /\.css$/i,
				use: [ 'style-loader', 'css-loader' ],
			},
			{
				test: /\.(eot|otf|svg|ttf|woff)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
				loader: 'file-loader',
				options: {
					outputPath: 'fonts',
				},
			},
			{
				test: /\.(gif|png)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
				loader: 'file-loader',
				options: {
					outputPath: 'img',
				},
			},
		],
	},
};
