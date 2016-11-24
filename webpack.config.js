var webpack = require('webpack');

module.exports = {
    entry: {
        app: './js/base.app.js',
        vendor: ['angular', 'jquery', 'bootstrap', 'highcharts', 'angular-ui-router', 'pdfobject']
    },
    output: {
        path:'./dist',
        filename: 'app.bundle.js'
    },
    module: {
        loaders: [
            {   test: /\.html$/,
                loader: 'html-loader'
            },
            {   test: /\.css$/,
                loader: 'style!css'
            },
        ]
    },
    devServer:{
        inline: true
    },
    plugins: [
        new webpack.optimize.CommonsChunkPlugin(/* chunkName= */"vendor", /* filename= */"vendor.bundle.js")
    ]
};