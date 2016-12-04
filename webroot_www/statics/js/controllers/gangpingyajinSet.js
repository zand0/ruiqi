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
            $('.addYajin').on('click', function () {
                var _this = this;

                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'bottle'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        if (preview['bottle']) {
                            for (key in preview['bottle']) {
                                bottleHtml += "<option value='" + key + "'>" + preview['bottle'][key] + "</option>";
                            }
                        }

                        str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加钢瓶押金\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">钢瓶规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="bottle_id" id="bottle_id" class="selectmenu" style="width:170px;">\
										<option selected="selected">选择规格</option>\
										' + bottleHtml + '\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户押金：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="suggested_price" id="suggested_price" style="width:150px;">-\
                                                                <input type="text" name="suggested_price_business" id="suggested_price_business" style="width:150px;">\
                                                                <b>*客户押金(居民-商业)</b>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">直营押金：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="other_price" id="other_price" style="width:150px;">-\
                                                                <input type="text" name="other_price_business" id="other_price_business" style="width:150px;">\
                                                                <b>*直营押金(居民-商业)</b>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">加盟押金：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="price" id="price" style="width:150px;">-\
                                                                <input type="text" name="price_business" id="price_business" style="width:150px;">\
                                                                <b>*加盟押金(居民-商业)</b>\
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
                    height: '400px'
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
                        url: "/cylinderdeposit/add",
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
            });
        }
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 添加商品规格
            this.dialogFn();
        }
        funcs.init();
    });
});
