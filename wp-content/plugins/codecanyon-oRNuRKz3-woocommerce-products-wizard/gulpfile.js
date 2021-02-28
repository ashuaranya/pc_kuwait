const gulp = require('gulp');
const autoprefixer = require('gulp-autoprefixer');
const babel = require('gulp-babel');
const concat = require('gulp-concat');
const iconfont = require('gulp-iconfont');
const iconfontCss = require('gulp-iconfont-css');
const minifyCSS = require('gulp-clean-css');
const plumber = require('gulp-plumber');
const rename = require('gulp-rename');
const sass = require('gulp-sass');
const uglify = require('gulp-uglify');
const wpPot = require('./tools/vendor/wp-pot');

gulp.task('watch', () => gulp.watch('src/**/*.scss', gulp.series('styles')));

gulp.task(
    'styles-build',
    () => gulp
        .src(
            [
                './src/admin/scss/app.scss',
                './src/front/scss/app.scss',
                './src/front/scss/app-full.scss'
            ],
            {base: '.'}
        )
        .pipe(plumber({
            errorHandler: (err) => {
                console.log(err);
                this.emit('end');
            }
        }))
        .pipe(sass({errLogToConsole: true}))
        .pipe(autoprefixer({cascade: true}))
        .pipe(gulp.dest('.'))
);

gulp.task(
    'styles-copy',
    () => gulp
        .src(
            [
                './src/admin/scss/*',
                './src/front/scss/*',
                '!./**/*.scss'
            ],
            {
                base: '.',
                nodir: true
            }
        )
        .pipe(rename(function (path) {
            path.dirname = path.dirname.replace('src', 'assets');
            path.dirname = path.dirname.replace('scss', 'css');
            path.extname = path.extname === '.css' ? '.min' + path.extname : path.extname;
        }))
        .pipe(gulp.dest('.'))
);

gulp.task(
    'styles-compress',
    () => gulp
        .src(['./assets/**/css/*.css'], {base: '.'})
        .pipe(minifyCSS({
            compatibility: 'ie8',
            level: {1: {specialComments: 0}}
        }))
        .pipe(gulp.dest('.'))
);

gulp.task(
    'styles',
    gulp.series(
        'styles-build',
        'styles-copy',
        'styles-compress'
    )
);

gulp.task(
    'icon-font',
    () => gulp
        .src('./src/front/icons/*.svg')
        .pipe(iconfontCss({
            fontName: 'woocommerce-products-wizard',
            fontPath: '../fonts/icons',
            path: './src/front/icons/_template',
            targetPath: '../scss/_icons.scss'
        }))
        .pipe(iconfont({
            fontName: 'icons',
            formats: ['ttf', 'woff', 'woff2']
        }))
        .pipe(gulp.dest('./src/front/fonts'))
);

gulp.task(
    'assets-copy',
    () => gulp
        .src(
            [
                './src/admin/**/*',
                './src/front/**/*',
                '!./src/**/icons/**/*',
                '!./**/scss/',
                '!./**/*.scss'
            ],
            {
                base: '.',
                nodir: true
            }
        )
        .pipe(rename(function (path) {
            path.dirname = path.dirname.replace('src', 'assets');
            path.dirname = path.dirname.replace('scss', 'css');
            path.extname = path.extname === '.js' || path.extname === '.css' ? '.min' + path.extname : path.extname;
        }))
        .pipe(gulp.dest('.'))
);

gulp.task(
    'scripts-compress',
    () => gulp
        .src(['./assets/**/js/*.js'], {base: '.'})
        .pipe(babel({presets: [['@babel/preset-env', {modules: false}]]}))
        .pipe(uglify())
        .pipe(gulp.dest('.'))
);

gulp.task(
    'scripts-concat',
    () => gulp
        .src([
            './src/front/js/util.js',
            './src/front/js/modal.js',
            './src/front/js/wNumb.js',
            './src/front/js/nouislider.js',
            './src/front/js/nouislider-launcher.js',
            './src/front/js/sticky-kit.js',
            './src/front/js/app.js',
            './src/front/js/elements-events.js',
            './src/front/js/variation-form.js',
            './src/front/js/hooks.js'
        ])
        .pipe(concat('scripts.min.js'))
        .pipe(babel({presets: [['@babel/preset-env', {modules: false}]]}))
        .pipe(uglify())
        .pipe(gulp.dest('./assets/front/js/'))
);

gulp.task(
    'scripts-copy',
    () => gulp
        .src(
            [
                './src/admin/js/*',
                './src/front/js/*'
            ],
            {
                base: '.',
                nodir: true
            }
        )
        .pipe(rename(function (path) {
            path.dirname = path.dirname.replace('src', 'assets');
            path.extname = path.extname === '.js' ? '.min' + path.extname : path.extname;
        }))
        .pipe(gulp.dest('.'))
);

gulp.task(
    'scripts',
    gulp.series(
        'scripts-copy',
        gulp.parallel([
            'scripts-compress',
            'scripts-concat'
        ])
    )
);

gulp.task('pot', function () {
    return new Promise(resolve => {
        wpPot({
            domain: 'woocommerce-products-wizard',
            'package': 'WooCommerce Products Wizard',
            destFile: 'languages/woocommerce-products-wizard.pot',
            src: [
                './woocommerce-products-wizard.php',
                'views/**/*.php',
                'includes/**/*.php',
                '!includes/vendor/**/*.php'
            ]
        });

        resolve();
    });
});

gulp.task(
    'default',
    gulp.series(
        'pot',
        'icon-font',
        'styles-build',
        'assets-copy',
        gulp.parallel([
            'scripts-compress',
            'scripts-concat',
            'styles-compress'
        ])
    )
);
