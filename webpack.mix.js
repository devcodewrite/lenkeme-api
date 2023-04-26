const mix = require('laravel-mix');

mix.js('src/js/app.js', 'assets/js/app.js')
    .setPublicPath('assets');
mix.postCss('src/css/app.css', 'assets/css/app.css');