const path = require('path');
const webpack = require('webpack');

module.exports = {
    mode: 'development',
    entry: './assets/js/sizes-editor.js',
    output: {
        filename: 'sizes-editor.build.js',
        path: path.resolve(__dirname, 'assets/build'),
    },
    externals: {
        jquery: 'jQuery',
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery'
        })
    ]
};
