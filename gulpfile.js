let gulp = require('gulp');
let del = require('del');
let pump = require('pump');

const exec = require( 'child_process' ).exec;

let assets = {};
assets.path = './assets';
assets.css = assets.path + '/css';
assets.js = assets.path + '/js';
assets.build = assets.path + '/build';


// Cropper
function cropper(cb) {
    pump([
        gulp.src([
            './node_modules/cropperjs/dist/cropper.css',
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
    cropper,
    webpack
);

// Watchers
const watch__js = () => {
    gulp.watch([
        assets.js + '/**/*.js',
    ], gulp.series(webpack));
};

const watch__all = gulp.parallel(
    watch__js,
);


exports.default = build;
exports.watch = watch__all;
