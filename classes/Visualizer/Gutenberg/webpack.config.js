const webpack = require('webpack');
const NODE_ENV = process.env.NODE_ENV || 'development';
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    externals: {
        'lodash': 'lodash'
    },
    mode: NODE_ENV,
    entry: {
    	block: './src/index.js'
    },
    output: {
        path: __dirname,
		filename: './build/[name].js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
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
                exclude: /node_modules/
            },
            {
                test: /\.(css|scss)$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    'css-loader',
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: [
                                    require('autoprefixer')
                                ]
                            }
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            implementation: require('sass'),
                            sassOptions: {
                                outputStyle: NODE_ENV === 'production' ? 'compressed' : 'expanded'
                            }
                        }
                    }
                ]
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
					chunks: 'all',
					enforce: true
				}
			}
		}
	},
    plugins: [
        new webpack.DefinePlugin({
            'process.env.NODE_ENV': JSON.stringify(NODE_ENV)
        }),
        new MiniCssExtractPlugin({
            filename: './build/[name].css'
        }),
        new TerserPlugin({
            terserOptions: {
                format: {
                    comments: false  // Disable comments
                }
            },
            extractComments: false  // Prevents LICENSE.txt generation
        })
    ]
};
