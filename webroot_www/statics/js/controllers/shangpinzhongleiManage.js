require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages', 'vendors/jqueryUI/selectmenu'], function ($, utils, PageNav, selectmenu) {
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
                    if (getparamlist != '' && getparamlist != undefined) {
                        url += '&' + getparamlist;
                    }
                    location.href = url;
                }
            });
        }
        
        funcs.dialogFn = function () {
            $('.addShangpin').on('click', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
                                <div class="contRTitle2">\
                                        添加商品种类\
                                        <span></span>\
                                </div>\
                                <div class="rqLine">\
                                        <div class="rqOption rqOption4 clearfix">\
                                                <label class="fl">商品名称：</label>\
                                                <div class="inputBox fl placeholder">\
                                                        <input type="text" name="name" id="name" value="" style="width:148px;">\
                                                </div>\
                                        </div>\
                                </div>\
                                <div class="rqLine">\
                                        <div class="rqOption rqOption4 clearfix">\
                                                <label class="fl">备注：</label>\
                                                <textarea name="treatment" style="width:520px;height:90px;"></textarea>\
                                        </div>\
                                </div>\
                                <div  class="rqLine rqLineBtn4 clearfix">\
                                        <div class="rqOption fl">\
                                                <a href="javascript:;" class="blueBtn saveBtn">保存</a>\
                                        </div>\
                                        <div class="rqOption fl">\
                                                <a href="javascript:;" class="grayBtn closeBtn">取消</a>\
                                        </div>\
                                </div>\
                        </div>';
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '400px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    alert('保存成功');
                    dlg.close().remove();
                });
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
            });
        }
                
        funcs.guigeFn = function () {
            $(".typeID").click(function () {
                var type_id = $(this).val();
                if (type_id == 1 || type_id == 4 || type_id == 5) {
                    //获取钢瓶规格
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/datatype",
                        data: {'type': 'bottle'},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            var bottleHtml = '<option value="norm_id">选择商品规格</option>';
                            if (preview['bottle']) {
                                for (key in preview['bottle']) {
                                    bottleHtml += "<option value='" + key + "'>" + preview['bottle'][key] + "</option>";
                                }
                            }
                            $(".sptype").show();
                            if (type_id == 4 || type_id == 5) {
                                $(".tcnum").show();
                            }
                            $("#norm_id").html(bottleHtml).selectmenu('refresh');
                        }
                    });
                } else if (type_id == 2) {
                    //获取配件规格
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/datatype",
                        data: {'type': 'producttype'},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            var productypeHtml = '<option value="norm_id">选择商品规格</option>';
                            if (preview['producttype']) {
                                for (key in preview['producttype']) {
                                    productypeHtml += "<option value='" + key + "'>" + preview['producttype'][key]['name'] + "</option>";
                                }
                            }
                            $(".sptype").show();
                            $("#norm_id").html(productypeHtml).selectmenu('refresh');
                        }
                    });
                } else {
                    $(".sptype").hide();
                    $(".tcnum").hide();
                }
            });
        }

        funcs.priceFn = function() {
            
            $("#commditylist").live('selectmenuchange', function () {
                
                var commditylist = $(this).val();
                var istype = $("#istype").val();
                
                var typeID;
                if (istype == 'shop') {
                    typeID = $("#shop_id").val();
                    if (typeID == '') {
                        alert('请选择门店');
                        return false;
                    }
                } else {
                    typeID = $("#area_id").val();
                    if (typeID == '') {
                        alert('请选择区域');
                        return false;
                    }
                }
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/isareaprice",
                    data: {'type_id': typeID,'commditylist':commditylist,'istype':istype},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        if(preview['data'] != ''){
                            $("#commoditysn").val(preview['data']['commoditysn']);
                            $("#cid").val(preview['data']['id']);
                            $("#ctype").val(preview['data']['type']);
                            
                            $("#retail_price").val(preview['data']['retail_price']);
                            $("#retail_price_business").val(preview['data']['retail_price_business']);
                            $("#direct_price").val(preview['data']['direct_price']);
                            $("#direct_price_business").val(preview['data']['direct_price_business']);
                            $("#affiliate_price").val(preview['data']['affiliate_price']);
                            $("#affiliate_price_business").val(preview['data']['affiliate_price_business']);
                        }else{
                            $("#commoditysn").val('');
                            $("#cid").val('');
                            $("#ctype").val('');
                            
                            $("#retail_price").val('');
                            $("#retail_price_business").val('');
                            $("#direct_price").val('');
                            $("#direct_price_business").val('');
                            $("#affiliate_price").val('');
                            $("#affiliate_price_business").val('');
                        }
                    }
                });
            });
        }

        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 添加商品种类
            this.dialogFn();

            //判断当前商品是否已经定价
            this.priceFn();

            //根据类型获取规格
            this.guigeFn();
        }
        funcs.init();
    });
});
