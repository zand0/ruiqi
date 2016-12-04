require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages'], function ($, utils, PageNav) {
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
            $(document).on('click', '.chuli', function () {
                var bid = $(this).attr('bid');
                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						报修处理\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">处理结果：</label>\
							<textarea name="treatment" style="width:520px;height:90px;"></textarea>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn clearfix">\
                                                <input type="hidden" name="bid" id="bid" value="' + bid + '" />\
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
                    width: '726px',
                    height: '260px'
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
                        url: "/baoxiu/edite",
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
            });
        }
        funcs.paifaFn = function() {
            $(document).on('click', '.paifaBtn',function() {
                var baoxiusn = $(this).attr('baoxiusn');
                var ordershop = $(this).attr('shop_id');
                
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'shop','idsn':ordershop},
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
                                shipperHtml += "<option value='" + key + '|' + preview['shipper'][key]['shipper_name'] + '|' + preview['shipper'][key]['mobile_phone'] +"'>" + preview['shipper'][key]['shipper_name'] + "</option>";
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
                                                        <input type="hidden" name="baoxiusn" id="baoxiusn" value="' + baoxiusn + '" />\
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
                        url: "/baoxiu/distribution",
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
                $(dlg.node).find("#shop_id").live('selectmenuchange',function () {
                    var shop_id = $(this).val();
                    getShipperHtml(shop_id);
                });
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
            });
        }
        
        function getShipperHtml(shop_id) {
            $.ajax({
                type: "POST",
                async: false,
                url: "/ajaxdata/datatype",
                data: {'type': 'shipper','shop_id':shop_id},
                success: function (result) {
                    var preview = eval("(" + result + ")");
                    var shipperHtml = "<option value='0'>选择送气工</option>";
                    if (preview['shipper']) {
                        for (key in preview['shipper']) {
                            shipperHtml += "<option value='" + key + '|' + preview['shipper'][key]['shipper_name'] + '|' + preview['shipper'][key]['mobile_phone'] +"'>" + preview['shipper'][key]['shipper_name'] + "</option>";
                        }
                    }
                    $("#shipper_id").empty().append(shipperHtml).selectmenu('refresh');
                }
            });
        }
        
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 派发
            this.paifaFn();
            //处理结果
            this.dialogFn();
        }
        funcs.init();
    });
});