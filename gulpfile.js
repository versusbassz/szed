const gulp = require('gulp');
const del = require('del');
const concat = require('gulp-concat');
const pump = require('pump');
const scss = require('gulp-sass')(require('sass'));
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const sourcemaps = require('gulp-sourcemaps');
const stylelint = require('gulp-stylelint');

const { exec } = require('child_process');

const assets = {};
assets.path = './assets';
assets.css = `${assets.path}/styles`;
assets.js = `${assets.path}/js`;
assets.build = `${assets.path}/build`;

// Styles
function cssAdmin(cb) {
  const plugins = [
    autoprefixer,
  ];

  pump([
    gulp.src([
      './assets/styles/editor-page.scss',
    ]),
    sourcemaps.init(),
    scss().on('error', scss.logError),
    concat('editor-page.css'),
    postcss(plugins),
    sourcemaps.write('.'),
    gulp.dest(assets.build),
  ], cb);
}

// Cropper
function cropper(cb) {
  pump([
    gulp.src([
      './node_modules/cropperjs/dist/cropper.css',
    ]),
    gulp.dest(assets.build),
  ], cb);
}

// Fancybox
function fancybox(cb) {
  pump([
    gulp.src([
      './node_modules/@fancyapps/fancybox/dist/jquery.fancybox.min.css',
      './node_modules/@fancyapps/fancybox/dist/jquery.fancybox.min.js',
    ]),
    gulp.dest(assets.build),
  ], cb);
}

const webpack = (cb) => {
  exec('npx webpack --env mode=development', (err, stdout, stderr) => {
    /* eslint-disable no-console */
    console.log(stdout);
    console.log(stderr);
    /* eslint-enable no-console */
    cb(err);
  });
};

const webpackProduction = (cb) => {
  exec('npx webpack --env mode=production', (err, stdout, stderr) => {
    /* eslint-disable no-console */
    console.log(stdout);
    console.log(stderr);
    /* eslint-enable no-console */
    cb(err);
  });
};

// Lint css
function lintCss(cb) {
  pump([
    gulp.src([
      `${assets.css}/**/*.scss`,
    ]),
    stylelint({
      reporters: [
        {
          formatter: 'string',
          console: true,
        },
      ],
    }),
  ], cb);
}

// Lint css
function lintCssFix(cb) {
  pump([
    gulp.src([
      `${assets.css}/**/*.scss`,
    ]),
    stylelint({
      reporters: [
        {
          formatter: 'string',
          console: true,
        },
      ],
      fix: true,
    }),
    gulp.dest(assets.css),
  ], cb);
}

// Tech tasks
function clean() {
  return del(`${assets.build}/*`);
}

const build = gulp.series(
  clean,
  cssAdmin,
  cropper,
  fancybox,
  webpack,
);

const release = gulp.series(
  clean,
  cssAdmin,
  cropper,
  fancybox,
  webpackProduction,
);

// Watchers
const watchCss = () => {
  gulp.watch([
    `${assets.css}/**/*.scss`,
  ], gulp.series(cssAdmin));
};

const watchJs = () => {
  gulp.watch([
    `${assets.js}/**/*.js`,
  ], gulp.series(webpack));
};

const watchAll = gulp.parallel(
  watchCss,
  watchJs,
);

exports.default = build;
exports.build = build;
exports.release = release;
exports.watch = watchAll;

exports.webpack = webpack;
exports.webpack_production = webpackProduction;
exports['lint-css'] = lintCss;
exports['lint-css-fix'] = lintCssFix;
