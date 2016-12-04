require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages'], function ($, utils, PageNav) {
        var funcs = {};
        funcs.dialogFn = function () {
            $('.addRecord').on('click', function () {

                //赋值一个ajax


                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						添加安检记录\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户姓名：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix riliInputBox">\
							<label class="fl">安检日期：</label>\
							<div class="fl riliInput">\
								<input type="text" class="datepicker" style="width:148px;" readonly>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">安检员：</label>\
							<div class="selectBox fl rqOption">\
								<div class="mySelect">\
									<select name="" class="selectmenu"  style="width:172px;">\
										<option selected="selected">请选择安检员</option>\
										<option>Slow</option>\
										<option>Medium</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">安检结果：</label>\
							<textarea class="fl" style="width:580px;height:138px;"></textarea>\
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
				</div>';
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '520px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    alert(1)
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    alert('保存成功');
                    dlg.close().remove();
                });
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
                //日历
                utils.datepickerFn();
            });
        }
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
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 弹出层
            this.dialogFn();
        }
        funcs.init();
    });
});
