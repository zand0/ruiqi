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

        funcs.selectRegion = function (id, shi, xid, pid, cid) {
            regionlist('.region_1', 10, shi);
            regionlist('.region_2', shi, xid);
            if (pid > 0) {
                regionlist('.region_3', xid, pid);
                regionlist('.region_4', pid);
            }
        }

        function regionlist(select_object, id, tid) {
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
           
        funcs.dialogFn = function () {
            $(document).on('click', '.chongzhi', function () {
                var shop_id = $(this).attr('shop_id');
                if(shop_id == '' || shop_id == 'underfined'){
                    alert('门店不存在');
                    return false;
                }
                var shop_payment = $(this).attr('shop_payment');
                var shop_name = $(this).attr('shop_name');
                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						预付款充值\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix poptxt">\
							<label class="fl">充值门店：</label>\
							<p class="fl">' + shop_name + '</p>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix poptxt">\
							<label class="fl">当前余额：</label>\
							<p class="fl">' + shop_payment + '元</p>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">充值金额：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="money" id="money" value="" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn4 clearfix">\
						<div class="rqOption fl">\
                                                        <input type="hidden" name="shop_id" id="shop_id" value="' + shop_id + '" style="width:148px;">\
							<a href="javascript:;" class="blueBtn saveBtn">保存</a>\
						</div>\
						<div class="rqOption fl">\
							<a href="javascript:;" class="grayBtn closeBtn">取消</a>\
						</div>\
					</div>\
                                        </form>\
				</div>';
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '280px'
                })

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var money = $("#money").val();
                    if(money <=0){
                        alert("充值金额有问题");
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/addpayment",
                        data: formdata,
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                dlg.close().remove();
                                location.reload();
                            } else {
                                alert('充值失败');
                            }
                        }
                    });
                });
            });
        }
        
        funcs.sjiaoFn = function() {
            $(document).on('click', '.shangjiao', function () {
                var shop_id = $(this).attr('shop_id');
                if(shop_id == '' || shop_id == 'underfined'){
                    alert('门店不存在');
                    return false;
                }
                var shop_payment = $(this).attr('shop_payment');
                var shop_name = $(this).attr('shop_name');
                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						门店费用上缴\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix poptxt">\
							<label class="fl">门店费用上缴：</label>\
							<p class="fl">' + shop_name + '</p>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix poptxt">\
							<label class="fl">当前余额：</label>\
							<p class="fl">' + shop_payment + '元</p>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">上缴金额：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" name="money" id="money" value="" style="width:148px;">\
							</div>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn4 clearfix">\
						<div class="rqOption fl">\
                                                        <input type="hidden" name="shop_id" id="shop_id" value="' + shop_id + '" style="width:148px;">\
							<a href="javascript:;" class="blueBtn saveBtn">保存</a>\
						</div>\
						<div class="rqOption fl">\
							<a href="javascript:;" class="grayBtn closeBtn">取消</a>\
						</div>\
					</div>\
                                        </form>\
				</div>';
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '280px'
                })

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var money = $("#money").val();
                    if(money <=0){
                        alert("上缴金额有问题");
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/uppayment",
                        data: formdata,
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                dlg.close().remove();
                                location.reload();
                            } else {
                                alert('充值失败');
                            }
                        }
                    });
                });
            });
        }
        
        funcs.addjdFn = function () {
            $(document).on('click', '.addjd', function () {
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'other'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        var tHtml = '';
                        var shopHtml = '';
                        var carHtml = '';
                        var userHtml = '';
                        if (preview['bottle']) {
                            for (key in preview['bottle']) {
                                bottleHtml += "<option value='" + key + "'>" + preview['bottle'][key] + "</option>";
                            }
                        }

                        if (preview['shop']) {
                            for (key in preview['shop']) {
                                shopHtml += "<option value='" + preview['shop'][key]['shop_id'] + "'>" + preview['shop'][key]['shop_name'] + "</option>";
                            }
                        }

                        if (preview['type']) {
                            for (key in preview['type']) {
                                tHtml += "<option value='" + key + "'>" + preview['type'][key] + "</option>";
                            }
                        }

                        if (preview['car']) {
                            for (key in preview['car']) {
                                carHtml += "<option value='" + preview['car'][key]['license_plate'] + "'>" + preview['car'][key]['license_plate'] + "</option>";
                            }
                        }

                        if (preview['user']) {
                            for (key in preview['user']) {
                                userHtml += "<option value='" + preview['user'][key]['username'] + "'>" + preview['user'][key]['username'] + "</option>";
                            }
                        }

                        str = '<div class="pop_cont">\
                                    <div class="contRTitle2">\
                                        门店钢瓶借调\
                                        <span></span>\
                                    </div>\
                                    <form name="form" method="POST" id="formtable">\
                                        <div class="rqLine clearfix">\
                                            <div class="rqOption fl">\
                                                <label class="fl">车牌号：</label>\
                                                <div class="selectBox fl">\
                                                    <div class="mySelect">\
                                                            <select name="license_plate" id="license_plate" class="selectmenu"  style="width:132px;">' + carHtml + '</select>\
                                                    </div>\
                                                </div>\
                                            </div>\
                                            <div class="rqOption fl">\
                                                <label class="fl">押运员：</label>\
                                                <div class="selectBox fl">\
                                                    <div class="mySelect">\
                                                            <select name="guards" id="guards" class="selectmenu"  style="width:132px;">' + userHtml + '</select>\
                                                    </div>\
                                                </div>\
                                            </div>\
                                        </div>\
                                        <div class="rqLine clearfix">\
                                            <div class="rqOption fl">\
                                                    <label class="fl">出库门店：</label>\
                                                    <div class="selectBox fl">\
                                                        <div class="mySelect">\
                                                                <select name="shop_id" class="selectmenu" id="shop_type" style="width:132px;">' + shopHtml + '</select>\
                                                        </div>\
                                                    </div>\
                                            </div>\
                                        </div>\
                                        <div class="rqLine clearfix">\
                                            <div class="rqLine clearfix">\
                                                <div class="rqOption fl">\
                                                    <label class="fl">钢瓶类型：</label>\
                                                    <div class="selectBox fl">\
                                                        <div class="mySelect">\
                                                                <select name="" id="bottle_type" class="selectmenu"  style="width:132px;">' + bottleHtml + '</select>\
                                                        </div>\
                                                    </div>\
                                                </div>\
                                                <div class="rqOption fl">\
                                                    <label class="fl">钢瓶数量：</label>\
                                                    <div class="inputBox fl placeholder">\
                                                        <input type="text" id="bottle_num" style="width:56px;">\
                                                    </div>\
                                                </div>\
                                                <div class="fl mt10"><a href="javascript:;" class="buleColor" id="add_bottle">+添加</a></div>\
                                            </div>\
                                        </div>\
                                        <div class="rqLine">\
                                            <div class="rqOption clearfix">\
                                                <div class="rqTabItem">\
                                                    <table>\
                                                        <thead>\
                                                                <tr>\
                                                                        <th>商品规格</th>\
                                                                        <th>数量</th>\
                                                                        <th>操作</th>\
                                                                </tr>\
                                                        </thead>\
                                                        <tbody id="plist"></tbody>\
                                                    </table>\
                                                </div>\
                                            </div>\
                                        </div>\
                                        <div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
							<textarea class="fl" name="comment" id="comment" style="width:558px;height:140px;"></textarea>\
						</div>\
					</div>\
                                        <div class="rqLine rqLineBtn clearfix">\
                                            <div class="rqOption fl">\
                                                <a href="javascript:;" class="blueBtn saveBtn">保存</a>\
                                            </div>\
                                            <div class="rqOption fl">\
                                                <a href="javascript:;" class="grayBtn closeBtn">取消</a>\
                                            </div>\
                                        </div>\
                                    </form>\
                            </div>';
                    }
                });
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '420px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var plist = $("#plist").html();
                    if (plist == '') {
                        alert('请添加借调数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/shop/addsecondment",
                        data: formdata,
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                dlg.close().remove();
                                location.reload();
                            } else {
                                alert('创建失败');
                            }
                        }
                    });
                });

                $(dlg.node).find('#del').off('click').live('click', function () {
                    $(this).parent().parent().remove();
                });

                //添加按钮
                $(dlg.node).find('#add_bottle').off('click').on('click', function () {
                    var bottle_id = $("#bottle_type option:selected").val();
                    var bottle_text = $("#bottle_type option:selected").text();
                    var bottle_num = $("#bottle_num").val();
                    var blist = bottle_id + '|' + bottle_num;
                    var fhtml = '<tr>\
                                    <td>' + bottle_text + '</td>\
                                    <td>' + bottle_num + '</td>\
                                    <td class="tableBtn"><a href="javascript:;" id="del">删除</a><input type="hidden" name="bottle_list[]" value="' + blist + '" /></td>\
                            </tr>';
                    if (bottle_num > 0) {
                        $("#plist").append(fhtml);
                    } else {
                        alert('请添加数量');
                    }
                });
                
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
            });
        }
        
        funcs.tjbtnFn = function () {
            $(".qtjbtn").click(function () {
                var num = $("#num").val();
                if(num == 0){
                    alert('请录入正确钢瓶信息');
                    return false;
                }
                var number = $("#number").val();
                var total = $("#total").val();
                var sn = $("#sn").val();
                var to_shop_id = $("#to_shop_id").val();
                var shop_id = $("#shop_id").val();
                if (number == '' || total == '') {
                    alert('请输入钢瓶号');
                    return false;
                }
                if(confirm('确认提交？')){
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/shop/roambottle",
                        data: {number: number, total: total, sn: sn, to_shop_id: to_shop_id},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                location.href = "/filling/confirmebottle?sn=" + sn + "&shop_id=" + shop_id;
                            } else {
                                alert(preview.msg);
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

            var pid = $("#pid").val();
            var xid = $("#xid").val();
            this.selectRegion(10, 140, xid, pid);

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
            
            //门店添加预付款记录
            this.dialogFn();
           
            //门店添加缴费记录
            this.sjiaoFn();
            
            //门店钢瓶借调
            this.addjdFn();
            
            //门店录入钢瓶
            this.tjbtnFn();
        }
        funcs.init();
    });
});

