const webpack = require( 'webpack' );
const NODE_ENV = process.env.NODE_ENV || 'development';
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );

module.exports = {
	mode: NODE_ENV,
	entry: './src/index.js',
	output: {
		path: __dirname,
		filename: './build/block.js',
		chunkFilename: './build/[name].js'
	},
	module: {
		rules: [
			{
				test: /.js?$/,
				use: [ {
					loader: 'babel-loader',
					options: {
						presets: [ '@babel/preset-env' ],
						plugins: [
							'@babel/plugin-transform-async-to-generator',
							'@babel/plugin-proposal-object-rest-spread',
							[
								'@babel/plugin-transform-react-jsx', {
									'pragma': 'wp.element.createElement'
								}
							]
						]
					}
				},
				'eslint-loader' ],
				exclude: /node_modules/
			},
			{
				test: /\.(css|scss)$/,
				use: [ {
					loader: MiniCssExtractPlugin.loader
				},
				'css-loader',
				{
					loader: 'postcss-loader',
					options: {
						plugins: [
							require( 'autoprefixer' )
						]
					}
				},
				{
					loader: 'sass-loader',
					query: {
						outputStyle:
							'production' === process.env.NODE_ENV ? 'compressed' : 'nested'
					}
				} ]
			},
			{
				test: /\.(png|jpe?g|gif)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: './[name].[ext]'
						}
					}
				]
			}
		]
	},
	optimization: {
		splitChunks: {
			cacheGroups: {
				handsontable: {
					name: 'handsontable',
					test: /[\\/]node_modules[\\/]handsontable/,
					chunks: 'all'
				}
			}
		}
	},
	plugins: [
		new webpack.DefinePlugin({
			'process.env.NODE_ENV': JSON.stringify( NODE_ENV )
		}),
		new MiniCssExtractPlugin({
			filename: './build/block.css',
			chunkFilename: './build/[name].css'
		})
	]
};
