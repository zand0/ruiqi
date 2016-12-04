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

        funcs.qrPay = function () {
            $(".qrpay").click(function () {
                if (confirm('确认收款？')) {
                    var pay_id = $(this).attr('payid');
                    var shipper_id = $(this).attr('shipperid');
                    var shop_id = $(this).attr('shop_id');
                    var that = $(this);
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/qrpay",
                        data: {pay_id: pay_id, shipper_id: shipper_id, shop_id: shop_id},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.code >= 1) {
                                that.removeClass("qrpay");
                                that.html('收款成功');
                            } else {
                                alert('收款失败');
                            }
                        }
                    });
                }
            });
        }
        
        funcs.uppay = function () {
            $(".uppay").click(function () {
                var money = $(this).attr('money');
                var shop_id = $(this).attr('shop_id');
                var shipper_id = $(this).attr('shipper_id');
                var that = $(this);
                var str = '<div class="pop_cont">\
                        <div class="contRTitle2">\
                            缴款\
                            <span></span>\
                        </div>\
                        <form name="form" method="POST" id="formtable">\
                        <div class="rqLine clearfix">\
                                <div class="rqOption fl">\
                                        <label class="fl">上缴额度：</label>\
                                        <div class="inputBox fl placeholder">\
                                                <input type="text" id="money" name="money" value="' + money + '" style="width:100px;">\
                                                <input type="hidden" id="shipper_id" name="shipper_id" value="' + shipper_id + '">\
                                                <input type="hidden" id="shop_id" name="shop_id" value="' + shop_id + '">\
                                        </div>\
                                </div>\
                        </div>\
                        <div  class="rqLine rqLineBtn clearfix">\
                                <div class="rqOption fl">\
                                        <a href="javascript:;" class="blueBtn saveBtn">保存</a>\
                                </div>\
                                <div class="rqOption fl">\
                                        <a href="javascript:;" class="grayBtn closeBtn">取消</a>\
                                </div>\
                        </div>\
                        </form>\
                </div>';
                
                var dlg = utils.showLog({
                    html: str,
                    width: '406px',
                    height: '260px'
                });
                
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    if (confirm('确认收款？')) {
                        var formdata = $('#formtable').serialize();
                        $.ajax({
                            type: "POST",
                            async: false,
                            url: "/ajaxdata/uppay",
                            data: formdata,
                            success: function (result) {
                                var preview = eval("(" + result + ")");
                                if (preview.code >= 1) {
                                    that.removeClass("uppay");
                                    dlg.close().remove();
                                    location.reload();
                                } else {
                                    alert('收款失败');
                                }
                            }
                        });
                    }
                });
            });
        }
        
        funcs.hkFn = function() {
            $("#confirmbtn").click(function(){ 
                if (confirm('确认回库？')) {
                    $(this).attr('id', '');
                    var sn = $("#sn").val();
                    var bottle = $("#bottle").val();
                    var bottle_data = $("#bottle_data").val();
                    var shop_id = $("#shop_id").val();
                    var shipper_id = $("#shipper_id").val();
                    var ftype = $("#ftype").val();
                    var that = $(this);
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/shipper/ajaxhkbottle",
                        data: {sn: sn, bottle: bottle, bottle_data: bottle_data, shop_id: shop_id, shipper_id: shipper_id, ftype: ftype},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                alert('更新成功');
                                location.href = '/shipper/hkshop';
                                return false;
                            } else {
                                alert(preview.msg);
                                return false;
                            }
                        }
                    });
                }
            });
        }
        
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            
            //确认收款
            this.qrPay();
            
            //送气工缴费
            this.uppay();
            
            //送气工回库
            this.hkFn();
        }
        funcs.init();
    });
});
