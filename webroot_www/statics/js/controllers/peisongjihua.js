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
            $('.addDelivery').on('click', function () {

                //获取相关配件类型 钢瓶类型
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': ''},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        var productHtml = '';
                        var bstatusHtml = '';
                        if (preview['bottle']) {
                            for (key in preview['bottle']) {
                                bottleHtml += "<option value='" + key + "'>" + preview['bottle'][key] + "</option>";
                            }
                        }

                        var productHtml = '';
                        if (preview['product']) {
                            for (key in preview['product']) {
                                productHtml += "<option value='" + preview['product'][key]['id'] + "|" + preview['product'][key]['norm_id'] + "'>" + preview['product'][key]['name'] + "|" + preview['product'][key]['typename'] + "</option>";
                            }
                        }

                        if (preview['bottle_status']) {
                            for (key in preview['bottle_status']) {
                                bstatusHtml += "<option value='" + key + "'>" + preview['bottle_status'][key] + "</option>";
                            }
                        }

                        str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加配送计划\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix poptxt">\
							<label class="fl">配送类别：</label>\
							<p class="fl">门店配送</p>\
						</div>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">重瓶规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="ps_bottle_type" class="selectmenu"  style="width:120px;">' + bottleHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">配送数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="ps_bottle_num" style="width:100px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="ps_add_bottle">+添加</a></div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">配件规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="ps_product_type" class="selectmenu"  style="width:120px;">' + productHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">配送数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="ps_product_num" style="width:56px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="ps_add_product">+添加</a></div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">配送明细：</label>\
							<div class="rqTabItem fl rqTabItem2" style="width:580px;">\
								<table>\
									<thead>\
										<tr>\
											<th>类别</th>\
											<th>数量</th>\
											<th>操作</th>\
										</tr>\
									</thead>\
									<tbody id="ps_list"></tbody>\
								</table>\
							</div>\
						</div>\
					</div>\
					<div class="cuttingLine mb20"></div>\
					<div class="rqLine">\
						<div class="rqOption clearfix poptxt">\
							<label class="fl">配送类别：</label>\
							<p class="fl">门店回库</p>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">钢瓶类别：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="hk_bottle" class="selectmenu"  style="width:100px;">' + bstatusHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">钢瓶规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="hk_bottle_type" class="selectmenu"  style="width:100px;">' + bottleHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">回库数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="hk_bottle_num" style="width:40px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="hk_bottle_add">+添加</a></div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">配件规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="hk_product_type" class="selectmenu"  style="width:132px;">' + productHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">回库数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="hk_product_num" style="width:56px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="hk_product_add">+添加</a></div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">回库明细：</label>\
							<div class="rqTabItem rqTabItem2 fl" style="width:580px;">\
								<table>\
									<thead>\
										<tr>\
											<th>类别</th>\
											<th>规格</th>\
											<th>数量</th>\
											<th>操作</th>\
										</tr>\
									</thead>\
									<tbody id="hk_list"></tbody>\
								</table>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
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
                    var ps_list = $("#ps_list").html();
                    if (ps_list == '') {
                        alert('请添加配送数据');
                        return false;
                    }

                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/filling/add",
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

                //添加按钮
                $(dlg.node).find('#ps_add_bottle').off('click').on('click', function () {
                    var bottle_id = $("#ps_bottle_type option:selected").val();
                    var bottle_text = $("#ps_bottle_type option:selected").text();
                    var bottle_num = $("#ps_bottle_num").val();
                    var blist = bottle_id + '|' + bottle_num;
                    var fhtml = '<tr><td>' + bottle_text + '</td>\
                                <td>' + bottle_num + '</td>\
                                <td class="tableBtn"><a href="javascript:;" id="ps_del">删除</a><input type="hidden" name="ps_bottle_list[]" value="' + blist + '" /></td></tr>';
                    if (bottle_num > 0) {
                        $("#ps_list").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#hk_bottle_add').off('click').on('click', function () {
                    var bottle_type_id = $("#hk_bottle option:selected").val();
                    var bottle_type_text = $("#hk_bottle option:selected").text();
                    var bottle_id = $("#hk_bottle_type option:selected").val();
                    var bottle_text = $("#hk_bottle_type option:selected").text();
                    var bottle_num = $("#hk_bottle_num").val();
                    var blist = bottle_type_id + '|' + bottle_id + '|' + bottle_num;
                    var fhtml = '<tr><td>' + bottle_type_text + '</td>\
                                <td>' + bottle_text + '</td>\
                                <td>' + bottle_num + '</td>\
                                <td class="tableBtn"><a href="javascript:;" id="ps_del">删除</a><input type="hidden" name="hk_bottle_list[]" value="' + blist + '" /></td></tr>';
                    if (bottle_num > 0) {
                        $("#hk_list").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#ps_add_product').off('click').on('click', function () {
                    var product_id = $("#ps_product_type option:selected").val();
                    var product_text = $("#ps_product_type option:selected").text();
                    var product_num = $("#ps_product_num").val();
                    var plist = product_id + '|' + product_num;
                    var fhtml = '<tr><td>' + product_text + '</td>\
                                <td>' + product_num + '</td>\
                                <td class="tableBtn"><a href="javascript:;" id="ps_del">删除</a><input type="hidden" name="ps_product_list[]" value="' + plist + '" /></td></tr>';
                    if (product_num > 0) {
                        $("#ps_list").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#hk_product_add').off('click').on('click', function () {
                    var product_id = $("#hk_product_type option:selected").val();
                    var product_text = $("#hk_product_type option:selected").text();
                    var product_num = $("#hk_product_num").val();
                    var plist = product_id + '|' + product_num;
                    var fhtml = '<tr><td>配件</td>\
                                <td>' + product_text + '</td>\
                                <td>' + product_num + '</td>\
                                <td class="tableBtn"><a href="javascript:;" id="ps_del">删除</a><input type="hidden" name="hk_product_list[]" value="' + plist + '" /></td></tr>';
                    if (product_num > 0) {
                        $("#hk_list").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#ps_del').off('click').live('click', function () {
                    $(this).parent().parent().remove();
                });
            });
        }
        funcs.shenheFn = function () {
            $(document).on('click', '.shenheBtn', function () {
                var type_sn = $(this).attr('fsn');
                var that = $(this);
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datastatus",
                    data: {'type_sn': type_sn, 'type_name': 'filling'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        if (preview.status >= 1) {
                            that.removeClass('blueColor').addClass('grayColor').html('已受理');
                        } else {
                            alert('操作失败');
                        }
                    }
                });

            });
        }
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 发布消息
            this.dialogFn();
            // 审核
            this.shenheFn();
        }
        funcs.init();
    });
});
