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

mix.js('src/resources/js/kpamaeasybuilder.js', 'src/public/js')
mix.js('src/resources/js/swagger.js', 'src/public/js')
    .sass('src/resources/sass/kpamaeasybuilder.scss', 'src/public/css');
mix.copy('node_modules/swagger-ui/dist/swagger-ui.css', 'src/public/css');
mix.copy('node_modules/swagger-ui/dist/swagger-ui.css.map', 'src/public/css');