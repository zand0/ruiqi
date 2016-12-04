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
                    if(getparamlist != '' && getparamlist != undefined){
                        url += '&'+getparamlist;
                    }
                    location.href = url;
                }
            });
        }
        funcs.dialogFn = function () {
            //送气工资金上缴
            $('.zjsj').on('click', function () {
                
                var shipper_id = $(this).attr('shipper_id');
                alert(shipper_id);
                
                
                var td = $(this).parent();
                    money = td.siblings('.money').text(),
                    shipperid = td.siblings('.shipperid').text(),
                    name = td.siblings('.tableBtn').text();//送气工
                var _this = this;
                var str = '';
                
                        str = '<div class="pop_cont">\
                    <div class="contRTitle2">\
                        送气工：'+name+'缴款\
                        <span></span>\
                    </div>\
                    <form name="form" method="POST" id="formtable">\
                    <input type="hidden" name="shipper_id" value="'+shipperid+'" />\
                    <div class="rqLine">\
                        <div class="rqOption clearfix" style="margin-top:20px;">\
                            <div class="rqOption fl">\
                                <div class="inputBox fl placeholder" id="qsj">\
                                <label style="font-size:24px;">请确认上缴全部余款？</label>\
                                </div>\
                                <div id="zdy">\
                                <span style="font-size:10px;width:50px;">或请点击</span><a href="javascript:;" class="buleColor" id="paynum" style="font-size:12px;"> ++ 自定义上缴</a>\
                                </div>\
                            </div>\
                            <div id="paydiv">\
                            </div>\
                        </div>\
                    </div>\
                    <div  class="rqLine rqLineBtn clearfix" style="margin-top:30px;">\
                        <div class="rqOption fl">\
                            <a href="javascript:;" class="blueBtn saveBtn">全部上缴</a>\
                        </div>\
                        <div class="rqOption fl" style="width:100px;">\
                            <a href="javascript:;" class="grayBtn closeBtn" ">取消</a>\
                        </div>\
                    </div>\
                    </form>\
                </div>';


                var dlg = utils.showLog({
                    html: str,
                    width: '400px',
                    height: '230px'
                });

                // //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var status = $("#status").val();
                    var pay_num = $("#pay_num").val();
                    if (status == 1 && pay_num == '') {
                            alert('请填写上缴金额！');
                            return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/shipperup",
                        data: formdata,
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                dlg.close().remove();
                                alert('上缴成功');
                                location.reload();
                            } else {
                                alert('上缴失败');
                            }
                        }
                    });
                });
                //添加按钮
                $(dlg.node).find('#paynum').off('click').on('click', function () {
                    
                    var fhtml = '<label class="fl" style="padding-right:20px;width:100px;">自定义上缴：</label>\
                                <div class="inputBox fl placeholder">\
                                    <input type="hidden" id="status" name="status" value="1" />\
                                    <input type="text" id="pay_num" name="money" style="width:110px;">\
                                </div>';
                        $("#paydiv").append(fhtml);
                        $('.saveBtn').text('自定义上缴');
                        $("#qsj").remove();$("#zdy").remove();
                });
            });

            //门店资金上缴
            $('.mdsj').on('click', function () {
                var td = $(this).parent();
                    money = td.siblings('.money').text(),
                    shopid = td.siblings('.shopid').text(),
                    name = td.siblings('.tableBtn').text();//门店名
                var _this = this;
                var str = '';
                
                        str = '<div class="pop_cont">\
                    <div class="contRTitle2">\
                        门店：'+name+'缴款\
                        <span></span>\
                    </div>\
                    <form name="form" method="POST" id="formtable">\
                    <input type="hidden" name="shop_id" value="'+shopid+'" />\
                    <div class="rqLine">\
                        <div class="rqOption clearfix" style="margin-top:20px;">\
                            <div class="rqOption fl">\
                                <div class="inputBox fl placeholder" id="qsj">\
                                <label style="font-size:24px;">请确认上缴全部余款？</label>\
                                </div>\
                                <div id="zdy">\
                                <span style="font-size:10px;width:50px;">或请点击</span><a href="javascript:;" class="buleColor" id="paynum" style="font-size:12px;"> ++ 自定义上缴</a>\
                                </div>\
                            </div>\
                            <div id="paydiv">\
                            </div>\
                        </div>\
                    </div>\
                    <div  class="rqLine rqLineBtn clearfix" style="margin-top:30px;">\
                        <div class="rqOption fl">\
                            <a href="javascript:;" class="blueBtn saveBtn">全部上缴</a>\
                        </div>\
                        <div class="rqOption fl" style="width:100px;">\
                            <a href="javascript:;" class="grayBtn closeBtn" ">取消</a>\
                        </div>\
                    </div>\
                    </form>\
                </div>';


                var dlg = utils.showLog({
                    html: str,
                    width: '400px',
                    height: '230px'
                });

                // //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var status = $("#status").val();
                    var pay_num = $("#pay_num").val();
                    if (status == 1 && pay_num == '') {
                            alert('请填写上缴金额！');
                            return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/shopup",
                        data: formdata,
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                dlg.close().remove();
                                alert('上缴成功');
                                location.reload();
                            } else {
                                alert('上缴失败');
                            }
                        }
                    });
                });
                //添加按钮
                $(dlg.node).find('#paynum').off('click').on('click', function () {
                    
                    var fhtml = '<label class="fl" style="padding-right:20px;width:100px;">自定义上缴：</label>\
                                <div class="inputBox fl placeholder">\
                                    <input type="hidden" id="status" name="status" value="1" />\
                                    <input type="text" id="pay_num" name="money" style="width:110px;">\
                                </div>';
                        $("#paydiv").append(fhtml);
                        $('.saveBtn').text('自定义上缴');
                        $("#qsj").remove();$("#zdy").remove();
                });
            });
        }
        funcs.jfFn = function () 
        {
            
             //送气工上缴审核
            $(".sh").live('click', function () 
            {
                if (confirm('您是否已经审核确定？')) {
                    var td = $(this).parent();
                    var id = td.siblings('.id').text();
                    var shipper_id = $(this).siblings('.shipper_id').val();
                    var paymoney = td.siblings('.paymoney').text();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/confirmShipperPay",
                        data: {'id':id,'money':paymoney,'shipper_id':shipper_id},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status) {
                                // alert('已审核'+preview.msg+'元,'+preview.num+'笔app上缴金！');
                                // $("#form1").submit();
                                location.href="/payshop/paylog";
                            }
                            else
                            {
                                alert('请正确输入上缴金额');
                            }
                        }
                    });
                }
               
            });
             //门店上缴审核
            $(".mdsh").live('click', function () 
            {
                if (confirm('您是否已经审核确定？')) {
                    var td = $(this).parent();
                    var id = td.siblings('.id').text();//上缴记录id
                    var shop_id = $(this).siblings('.shop_id').val();
                    var paymoney = td.siblings('.paymoney').text();//审核资金
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/confirmShopPay",
                        data: {'id':id,'money':paymoney,'shop_id':shop_id},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status) {
                                // alert('已审核'+preview.msg+'元,'+preview.num+'笔app上缴金！');
                                // $("#form1").submit();
                                location.href="/payfilling/paylog";
                            }
                            else
                            {
                                alert('请正确输入上缴金额');
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
            this.jfFn();
            this.dialogFn();
        }
        funcs.init();
    });
});
