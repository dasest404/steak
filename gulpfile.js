//
// Dependencies
//
var argv = require('yargs').demand(['dest']).argv;
var autoprefixer = require('gulp-autoprefixer');
var browserSync = require('browser-sync').create();
var changed = require('gulp-changed');
var colors = require('gulp-util').colors;
var concat = require('gulp-concat');
var count = require('gulp-count');
var cssnano = require('gulp-cssnano');
var exec = require('child_process').exec;
var filter = require('gulp-filter');
var fs = require('fs');
var gulp = require('gulp');
var logger = require('gulp-util').log;
var path = require('path');
var sass = require('gulp-sass');


// Note that excluding node_modules from the positive pattern - as opposed to
// simply having a negative pattern exclude it - gives a huge performance gain.
// Perhaps unsurprisingly given the number of files within node_modules, even
// with just a few modules installed...
var PATTERNS = {
    STATIC: [
        './!(node_modules|_*){/**/[^_]*,}',
        '!package.json',
        '!gulpfile.js',
        '!**/*.{php,md}',
        '!**/_*/**'
    ],
    PHP: './!(node_modules){/**/*,}.{php,md}',
    SASS: '_sass/**/*.scss'
};
var OUTPUT_DIR = path.join(argv.phpwd || '.', argv.dest);

/*
 * Rebuild the
 */
gulp.task('steak:build', ['steak:vendor', 'steak:publish', 'steak:styles']);

/*
 * Start the local server and watch for file change.
 */
gulp.task('steak:serve', ['steak:build', 'steak:start', 'steak:watch']);



gulp.task('steak:vendor', ['steak:vendor:assets', 'steak:vendor:scripts', 'steak:vendor:styles']);

gulp.task('steak:vendor:assets', () => {
    return gulp
        .src([
            'node_modules/semantic-ui-css/{themes/**/*,}'
        ])
        .pipe(gulp.dest(path.join(OUTPUT_DIR, 'vendor')))
    ;
});

gulp.task('steak:vendor:styles', () => {
    return gulp
        .src([
            'node_modules/semantic-ui-css/semantic.min.css',
            'node_modules/prismjs/themes/prism.css',
        ])
        .pipe(concat('all.css'))
        .pipe(gulp.dest(path.join(OUTPUT_DIR, 'vendor')))
        ;
});

gulp.task('steak:vendor:scripts', () => {
    return gulp
        .src([
            'node_modules/semantic-ui-css/semantic.min.js',
            'node_modules/prismjs/prism.js',
            'node_modules/prismjs/components/prism-{javascript,php,yaml}.js',
        ])
        .pipe(concat('all.js'))
        .pipe(gulp.dest(path.join(OUTPUT_DIR, 'vendor')))
    ;
});

/*
 * Publish all assets.
 */
gulp.task('steak:publish', () => {
    return gulp
        .src(PATTERNS.STATIC)
        .pipe(filter(file => ! file.isDirectory()))
        .pipe(changed(OUTPUT_DIR))
        .pipe(gulp.dest(OUTPUT_DIR))
        .pipe(count('Published ## files'))
    ;
});

/*
 * Compile SASS to CSS
 */
gulp.task('steak:styles', () => {
    return gulp.src(PATTERNS.SASS)
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer())
        .pipe(cssnano())
        .pipe(gulp.dest(path.join(OUTPUT_DIR, 'assets')))
    ;
});

/*
 * Start a BrowserSync server and use its built-in watcher for changed files.
 */
gulp.task('steak:start', () => {

    // Define browserSync config - serve from the build folder
    // and watch the build folder for changes
    var config = {
        server: {
            baseDir: OUTPUT_DIR
        },
        files: [
            OUTPUT_DIR
        ]
    };

    // By default, sites are served from the root. github-pages sites exist
    // in a /<projectName> subdirectory. The --subdir option emulates this.
    if (argv.subdir) {
        config.server.routes = {};
        config.server.routes['/'+argv.subdir] = OUTPUT_DIR;
        config.startPath = '/'+argv.subdir;
    }

    browserSync.init(config);
});

/*
 * Watch source files that require rebuilding.
 */
gulp.task('steak:watch', () => {
    gulp.watch(PATTERNS.STATIC, ['steak:publish']);
    gulp.watch(PATTERNS.SASS, ['steak:styles']);
    gulp.watch(PATTERNS.PHP).on('change', runSteak);
});


function runSteak(file)
{
    logger(`Starting '${colors.cyan('rebuild')}' of ${colors.magenta(file.path)}`);

    exec(`php steak build ${file.path} --no-gulp --no-clean`, { cwd: argv.phpwd }, (error, stdout, stderr) => {

        if (error !== null) {
            logger(`Rebuild of ${colors.magenta(file.path)} failed:\n${colors.bgRed(error.message.trim())}`);

            if (stderr.trim()) logger(`${colors.red('stderr>')} ${stderr.trim()}`);

            if (stdout.trim()) logger(`${colors.red('stdout>')} ${stdout.trim()}`);

            return;
        }

        logger(`Finished '${colors.cyan('rebuild')}' of ${colors.magenta(file.path)}`);
    });
}
