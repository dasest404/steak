var gulp = require('gulp');
var phpunit = require('gulp-phpunit');

gulp.task('phpunit', () => {
    gulp.src('phpunit.xml')
        .pipe(phpunit()).on('error', err => console.log(err));
});

gulp.task('tdd', () => {
    gulp.watch(['src/**/*.php', 'tests/**/*.php'], ['phpunit']);
});