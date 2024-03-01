const mix = require('laravel-mix')
const path = require('path')
require('laravel-mix-blade-reload')
require('laravel-mix-purgecss')

mix.setPublicPath('public')
mix.version()

if (!mix.inProduction()) {
  mix.webpackConfig({
    devtool: 'inline-source-map',
  })
  mix.bladeReload().alias({
    '@': path.join(__dirname, 'resources/js'),
  })
}

mix.sourceMaps(true, 'eval-source-map', 'hidden-source-map')

mix.copy('resources/assets/images', 'public/assets/images').
  copy('resources/assets/fonts', 'public/assets/fonts').
  copy('public/assets/images/favicon.ico', 'public/favicon.ico')

mix.postCss('resources/assets/css/landing/slick.css', 'public/assets/css/landing').
  postCss('resources/assets/css/landing/style.css', 'public/assets/css/landing')

mix.
  js('resources/assets/js/landing/slick.min.js', 'assets/js/landing').
  js('resources/assets/js/landing/matchHeight.js', 'assets/js/landing').
  js('resources/assets/js/landing/main.js', 'assets/js/landing')

mix.js('resources/assets/js/app.js', 'assets/js').
  js('resources/assets/js/vendor/autocomplete.js', 'assets/js/vendor').
  js('resources/assets/js/vendor/imask.js', 'assets/js/vendor').
  js('resources/assets/js/vendor/daterangepicker.js', 'assets/js/vendor').
  js('resources/assets/js/scripts/hsbc.js', 'assets/js').
  js('resources/assets/js/scripts/lazy-clubs-list.js', 'assets/js').
  js('resources/assets/js/components/booking/gift-cards/gems-points.js', 'assets/js')

mix.sass('resources/assets/sass/app.scss', 'assets/css', {
  sassOptions: {
    outputStyle: 'compressed',
  },
})//.purgeCss({})

mix.sass('resources/assets/sass/landings/hsbc.scss', 'assets/css', {
  sassOptions: {
    outputStyle: 'compressed',
  },
})

mix.options({
  hmrOptions: {
    host: 'localhost', port: '8103',
  }, terser: {
    extractComments: false,
  },
}).webpackConfig({
  devServer: {
    port: '8103', host: '0.0.0.0', allowedHosts: 'all', headers: {
      'Access-Control-Allow-Origin': '*',
    },
  },
})
