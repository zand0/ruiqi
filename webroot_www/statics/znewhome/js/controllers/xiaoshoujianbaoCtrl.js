require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils'], function ($, utils) {
        var funcs = {};
        
        funcs.init = function () {
            
            // 统计汇总页--右侧选项卡  最近7天 14天...
            utils.tabFn();
            // 双日历
            utils.doubleRiliFn($('#start'), $('#end'));
            
        }
        funcs.init();
    });
});