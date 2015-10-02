var ngHtml2Js = require("gulp-ng-html2js");
var gulp = require("gulp");
var less = require("gulp-less");
var minifyCSS = require('gulp-minify-css');
var fbrowserify = require('gulp-faster-browserify');
var concat = require('gulp-concat');



/*******************************************************************************
 * General task for processing js files and ngTemplates, which are converted
 * to JS files
 *
 ******************************************************************************/
gulp.task('default', ['less', 'js']);


/*******************************************************************************
 * Faster-browserify task for javascript files
 *
 ******************************************************************************/
gulp.task('js', ['templates'], function() {
	return gulp.src('./asset/js/app.js')
		.pipe(fbrowserify({
			insertGlobals : true,
			debug : false,//!gulp.env.production
		}))
		.pipe(gulp.dest('./public/js'));
});


/*******************************************************************************
 * Preprocess ngTemplates
 *
 ******************************************************************************/
gulp.task('templates', function() {
	return gulp.src("./asset/js/**/*.html")
		.pipe(ngHtml2Js({
			moduleName: 'partialsModule',
			prefix : ''
		}))
		.pipe(concat('partials.js'))
		.pipe(gulp.dest('./asset/js/core'));
});


/*******************************************************************************
 * Transpile LESS files
 *
 ******************************************************************************/
 gulp.task('less', ['app-less']);


 gulp.task('app-less', function() {
	return gulp.src(['./asset/less/custom.less'])
		.pipe(less({
			//paths: [path.join(__] // Path for imports -- Nevim jestli je tu potreba, to necham nekomu chytrejsimu...
		}))
		.pipe(minifyCSS())
		.pipe(gulp.dest('./public/css'));
});
