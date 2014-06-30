var gulp   = require("gulp");
var jshint = require("gulp-jshint");
var uglify = require("gulp-uglify");
var concat = require("gulp-concat");
var rename = require("gulp-rename");
var ngmin   = require("gulp-ngmin");
var watch   = require("gulp-watch");

var src = {
    client: [
        "public/js/src/flexitime.js",
        "public/js/src/controllers/*.js",
        "public/js/src/filters/*.js",
        "public/js/src/directives/*.js",
        "public/js/src/services/*.js",
        "public/js/src/Factory.js"
    ]
};

gulp.task("build-lib", function () {
    return gulp.src(src.client)
        .pipe(concat("flexitime.js"))
        //.pipe(ngmin({dynamic:true}))
        .pipe(gulp.dest("./public/js/dist"))
        .pipe(rename("flexitime.min.js"))
        //.pipe(uglify())
        .pipe(gulp.dest("./public/js/dist"));
});

gulp.task('lint', function () {
    gulp.src(src.client)
        .pipe(jshint())
        .pipe(jshint.reporter("default"))
        .pipe(jshint.reporter("fail"));
});

gulp.task('lint-dev', function () {
    gulp.src(src.client)
        .pipe(jshint())
        .pipe(jshint.reporter("default"));
});



gulp.task('default', ['lint', 'build-lib']);

gulp.task("dev", function () {
    gulp.watch(src.client, ['lint-dev', 'build-lib']);
});