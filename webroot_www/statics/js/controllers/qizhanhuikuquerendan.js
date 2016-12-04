require(['/statics/js/config.js'],function (){
	require(['jquery','utils','vendors/jqueryUI/selectmenu'],function ($,utils,selectmenu){
		var funcs={};
		funcs.dialogFn=function (){
		    $(document).on('click','.qrdEdit',function(){
		        var _this = this;
		        var str='<div class="pop_cont">\
					<div class="contRTitle2 ">\
						修改\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix poptxt">\
							<label class="fl">回库数量：</label>\
							<p class="fl">15</p>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">入库数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
							<textarea class="fl" style="width:558px;height:140px;"></textarea>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn clearfix">\
						<div class="rqOption fl">\
							<a href="javascript:;" class="blueBtn">保存</a>\
						</div>\
						<div class="rqOption fl">\
							<a href="javascript:;" class="grayBtn closeBtn">取消</a>\
						</div>\
					</div>\
				</div>';
		        var dlg = showLog({
		            html:str,
		            width:'726px',
		            height:'420px'
		        });

		        //处理自定义按钮事件
		        // 取消
		         $(dlg.node).find('.closeBtn').off('click').on('click',function(){
		            alert(1)
		               dlg.close().remove();
		         });
		        //保存
		        $(dlg.node).find('.saveBtn').off('click').on('click',function(){
		            alert('发布成功');
		            dlg.close().remove();
		        });
		        // 下拉框
		        $(dlg.node).find('.selectmenu').selectmenu();
		    });
		}
		// 初始化
		funcs.init=function (){
			// 确认单修改
			this.dialogFn();
		}
		funcs.init();
	});
});
