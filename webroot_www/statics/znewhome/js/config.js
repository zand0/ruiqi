/**
 * 程序配置
 */
require.config({
    baseUrl: '../statics/znewhome/js/',
    paths: {
        'jquery': 'vendors/jquery/jquery.min',
        'utils': 'modules/common/utils',
        'highcharts': 'vendors/highcharts/highcharts',
        'circliful': 'vendors/circliful/jquery.circliful'
    },
    shim: {
        artDialog: {
            deps: ['jquery'],
            exports: 'dialog'
        },
        highcharts: {
            deps: ['jquery'],
            exports: 'Highcharts'
        },
        circliful: {
            deps: ['jquery'],
            exports: 'circliful'
        }
    }
});
