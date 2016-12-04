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
		    $('.addGuige').on('click',function(){
				var _this = this;
                                
                                var str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加钢瓶规格\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">规格名称：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="bottle_name" id="bottle_name" style="width:150px;">\
							</div>\
						</div>\
					</div>\
                                        <div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">净重：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="bottle_netweight" id="bottle_netweight" style="width:150px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
							<textarea name="bottle_comment" style=""></textarea>\
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
                                        </form>\
				</div>';
                                
				var dlg = utils.showLog({
		            html:str,
					width:'726px',
					height:'425px'
				});

				//处理自定义按钮事件
				// 取消
				 $(dlg.node).find('.closeBtn').off('click').on('click',function(){
		            dlg.close().remove();
			     });
				//保存
				$(dlg.node).find('.saveBtn').off('click').on('click',function(){
					var formdata = $('#formtable').serialize();
                                        $.ajax({
                                            type :"POST",
                                            async: false,
                                            url : "/bottletype/add",
                                            data:formdata,
                                            success: function(result){
                                                var preview = eval("("+ result+")");
                                                if(preview.status >= 1){
                                                    dlg.close().remove();
                                                    location.reload();
                                                }else{
                                                    alert('创建失败');
                                                }
                                            }
                                        });
				});
				// 下拉框
				$('.selectmenu').selectmenu();
			});
		}
		// 初始化
		funcs.init=function (){
			// 分页
			this.pageFn();
			// 添加商品规格
			this.dialogFn();
		}
		funcs.init();
	});
});
