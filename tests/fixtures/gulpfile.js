var argv = require('yargs').demand(['source', 'dest']).argv;
var browserSync = require('browser-sync').create();
var changed = require('gulp-changed');
var exec = require('child_process').exec;
var gulp = require('gulp');
var filter = require('gulp-filter');
var print = require('gulp-print');

var paths = {
    source: argv.source,
    output: argv.dest,
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

gulp.task('steak:publish', () => {
    return gulp
        .src(paths.patterns.not.blade)
        .pipe(changed(paths.output))
        .pipe(filter(isFile))
        .pipe(print())
        .pipe(gulp.dest(paths.output))
    ;
});

gulp.task('steak:serve', () => {
    browserSync.init({
        server: {
            baseDir: paths.output,
            routes: {
                "/steak": paths.output
            }
        },
        files: [
            paths.output
        ],
        startPath: "/steak"
    });

    gulp.watch(paths.patterns.not.blade, ['steak:publish']);

    gulp.watch(paths.patterns.blade).on('change', (file) => {
        exec('php steak build ' + file.path + ' --no-gulp --no-clean');
    });
});

var isFile = function (file) {
    return ! file.isDirectory();
};
