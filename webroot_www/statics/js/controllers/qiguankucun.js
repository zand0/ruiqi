require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages'], function ($, utils, PageNav) {
        var funcs = {};
        funcs.dialogFn = function () {
            $('#guiji').on('click', function () {
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
									<p>瓶重量：15KG</p>\
									<p>芯片号：APS000576</p>\
									<p>钢瓶号：86757721</p>\
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
							<li>\
								<i></i>\
								<span>2016-06-17<br>11:58:26</span>\
								<h4>用户</h4>\
								<p>张无忌<strong></strong>空瓶</p>\
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
        funcs.countFn = function () {
            
            $(".guanImg").each(function () {
                var num = Number($(this).find('.qiBox').eq(0).data('value'));
                var total = Number($(this).find('.qiBox').eq(0).data('total'));
                if (num || num == 0) {
                    if (total) {
                        var ratio = num / total;
                        ratio = ratio.toFixed(4);
                        $(this).find('.qiBox').css({'top': 156 * (1 - ratio)});
                        $(this).children('span').html(ratio * 100 + '%');
                        $(this).children('span').css({'top': (130 - 156 * ratio)});
                    }
                }
            });
        }
        
        //添加燃气盘库
        funcs.gaslogFn = function(){
            $(".addgaslog").click(function () {
                var formlist = $(this).parents('div').children('form');
                var tankid = formlist.find("#tankid").val();
                var total = formlist.find("#total").val(); //总储量
                var nowtotal = formlist.find("#nowtotal").val(); //当前储量
                var height = formlist.find("#height").val(); //当前高度
                if (height > 0) {
                    var density = formlist.find("#density").val(); //当前密度
                    var temperature = formlist.find("#temperature").val(); //当前温度

                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/stocktaking",
                        data: {'tankid': tankid, 'height':height,'density':density,'temperature':temperature},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                alert('盘库成功');
                                location.reload();
                            } else {
                                alert('盘库失败');
                            }
                        }
                    });
                } else {
                    alert('请添加高度');
                }
                return false;
            });
            
            $(".qkgaslog").click(function(){
                var formlist = $(this).parents('div').children('form');
                var nowtotal = formlist.find("#nowtotal").val(''); //当前储量
                var height = formlist.find("#height").val(''); //当前高度
                var density = formlist.find("#density").val(''); //当前密度
                var temperature = formlist.find("#temperature").val(''); //当前温度

                return false;
            });
        }
        
        // 初始化
        funcs.init = function () {
            // 弹出层
            this.dialogFn();
            
            //汇总统计
            this.countFn();
            
            //添加燃气盘库
            this.gaslogFn();
        }
        funcs.init();
    });
});
