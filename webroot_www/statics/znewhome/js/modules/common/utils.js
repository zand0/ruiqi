/**
 * 通用函数库.
 */
define(['jquery', 'vendors/jqueryUI/selectmenu', 'modules/common/datepicker'], function ($, selectmenu, datepicker) {
    var rets = {};
    // 设置头部导航 header
    rets.navFn = (function () {
        // 线条宽度
        $('.nav a').each(function () {
            $(this).find('span').css({width: $(this).innerWidth()});
        });
        $('.nav a').on('click', function () {
            $(this).addClass('active').siblings().removeClass('active');
        });
    })();

    // 头部选项卡--运营简报  运营管理
    rets.tabFn = (function () {
        $('#tabBtn li').on('click', function () {
            $(this).addClass('active').siblings().removeClass('active');
        });
    })();
    // 统计汇总页 左侧导航点击效果 统计汇总 销售额简报
    rets.slideFn = (function () {
        if ($('.left-nav li').length > 1) {
            // 初始化span的位置
            $('.left-nav li').each(function () {
                if ($(this).hasClass('active')) {
                    $('.left-nav span').stop().animate({'top': $(this).position().top}, '100');
                }
            })
            // 点击事件
            $('.left-nav li').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
                $('.left-nav span').stop().animate({'top': $(this).position().top}, '100');
            });
        }
    })();

    // 统计汇总页--右侧选项卡  最近7天 14天
    rets.tabFn = function () {
        if ($('.tabContrl').length > 0) {
            $('.tabContrl a').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
                $('.tabItem .listItem').eq($(this).index()).show().siblings().hide();
            });
        }
    }
    // 下拉框
    rets.selectmenuFn = function () {
        if ($(".selectmenu").length > 0) {
            $(".selectmenu").selectmenu();
        }
    }
    // 单日历
    rets.datepickerFn = function () {
        if ($('.datepicker').length > 0) {
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                showOn: 'button',
                buttonImage: 'images/calendar.png',
                buttonImageOnly: true,
                buttonText: 'Select date'
            });
        }
    }
    // 双日历
    rets.doubleRiliFn = function (start, end) {
        if (start.length > 0) {
            start.datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                onClose: function (selectedDate) {
                    end.datepicker("option", "minDate", selectedDate);
                }
            });
            end.datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                onClose: function (selectedDate) {
                    start.datepicker("option", "maxDate", selectedDate);
                }
            });
        }
    }

    rets.chooseFn = function () {
        $('.chooseOption').each(function () {
            var $this = $(this);
            //初始化
            $(this).find('a').each(function () {
                if ($(this).hasClass('active')) {
                    $this.siblings('input').val($(this).data('value'));
                }
            })
            // 点击事件
            $(this).find('a').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
                $this.siblings('input').val($(this).data('value'));
            });
        })
    }
    // 订单详情 返回按钮
    rets.backFn = function () {
        $('.backBtn').on('click', function () {
            history.go(-1);
        });
    }
    rets.init = function () {}
    // 调用
    rets.init();

    return rets;
});