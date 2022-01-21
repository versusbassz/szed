module.exports = {
  env: {
    browser: true
  },
  'extends': 'airbnb-base',
  ignorePatterns: [
    'node_modules/*',
    'vendor/*',

    'assets/build/*',
    'custom/*',
    'dist/*',
    '.eslintrc.js',
  ],
  globals: {
    'szed': true,
    '$': true,
    'jQuery': true,
    'wp': true,
  },
};
