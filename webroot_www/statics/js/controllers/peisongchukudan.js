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

        funcs.chukuFn = function () {
            $('.addChukudan').on('click', function () {
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'all'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        var productHtml = '';
                        var shopHtml = '';
                        var carHtml = '';
                        var userHtml = '';
                        if (preview['bottle']) {
                            for (key in preview['bottle']) {
                                bottleHtml += "<option value='" + preview['bottle'][key]['norm_id'] + "|" + preview['bottle'][key]['direct_price'] + "'>" + preview['bottle'][key]['typename'] + "</option>";
                            }
                        }

                        if (preview['product']) {
                            for (key in preview['product']) {
                                productHtml += "<option value='" + preview['product'][key]['id'] + "|" + preview['product'][key]['norm_id'] + "|" + preview['product'][key]['direct_price'] + "'>" + preview['product'][key]['name'] + "|" + preview['product'][key]['typename'] + "</option>";
                            }
                        }

                        if (preview['shop']) {
                            for (key in preview['shop']) {
                                shopHtml += "<option value='" + preview['shop'][key]['shop_id'] + "'>" + preview['shop'][key]['shop_name'] + "</option>";
                            }
                        }

                        if (preview['car']) {
                            for (key in preview['car']) {
                                carHtml += "<option value='" + preview['car'][key]['license_plate'] + "'>" + preview['car'][key]['license_plate'] + "</option>";
                            }
                        }

                        if (preview['user']) {
                            for (key in preview['user']) {
                                userHtml += "<option value='" + preview['user'][key]['user_id'] + "|" + preview['user'][key]['username'] + "'>" + preview['user'][key]['username'] + "</option>";
                            }
                        }
                        
                        str = '<div class="pop_cont">\
			        <div class="contRTitle2">\
						添加配送出库单\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">车牌号：</label>\
                                                        <div class="selectBox fl">\
								<div class="mySelect">\
									<select name="license_plate" id="license_plate" class="selectmenu"  style="width:132px;">' + carHtml + '</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">押运员：</label>\
                                                        <div class="mySelect">\
                                                                <select name="guards" id="guards" class="selectmenu"  style="width:132px;">' + userHtml + '</select>\
                                                        </div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">选择门店：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" id="shop_type" style="width:132px;">' + shopHtml + '</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
                                                <div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">钢瓶类型：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="bottle_type" class="selectmenu"  style="width:132px;">' + bottleHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">钢瓶数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="bottle_num" style="width:110px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="add_bottle">+添加</a></div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">配件：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="product_type" class="selectmenu"  style="width:132px;">' + productHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">配送数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="product_num" style="width:110px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="add_product">+添加</a></div>\
					</div>\
					<div class="rqTabItem">\
						<table>\
							<thead>\
								<tr>\
									<th>配送对象</th>\
									<th>商品类别</th>\
									<th>商品规格</th>\
									<th>数量</th>\
									<th>操作</th>\
								</tr>\
							</thead>\
							<tbody id="plist"></tbody>\
						</table>\
					</div>\
					<div class="rqLine clearfix">\
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
                        alert('请添加出库数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/filling/adddelivery",
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
                $(dlg.node).find('#add_bottle').off('click').on('click', function () {
                    var shop_id = $("#shop_type option:selected").val();
                    var shop_name = $("#shop_type option:selected").text();
                    var bottle_id = $("#bottle_type option:selected").val();
                    var bottle_text = $("#bottle_type option:selected").text();
                    var bottle_num = $("#bottle_num").val();
                    var blist = shop_id + '|' + bottle_id + '|' + bottle_num;
                    var fhtml = '<tr>\
                                            <td>' + shop_name + '</td>\
                                            <td>液化气</td>\
                                            <td>' + bottle_text + '</td>\
                                            <td>' + bottle_num + '</td>\
                                            <td class="tableBtn"><a href="javascript:;" id="del">删除</a><input type="hidden" name="bottle_list[]" value="' + blist + '" /></td>\
                                    </tr>';
                    if (bottle_num > 0) {
                        $("#plist").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#add_product').off('click').on('click', function () {
                    var shop_id = $("#shop_type option:selected").val();
                    var shop_name = $("#shop_type option:selected").text();
                    var product_id = $("#product_type option:selected").val();
                    var product_text = $("#product_type option:selected").text();
                    var product_num = $("#product_num").val();
                    var blist = shop_id + '|' + product_id + '|' + product_num;
                    var fhtml = '<tr>\
                                            <td>' + shop_name + '</td>\
                                            <td>配件</td>\
                                            <td>' + product_text + '</td>\
                                            <td>' + product_num + '</td>\
                                            <td class="tableBtn"><a href="javascript:;" id="del">删除</a><input type="hidden" name="product_list[]" value="' + blist + '" /></td>\
                                    </tr>';
                    if (product_num > 0) {
                        $("#plist").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#del').off('click').live('click', function () {
                    $(this).parent().parent().remove();
                });
            });

            $(".editChukudan").on('click', function(){
                var _this = this;
                var str = '';
                var delivery_no = $(this).attr('dso');
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'all', 'dso': delivery_no},
                    success: function(result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        var productHtml = '';
                        var shopHtml = '';
                        var carHtml = '';
                        var userHtml = '';
                        var dataHtml = '';
                        if (preview['bottle']) {
                            for (key in preview['bottle']) {
                                bottleHtml += "<option value='" + preview['bottle'][key]['norm_id'] + "|" + preview['bottle'][key]['direct_price'] + "'>" + preview['bottle'][key]['typename'] + "</option>";
                            }
                        }

                        if (preview['product']) {
                            for (key in preview['product']) {
                                productHtml += "<option value='" + preview['product'][key]['id'] + "|" + preview['product'][key]['norm_id'] + "|" + preview['product'][key]['direct_price'] + "'>" + preview['product'][key]['name'] + "|" + preview['product'][key]['typename'] + "</option>";
                            }
                        }

                        if (preview['shop']) {
                            for (key in preview['shop']) {
                                shopHtml += "<option value='" + preview['shop'][key]['shop_id'] + "'>" + preview['shop'][key]['shop_name'] + "</option>";
                            }
                        }

                        if (preview['car']) {
                            for (key in preview['car']) {
                                carHtml += "<option value='" + preview['car'][key]['license_plate'] + "'>" + preview['car'][key]['license_plate'] + "</option>";
                            }
                        }

                        if (preview['user']) {
                            for (key in preview['user']) {
                                userHtml += "<option value='" + preview['user'][key]['user_id'] + "|" + preview['user'][key]['username'] + "'>" + preview['user'][key]['username'] + "</option>";
                            }
                        }
                        
                        if (preview['cdata']) {
                            for (key in preview['cdata']) {
                                var md_id = preview['cdata'][key]['shop_id'];
                                var md_tid = preview['cdata'][key]['ftype'];
                                var md_fid = preview['cdata'][key]['fid'];

                                if (preview['cdata'][key]['ftype'] == 1) {
                                    var md_list = preview['cdata'][key]['shop_id'] + '|' + preview['cdata'][key]['fid'] + '|' + preview['cdata'][key]['money'] + '|' + preview['cdata'][key]['num'];
                                    dataHtml += "<tr>" +
                                            "<td>" + preview['shop'][md_id]['shop_name'] + "</td>" +
                                            "<td>液化气</td><td>液化气-" + preview['jbottle'][md_fid] + "</td><td>" + preview['cdata'][key]['num'] + "</td>" +
                                            "<td class='tableBtn'><a href='javascript:;' id='del'>删除</a>" +
                                            "<input type='hidden' name='bottle_list[]' value='" + md_list + "'></td>" +
                                            "</tr>";
                                } else {
                                    var md_list = preview['cdata'][key]['shop_id'] + '|' + preview['cdata'][key]['fid'] + '|' + preview['cdata'][key]['type'] + '|' + preview['cdata'][key]['money'] + '|' + preview['cdata'][key]['num'];
                                    var name_list = preview['jcommdity'][md_fid]['name'];
                                    if (preview['jcommdity'][md_fid]['typename']) {
                                        name_list += "-" + preview['jcommdity'][md_fid]['typename'];
                                    }
                                    dataHtml += "<tr>" +
                                            "<td>" + preview['shop'][md_id]['shop_name'] + "</td>" +
                                            "<td>配件</td><td>" + name_list + "</td><td>" + preview['cdata'][key]['num'] + "</td>" +
                                            "<td class='tableBtn'><a href='javascript:;' id='del'>删除</a>" +
                                            "<input type='hidden' name='product_list[]' value='" + md_list + "'></td>" +
                                            "</tr>";
                                }
                            }
                        }
                        
                        str = '<div class="pop_cont">\
			        <div class="contRTitle2">\
						添加配送出库单\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">车牌号：</label>\
                                                        <div class="selectBox fl">\
								<div class="mySelect">\
									<select name="license_plate" id="license_plate" class="selectmenu"  style="width:132px;">' + carHtml + '</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">押运员：</label>\
                                                        <div class="mySelect">\
                                                                <select name="guards" id="guards" class="selectmenu"  style="width:132px;">' + userHtml + '</select>\
                                                        </div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">选择门店：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" id="shop_type" style="width:132px;">' + shopHtml + '</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
                                                <div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">钢瓶类型：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="bottle_type" class="selectmenu"  style="width:132px;">' + bottleHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">钢瓶数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="bottle_num" style="width:110px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="add_bottle">+添加</a></div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">配件：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="product_type" class="selectmenu"  style="width:132px;">' + productHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">配送数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="product_num" style="width:110px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="add_product">+添加</a></div>\
					</div>\
					<div class="rqTabItem">\
						<table>\
							<thead>\
								<tr>\
									<th>配送对象</th>\
									<th>商品类别</th>\
									<th>商品规格</th>\
									<th>数量</th>\
									<th>操作</th>\
								</tr>\
							</thead>\
							<tbody id="plist">' + dataHtml + '</tbody>\
						</table>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
                                                        <input type="hidden" name="delivery_no" id="delivery_no" value="' + delivery_no + '" />\
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
                        alert('请添加出库数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/filling/editdelivery",
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
                $(dlg.node).find('#add_bottle').off('click').on('click', function () {
                    var shop_id = $("#shop_type option:selected").val();
                    var shop_name = $("#shop_type option:selected").text();
                    var bottle_id = $("#bottle_type option:selected").val();
                    var bottle_text = $("#bottle_type option:selected").text();
                    var bottle_num = $("#bottle_num").val();
                    var blist = shop_id + '|' + bottle_id + '|' + bottle_num;
                    var fhtml = '<tr>\
                                            <td>' + shop_name + '</td>\
                                            <td>液化气</td>\
                                            <td>' + bottle_text + '</td>\
                                            <td>' + bottle_num + '</td>\
                                            <td class="tableBtn"><a href="javascript:;" id="del">删除</a><input type="hidden" name="bottle_list[]" value="' + blist + '" /></td>\
                                    </tr>';
                    if (bottle_num > 0) {
                        $("#plist").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#add_product').off('click').on('click', function () {
                    var shop_id = $("#shop_type option:selected").val();
                    var shop_name = $("#shop_type option:selected").text();
                    var product_id = $("#product_type option:selected").val();
                    var product_text = $("#product_type option:selected").text();
                    var product_num = $("#product_num").val();
                    var blist = shop_id + '|' + product_id + '|' + product_num;
                    var fhtml = '<tr>\
                                            <td>' + shop_name + '</td>\
                                            <td>配件</td>\
                                            <td>' + product_text + '</td>\
                                            <td>' + product_num + '</td>\
                                            <td class="tableBtn"><a href="javascript:;" id="del">删除</a><input type="hidden" name="product_list[]" value="' + blist + '" /></td>\
                                    </tr>';
                    if (product_num > 0) {
                        $("#plist").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#del').off('click').live('click', function () {
                    $(this).parent().parent().remove();
                });
            });

            //气站出库单审核
            $(".shenheBtn").live('click', function () {
                var dso = $(this).attr('dso');
                var that = $(this);
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/ckstock",
                    data: {dso: dso},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        if (preview.status >= 1) {
                            that.parent('td').html('已受理');
                            that.removeClass('shenheBtn');
                            that.removeClass('blueColor').addClass('grayColor').html('已受理');
                        } else {
                            alert('操作失败');
                        }
                    }
                });
            });
            
        }
        funcs.printarea = function() {
            var printAreaCount = 0;
            $.fn.printArea = function () {
                var ele = $(this);
                var idPrefix = "printArea_";
                removePrintArea(idPrefix + printAreaCount);
                printAreaCount++;
                var iframeId = idPrefix + printAreaCount;
                var iframeStyle = 'position:absolute;width:0px;height:0px;left:-500px;top:-500px;';
                iframe = document.createElement('IFRAME');
                $(iframe).attr({
                    style: iframeStyle,
                    id: iframeId
                });
                document.body.appendChild(iframe);
                var doc = iframe.contentWindow.document;
                $(document).find("link").filter(function () {
                    return $(this).attr("rel").toLowerCase() == "stylesheet";
                }).each(
                        function () {
                            doc.write('<link type="text/css" rel="stylesheet" href="'
                                    + $(this).attr("href") + '" >');
                        });
                doc.write('<div class="' + $(ele).attr("class") + '">' + $(ele).html()
                        + '</div>');
                doc.close();
                var frameWindow = iframe.contentWindow;
                frameWindow.close();
                frameWindow.focus();
                frameWindow.print();
            }
            var removePrintArea = function (id) {
                $("iframe#" + id).remove();
            };
            
            //打单
            $("#btnPrint").on('click', function() {
                $("#printContent").printArea();
            });
        }
        
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 添加出库单
            this.chukuFn();
            
            //局部打印功能
            this.printarea();
        }
        funcs.init();
    });
});