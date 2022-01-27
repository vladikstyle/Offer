var gulp = require('gulp'),
    sass = require('gulp-sass'),
    less = require('gulp-less'),
    rename = require('gulp-rename'),
    autoprefixer = require('gulp-autoprefixer'),
    cleanCSS = require('gulp-clean-css'),
    rtlcss = require('gulp-rtlcss'),
    pckg = require('./package.json');

gulp.task('styles-admin', function () {
    return gulp.src('application/modules/admin/static/less/admin.less', { base: '.' })
        .pipe(less({
            precision: 8,
            outputStyle: 'expanded'
        }).on('error', onError))
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(rename('admin.min.css'))
        .pipe(cleanCSS())
        .pipe(gulp.dest('application/modules/admin/static/css'))
});

gulp.task('styles-theme', function (done) {
    /**
     * Always light / Main CSS file
     */
    gulp.src('content/themes/youdate/static/scss/app.scss', { base: '.' })
        .pipe(sass({
            precision: 8,
            outputStyle: 'expanded',
            includePaths: [
                'node_modules/bootstrap/scss/',
                'node_modules/tabler-ui/src/assets/scss'
            ]
        }).on('error', onError))
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(rename('app.min.css'))
        .pipe(cleanCSS())
        .pipe(gulp.dest('content/themes/youdate/static/css'))

        .pipe(rtlcss())
        .pipe(rename('app.rtl.min.css'))
        .pipe(cleanCSS())
        .pipe(gulp.dest('content/themes/youdate/static/css/'));

    /**
     * Always dark
     */
    gulp.src('content/themes/youdate/static/scss/app-dark.scss', { base: '.' })
        .pipe(sass({
            precision: 8,
            outputStyle: 'expanded',
            includePaths: [
                'node_modules/bootstrap/scss/',
                'node_modules/tabler-ui/src/assets/scss'
            ]
        }).on('error', onError))
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(rename('app-dark.min.css'))
        .pipe(cleanCSS())
        .pipe(gulp.dest('content/themes/youdate/static/css'));

    /**
     * Auto light/dark
     */
    gulp.src('content/themes/youdate/static/scss/app-auto.scss', { base: '.' })
        .pipe(sass({
            precision: 8,
            outputStyle: 'expanded',
            includePaths: [
                'node_modules/bootstrap/scss/',
                'node_modules/tabler-ui/src/assets/scss'
            ]
        }).on('error', onError))
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(rename('app-auto.min.css'))
        .pipe(cleanCSS())
        .pipe(gulp.dest('content/themes/youdate/static/css'));

    done();
});

gulp.task('watch', gulp.series('styles-admin', 'styles-theme', function() {
    gulp.watch('application/modules/admin/static/less/**/*.less', gulp.series('styles-admin'));
    gulp.watch('content/themes/youdate/static/scss/**/*.scss', gulp.series('styles-theme'));
}));

gulp.task('build', gulp.series('styles-admin', 'styles-theme'));

gulp.task('default', gulp.series('build'));

var onError = function (err) {
    console.log(err);
};
