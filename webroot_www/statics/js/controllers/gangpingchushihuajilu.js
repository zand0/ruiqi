require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages'], function ($, utils, PageNav) {
        var funcs = {};
        funcs.pageFn = function () {
            var url = window.location.protocol;  //得到链接地址

            var curPage = parseInt($('.currentPage').val() || 1);
            var totalPage = parseInt($('.totalPage').val() || 1);
            var getparamlist = $(".paramlist").val();
            var pager = new PageNav({
                currentPage: curPage,
                totalPage: totalPage,
                wrapId: '#pageNav',
                callback: function (curPage) {
                    url += '?page=' + curPage;
                    if(getparamlist != '' && getparamlist != undefined){
                        url += '&'+getparamlist;
                    }
                    location.href = url;
                }
            });
        }
        
        funcs.czbottle = function () {
            var time = '';
            $("#tbpda").click(function () {   
                var page = 1;
                var pageSize = 100;
                tbbottle(page, pageSize);
            });
        }
        
        //初始化数据同步
        function tbbottle(page, pageSize) {
            $.ajax({
                type: "POST",
                async: false,
                url: "/tank/bottletb",
                data: {page: page, pageSize: pageSize},
                success: function (result) {
                    var preview = eval("(" + result + ")");
                    if (preview.code > 0) {
                        if (preview.page == 1) {
                            alert('同步完成');
                            window.location.reload();
                        }else if (preview.page > 1) {
                            tbbottle(preview.page, preview.pageSize);
                        }
                    }else{
                        alert('同步完成');
                    }
                }
            });
        }
        
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 初始化数据同步
            this.czbottle();
        }
        funcs.init();
    });
});