var gulp = require('gulp');
var argv = require('yargs').demand(['source', 'dest']).argv;
var browserSync = require('browser-sync');
var changed = require('gulp-changed');
var filter = require('gulp-filter');
var print = require('gulp-print');

var source = argv.source;
var dest = argv.dest;
var publishable = [source+'/**', '!**/*.php', '!'+source+'/**/_*/**', '!'+source+'/**/_*'];

var filesOnly = (file) => !file.isDirectory();

gulp.task('steak:publish', () => {
    return gulp
        .src(publishable)
        .pipe(filter(filesOnly))
        .pipe(print())
        .pipe(gulp.dest(dest))
    ;
});

gulp.task('steak:republish', () => {
   return gulp
       .src(publishable)
       .pipe(filter(filesOnly))
       .pipe(changed(dest))
       .pipe(gulp.dest(dest));
});

gulp.task('steak:serve', () => {
    browserSync({
        server: {
            baseDir: dest,
            routes: {
                "/steak": dest
            }
        }
    });

    gulp.watch(publishable, ['steak:republish']);
    gulp.watch(publishable).on('change', browserSync.reload);
});

gulp.task('steak:blade', () => {
    return gulp
        .src(publishable)
        .pipe(filter(filesOnly))
        .pipe(print())
        .pipe(gulp.dest(dest));
});