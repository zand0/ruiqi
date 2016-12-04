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
                    if(getparamlist != '' && getparamlist != undefined){
                        url += '&'+getparamlist;
                    }
                    location.href = url;
                }
            });
        }
        funcs.dialogFn = function () {
            $('.addGongyingshang').on('click', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加供应商\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">供应商类别：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu"  style="width:160px;">\
										<option selected="selected">选择供应商类别</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">产品类别：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" class="selectmenu"  style="width:160px;">\
										<option selected="selected">不限</option>\
										<option>Slow</option>\
									</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption rqOption4 fl">\
							<label class="fl">供应商名称：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:138px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption rqOption4 fl">\
							<label class="fl">联系人：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:138px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption rqOption4 fl">\
							<label class="fl">联系电话：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:138px;">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption rqOption4 fl">\
							<label class="fl">所在地：</label>\
							<div class="fl areaSelect rqOption">\
								<select id="loc_province" style="width:160px;" class="selectmenu"></select>\
							</div>\
							<div class="fl areaSelect rqOption">\
								<select id="loc_city" style="width:160px;" class="selectmenu"></select>\
							</div>\
							<div class="fl areaSelect rqOption">\
								<select id="loc_town" style="width:160px;" class="selectmenu"></select>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption rqOption4 fl">\
							<label class="fl">详细地址：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:530px;">\
							</div>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn4 clearfix">\
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
                // 地区
                location.init();
            });
        }
                
        funcs.ajaxData = function () {
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

        funcs.ajaxDel = function() {
            $(".del_supplier").on('click', function() {
                var supplier_id = $(this).attr('supplier_id');
                var supplier_type = $(this).attr('supplier_type');
                var _this = $(this);

                if(confirm('确认删除？')){
                    var str = '';
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/supplier/del",
                        data: {'id': supplier_id, 'type': supplier_type},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if(preview.code == 1){
                                alert('删除成功');
                                _this.parent().parent().remove();
                            }else{
                                alert('删除失败');
                            }
                        }
                    });
                }
            });
        }

        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 添加供应商
            this.dialogFn();

            this.ajaxData();
            
            //删除供应商
            this.ajaxDel();
        }
        funcs.init();
    });
});
