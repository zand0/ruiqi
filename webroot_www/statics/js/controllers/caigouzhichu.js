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
		funcs.addCgzcFn=function (){
		    $('.addCgzc').on('click',function(){
		        var _this = this;
		        var str='<div class="pop_cont">\
					<div class="contRTitle2 ">\
						添加采购支出\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">产品类别：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:170px;">\
										<option selected="selected">选择产品类别</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">种类/规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:170px;">\
										<option selected="selected">选择产品类别</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">供应商：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:170px;">\
										<option selected="selected">选择入库对象</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">数量/重量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">商品单价：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">经办人：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix riliInputBox">\
							<label class="fl">支出时间：</label>\
							<div class="fl riliInput">\
								<input type="text" class="datepicker" style="width:148px;" readonly>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption2 clearfix">\
							<label class="fl">备注：</label>\
							<textarea style="width:540px;height:100px;"></textarea>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn rqLineBtn3 clearfix">\
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
			// 采购支出
			this.addCgzcFn();
                        
                        $("#typelist").live('selectmenuchange', function () {
                        var dataVal = $(this).val();
                        $.get('/supplier/ajaxdata', {dataVal: dataVal}, function (data) {
                            var option_html = '<option value="">请选择</option>';
                            if (data) {
                                if (dataVal == 1) {
                                    for (var key in data) {
                                        option_html += '<option value="' + data[key].gid + '">' + data[key].gas_name + '</option>';
                                    }
                                } else if (dataVal == 2) {
                                    for (var key in data) {
                                        option_html += '<option value="' + data[key].id + '">' + data[key].bottle_name + '</option>';
                                    }
                                } else {
                                    for (var key in data) {
                                        option_html += '<option value="' + data[key].id + '">' + data[key].products_name + '</option>';
                                    }
                                }
                            }
                            $('#goods_type').empty().append(option_html).selectmenu('refresh');
                            return true;
                        }, "json");
                    });
		}
		funcs.init();
	});
});
