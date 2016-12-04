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
                    if(getparamlist != '' && getparamlist != undefined){
                        url += '&'+getparamlist;
                    }
                    location.href = url;
                }
            });
        }
		funcs.dialogFn=function (){
			$(document).on('click','.paifaBtn',function(){
		        var _this = this;
		        var str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						退瓶派发\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">派发对象：</label>\
							<div class="selectBox fl rqOption" style="width:170px;">\
								<div class="mySelect">\
									<select name="" class="selectmenu">\
										<option selected="selected">请选择对象</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn clearfix">\
						<div class="rqOption fl">\
							<a href="javascript:;" class="blueBtn saveBtn">派发</a>\
						</div>\
						<div class="rqOption fl">\
							<a href="javascript:;" class="grayBtn closeBtn">取消</a>\
						</div>\
					</div>\
				</div>';
		        var dlg = utils.showLog({
		            html:str,
		            width:'726px',
		            height:'200px'
		        })

		        //处理自定义按钮事件
		        // 取消
		         $(dlg.node).find('.closeBtn').off('click').on('click',function(){
		            alert(1)
		            dlg.close().remove();
		         });
		        //保存
		        $(dlg.node).find('.saveBtn').off('click').on('click',function(){
		            alert('保存成功');
		            dlg.close().remove();
		        });
		        // 下拉框
		        $(dlg.node).find('.selectmenu').selectmenu();
		    });
		}
		// 初始化
		funcs.init=function (){
			// 分页
			this.pageFn();
			// 派发
			this.dialogFn();
		}
		funcs.init();
	});
});
