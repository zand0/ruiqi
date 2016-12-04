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
        
        funcs.addGangpingFn = function () {
            $('.addGangping').on('click', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						添加钢瓶\
						<span></span>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">钢印号：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">芯片号：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">条形码：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">瓶规格：</label>\
							<div class="selectBox fl rqOption">\
								<div class="mySelect">\
									<select name="" class="selectmenu"  style="width:170px;">\
										<option selected="selected">选择规格</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">瓶状态：</label>\
							<div class="selectBox fl" style="width:170px;">\
								<div class="mySelect">\
									<select name="" class="selectmenu">\
										<option selected="selected">选择瓶状态</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl riliInputBox">\
							<label class="fl">出厂日期：</label>\
							<div class="fl riliInput">\
								<input type="text" class="datepicker" style="width:148px;" readonly>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">生产厂家：</label>\
							<div class="selectBox fl" style="width:170px;">\
								<div class="mySelect">\
									<select name="" class="selectmenu">\
										<option selected="selected">选择生产厂家</option>\
										<option>Slow</option>\
									</select>\
								</div>\
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
				</div>';
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '400px'
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
                // 日历
                utils.datepickerFn();
            })
        }
        
        funcs.dialogFn = function () {
            $('.guiji').on('click', function () {
                var number = $(this).attr('number');
                var xinpian = $(this).attr('xinpian');
                var type_name = $(this).attr('type_name');
                
                var _this = this;
                var str = '<div class="pop_cont">\
                                <div class="contRTitle2">\
                                        气瓶轨迹\
                                        <span></span>\
                                        <i class="closeBtn"></i>\
                                </div>\
                                <div class="rqTabItem">\
                                    <table>\
                                        <tr>\
                                            <td class="pingImg"></td>\
                                            <td class="guige">\
                                                    <p>瓶重量：' + type_name + '</p>\
                                                    <p>钢印号：' + number + '</p>\
                                                    <p>芯片号：' + xinpian + '</p>\
                                            </td>\
                                        </tr>\
                                    </table>\
                                </div>\
                                <div class="shiguangzhou">\
                                    <ul>\
                                        <li class="active">\
                                            <i></i>\
                                            <span>2016-06-17<br>11:58:26</span>\
                                            <h4>灌装</h4>\
                                            <p>重瓶</p>\
                                        </li>\
                                        <li>\
                                            <i></i>\
                                            <span>2016-06-17<br>11:58:26</span>\
                                            <h4>用户</h4>\
                                            <p>张无忌<strong></strong>空瓶</p>\
                                        </li>\
                                    </ul>\
                                </div>\
                        </div>';
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '600px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
            });
        }
        
        // 初始化
        funcs.init = function () {
            // 弹出层
            this.dialogFn();
            // 分页
            this.pageFn();
            // 添加钢瓶 
            this.addGangpingFn();
        }
        funcs.init();
    });
});
