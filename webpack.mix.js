'use strict';

const mix = require('laravel-mix');
const webpack = require('webpack');
const ImageminPlugin = require('imagemin-webpack-plugin').default;
const glob = require('glob');
const fs = require('fs');

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
mix.options({
    clearConsole: false
});

mix.disableNotifications();

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

// copy images
glob('resources/images/**', (err, files) => {
    files.forEach(file => {
        fs.stat(file, (err, stats) => {
            if (stats.isFile() && !stats.isSymbolicLink()) {
                mix.copy(file, file.replace(/resources\/images/, 'public/images'));
            }
        });

    });
});

mix.webpackConfig({
    plugins: [
        new webpack.LoaderOptionsPlugin({
            test: /\.(scss)$/,
            use: [
                // inject CSS to page
                {loader: 'style-loader'},
                // translates CSS into CommonJS modules
                {loader: 'css-loader'},
                // Run post css actions
                {
                    loader: 'postcss-loader',
                    options: {
                        // post css plugins, can be exported to postcss.config.js
                        plugins: function () {
                            return [
                                require('precss'),
                                require('autoprefixer')
                            ];
                        }
                    }
                },
                // compiles Sass to CSS
                {loader: 'sass-loader'}
            ]
        }),
        new ImageminPlugin({
            disable: !mix.inProduction(), // Disable during development
            test: /\.(jpe?g|png|gif|svg)$/i,
        })
    ]
});

mix.sourceMaps(!mix.inProduction(), 'cheap-source-map');
mix.version();
