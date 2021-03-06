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
        funcs.chukuFn = function () {
            $('.addChuku').on('click', function () {
                var _this = this;

                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'bottle_kucun'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        var typeHtml = '';
                        if (preview['bottle']) {
                            for (key in preview['bottle']) {
                                bottleHtml += "<option value='" + key + "'>" + preview['bottle'][key] + "</option>";
                            }
                        }
                        if (preview['type']) {
                            for (key in preview['type']) {
                                typeHtml += "<option value='" + key + "'>" + preview['type'][key] + "</option>";
                            }
                        }
                        var shopHtml = "<option value=''>选择门店</option>";
                        if (preview['shop']) {
                            for (key in preview['shop']) {
                                shopHtml += "<option value='" + preview['shop'][key]['shop_id'] + "'>" + preview['shop'][key]['shop_name'] + "</option>";
                            }
                        }
                        str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						添加钢瓶出库\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">钢瓶类别：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="typeid" id="type_id" class="selectmenu" style="width:100px;">' + typeHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption3 fl">\
							<label class="fl">规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="bottle_type" id="bottle_type" class="selectmenu" style="width:100px;">' + bottleHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption3 fl">\
							<label class="fl">数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="bottle_num" name="bottle_num" style="width:80px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">出库对象：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="shop_id" id="shop_id" class="selectmenu" style="width:120px;">' + shopHtml + '</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="pl95 mb20"><a href="javascript:;" class="buleColor" id="addplan">+添加出库明细</a></div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">出库明细：</label>\
							<div class="rqTabItem fl" style="width:580px;">\
								<table>\
									<thead>\
										<tr>\
											<th>产品类别</th>\
											<th>产品规格</th>\
											<th>数量</th>\
											<th>出库对象</th>\
                                                                                        <th>出库对象</th>\
										</tr>\
									</thead>\
									<tbody id="plist"></tbody>\
								</table>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
                                                        <input type="hidden" id="btype" name="btype" value="2" />\
							<textarea class="fl" name="comment" id="comment" style="width:558px;height:140px;"></textarea>\
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
                    height: '560px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var plist = $("#plist").html();
                    if (plist == '') {
                        alert('请选择出入库数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/stock/addflow",
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

                //添加按钮
                $(dlg.node).find('#addplan').off('click').on('click', function () {
                    var type_id = $("#type_id option:selected").val();
                    var type_name = $("#type_id option:selected").text();
                    var bottle_type = $("#bottle_type option:selected").val();
                    var bottle_name = $("#bottle_type option:selected").text();
                    var num = $("#bottle_num").val();
                    var form_name = $("#shop_id option:selected").text();
                    var shop_id = $("#shop_id option:selected").val();
                    var blist = type_id + '|' + bottle_type + '|' + num + '|' + shop_id;
                    var fhtml = '<tr>\
                                            <td>' + type_name + '</td>\
                                            <td>' + bottle_name + '</td>\
                                            <td>' + num + '</td>\
                                            <td>' + form_name + '</td>\
                                            <td class="tableBtn"><a href="javascript:;" id="del">删除</a><input type="hidden" name="list[]" value="' + blist + '" /></td>\
                                    </tr>';
                    if (num > 0) {
                        $("#plist").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#del').off('click').live('click', function () {
                    $(this).parent().parent().remove();
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
                    data: {'type': 'bottle_kucun'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        var typeHtml = '';
                        if (preview['bottle']) {
                            for (key in preview['bottle']) {
                                bottleHtml += "<option value='" + key + "'>" + preview['bottle'][key] + "</option>";
                            }
                        }
                        if (preview['type']) {
                            for (key in preview['type']) {
                                typeHtml += "<option value='" + key + "'>" + preview['type'][key] + "</option>";
                            }
                        }
                        var shopHtml = "<option value=''>选择门店</option>";
                        if (preview['shop']) {
                            for (key in preview['shop']) {
                                shopHtml += "<option value='" + preview['shop'][key]['shop_id'] + "'>" + preview['shop'][key]['shop_name'] + "</option>";
                            }
                        }
                        str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						添加钢瓶入库\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">钢瓶类别：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="typeid" id="type_id" class="selectmenu" style="width:100px;">' + typeHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption3 fl">\
							<label class="fl">规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="bottle_type" id="bottle_type" class="selectmenu" style="width:100px;">' + bottleHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption3 fl">\
							<label class="fl">数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="bottle_num" name="bottle_num" style="width:80px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">出库对象：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="shop_id" id="shop_id" class="selectmenu" style="width:120px;">' + shopHtml + '</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="pl95 mb20"><a href="javascript:;" class="buleColor" id="addplan">+添加出库明细</a></div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">出库明细：</label>\
							<div class="rqTabItem fl" style="width:580px;">\
								<table>\
									<thead>\
										<tr>\
											<th>产品类别</th>\
											<th>产品规格</th>\
											<th>数量</th>\
											<th>出库对象</th>\
                                                                                        <th>出库对象</th>\
										</tr>\
									</thead>\
									<tbody id="plist"></tbody>\
								</table>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
                                                        <input type="hidden" id="btype" name="btype" value="1" />\
							<textarea class="fl" name="comment" id="comment" style="width:558px;height:140px;"></textarea>\
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
                    height: '560px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var plist = $("#plist").html();
                    if (plist == '') {
                        alert('请选择出入库数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/stock/addflow",
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

                //添加按钮
                $(dlg.node).find('#addplan').off('click').on('click', function () {
                    var type_id = $("#type_id option:selected").val();
                    var type_name = $("#type_id option:selected").text();
                    var bottle_type = $("#bottle_type option:selected").val();
                    var bottle_name = $("#bottle_type option:selected").text();
                    var num = $("#bottle_num").val();
                    var form_name = $("#shop_id option:selected").text();
                    var shop_id = $("#shop_id option:selected").val();
                    var blist = type_id + '|' + bottle_type + '|' + num + '|' + shop_id;
                    var fhtml = '<tr>\
                                            <td>' + type_name + '</td>\
                                            <td>' + bottle_name + '</td>\
                                            <td>' + num + '</td>\
                                            <td>' + form_name + '</td>\
                                            <td class="tableBtn"><a href="javascript:;" id="del">删除</a><input type="hidden" name="list[]" value="' + blist + '" /></td>\
                                    </tr>';
                    if (num > 0) {
                        $("#plist").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#del').off('click').live('click', function () {
                    $(this).parent().parent().remove();
                });

                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
            })
        }
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 添加出库
            this.chukuFn();
            // 添加入库
            this.rukuFn();
            // 级联日历
            utils.doubleRiliFn($('#time_start2'), $('#time_end2'));
        }
        funcs.init();
    });
});