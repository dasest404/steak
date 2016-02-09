var argv = require('yargs').demand(['source', 'dest']).argv;
var browserSync = require('browser-sync').create();
var changed = require('gulp-changed');
var exec = require('child_process').exec;
var gulp = require('gulp');
var filter = require('gulp-filter');
var print = require('gulp-print');

/**
 * Configurable paths
 */
var paths = {
    source: argv.source,
    output: argv.dest,
    serveRelative: argv.subdir,
    patterns: {
        blade: [
            `${argv.source}/**/*.php`,
            `!${argv.source}/**/_*/**`,
            `!${argv.source}/**/_*`
        ],
        not: {
            blade: [
                `${argv.source}/**/*`,
                `!${argv.source}/**/*.php`,
                `!${argv.source}/**/_*/**`,
                `!${argv.source}/**/_*`
            ]
        }
    }
};

/**
 * Publish static files from the source directory.
 */
gulp.task('steak:publish', () => {
    return gulp
        .src(paths.patterns.not.blade)
        .pipe(changed(paths.output))
        .pipe(filter(file => ! file.isDirectory()))
        .pipe(print())
        .pipe(gulp.dest(paths.output))
    ;
});

/**
 * Start browserSync server and watch for source changes.
 */
gulp.task('steak:serve', () => {
    browserSync.init(getBrowserSyncConfig());

    gulp.watch(paths.patterns.not.blade, ['steak:publish']);

    gulp.watch(paths.patterns.blade).on('change', (file) => {
        exec('php steak build ' + file.path + ' --no-gulp --no-clean');
    });
});


/**
 * Generate browserSync configuration.
 *
 * @returns {{server: {baseDir: *}, files: *[]}}
 */
var getBrowserSyncConfig = function () {
    // Create a server in our build folder, and
    // watch the build folder for changes
    var config = {
        server: {
            baseDir: paths.output
        },
        files: [
            paths.output
        ]
    };

    // By default, sites are served from the root.
    // gh-pages sites exist in a /<projectName> subdirectory.
    // To emulate this, the --subdir option can be used.
    if (paths.serveRelative) {
        config.server.routes = {};
        config.server.routes['/'+paths.serveRelative] = paths.output;
        config.startPath = '/'+paths.serveRelative;
    }

    return config;
};
