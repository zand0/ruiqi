require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils'], function ($, utils) {
        var funcs = {};
        
        funcs.paifaFn = function () {
            $(document).on('click', '.paifaBtn', function () {
                var ordersn = $(this).attr('ordersn');
                var ordershop = $(this).attr('ordershop');

                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'shop', 'idsn': ordershop},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var shopHtml = '';
                        var shipperHtml = '';
                        if (preview['shop']) {
                            for (key in preview['shop']) {
                                if (preview['shop'][key]['shop_id'] == ordershop) {
                                    shopHtml += "<option value='" + preview['shop'][key]['shop_id'] + "' selected='selected'>" + preview['shop'][key]['shop_name'] + "</option>";
                                } else {
                                    shopHtml += "<option value='" + preview['shop'][key]['shop_id'] + "'>" + preview['shop'][key]['shop_name'] + "</option>";
                                }
                            }
                        }
                        var shipperHtml = "<option value='0'>选择送气工</option>";
                        if (preview['shipper']) {
                            for (key in preview['shipper']) {
                                shipperHtml += "<option value='" + key + '|' + preview['shipper'][key]['shipper_name'] + '|' + preview['shipper'][key]['mobile_phone'] + "'>" + preview['shipper'][key]['shipper_name'] + "</option>";
                            }
                        }
                        str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						派发订单\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">派发对象：</label>\
							<div class="selectBox fl rqOption" style="width:170px;">\
								<div class="mySelect">\
									<select name="shop_id" id="shop_id" class="selectmenu">\
										<option selected="selected">请选择对象</option>\
										' + shopHtml + '\
									</select>\
								</div>\
							</div>\
                                                        <label class="fl">派发对象：</label>\
							<div class="selectBox fl rqOption" style="width:170px;">\
								<div class="mySelect">\
									<select name="shipper_id" id="shipper_id" class="selectmenu">\
										<option selected="selected">请选择送气工</option>\\n\
                                                                                ' + shipperHtml + '\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn clearfix">\
						<div class="rqOption fl">\
                                                        <input type="hidden" name="order_sn" id="ordersn" value="' + ordersn + '" />\
							<a href="javascript:;" class="blueBtn saveBtn">派发</a>\
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
                    height: '200px'
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
                        url: "/order/distribution",
                        data: formdata,
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                dlg.close().remove();
                                location.reload();
                            } else {
                                alert('派发失败');
                            }
                        }
                    });
                });
                $(dlg.node).find("#shop_id").live('selectmenuchange', function () {
                    var shop_id = $(this).val();
                    getShipperHtml(shop_id);
                });
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
            });
        }
        funcs.createOrderFn = function () {
            $('.createOrder').on('click', function () {
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'order'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var shopHtml = '';
                        if (preview['shop']) {
                            for (key in preview['shop']) {
                                shopHtml += "<option value='" + preview['shop'][key]['shop_id'] + "'>" + preview['shop'][key]['shop_name'] + "</option>";
                            }
                        }
                        var commdityHtml = '';
                        if (preview['commdity']) {
                            for (key in preview['commdity']) {
                                var idlist = preview['commdity'][key]['id'] + '|' + preview['commdity'][key]['type'] + '|' + preview['commdity'][key]['name'] + '|' + preview['commdity'][key]['norm_id'] + '|' + preview['commdity'][key]['retail_price'];
                                commdityHtml += "<option value='" + idlist + "'>" + preview['commdity'][key]['name'] + "--" + preview['commdity'][key]['typename'] + "</option>";
                            }
                        }
                        var kehuHtml = '';
                        if (preview['kehu_type']) {
                            for (key in preview['kehu_type']) {
                                kehuHtml += "<input type='radio' name='kehu_type' id='kehu_type' value='" + key + "' /> " + preview['kehu_type'][key];
                            }
                        }
                        var orderHtml = '';
                        if (preview['order_type']) {
                            for (key in preview['order_type']) {
                                orderHtml += "<input type='radio' name='ordertype' id='ordertype' value='" + key + "' /> " + preview['order_type'][key];
                            }
                        }
                        var statusHtml = '';
                        if (preview['order_status']) {
                            for (key in preview['order_status']) {
                                statusHtml += "<input type='checkbox' name='orderstatus[]' id='orderstatus' value='" + key + "' />" + preview['order_status'][key];
                            }
                        }
                        str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						创建订单\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户姓名：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="kehu_name" id="kehu_name" style="width:158px;">\
								<span>输入客户姓名</span>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户电话：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="kehu_mobile" id="kehu_mobile" style="width:158px;">\
								<span>输入客户电话</span>\
							</div>\
						</div>\
					</div>\
					<!--div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">所在省份：</label>\
							<div class="areaSelect fl">\
								<select name="" id="loc_province" style="width:136px;" class="selectmenu"></select>\
							</div>\
						</div>\
						<div class="rqOption3 fl">\
							<label class="fl">所在城市：</label>\
							<div class="areaSelect fl">\
								<select name=""  id="loc_city" style="width:136px;" class="selectmenu"></select>\
							</div>\
						</div>\
						<div class="rqOption3 fl mr0">\
							<label class="fl">所在区县：</label>\
							<div class="areaSelect fl">\
								<select name="" id="loc_town" style="width:136px;" class="selectmenu"></select>\
							</div>\
						</div>\
					</div-->\
					<div class="rqLine">\
						<div class="rqOption clearfix mr0">\
							<label class="fl">详细地址：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="address" id="address" style="width:370px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">配送站点：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="shop_id" id="shop_id" class="selectmenu" style="width:180px;">\
										<option value="0" selected="selected">选择站点</option>\
                                                                                ' + shopHtml + '\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">客户类型：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									' + kehuHtml + '\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">发展渠道：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									' + orderHtml + '\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">商品种类：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="commdityname" id="commdityname" class="selectmenu" style="width:106px;">\
										<option value="0" selected="selected">选择商品</option>\
										' + commdityHtml + '\
									</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption3 fl">\
							<label class="fl">数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="commditynum" id="commditynum" style="width:66px;">\
							</div>\
						</div>\
					</div>\
					<div class="pl95 mb20"><a href="javascript:;" class="buleColor" id="addcommdity">+添加商品</a></div>\
					<div class="rqTabItem">\
						<table>\
							<thead>\
								<tr>\
									<th>规格</th>\
									<th>数量</th>\
									<th>单价</th>\
									<th>运送费</th>\
									<th>优惠</th>\
									<th>总金额</th>\
								</tr>\
							</thead>\
							<tbody id="plist"></tbody>\
						</table>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">订单备注：</label>\
							<textarea class="fl" name="comment" id="comment" style="width:562px;"></textarea>\
						</div>\
					</div>\
					<div class="rqLine mycheckList mycheckList2">\
						<div class="rqOption clearfix mycheckbox">\
							<label class="fl">附加信息：</label>\
							<div class="checkList fl">\
								' + statusHtml + '\
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
                    }
                });
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '560px'
                });

                //添加按钮
                $(dlg.node).find('#addcommdity').off('click').on('click', function () {
                    var commdityid = $("#commdityname option:selected").val();
                    var commdityname = $("#commdityname option:selected").text();
                    var commditynum = $("#commditynum").val();
                    var commditylist = commdityid + '|' + commditynum;
                    var fhtml = '<tr>\
                                            <td>' + commdityname + '</td>\
                                            <td>' + commditynum + '</td>\
                                            <td></td>\
                                            <td></td>\
                                            <td></td>\
                                            <td class="tableBtn"><a href="javascript:;" id="del">删除</a><input type="hidden" name="commditylist[]" value="' + commditylist + '" /></td>\
                                    </tr>';
                    if (commditynum > 0) {
                        $("#plist").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                $(dlg.node).find('#del').off('click').live('click', function () {
                    $(this).parent().parent().remove();
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
                        alert('请提交订单数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/order/add",
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
                // 单选
                utils.radioFn();
                // 复选框
                utils.checkFn();
                // placeholder
                utils.placeHolder($('.placeholder'));
                // 区域
                location.init();
            });
        }
        funcs.evaluateFn = function () {
            $(document).on('click', '.noEvalu', function () {
                var ordersn = $(this).attr('ordersn');

                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/evaluate",
                    data: {'ordersn': ordersn},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var comment = '';
                        if (preview.status >= 1) {
                            comment = preview.data['comment'];
                        }
                        str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						评价\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">评价内容：</label>\
							<textarea class="fl" style="width:562px;">' + comment + '</textarea>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn clearfix">\
						<div class="rqOption fl">\
							<a href="javascript:;" class="grayBtn closeBtn">关闭</a>\
						</div>\
					</div>\
				</div>';
                    }
                });
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '380px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
            });
        }

        function getShipperHtml(shop_id) {
            $.ajax({
                type: "POST",
                async: false,
                url: "/ajaxdata/datatype",
                data: {'type': 'shipper', 'shop_id': shop_id},
                success: function (result) {
                    var preview = eval("(" + result + ")");
                    var shipperHtml = "<option value='0'>选择送气工</option>";
                    if (preview['shipper']) {
                        for (key in preview['shipper']) {
                            shipperHtml += "<option value='" + key + '|' + preview['shipper'][key]['shipper_name'] + '|' + preview['shipper'][key]['mobile_phone'] + "'>" + preview['shipper'][key]['shipper_name'] + "</option>";
                        }
                    }
                    $("#shipper_id").empty().append(shipperHtml).selectmenu('refresh');
                }
            });
        }
        
        funcs.init = function () {
            // 订单派发
            this.paifaFn();
            // 创建订单
            this.createOrderFn();
            // 评价
            this.evaluateFn();
            
            // 双日历
            utils.doubleRiliFn($('#start'), $('#end'));
            // 详情--返回按钮
            utils.backFn();
            // 选择类型
            utils.chooseFn();
        }
        funcs.init();
    });
});
