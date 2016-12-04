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
        funcs.page2Fn = function () {
            var curPage = parseInt($('.currentPage').val() || 1);
            var totalPage = parseInt($('.totalPage').val() || 1);
            var pager = new PageNav({
                currentPage: curPage,
                totalPage: totalPage,
                wrapId: '#pageNav2',
                callback: function (curPage) {
                    alert(curPage)
                }
            });
        }
        funcs.chukuFn = function () {
            $('.addChuku').on('click', function () {
                var _this = this;

                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'gas_kucun'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var gasHtml = '';
                        var tankHtml = '';
                        if (preview['gas']) {
                            for (key in preview['gas']) {
                                gasHtml += "<option value='" + key + "'>" + preview['gas'][key]['gas_name'] + "</option>";
                            }
                        }
                        if (preview['tank']) {
                            for (key in preview['tank']) {
                                tankHtml += "<option value='" + key + "'>" + preview['tank'][key]['tank_name'] + "</option>";
                            }
                        }
                        str = '<div class="pop_cont">\
                                <div class="contRTitle2 ">\
                                        添加燃气出库\
                                        <span></span>\
                                </div>\
                                <form name="form" method="POST" id="formtable">\
                                <div class="rqLine clearfix">\
                                        <div class="rqOption fl">\
                                                <label class="fl">燃气类别：</label>\
                                                <div class="selectBox fl">\
                                                        <div class="mySelect">\
                                                                <select id="gas_type" name="gas_type" class="selectmenu" style="width:150px;">' + gasHtml + '</select>\
                                                        </div>\
                                                </div>\
                                        </div>\
                                        <div class="rqOption3 fl">\
                                                <label class="fl">漕车车牌号：</label>\
                                                <div class="inputBox fl placeholder">\
                                                        <input type="text" id="car_type" name="car_type" style="width:80px;">\
                                                </div>\
                                        </div>\
                                        <div class="rqOption3 fl">\
                                                <label class="fl">重量：</label>\
                                                <div class="inputBox fl placeholder">\
                                                        <input type="text" id="num" name="num" style="width:80px;">\
                                                </div>\
                                        </div>\
                                </div>\
                                <div class="rqLine clearfix">\
                                        <div class="rqOption fl">\
                                                <label class="fl">出库对象：</label>\
                                                <div class="selectBox fl">\
                                                        <div class="mySelect">\
                                                                <select id="tank_type" name="tank_type" class="selectmenu" style="width:150px;">' + tankHtml + '</select>\
                                                        </div>\
                                                </div>\
                                        </div>\
                                        <div class="rqOption fl">\
                                                <label class="fl">操作人：</label>\
                                                <div class="inputBox fl placeholder">\
                                                        <input type="text" id="cz_user" name="cz_user" style="width:120px;">\
                                                </div>\
                                        </div>\
                                </div>\
                                <div class="rqLine">\
                                        <div class="rqOption clearfix riliInputBox">\
                                                <label class="fl">出库时间：</label>\
                                                <div class="fl riliInput">\
                                                        <input type="text" name="ck_time" class="datepicker" id="ck_time" style="width:140px;" readonly>\
                                                </div>\
                                        </div>\
                                </div>\
                                <div class="rqLine">\
                                        <div class="rqOption clearfix">\
                                                <label class="fl">备注：</label>\
                                                <input type="hidden" id="type" name="type" value="1" />\
                                                <textarea class="fl" id="comment" name="comment" style="width:558px;height:140px;"></textarea>\
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
                    }
                });

                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '520px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/gasinout/add",
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
            })
        }
        funcs.rukuFn = function () {
            $('.addRuku').on('click', function () {
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'gas_kucun'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var gasHtml = '';
                        var tankHtml = '';
                        if (preview['gas']) {
                            for (key in preview['gas']) {
                                gasHtml += "<option value='" + key + "'>" + preview['gas'][key]['gas_name'] + "</option>";
                            }
                        }
                        if (preview['tank']) {
                            for (key in preview['tank']) {
                                tankHtml += "<option value='" + key + "'>" + preview['tank'][key]['tank_name'] + "</option>";
                            }
                        }
                        str = '<div class="pop_cont">\
                                    <div class="contRTitle2 ">\
                                            添加燃气入库\
                                            <span></span>\
                                    </div>\
                                    <form name="form" method="POST" id="formtable">\
                                    <div class="rqLine clearfix">\
                                            <div class="rqOption fl">\
                                                    <label class="fl">燃气类别：</label>\
                                                    <div class="selectBox fl">\
                                                            <div class="mySelect">\
                                                                    <select name="gas_type" id="gas_type" class="selectmenu" style="width:150px;">' + gasHtml + '</select>\
                                                            </div>\
                                                    </div>\
                                            </div>\
                                            <div class="rqOption3 fl">\
                                                    <label class="fl">漕车车牌号：</label>\
                                                    <div class="inputBox fl placeholder">\
                                                            <input type="text" name="car_type" id="car_type" style="width:80px;">\
                                                    </div>\
                                            </div>\
                                            <div class="rqOption3 fl">\
                                                    <label class="fl">重量：</label>\
                                                    <div class="inputBox fl placeholder">\
                                                            <input type="text" name="num" id="num" style="width:80px;">\
                                                    </div>\
                                            </div>\
                                    </div>\
                                    <div class="rqLine clearfix">\
                                            <div class="rqOption fl">\
                                                    <label class="fl">出库对象：</label>\
                                                    <div class="selectBox fl">\
                                                            <div class="mySelect">\
                                                                    <select name="tank_type" id="tank_type" class="selectmenu" style="width:150px;">' + tankHtml + '</select>\
                                                            </div>\
                                                    </div>\
                                            </div>\
                                            <div class="rqOption fl">\
                                                    <label class="fl">操作人：</label>\
                                                    <div class="inputBox fl placeholder">\
                                                            <input type="text" name="cz_user" id="cz_user" style="width:120px;">\
                                                    </div>\
                                            </div>\
                                    </div>\
                                    <div class="rqLine">\
                                            <div class="rqOption clearfix riliInputBox">\
                                                    <label class="fl">出库时间：</label>\
                                                    <div class="fl riliInput">\
                                                            <input type="text" name="ck_time" class="datepicker" id="ck_time" style="width:120px;" readonly>\
                                                    </div>\
                                            </div>\
                                    </div>\
                                    <div class="rqLine">\
                                            <div class="rqOption clearfix">\
                                                    <label class="fl">备注：</label>\
                                                    <textarea class="fl" name="comment" id="comment" style="width:558px;height:120px;"></textarea>\
                                                    <input type="hidden" id="type" name="type" value="0" />\
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
                    }
                });
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '520px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/gasinout/add",
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
                // 日历
                utils.datepickerFn();
            })
        }
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 分页2
            this.page2Fn();
            // 添加出库
            this.chukuFn();
            // 添加入库
            this.rukuFn();
            // 日历
            utils.doubleRiliFn($('#time_start2'), $('#time_end2'));
        }
        funcs.init();
    });
});