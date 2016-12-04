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
		funcs.dialogFn=function (){
		    $('.addZhuangtai').on('click',function(){
				var _this = this;
                                
				var str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加钢瓶状态\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">钢瓶状态：</label>\
							<div class="inputBox fl placeholder">\
								<input name="typemanagername" id="typemanagername" type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
							<textarea style="" name="comment" id="comment"></textarea>\
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
					height:'380px'
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
                                            url : "/bottlestatus/add",
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
			});
		}
		// 初始化
		funcs.init=function (){
			// 分页
			this.pageFn();
			// 添加商品状态
			this.dialogFn();
		}
		funcs.init();
	});
});
