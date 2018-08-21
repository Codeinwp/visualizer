var webpack = require( 'webpack' ),
	NODE_ENV = process.env.NODE_ENV || 'development',
	webpackConfig = {
	entry: './js/gutenberg/block.js',
	output: {
		path: __dirname,
		filename: './js/gutenberg/block.build.js',
	},
	module: {
		loaders: [
			{
				test: /.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
			},
		],
	},
	plugins: [
		new webpack.DefinePlugin( {
			'process.env.NODE_ENV': JSON.stringify( NODE_ENV )
		} ),
	]
};

if ( 'production' === NODE_ENV ) {
	webpackConfig.plugins.push( new webpack.optimize.UglifyJsPlugin() );
}

module.exports = webpackConfig;