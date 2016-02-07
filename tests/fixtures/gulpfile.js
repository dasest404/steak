var gulp = require('gulp');
var argv = require('yargs').demand(['source', 'dest']).argv;
var print = require('gulp-print');
var filter = require('gulp-filter');

gulp.task('steak:publish', () => {
    var source = argv.source;
    var dest = argv.dest;

    return gulp
        .src([source+'/**/*', '!**/*.php'])
        .pipe(filter(file => {
            return !file.isDirectory() && file.relative.split('/').every(name => name.charAt(0) !== '_');
        }))
        .pipe(print())
        .pipe(gulp.dest(dest))
    ;
});