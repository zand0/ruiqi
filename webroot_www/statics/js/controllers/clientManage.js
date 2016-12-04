require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages', 'vendors/jqueryUI/selectmenu'], function ($, utils, PageNav, selectmenu) {
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
                    if (getparamlist != '' && getparamlist != undefined) {
                        url += '&' + getparamlist;
                    }
                    location.href = url;
                }
            });
        }
        funcs.dialogFn = function () {
            $('.addClient').on('click', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加客户\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户姓名：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
								<span>输入客户姓名</span>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户电话：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
								<span>输入客户电话</span>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">电话列表：</label>\
							<textarea class="fl" style="width:560px;height:100px;"></textarea>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">客户性别：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="0">保密</a>\
									<a href="javascript:;" data-value="1">男</a>\
									<a href="javascript:;" data-value="2">女</a>\
								</div>\
								<input type="hidden" value="0">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户地址：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:430px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">客户类型：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="0">普通用户</a>\
									<a href="javascript:;" data-value="1">商铺用户</a>\
									<a href="javascript:;" data-value="2">其他</a>\
								</div>\
								<input type="hidden" value="0">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">结算方式：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="0">现金</a>\
									<a href="javascript:;" data-value="1">网上支付</a>\
									<a href="javascript:;" data-value="2">月结</a>\
								</div>\
								<input type="hidden" value="0">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine mycheckList mycheckList2">\
						<div class="rqOption clearfix mycheckbox">\
							<label class="fl">发展渠道：</label>\
							<div class="checkList fl">\
								<a href="javascript:;" data-value="0">门店拓展</a>\
								<a href="javascript:;" data-value="1">合作人拓展</a>\
								<a href="javascript:;" data-value="2">呼叫中心</a>\
								<a href="javascript:;" data-value="3">气站</a>\
							</div>\
							<input type="hidden" value="">\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">所属门店：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:170px;">\
										<option selected="selected">门店一</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">状态：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="0">正常</a>\
									<a href="javascript:;" data-value="1">销户</a>\
								</div>\
								<input type="hidden" value="0">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
							<textarea style="width:560px;height:100px;"></textarea>\
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
                    html: str,
                    width: '726px',
                    height: '560px'
                })

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    alert(1)
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    alert('保存成功');
                    dlg.close().remove();
                });
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
                // 单选按钮
                utils.radioFn();
                // placeHolder
                utils.placeHolder($('.placeholder'));
                // 复选框
                utils.checkFn();
            });
        }
        funcs.modifyFn = function () {
            $(document).on('click', '.cmModify', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						修改客户\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户姓名：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
								<span>输入客户姓名</span>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户电话：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:148px;">\
								<span>输入客户电话</span>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">电话列表：</label>\
							<textarea class="fl" style="width:560px;height:100px;"></textarea>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">客户性别：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="0">保密</a>\
									<a href="javascript:;" data-value="1">男</a>\
									<a href="javascript:;" data-value="2">女</a>\
								</div>\
								<input type="hidden" value="0">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">客户地址：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:430px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">客户类型：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="0">普通用户</a>\
									<a href="javascript:;" data-value="1">商铺用户</a>\
									<a href="javascript:;" data-value="2">其他</a>\
								</div>\
								<input type="hidden" value="0">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">结算方式：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="0">现金</a>\
									<a href="javascript:;" data-value="1">网上支付</a>\
									<a href="javascript:;" data-value="2">月结</a>\
								</div>\
								<input type="hidden" value="0">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine mycheckList mycheckList2">\
						<div class="rqOption clearfix mycheckbox">\
							<label class="fl">发展渠道：</label>\
							<div class="checkList fl">\
								<a href="javascript:;" data-value="0">门店拓展</a>\
								<a href="javascript:;" data-value="1">合作人拓展</a>\
								<a href="javascript:;" data-value="2">呼叫中心</a>\
								<a href="javascript:;" data-value="3">气站</a>\
							</div>\
							<input type="hidden" value="">\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">所属门店：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu" style="width:170px;">\
										<option selected="selected">门店一</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">状态：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="0">正常</a>\
									<a href="javascript:;" data-value="1">销户</a>\
								</div>\
								<input type="hidden" value="0">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
							<textarea style="width:560px;height:100px;"></textarea>\
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
                    html: str,
                    width: '726px',
                    height: '560px'
                })

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    alert(1)
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    alert('保存成功');
                    dlg.close().remove();
                });
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
                // 单选按钮
                utils.radioFn();
                // placeHolder
                utils.placeHolder($('.placeholder'));
                // 复选框
                utils.checkFn();
            });
        }
        
        
        funcs.selectRegion = function(id,shi,xid,pid,cid) {
            regionlist('.region_1',10,shi);
            regionlist('.region_2', shi,xid);
            if(pid>0){
                regionlist('.region_3', xid,pid);
                regionlist('.region_4', pid);
            }
        }
        
        funcs.orderlistFn = function(){
            $(".khorder").click(function () {
                var orderlist = $(this).attr('orderlist');
                if (orderlist == '') {
                    $(this).attr('orderlist', 'up');
                } else {
                    $(this).attr('orderlist', '');
                }
                location.href = "/custum/index?ordertype=khorder&orderlist=" + orderlist;
            });
            $(".updatetime").click(function () {
                var orderlist = $(this).attr('orderlist');
                if (orderlist == '') {
                    $(this).attr('orderlist', 'up');
                } else {
                    $(this).attr('orderlist', '');
                }
                location.href = "/custum/index?ordertype=updatetime&orderlist=" + orderlist;
            });
            $(".ordertimes").click(function () {
                var orderlist = $(this).attr('orderlist');
                if (orderlist == '') {
                    $(this).attr('orderlist', 'up');
                } else {
                    $(this).attr('orderlist', '');
                }
                location.href = "/custum/index?ordertype=ordertimes&orderlist=" + orderlist;
            });
        }
        
        function regionlist(select_object,id,tid) {
            $.ajax({
                type: "POST",
                url: "/region/regionList",
                data: "id=" + id + "&opt_cat=list",
                dataType: "json",
                async: false,
                success: function (data) {
                    if (data['status'] == 200) {
                        var option_arr = data['data'];
                        var option_html = '<option value="0">请选择</option>';
                        if ($.isArray(option_arr)) {
                            for (var key in option_arr) {
                                if (option_arr[key]['region_id'] == tid) {
                                    option_html += '<option value="' + option_arr[key]['region_id'] + '" selected=selected>' + option_arr[key]['region_name'] + '</option>';
                                } else {
                                    option_html += '<option value="' + option_arr[key]['region_id'] + '">' + option_arr[key]['region_name'] + '</option>';
                                }
                            }
                        }
                        $(select_object).html(option_html).selectmenu('refresh');
                        return true;
                    }
                }
            });
        }
        
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 添加客户
            this.dialogFn();
            //修改客户
            this.modifyFn();
            
            var pid = $("#pid").val();
            var xid = $("#xid").val();
            this.selectRegion(10,140,xid,pid);
            
            $(".region_1").live('selectmenuchange', function () {
                var region_id = $(this).val();
                regionlist('.region_2', region_id);
            });
            
            $(".region_2").live('selectmenuchange', function () {
                var region_id = $(this).val();
                regionlist('.region_3', region_id);
            });
            
            $(".region_3").live('selectmenuchange', function () {
                var region_id = $(this).val();
                regionlist('.region_4', region_id);
            });
            
            //排序
            this.orderlistFn();
        }
        funcs.init();
    });
});
