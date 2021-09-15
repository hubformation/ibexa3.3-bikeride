const Encore = require('@symfony/webpack-encore');
const path = require('path');
const getEzConfig = require('./ez.webpack.config.js');
const eZConfigManager = require('./ez.webpack.config.manager.js');
const eZConfig = getEzConfig(Encore);
const customConfigs = require('./ez.webpack.custom.configs.js');

Encore.reset();
Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .enableStimulusBridge('./assets/controllers.json')
    .enableSassLoader()
    .enableReactPreset()
    .enableSingleRuntimeChunk()
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]',
        pattern: /\.(png|svg)$/
    })
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
;
// Put your config here
Encore
  .addStyleEntry('training', [
    path.resolve(__dirname, './assets/css/normalize.css'),
    path.resolve(__dirname, './assets/css/bootstrap.min.css'),
    path.resolve(__dirname, './assets/css/bootstrap-theme.css'),
    path.resolve(__dirname, './assets/scss/style.scss') // Our custom styling
  ])
  .addEntry('training-js', [
    path.resolve(__dirname, './assets/js/bootstrap.min.js')
  ])
  .autoProvidejQuery();

Encore
  .addStyleEntry('formation_juan', [
    path.resolve(__dirname, './assets/css/normalize.css'),
    path.resolve(__dirname, './assets/css/bootstrap.min.css'),
    path.resolve(__dirname, './assets/css/bootstrap-theme.css'),
    path.resolve(__dirname, './assets/scss/style-formation-juan.scss') // Our custom styling
  ])
  .addEntry('formation_juan-js', [
    path.resolve(__dirname, './assets/js/bootstrap.min.js')
  ])
  .autoProvidejQuery();

Encore.addEntry('app', './assets/app.js');

const projectConfig = Encore.getWebpackConfig();
module.exports = [ eZConfig, ...customConfigs, projectConfig ];

// uncomment this line if you've commented-out the above lines
// module.exports = [ eZConfig, ...customConfigs ];
