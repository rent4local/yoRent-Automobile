const { src, dest, watch, series, parallel } = require("gulp");
const sass = require("gulp-sass");
sass.compiler = require("node-sass");
const sourcemaps = require("gulp-sourcemaps");
const autoprefixer = require("gulp-autoprefixer");
const minify = require("gulp-minify");

// Tasks
function base() {
  return src("./application/views/default/scss/*.scss")
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(sass({ outputStyle: "compressed" }))
    .pipe(autoprefixer())
    .pipe(sourcemaps.write("."))
    .pipe(dest("./application/views/default/css")); 
}

function dashboard() {
  return src("./application/views/default/dashboard/scss/*.scss")
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(sass({ outputStyle: "compressed" }))
    .pipe(autoprefixer())
    .pipe(sourcemaps.write("."))
    .pipe(dest("./application/views/default/dashboard/css"));
}

function fashion() {
  return src("./application/views/fashion/scss/*.scss")
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(sass({ outputStyle: "compressed" }))
    .pipe(autoprefixer())
    .pipe(sourcemaps.write("."))
    .pipe(dest("./application/views/fashion/css"));
}

function heavy_equipment() {
  return src("./application/views/heavy_equipment/scss/*.scss")
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(sass({ outputStyle: "compressed" }))
    .pipe(autoprefixer())
    .pipe(sourcemaps.write("."))
    .pipe(dest("./application/views/heavy_equipment/css"));
}

function automobile() {
  return src("./application/views/automobile/scss/*.scss")
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(sass({ outputStyle: "compressed" }))
    .pipe(autoprefixer())
    .pipe(sourcemaps.write("."))
    .pipe(dest("./application/views/automobile/css"));
}

// Watch files
function watchFiles() {
  // Watch SCSS changes
  watch(["./application/views/default/scss"], base);
  watch(["./application/views/default/dashboard/scss"], dashboard);
  watch(["./application/views/fashion/scss"], fashion);
  watch(["./application/views/heavy_equipment/scss"], heavy_equipment);
  watch(["./application/views/automobile/scss"], automobile);
  //watch(['./application/views/scss'], themes);
}

exports.default = series(base, dashboard, fashion, heavy_equipment, automobile);
exports.watch = watchFiles;
