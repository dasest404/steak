var gulp = require('gulp');
var phpspec = require('gulp-phpspec');
var phpunit = require('gulp-phpunit');

gulp.task('phpspec', () => {
    gulp.src('phpspec.yml')
        .pipe(phpspec());
});

gulp.task('phpunit', () => {
    gulp.src('phpunit.xml')
        .pipe(phpunit()).on('error', err => console.log(err));
});

gulp.task('tdd', () => {
    gulp.watch(['src/**/*.php', 'spec/**/*.php'], ['phpspec']);
    gulp.watch(['src/**/*.php', 'tests/**/*.php'], ['phpunit']);
});