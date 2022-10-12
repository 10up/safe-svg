const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
    ...defaultConfig,
	output: {
		...defaultConfig.output,
		path: path.resolve( process.cwd(), 'dist' ),
	},
    entry: {
        'safe-svg-block': [ './includes/blocks/safe-svg/index.js' ],
        'safe-svg-block-frontend': [ './includes/blocks/safe-svg/frontend.js' ],
    }
};
