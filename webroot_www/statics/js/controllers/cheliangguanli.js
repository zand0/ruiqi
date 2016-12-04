require(['/statics/js/config.js'],function (){
	require(['jquery','utils','modules/common/pages'],function ($,utils,PageNav){
		var funcs={};
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
		funcs.addCarsFn=function (){
		    $('.addCars').on('click',function(){
		        var _this = this;
		        var str = '<div class="pop_cont"><div class="contRTitle2">添加车辆 <span></span></div><div class="rqLine clearfix"><div class="rqOption fl rqOption4"><label class="fl">车牌号码：</label><div class="inputBox fl placeholder"><input type="text" style="width:110px"></div></div><div class="rqOption3 fl"><label class="fl">车型号：</label><div class="selectBox fl"><div class="mySelect"><select name="" class="selectmenu" style="width:134px"><option selected>奥迪</option><option>Slow</option><option>Medium</option><option>Fast</option><option>Faster</option></select></div></div></div><div class="rqOption3 fl mr0"><label class="fl">品牌：</label><div class="inputBox fl placeholder"><input type="text" style="width:110px"></div></div></div><div class="rqLine clearfix"><div class="rqOption fl rqOption4"><label class="fl">驾驶员：</label><div class="inputBox fl placeholder"><input type="text" style="width:110px"></div></div><div class="rqOption3 fl"><label class="fl">载重：</label><div class="inputBox fl placeholder"><input type="text" style="width:110px"></div></div><div class="rqOption3 fl mr0"><label class="fl">车辆原重：</label><div class="inputBox fl placeholder"><input type="text" style="width:110px"></div></div></div><div class="rqLine clearfix"><div class="rqOption fl rqOption4"><label class="fl">车辆识别代码：</label><div class="inputBox fl placeholder"><input type="text" style="width:110px"></div></div><div class="rqOption3 fl"><label class="fl">年检状态：</label><div class="inputBox fl placeholder"><input type="text" style="width:116px"></div></div></div><div class="rqLine clearfix"><div class="rqOption fl rqOption4"><label class="fl">行驶里程：</label><div class="inputBox fl placeholder"><input type="text" style="width:110px"></div></div><div class="rqOption3 fl riliInputBox"><label class="fl">购买日期：</label><div class="fl riliInput"><input type="text" class="datepicker" style="width:114px" readonly></div></div></div><div class="rqLine"><div class="rqOption clearfix rqOption4"><label class="fl">备注：</label><textarea class="fl" style="width:530px"></textarea></div></div><div class="rqLine rqLineBtn4 clearfix"><div class="rqOption fl"><a href="javascript:;" class="blueBtn saveBtn">保存</a></div><div class="rqOption fl"><a href="javascript:;" class="grayBtn closeBtn">取消</a></div></div></div>';
		        var dlg = utils.showLog({
		            html:str,
		            width:'726px',
		            height:'560px'
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
		        // 日历
		        utils.datepickerFn();
		    });
		}
		// 初始化
		funcs.init=function (){
			// 分页
			this.pageFn();
			// 添加车辆
			this.addCarsFn();
		}
		funcs.init();
	});
});
