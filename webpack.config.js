const path = require( 'path' );
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

const {mode} = require( './src/js/settings.js' );
const devtool = (mode === 'development')
	? 'eval'
	: 'source-map'
;

module.exports = {
	mode,
	devtool,
	entry: "./src/js/main.js",
	output: {
		filename: '[name].bundle.js',
		chunkFilename: '[name].bundle.js',
		path: path.resolve( __dirname, 'dist/js' ),
		publicPath: '/wp-content/plugins/classifieds-by-plugible/dist/js/',
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
	plugins: [
		new CleanWebpackPlugin(),
	],
};
