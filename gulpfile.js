var gulp   = require("gulp");
var jshint = require("gulp-jshint");
var uglify = require("gulp-uglify");
var concat = require("gulp-concat");
var rename = require("gulp-rename");
var ngmin = require("gulp-ngmin");

var src = {
    client: ["public/js/src/controllers/*.js"]
};

gulp.task("build-lib", function () {
    return gulp.src(src.client)
        .pipe(concat("app.js"))
        .pipe(ngmin({dynamic:true}))
        .pipe(uglify())
        .pipe(gulp.dest("./public/js/dist"));
});

gulp.task('lint', function () {
    gulp.src(src.client)
        .pipe(jshint())
        .pipe(jshint.reporter("default"))
        .pipe(jshint.reporter("fail"));
});

gulp.task('default', ['lint', 'build-lib']);