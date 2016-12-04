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
            $('.addCaigou').on('click', function () {
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
                        var admin_userHtml = '';
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
                        
                        if (preview['user']) {
                            for (key in preview['user']) {
                                admin_userHtml += "<option value='" + preview['user'][key]['user_id'] + "|" + preview['user'][key]['real_name'] + "'>" + preview['user'][key]['real_name'] + "</option>";
                            }
                        }

                        str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加采购计划单\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption poptxt clearfix">\
							<label class="fl">商品类型：</label>\
							<p class="fl">钢瓶</p>\
						</div>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">供应商：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="supplier_list" class="selectmenu" id="supplier_list" style="width:110px;">' + supplierHtml + '</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">种类：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="bottle_list" class="selectmenu"  style="width:110px;">' + bottleHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">采购数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="num" style="width:64px;">\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">采购单价：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="price" name="price" style="width:64px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="addplan">+添加</a></div>\
					</div>\
                                        <div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">审批人：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="admin_user_list" class="selectmenu" id="admin_user_list" style="width:110px;">' + admin_userHtml + '</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">采购明细：</label>\
							<div class="rqTabItem fl rqTabItem2" style="width:580px;">\
								<table>\
									<thead>\
										<tr>\
											<th>种类</th>\
											<th>数量</th>\
											<th>单价</th>\
											<th>金额</th>\
											<th>操作</th>\
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
							<textarea class="fl" name="comment" style="width:558px;height:140px;"></textarea>\
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
                    if(plist == ''){
                        alert('请添加数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/instock/bottleadd",
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
                $(dlg.node).find('#addplan').off('click').on('click', function () {
                    var bottle_id = $("#bottle_list option:selected").val();
                    var bottle_name = $("#bottle_list option:selected").text();
                    var supplier_id = $("#supplier_list option:selected").val();
                    var supplier_name = $("#supplier_list option:selected").text();
                    var num = $("#num").val();
                    var price = $("#price").val();
                    var blist = bottle_id + '|' + supplier_id + '|' + num + '|' + price;
                    var fhtml = '<tr>\
                                        <td>' + bottle_name + '</td>\
                                        <td>' + num + '</td>\
                                        <td>' + price + '</td>\
                                        <td>' + num * price + '</td>\
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

                //单选框
                utils.radioFn();
            });
        }
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 添加采购单
            this.dialogFn();
        }
        funcs.init();
    });
});
