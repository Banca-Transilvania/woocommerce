const path          = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const WooCommerceDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );

// WooCommerce dependency maps
const wcDepMap = {
	'@woocommerce/blocks-registry': ['wc', 'wcBlocksRegistry'],
	'@woocommerce/settings': ['wc', 'wcSettings']
};

const wcHandleMap = {
	'@woocommerce/blocks-registry': 'wc-blocks-registry',
	'@woocommerce/settings': 'wc-settings'
};

const requestToExternal = (request) => {
	if (wcDepMap[request]) {
		return wcDepMap[request];
	}
};

const requestToHandle = (request) => {
	if (wcHandleMap[request]) {
		return wcHandleMap[request];
	}
};

module.exports = {
	entry: {
		'blocks': './blocks/index.js' // Adjust this path to your blocks index.js
	},
	output: {
		path: path.resolve( __dirname, 'dist' ),
		filename: '[name].js', // Output each entry to a unique file
	},
	module: {
		rules: [
		{
			test: /\.js$/,
			exclude: /node_modules/,
			use: {
				loader: 'babel-loader',
				options: {
					presets: ['@babel/preset-env', '@babel/preset-react'] // Add React preset for JSX
				}
			}
		},
		{
			test: /\.css$/,
			use: ['style-loader', 'css-loader'],
		},
		]
	},
	plugins: [
	...defaultConfig.plugins.filter(
		(plugin) =>
		plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
	),
	new WooCommerceDependencyExtractionWebpackPlugin(
		{
			requestToExternal,
			requestToHandle
		}
	)
	]
};
