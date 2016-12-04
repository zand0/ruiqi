require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages'], function ($, utils, PageNav) {
        var funcs = {};
        // 下拉框选择本月  本季 本年订单数量
        funcs.selectFn = (function () {
            var timer = null;
            $('.bgBtn').hover(function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    $('.mySelect').show();
                }, 200);
                $('.bgBtn span').addClass('on');
            }, function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    $('.mySelect').hide();
                }, 200);
                $('.bgBtn span').removeClass('on');
            });
            // 点击下拉框选择
            $('.bgBtn .mySelect a').on('click', function () {
                $('.bgBtn p').html($(this).html());
                $('.mySelect').hide();
                $('.mySelect input').val($(this).data('id'));

            });
        })();
        funcs.init = function () {
            // 双日历
            utils.doubleRiliFn($('#start'), $('#end'));
        }
        funcs.init();
    });
});