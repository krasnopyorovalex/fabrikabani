let mix = require('laravel-mix');

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

mix.js('resources/js/script.js', 'public/js/app.min.js')
    .styles([
        'resources/css/bootstrap.css',
        'resources/css/fonts.css',
        'resources/css/style.css',
        'resources/css/custom.css',
        'resources/css/responsive.css'
    ], 'public/css/app.min.css')
    .version();
