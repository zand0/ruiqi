require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils', 'highcharts'], function ($, utils, highcharts) {
        var funcs = {};
        funcs.init = function () {
            // 双日历
            utils.doubleRiliFn($('#start'), $('#end'));
            // 详情--返回按钮
            utils.backFn();
            // 选择类型
            utils.chooseFn();
        }
        funcs.init();
    });
});
