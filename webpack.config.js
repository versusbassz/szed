const path = require('path');

module.exports = {
    mode: 'development',
    entry: './assets/js/sizes-editor.js',
    output: {
        filename: 'sizes-editor.build.js',
        path: path.resolve(__dirname, 'assets/build'),
    },
};
