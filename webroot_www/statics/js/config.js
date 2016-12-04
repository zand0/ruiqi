/**
 * 程序配置
 */
require.config({
    baseUrl: '/statics/js/',
    paths: {
        'jquery': 'vendors/jquery/jquery.min',
        'utils': 'modules/common/utils',
        'core': 'vendors/jqueryUI/core.min',
        'webuploader': 'vendors/webUploader/webuploader.min', 
        'artDialog':'vendors/artDialog/dialog-plus-min',
        'highcharts':'vendors/highcharts/highcharts'
    },
    shim: {
        artDialog:{
            deps: [ 'jquery'],
            exports: 'dialog'
        },
        highcharts:{
            deps:['jquery'],
            exports:'Highcharts'
        }
    }
});
