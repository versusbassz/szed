const path = require('path');
const webpack = require('webpack');

module.exports = function(env, argv) {

    let is_mode_production = env.mode === 'production';
    let is_mode_dev = ! is_mode_production;

    let mode = is_mode_production ? 'production' : 'development';

    // Devtool
    let source_maps = is_mode_production ? 'source-map' : 'inline-source-map';

    // Plugins
    let plugins = [];

    plugins.push(new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery'
    }));

    return {
        mode: mode,
        entry: './assets/js/sizes-editor.js',
        output: {
            filename: 'sizes-editor.build.js',
            path: path.resolve(__dirname, 'assets/build'),
        },
        devtool: source_maps,
        module: {
            rules : [
                {
                    test: /\.js$/,
                    exclude: '/node_modules/',
                    include: [
                        path.resolve(__dirname, 'assets/js'),
                    ],
                    use: {
                        loader: 'babel-loader',
                    }
                }
            ]
        },
        externals: {
            jquery: 'jQuery',
        },
        plugins: plugins,
    }
};
