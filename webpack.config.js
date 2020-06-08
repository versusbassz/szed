const path = require('path');
const webpack = require('webpack');

// eslint-disable-next-line no-unused-vars
module.exports = (env, argv) => {
  const isModeProduction = env.mode === 'production';

  const mode = isModeProduction ? 'production' : 'development';

  // Devtool
  const sourceMaps = isModeProduction ? 'source-map' : 'inline-source-map';

  // Plugins
  const plugins = [];

  plugins.push(new webpack.ProvidePlugin({
    $: 'jquery',
    jQuery: 'jquery',
  }));

  return {
    mode,
    entry: './assets/js/sizes-editor.js',
    output: {
      filename: 'sizes-editor.build.js',
      path: path.resolve(__dirname, 'assets/build'),
    },
    devtool: sourceMaps,
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: '/node_modules/',
          include: [
            path.resolve(__dirname, 'assets/js'),
          ],
          use: {
            loader: 'babel-loader',
          },
        },
      ],
    },
    externals: {
      jquery: 'jQuery',
    },
    plugins,
  };
};
