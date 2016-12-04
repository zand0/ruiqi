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
		funcs.addPriceFn=function (){
		    $('.addPrice').on('click',function(){
		        var _this = this;
		        var str='<div class="pop_cont">\
					<div class="contRTitle2">\
						添加商品价格\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">商品名称：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:180px;">\
										<option selected="selected">选择商品名称</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">商品规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:180px;">\
										<option selected="selected">选择商品规格</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">居民价格：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:158px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">商业价格：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:158px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">所属门店：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:182px;">\
										<option selected="selected">选择门店</option>\
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
		            alert('发布成功');
		            dlg.close().remove();
		        });
		        // 下拉框
		        $(dlg.node).find('.selectmenu').selectmenu();
		    });
		}
		funcs.editFn=function (){
		    $(document).on('click','.pmEdit',function(){
		        var _this = this;
		        var str='<div class="pop_cont">\
					<div class="contRTitle2">\
						编辑\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix poptxt">\
							<label class="fl">商品编号：</label>\
							<p>yhq20160213</p>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqOption2">\
							<label class="fl">商品名称：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:180px;">\
										<option selected="selected">液化石油气</option>\
										<option>选择商品名称</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqOption2">\
							<label class="fl">商品规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:180px;">\
										<option selected="selected">15KG</option>\
										<option>选择商品规格</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqOption2">\
							<label class="fl">居民零售价：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:158px;" value="95">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">商业零售价：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:158px;" value="90">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">所属门店：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:182px;">\
										<option selected="selected">朝阳四惠店</option>\
										<option>选择门店</option>\
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
		            html:str,
		            width:'726px',
		            height:'520px'
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
			// 分页
			this.pageFn();
			// 添加价格
			this.addPriceFn();
			// 编辑
			this.editFn();
		}
		funcs.init();
	});
});
