require(['/statics/js/config.js'],function (){
	require(['jquery','utils','modules/common/pages','vendors/jqueryUI/selectmenu'],function ($,utils,PageNav,selectmenu){
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
		funcs.dialogFn=function (){
		    $('.addPrice').on('click',function(){
				var _this = this;
				var str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加商品种类\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">商品名称：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu"  style="width:170px;">\
										<option selected="selected">选择商品</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">商品规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu"  style="width:170px;">\
										<option selected="selected">选择规格</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">零售价格：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">直营价格：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">加盟价格：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
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
		            html:str,
					width:'726px',
					height:'480px'
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
			});
		}
		// 初始化
		funcs.init=function (){
			// 分页
			this.pageFn();
			// 添加商品价格
			this.dialogFn();
		}
		funcs.init();
	});
});
