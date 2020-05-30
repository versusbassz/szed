let gulp = require('gulp');
let del = require('del');
let pump = require('pump');

const exec = require( 'child_process' ).exec;

let assets = {};
assets.path = './assets';
assets.css = assets.path + '/styles';
assets.js = assets.path + '/js';
assets.build = assets.path + '/build';


// Styles
function css_admin(cb) {
    pump([
        gulp.src([
            './assets/styles/editor-page.css',
        ]),
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
    exec('npx webpack', function (err, stdout, stderr) {
        console.log(stdout);
        console.log(stderr);
        cb(err);
    });
};

// Tech tasks
function clean() {
    return del(assets.build + '/*');
}

const build = gulp.series(
    clean,
    css_admin,
    cropper,
    fancybox,
    webpack
);

// Watchers
const watch__css = () => {
    gulp.watch([
        assets.css + '/**/*.css',
    ], gulp.series(css_admin));
};

const watch__js = () => {
    gulp.watch([
        assets.js + '/**/*.js',
    ], gulp.series(webpack));
};

const watch__all = gulp.parallel(
    watch__css,
    watch__js,
);


exports.default = build;
exports.build = build;
exports.watch = watch__all;
