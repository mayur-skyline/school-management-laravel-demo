const mix = require('laravel-mix');

mix
  .alias({
    '@astnext': './resources/astnext/src',
    '@sga': './resources/sga/src',
    '@atom': './resources/atom/src'
  })
  .setPublicPath('html/astnext')
  .setResourceRoot(new URL('html/astnext', `${process.env.MIX_ASTNEXT_APP_URL}${process.env.MIX_ASTNEXT_APP_URL.endsWith('/') ? '' : '/'}`).href);

mix
  .ts('resources/astnext/src/index.tsx', 'js/astnext.js')
  .ts('resources/sga/src/index.tsx', 'js/sga.js')
  .ts('resources/atom/src/index.tsx', 'js/atom.js')
  .react();

mix.sass('resources/astnext/src/styles/astnext.scss', 'css/astnext.css')
  .options({
    processCssUrls: false,
    postCss: [require('tailwindcss')],
  });

if (mix.inProduction()) {
  mix
    .version()
    .sourceMaps(false);
}
