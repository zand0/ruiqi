require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils'], function ($, utils) {
        var funcs = {};
        funcs.tabFn = function () {
            $('.allTc li').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
            });
        }
        funcs.init = function () {
            // 双日历
            utils.doubleRiliFn($('#start'), $('#end'));
            // 详情--返回按钮
            utils.backFn();
            // 收入分类
            this.tabFn();
            // 选择类型
            utils.chooseFn();
        }
        funcs.init();
    });
});