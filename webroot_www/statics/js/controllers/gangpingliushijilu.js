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
		funcs.addLiushiFn=function (){
		    $('.addGpls').on('click',function (){
		        var _this = this;
		        var str='<div class="pop_cont">\
					<div class="contRTitle2 ">\
						添加钢瓶流失\
						<span></span>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption rqOption2 fl poptxt">\
							<label class="fl">编号：</label>\
							<p class="fl">gp2016020489</p>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">钢印号：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">芯片号：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">钢瓶规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu"  style="width:170px;">\
										<option selected="selected">选择钢瓶规格</option>\
										<option>Slow</option>\
										<option>Medium</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix riliInputBox">\
							<label class="fl">流失时间：</label>\
							<div class="fl riliInput">\
								<input type="text" class="datepicker" style="width:148px;" readonly>\
							</div>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn3 clearfix">\
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
		            height:'440px'
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
		        // 日历
		        utils.datepickerFn();
		    })
		}
		// 初始化
		funcs.init=function (){
			// 分页
			this.pageFn();
			// 添加钢瓶流失
			this.addLiushiFn();
		}
		funcs.init();
	});
});
