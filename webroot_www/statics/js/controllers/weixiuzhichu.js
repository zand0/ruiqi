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
        funcs.dialogFn = function () {
            $('.addWxzc').on('click', function () {
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'bottle_plan'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        var supplierHtml = '';
                        if (preview['bottle']) {
                            for (key in preview['bottle']) {
                                bottleHtml += "<option value='" + key + "'>" + preview['bottle'][key] + "</option>";
                            }
                        }
                        if (preview['supplier']) {
                            for (key in preview['supplier']) {
                                supplierHtml += "<option value='" + key + "'>" + preview['supplier'][key]['name'] + "</option>";
                            }
                        }
                        str = '<div class="pop_cont">\
                                    <div class="contRTitle2">添加维修支出 <span></span></div>\
                                    <form name="form" method="POST" id="formtable">\
                                    <div class="rqLine">\
                                        <div class="rqOption rqOption2 clearfix">\
                                            <label class="fl">选择厂商：</label>\
                                            <div class="selectBox fl">\
                                                <div class="mySelect">\
                                                    <select name="supplier_id" id="supplier_id" class="selectmenu" style="width:180px">\
                                                        <option selected value="0">选择厂商</option>\
                                                        ' + supplierHtml + '\
                                                    </select>\
                                                </div>\
                                            </div>\
                                        </div>\
                                    </div>\
                                    <div class="rqLine">\
                                        <div class="rqOption rqOption2 clearfix">\
                                            <label class="fl">种类/规格：</label>\
                                            <div class="selectBox fl">\
                                                <div class="mySelect">\
                                                    <select name="btype" id="btype" class="selectmenu" style="width:180px">\
                                                        <option selected value="0">选择钢瓶规格</option>\
                                                        ' + bottleHtml + '\
                                                    </select>\
                                                </div>\
                                            </div>\
                                        </div>\
                                    </div>\
                                    <div class="rqLine">\
                                        <div class="rqOption rqOption2 clearfix">\
                                            <label class="fl">数量：</label>\
                                            <div class="inputBox fl placeholder"><input type="text" name="bnum" id="bnum" style="width:158px"></div>\
                                        </div>\
                                    </div>\
                                    <div class="rqLine">\
                                        <div class="rqOption rqOption2 clearfix">\
                                            <label class="fl">年检单价：</label>\
                                            <div class="inputBox fl placeholder"><input type="text" name="price" id="price" style="width:158px"></div>\
                                        </div>\
                                    </div>\
                                    <div class="pl95 mb20"><a href="javascript:;" class="buleColor " id="addspending">+添加维修支出</a></div>\
                                    <div class="rqLine">\
                                        <div class="rqOption rqOption2 clearfix">\
                                            <label class="fl">年检明细：</label>\
                                            <div class="rqTabItem rqTabItem2 fl" style="width:572px">\
                                                <table>\
                                                    <thead>\
                                                        <tr><th>钢瓶规格</th><th>数量</th><th>年检单价</th><th>金额</th><th>操作</th></tr>\
                                                    </thead>\
                                                    <tbody id="plist"></tbody>\
                                                </table>\
                                            </div>\
                                        </div>\
                                    </div>\
                                    <div class="rqLine">\
                                        <div class="rqOption rqOption2 clearfix riliInputBox">\
                                            <label class="fl">年检时间：</label><div class="fl riliInput"><input type="text" name="time" class="datepicker" style="width:158px" readonly></div>\
                                        </div>\
                                    </div>\
                                    <div class="rqLine">\
                                        <div class="rqOption rqOption2 clearfix">\
                                            <label class="fl">备注：</label>\
                                            <textarea class="fl" style="width:550px" name="comment" id="comment"></textarea>\
                                        </div>\
                                    </div>\
                                    <div class="rqLine rqLineBtn clearfix">\
                                        <div class="rqOption fl"><a href="javascript:;" class="blueBtn saveBtn">保存</a></div>\
                                        <div class="rqOption fl"><a href="javascript:;" class="grayBtn closeBtn">取消</a></div>\
                                    </div>\
                                    </form>\
                                </div>';
                    }
                });
                
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '560px'
                })

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var plist = $("#plist").html();
                    if(plist == ''){
                        alert('请添加数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/spending/addservice",
                        data: formdata,
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                dlg.close().remove();
                                location.reload();
                            } else {
                                alert('创建失败');
                            }
                        }
                    });
                });
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
                
                //添加按钮
                $(dlg.node).find('#addspending').off('click').on('click', function () {
                    var supplier_id = $("#supplier_id option:selected").val();
                    var supplier_text = $("#supplier_id option:selected").text();
                    var btype = $("#btype option:selected").val();
                    var btype_text = $("#btype option:selected").text();
                    var bnum = $("#bnum").val();
                    var price = $("#price").val();
                    var blist = supplier_id + '|' + supplier_text + '|' + btype + '|' + bnum + '|' + price + '|' + btype_text;
                    var fhtml = '<tr>\
                                                    <td>' + btype_text + '</td>\
                                                    <td>' + bnum + '</td>\
                                                    <td>' + price + '</td>\
                                                    <td>' + bnum * price + '</td>\
                                                    <td class="tableBtn"><a href="javascript:;" id="del">删除</a><input type="hidden" name="supplier_list[]" value="' + blist + '" /></td>\
                                            </tr>';
                    if (bnum > 0) {
                        $("#plist").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#del').off('click').live('click', function () {
                    $(this).parent().parent().remove();
                });
                
                //日历
                utils.datepickerFn();
            });
        }
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 派发
            this.dialogFn();
        }
        funcs.init();
    });
});