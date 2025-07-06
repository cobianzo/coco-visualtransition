import defaultConfig from '@wordpress/scripts/config/webpack.config.js';

export default {
    ...defaultConfig,
    externals: {
        ...defaultConfig.externals,
        '@wordpress/i18n': ['wp', 'i18n'],
    },
};
