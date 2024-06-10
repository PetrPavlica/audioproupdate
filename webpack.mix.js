let mix = require('laravel-mix');

const ImageminPlugin = require('imagemin-webpack-plugin').default;
const CopyWebpackPlugin = require('copy-webpack-plugin');
const imageminMozjpeg = require('imagemin-mozjpeg');
const ImageminWebpWebpackPlugin = require("imagemin-webp-webpack-plugin");

const webpack = require('webpack');

mix.setPublicPath('www/assets');
mix.setResourceRoot('../');

mix.autoload({  // or Mix.autoload() ?
    'jquery': ['$', 'global.jQuery',"jQuery","global.$"],
});

mix.copyDirectory('node_modules/bootstrap/fonts', 'www/assets/fonts');
mix.copyDirectory('node_modules/font-awesome/fonts', 'www/assets/fonts');
mix.copyDirectory('www/assets/packages/lightbox/images', 'www/assets/images');
mix.copyDirectory('www/assets/packages/nice_select/images', 'www/assets/images');

// Front assets
mix.sass('www/assets/scss/app.scss', 'www/assets/css/').options({
        autoprefixer: {
                options: {
                        browsers: [
                                'last 40 versions',
                        ]
                }
        },
        processCssUrls : true
});

mix.js('www/assets/js/app.js', 'www/assets/js/app.bundle.js');

mix.options({processCssUrls: false});

mix.webpackConfig({
        plugins: [
                new CopyWebpackPlugin([{
                        from: 'www/assets/images',
                        to: 'img',
                }]),
                new ImageminPlugin({
                        test: /\.(jpe?g|png|gif|svg)$/i,
                        plugins: [
                                imageminMozjpeg({
                                        quality: 80,
                                })
                        ]
                }),
                new ImageminWebpWebpackPlugin({
                        test: /\.(jpe?g|png)/,
                        options: {
                                quality: 80
                        }
                })
        ]
});

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.preact(src, output); <-- Identical to mix.js(), but registers Preact compilation.
// mix.coffee(src, output); <-- Identical to mix.js(), but registers CoffeeScript compilation.
// mix.ts(src, output); <-- TypeScript support. Requires tsconfig.json to exist in the same folder as webpack.mix.js
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.standaloneSass('src', output); <-- Faster, but isolated from Webpack.
// mix.fastSass('src', output); <-- Alias for mix.standaloneSass().
// mix.less(src, output);
// mix.stylus(src, output);
// mix.postCss(src, output, [require('postcss-some-plugin')()]);
// mix.browserSync('my-site.test');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.babelConfig({}); <-- Merge extra Babel configuration (plugins, etc.) with Mix's default.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.extend(name, handler) <-- Extend Mix's API with your own components.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   globalVueStyles: file, // Variables file to be imported in every component.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   uglify: {}, // Uglify-specific options. https://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
