const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/markocupiccontaogithublogin')
    .setManifestKeyPrefix('')

    .copyFiles({
        from: './assets/styles',
        to: 'css/[path][name].[hash:8].[ext]',
        pattern: /(\.css)$/,

    })
    .copyFiles({
        from: './assets/js',
        to: 'js/[path][name].[hash:8].[ext]',

    })

    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps()
    .enableVersioning()

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // Preprocessing SCSS to CSS
    .enableSassLoader()
    .enablePostCssLoader()
    .addStyleEntry('css/login_button', './assets/style/login_button.scss')
;

module.exports = Encore.getWebpackConfig();
