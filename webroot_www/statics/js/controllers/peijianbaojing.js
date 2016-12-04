require(['/statics/js/config.js'], function () {
    require(['jquery','utils','modules/common/pages','vendors/jqueryUI/selectmenu'],function ($,utils,PageNav,selectmenu){
        var funcs = {};
		funcs.pageFn=function (){
                    var url = window.location.protocol;  //得到链接地址
                    
			var curPage=parseInt($('.currentPage').val()||1);
			var totalPage=parseInt($('.totalPage').val()||1);
			var pager=new PageNav({
				currentPage:curPage,
				totalPage:totalPage,
				wrapId:'#pageNav',
				callback:function (curPage){
					url += '?page='+curPage;
                                        location.href = url;
				}
			});
		}
		funcs.dialogFn=function (){
		    $('.addBaojing').on('click',function (){
		        var _this = this;
		        var str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						添加钢瓶库存报警\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqOption4">\
							<label class="fl">报警产品规格：</label>\
							<div class="selectBox fl" style="width:170px;">\
								<div class="mySelect">\
									<select name="" class="selectmenu">\
										<option selected="selected">选择产品规格</option>\
										<option>Slow</option>\
										<option>Medium</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqOption4">\
							<label class="fl">设置报警数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:150px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqOption4">\
							<label class="fl">备注：</label>\
							<textarea class="fl" style="width:540px;height:150px;"></textarea>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn clearfix rqLineBtn4">\
						<div class="rqOption fl">\
							<a href="javascript:;" class="blueBtn saveBtn">保存</a>\
						</div>\
						<div class="rqOption fl">\
							<a href="javascript:;" class="grayBtn closeBtn">取消</a>\
						</div>\
					</div>\
				</div>';
		        var dlg = utils.showLog({
		            html:str,
		            width:'726px',
		            height:'470px'
		        });

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
		    })
		}
		// 初始化
		funcs.init=function (){
			// 分页
			this.pageFn();
			// 添加报警
			this.dialogFn();
		}
		funcs.init();
	});
});
