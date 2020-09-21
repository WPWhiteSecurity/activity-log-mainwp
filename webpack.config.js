/**
 * Webpack Configuration File.
 */
const path = require( 'path' );
const autoprefixer = require( 'autoprefixer' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const FixStyleOnlyEntriesPlugin = require( 'webpack-fix-style-only-entries' );

const PluginStylesheetsConfig = ( mode ) => {
	return [
		MiniCssExtractPlugin.loader,
		'css-loader',
		{
			loader: 'postcss-loader',
			options: {
				ident: 'postcss',
				plugins: [
					autoprefixer({
						browsers: [
							'>1%',
							'last 4 versions',
							'Firefox ESR',
							'not ie < 9' // React doesn't support IE8 anyway
						],
						flexbox: 'no-2009'
					})
				]
			}
		},
		{
			loader: 'resolve-url-loader',
			options: {}
		},
		{
			loader: 'sass-loader',
			options: {
				outputStyle: 'production' === mode ? 'compressed' : 'nested'
			}
		}
	];
};

const recursiveIssuer = ( m ) => {
	if ( m.issuer ) {
		return recursiveIssuer( m.issuer );
	} else if ( m.name ) {
		return m.name;
	} else {
		return false;
	}
};

module.exports = ( env, options ) => {
	const mode = options.mode;
	//const suffix = 'production' === mode ? '.min' : '';

	const config = {
		watch: 'development' === mode ? true : false,
		entry: {
			'index': './assets/js/src/index.js',
			'wizard': './assets/js/src/mwpal-setup-wizard.js',
			'search/build.search': './assets/js/src/search/search.js',
			'reports/build.reports': './assets/js/src/reports/reports.js',
			'styles.build': './assets/css/src/styles.scss',
			'mwpal-setup-wizard.build': './assets/css/src/mwpal-setup-wizard.scss'
		},
		output: {
			path: path.resolve( __dirname, 'assets/js/dist' ),
			filename: `[name].js`
		},
		optimization: {
			splitChunks: {
				cacheGroups: {
					settingsStyles: {
						name: 'styles.build',
						test: ( m, c, entry = 'styles' ) => 'CssModule' === m.constructor.name && recursiveIssuer( m ) === entry,
						chunks: 'all',
						enforce: true
					},
					fileChangesStyles: {
						name: 'mwpal-setup-wizard.build',
						test: ( m, c, entry = 'mwpal-setup-wizard' ) => 'CssModule' === m.constructor.name && recursiveIssuer( m ) === entry,
						chunks: 'all',
						enforce: true
					}
				}
			},
			minimize: false
		},
		devtool: 'development' === mode ? 'cheap-eval-source-map' : false,
		module: {
			rules: [
				{
					test: /\.(js|jsx|mjs)$/,
					exclude: /node_modules/,
					use: {
						loader: 'babel-loader',
						options: {
							babelrc: false,
							presets: [ '@babel/preset-env' ],
							cacheDirectory: true,
							'plugins': [ '@babel/plugin-transform-runtime' ]
						}
					}
				},
				{
					test: /\.s?css$/,
					use: PluginStylesheetsConfig( mode )
				},
				{
					test: /\.svg/,
					use: {
						loader: 'svg-url-loader'
					}
				}
			]
		},
		plugins: [
			new MiniCssExtractPlugin({
				filename: `../../css/dist/[name].css`
			}),
			new FixStyleOnlyEntriesPlugin()
		]
	};

	return config;
};
