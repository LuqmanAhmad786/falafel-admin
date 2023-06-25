const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
/* CSS */
    .js('resources/laravel/js/app.js', 'public/assets/js/laravel/app.js')
    .sass('resources/laravel/sass/app.scss', 'public/assets/styles/laravel/app.css')
    .sass('resources/gull/assets/styles/sass/themes.scss', 'public/assets/styles/css/themes.min.css');

mix.styles([
    'node_modules/perfect-scrollbar/dist/perfect-scrollbar.min.js',
], 'public/assets/styles/vendor/perfect-scrollbar.css');

/* JS */

/* Laravel JS */

mix.combine([
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
    'node_modules/perfect-scrollbar/dist/perfect-scrollbar.min.js',
], 'public/assets/js/common-bundle-script.js');

mix.js([
    'resources/gull/assets/js/script.js',
], 'public/assets/js/script.js');
