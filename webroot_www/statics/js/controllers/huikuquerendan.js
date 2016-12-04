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
        funcs.modifyFn = function () {
            $(document).on('click', '.hkModify', function () {
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
                        
                        if (preview['type']){
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
                                        配送回库确认单\
                                        <span></span>\
                                    </div>\
                                    <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
                                            <div class="rqOption clearfix">\
                                                <label class="fl">车牌号：</label>\
                                                <div class="mySelect">\
                                                    <select name="license_plate" id="license_plate" class="selectmenu"  style="width:132px;">' + carHtml + '</select>\
                                                </div>\
                                            </div>\
					</div>\
					<div class="rqLine">\
                                            <div class="rqOption clearfix">\
                                                <label class="fl">押运员：</label>\
                                                <div class="mySelect">\
                                                    <select name="guards" id="guards" class="selectmenu"  style="width:132px;">' + userHtml + '</select>\
                                                </div>\
                                            </div>\
					</div>\
					<div class="rqLine clearfix">\
                                            <div class="rqOption fl">\
                                                <label class="fl">选择门店：</label>\
                                                <div class="selectBox fl">\
                                                    <div class="mySelect">\
                                                        <select name="" class="selectmenu" id="shop_type" style="width:132px;">' + shopHtml + '</select>\
                                                    </div>\
                                                </div>\
                                            </div>\
					</div>\
					<div class="rqLine clearfix">\
                                            <div class="rqLine clearfix">\
                                                <div class="rqOption fl">\
                                                    <label class="fl">钢瓶类别：</label>\
                                                    <div class="selectBox fl">\
                                                        <div class="mySelect">\
                                                            <select name="" id="bottle" class="selectmenu"  style="width:132px;">' + tHtml + '</select>\
                                                        </div>\
                                                    </div>\
                                                </div>\
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
					<div class="rqTabItem">\
						<table>\
							<thead>\
								<tr>\
									<th>配送对象</th>\
									<th>商品类别</th>\
									<th>商品规格</th>\
									<th>数量</th>\
									<th>操作</th>\
								</tr>\
							</thead>\
							<tbody id="plist"></tbody>\
						</table>\
					</div>\
					<div class="rqLine clearfix">\
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
                })

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var plist = $("#plist").html();
                    if(plist == ''){
                        alert('请添加回库数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/filling/addconfirmback",
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
                    var shop_id = $("#shop_type option:selected").val();
                    var shop_name = $("#shop_type option:selected").text();
                    var bottle = $("#bottle option:selected").val();
                    var bottletext = $("#bottle option:selected").text();
                    var bottle_id = $("#bottle_type option:selected").val();
                    var bottle_text = $("#bottle_type option:selected").text();
                    var bottle_num = $("#bottle_num").val();
                    var blist = shop_id + '|' + bottle + '|' + bottle_id + '|' + bottle_num;
                    var fhtml = '<tr>\
                                    <td>' + shop_name + '</td>\
                                    <td>' + bottletext + '</td>\
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
            
            $(document).on('click', '.hkModifyUpd', function () {
                var _this = this;
                var confirme_no = $(this).attr('cno');
                
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'other_edit', 'cno': confirme_no},
                    success: function(result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        var tHtml = '';
                        var shopHtml = '';
                        var carHtml = '';
                        var userHtml = '';
                        var dataHtml = '';
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
                        
                        if (preview['cdata']) {
                            for (key in preview['cdata']) {
                                var md_id = preview['cdata'][key]['shop_id'];
                                var md_tid = preview['cdata'][key]['ftype'];
                                var md_list = preview['cdata'][key]['shop_id'] + '|' + preview['cdata'][key]['ftype'] + '|' + preview['cdata'][key]['type'] + '|' + preview['cdata'][key]['num'];
                                dataHtml += "<tr>" +
                                        "<td>" + preview['shop'][md_id]['shop_name'] + "</td>" +
                                        "<td>" + preview['type'][md_tid] + "</td><td>" + preview['cdata'][key]['typename'] + "</td><td>" + preview['cdata'][key]['num'] + "</td>" +
                                        "<td class='tableBtn'><a href='javascript:;' id='del'>删除</a>" +
                                        "<input type='hidden' name='bottle_list[]' value='" + md_list + "'></td>" +
                                        "</tr>";
                            }
                        }

                        str = '<div class="pop_cont">\
                                    <div class="contRTitle2">\
                                        配送回库确认单\
                                        <span></span>\
                                    </div>\
                                    <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
                                            <div class="rqOption clearfix">\
                                                <label class="fl">车牌号：</label>\
                                                <div class="mySelect">\
                                                    <select name="license_plate" id="license_plate" class="selectmenu"  style="width:132px;">' + carHtml + '</select>\
                                                </div>\
                                            </div>\
					</div>\
					<div class="rqLine">\
                                            <div class="rqOption clearfix">\
                                                <label class="fl">押运员：</label>\
                                                <div class="mySelect">\
                                                    <select name="guards" id="guards" class="selectmenu"  style="width:132px;">' + userHtml + '</select>\
                                                </div>\
                                            </div>\
					</div>\
					<div class="rqLine clearfix">\
                                            <div class="rqOption fl">\
                                                <label class="fl">选择门店：</label>\
                                                <div class="selectBox fl">\
                                                    <div class="mySelect">\
                                                        <select name="" class="selectmenu" id="shop_type" style="width:132px;">' + shopHtml + '</select>\
                                                    </div>\
                                                </div>\
                                            </div>\
					</div>\
					<div class="rqLine clearfix">\
                                            <div class="rqLine clearfix">\
                                                <div class="rqOption fl">\
                                                    <label class="fl">钢瓶类别：</label>\
                                                    <div class="selectBox fl">\
                                                        <div class="mySelect">\
                                                            <select name="" id="bottle" class="selectmenu"  style="width:132px;">' + tHtml + '</select>\
                                                        </div>\
                                                    </div>\
                                                </div>\
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
					<div class="rqTabItem">\
						<table>\
							<thead>\
								<tr>\
									<th>配送对象</th>\
									<th>商品类别</th>\
									<th>商品规格</th>\
									<th>数量</th>\
									<th>操作</th>\
								</tr>\
							</thead>\
							<tbody id="plist">' + dataHtml + '</tbody>\
						</table>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
                                                        <input type="hidden" name="confirm_no" id="confirm_no" value="'+confirme_no+'" />\
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
                })

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function() {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function() {
                    var plist = $("#plist").html();
                    if (plist == '') {
                        alert('请添加回库数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/filling/editconfirmback",
                        data: formdata,
                        success: function(result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                dlg.close().remove();
                                location.reload();
                            } else {
                                alert('更新失败');
                            }
                        }
                    });
                });

                $(dlg.node).find('#del').off('click').live('click', function() {
                    $(this).parent().parent().remove();
                });

                //添加按钮
                $(dlg.node).find('#add_bottle').off('click').on('click', function() {
                    var shop_id = $("#shop_type option:selected").val();
                    var shop_name = $("#shop_type option:selected").text();
                    var bottle = $("#bottle option:selected").val();
                    var bottletext = $("#bottle option:selected").text();
                    var bottle_id = $("#bottle_type option:selected").val();
                    var bottle_text = $("#bottle_type option:selected").text();
                    var bottle_num = $("#bottle_num").val();
                    var blist = shop_id + '|' + bottle + '|' + bottle_id + '|' + bottle_num;
                    var fhtml = '<tr>\
                                    <td>' + shop_name + '</td>\
                                    <td>' + bottletext + '</td>\
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
            
            $(".qrhk").live('click',function() {
                var cno = $(this).attr('cno');
                var that = $(this);
                if(confirm('是否确认？')){
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/rkstock",
                        data: {cno: cno},
                        success: function (result) {
                            var preview = eval("("+ result+")");
                            if(preview.status >= 1){
                                that.removeClass('qrhk').html('已确认');
                            }else{
                                alert('操作失败');
                            }
                        }
                    });
                }
            });
            
            $(".yqsh").live('click',function(){
                var cno = $(this).attr('cno');
                var shop_id = $(this).attr('shop_id');
                var that = $(this);
                if(confirm('确认审核？')){
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/yhkstock",
                        data: {cno: cno,shop_id:shop_id},
                        success: function (result) {
                            var preview = eval("("+ result+")");
                            if(preview.status >= 1){
                                that.removeClass('yqsh').html('已审核');
                            }else{
                                alert('操作失败');
                            }
                        }
                    });
                }
            });
        }
        // 初始化
        funcs.init = function () {
            this.pageFn();

            this.modifyFn();
        }
        funcs.init();
    });
});