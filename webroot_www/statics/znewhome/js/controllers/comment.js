require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages'], function ($, utils, PageNav) {
        var funcs = {};

        // 分页
        funcs.pageFn = (function () {
            var url = window.location.protocol;  //得到链接地址
            
            var curPage = parseInt($('.currentPage').val()) || 1;
            var totalPage = parseInt($('.totalPage').val()) || 1;
            var getparamlist = $(".paramlist").val();
            var pager = new PageNav({
                currentPage: curPage,
                totalPage: totalPage,
                wrapId: '#pageNav',
                callback: function (curPage) {
                    url += '?temptype=new&page=' + curPage;
                    if(getparamlist != '' && getparamlist != undefined){
                        url += '&'+getparamlist;
                    }
                    location.href = url;
                }
            });
        })();

        funcs.version = function () {
            //头部选项卡
            $('.nav a').on('click', function () {
                var _index = $(this).index();
                $(this).addClass('active').siblings().removeClass('active');
                $('.left-nav ul').eq(_index).show().siblings().hide();
            });
            
            var pathname = document.location.pathname;
            
            //首页头部导航条
            $('.left-nav ul li').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
            });

            //var nowTag = $('.left-nav ul li').find('a[href="' + pathname + '?temptype=new"]');
            var nowTag = $('.left-nav ul li').find('a[href="' + pathname + '"]');
            nowTag.parents('ul').show().siblings().hide();
            if (nowTag.length == 0) {
                nowTag = $('.left-nav').find('a[href="' + pathname + '"]');
                console.log(nowTag)
                if (nowTag.length == 0) {
                    var pathname = pathname.split('/');
                    nowTag = $('.left-nav').find('a[href="/' + pathname[1] + '/index"]');
                    if (nowTag.length == 0) {
                        //var pathnamelist = '/' + pathname[1] + '/index' + '?temptype=new';
                        var pathnamelist = '/' + pathname[1] + '/index';
                    }
                }
            } else {
                //var pathnamelist = pathname + '?temptype=new';
                var pathnamelist = pathname;
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

        funcs.formsubmit = function() {
            $("#formbtn").click(function() {
                if (confirm('确认提交表单？')) {
                    $("#formtable").submit();
                }
            });
        }

        // 初始化
        funcs.init = function () {
            //导航选中事件
            this.version();
            
            //表单提交
            this.formsubmit();
        }
        funcs.init();
    });
});