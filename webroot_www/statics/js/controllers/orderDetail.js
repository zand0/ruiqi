require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages'], function ($, utils, PageNav) {
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
        
        funcs.getshipper = function () {
            $("#shop_id").live('selectmenuchange',function () {
                var shop_id = $(this).val();
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'shipper','shop_id':shop_id},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var shipperHtml = "<option value='0'>选择送气工</option>";
                        if (preview['shipper']) {
                            for (key in preview['shipper']) {
                                shipperHtml += "<option value='" + key + "'>" + preview['shipper'][key]['shipper_name'] + "</option>";
                            }
                        }
                        $("#shipper_id").empty().append(shipperHtml).selectmenu('refresh');
                    }
                });
            });
        }
        
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            
            this.getshipper();
        }
        funcs.init();
    });
});
