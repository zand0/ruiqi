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
        funcs.page2Fn = function () {
            var url = window.location.protocol;  //得到链接地址
            
            var curPage = parseInt($('.currentPage').eq(1).val() || 1);
            var totalPage = parseInt($('.totalPage').eq(1).val() || 1);
            var pager = new PageNav({
                currentPage: curPage,
                totalPage: totalPage,
                wrapId: '#pageNav2',
                callback: function (curPage) {
                    url += '?page=' + curPage;
                    location.href = url;
                }
            });
        }
        funcs.dialogFn = function () {
            $(document).on('click', '.chongzhi', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
				<div class="contRTitle2 ">\
					客户充值\
					<span></span>\
				</div>\
				<div class="rqLine clearfix">\
					<div class="rqOption rqOption4 fl poptxt">\
						<label class="fl">充值客户：</label>\
						<p class="fl">李小虎</p>\
					</div>\
				</div>\
				<div class="rqLine clearfix">\
					<div class="rqOption rqOption4 fl poptxt">\
						<label class="fl">当前余额：</label>\
						<p class="fl">80元</p>\
					</div>\
				</div>\
				<div class="rqLine clearfix">\
					<div class="rqOption rqOption4 fl poptxt">\
						<label class="fl">充值金额：</label>\
						<div class="inputBox fl placeholder">\
							<input type="text" style="width:218px;">\
						</div>\
					</div>\
				</div>\
				<div  class="rqLine rqLineBtn4 clearfix">\
					<div class="rqOption fl">\
						<a href="javascript:;" class="blueBtn saveBtn">充值</a>\
					</div>\
					<div class="rqOption fl">\
						<a href="javascript:;" class="grayBtn closeBtn">取消</a>\
					</div>\
				</div>\
			</div>';
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '260px'
                })

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    alert(1)
                    dlg.close().remove();
                });
                //充值
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    alert('充值成功');
                    dlg.close().remove();
                });
            });
        }
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 分页
            this.page2Fn();
            // 充值
            this.dialogFn();
        }
        funcs.init();
    });
});
