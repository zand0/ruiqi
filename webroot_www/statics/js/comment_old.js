require(['../statics/js/config.js'], function () {
    require(['jquery'], function ($) {
        var funcs = {};

        funcs.version = function () {
            //头部选项卡
            $('.nav a').on('click', function () {
                var _index = $(this).index();
                $(this).addClass('active').siblings().removeClass('active');
                $('.left-nav ul').eq(_index).show().siblings().hide();
            });
            
            var pathname = document.location.pathname + document.location.search;
            
            //首页头部导航条
            $('.left-nav ul li').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
            });
            
            var pathnamelist;
            var nowTag = $('.left-nav ul li').find('a[href="' + pathname + '"]');
            nowTag.parents('ul').show().siblings().hide();
            if (nowTag.length == 0) {
                nowTag = $('.left-nav ul li').find('a[href="' + pathname + document.location.search + '"]');
                console.log(nowTag)
                if (nowTag.length == 0) {
                    var pathname = pathname.split('/');
                    nowTag = $('.left-nav ul li').find('a[href="/' + pathname[1] + '/index"]');
                    pathnamelist = '/' + pathname[1] + '/index';
                }
            } else {
                pathnamelist = pathname;
            }
            
            nowTag.parents('ul').show().siblings().hide();
            
            var indexnum = nowTag.parents('ul').attr('index');
            $('.nav a').eq(indexnum - 1).addClass('active').siblings().removeClass('active');

            $('.show_active').each(function () {
                var href = $(this).children('a').attr('href');
                if (href == pathnamelist) {
                    $(this).addClass('active').siblings().removeClass('active');
                    return false;
                }
            });
        }

        // 初始化
        funcs.init = function () {
            //导航选中事件
            this.version();
        }
        funcs.init();
    });
});