require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages', 'vendors/jqueryUI/selectmenu'], function ($, utils, PageNav, selectmenu) {
        var funcs = {};
        funcs.pageFn = function () {
            var url = window.location.protocol;  //得到链接地址
            var curPage = parseInt($('.currentPage').val() || 1);
            var totalPage = parseInt($('.totalPage').val() || 1);
            var pager = new PageNav({
                currentPage: curPage,
                totalPage: totalPage,
                wrapId: '#pageNav',
                callback: function (curPage) {
                    url += '?page=' + curPage;
                    location.href = url;
                }
            });
        }
        funcs.chukuFn = function () {
            $('#confirmbtn').off('click').live('click', function () {
                var sno = $("#sno").val();
                var tmoney = $("#tmoney").val();
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/filling/confirmedata",
                    data: {'sno': sno, 'tmoney': tmoney},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        if (preview.status >= 1) {
                            location.reload();
                        } else {
                            alert('创建失败');
                        }
                    }
                });
            });
            
            //门店入库确认单
            $(".qrrk").live('click', function () {
                var cno = $(this).attr('cno');
                var shop_id = $(this).attr('shop_id');
                var that = $(this);
                if(confirm('当前确认只是数据确认,请尽量从app端扫码确认？')){
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/rkshop",
                        data: {cno: cno,shop_id:shop_id},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                that.removeClass('qrrk');
                                that.removeClass('redColor').html('已接收');
                            } else {
                                alert('操作失败');
                            }
                        }
                    });
                }
            });
        }

        // 初始化
        funcs.init = function () {
            this.chukuFn();

            this.pageFn();
        }
        funcs.init();
    });
});