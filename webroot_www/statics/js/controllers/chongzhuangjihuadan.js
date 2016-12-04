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
            $('.addFilling').on('click', function () {
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'bottle'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var bottleHtml = '';
                        var productHtml = '';
                        var shopHtml = '';
                        if (preview['bottle']) {
                            for (key in preview['bottle']) {
                                bottleHtml += "<option value='" + key + "'>" + preview['bottle'][key] + "</option>";
                            }

                            str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加充装计划单\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine">\
						<div class="rqOption clearfix rqRadio">\
							<label class="fl">录入状态：</label>\
							<div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="0">手动添加</a>\
									<a href="javascript:;" data-value="1" id="zdhz">自动汇总</a>\
								</div>\
								<input type="hidden" value="0">\
							</div>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">钢瓶规格：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="" id="bottle_type" class="selectmenu"  style="width:170px;">' + bottleHtml + '</select>\
								</div>\
							</div>\
						</div>\
						<div class="rqOption fl">\
							<label class="fl">充装数量：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" id="bottle_num" style="width:100px;">\
							</div>\
						</div>\
						<div class="fl mt10"><a href="javascript:;" class="buleColor" id="add_bottle">+添加</a></div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">充装明细：</label>\
							<div class="rqTabItem fl rqTabItem2" style="width:580px;">\
								<table>\
									<thead>\
										<tr>\
											<th>钢瓶规格</th>\
											<th>充装数量</th>\
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
							<textarea class="fl" name="comment" style="width:558px;height:140px;"></textarea>\
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
                        }
                    }
                });

                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '560px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var plist = $("#plist").html();
                    if(plist == ''){
                        alert('请添加数据');
                        return false;
                    }
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/filling/addrecords",
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
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();

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
                $(dlg.node).find('#del').off('click').live('click', function () {
                    $(this).parent().parent().remove();
                });
                
                //同步配送计划未充装计划
                $(dlg.node).find("#zdhz").off('click').on('click', function () {
                    if(confirm('确认同步配送计划？')){
                        $.ajax({
                            type: "POST",
                            async: false,
                            url: "/filling/tbrecords",
                            success: function (result) {
                                var preview = eval("(" + result + ")");
                                if (preview.status >= 1) {
                                    alert('同步成功');
                                } else {
                                    alert('同步失败');
                                }
                                dlg.close().remove();
                                location.reload();
                            }
                        });
                        return false;
                    }
                });
                //单选框
                utils.radioFn();
            });
            
            //充装计单派发
            $(".pfbtn").on('click', function() {
                var fno = $(this).attr('fno');
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datatype",
                    data: {'type': 'czjh'},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var userHtml = '';
                        if (preview['user']) {
                            for (key in preview['user']) {
                                userHtml += "<option value='" + preview['user'][key]['user_id'] + '|' + preview['user'][key]['username'] +"'>" + preview['user'][key]['real_name'] + "</option>";
                            }
                        }

                            str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						充装计划单派发\
						<span></span>\
					</div>\
                                        <form name="form" method="POST" id="formtable">\
					<div class="rqLine clearfix">\
						<div class="rqOption fl">\
							<label class="fl">充装班长：</label>\
							<div class="selectBox fl">\
								<div class="mySelect">\
									<select name="cz_user_name" id="cz_user_name" class="selectmenu"  style="width:170px;">' + userHtml + '</select>\
								</div>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">备注：</label>\
							<textarea class="fl" name="comment" style="width:558px;height:140px;"></textarea>\
						</div>\
					</div>\
					<div  class="rqLine rqLineBtn clearfix">\
						<div class="rqOption fl">\
                                                        <input type="hidden" name="fno" value="'+fno+'" />\
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
                    height: '560px'
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    var formdata = $('#formtable').serialize();
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/filling/pfrecords",
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
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
            });
            
            //充装计划单确认入库
            $(".qrbtn").on('click', function () {
                var fno = $(this).attr('fno');
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/qzrkbottle",
                    data: {'cno': fno},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        if (preview.status >=0){
                            alert('入库成功');
                            location.reload();
                        }
                    }
                });
            });
        }
        
        funcs.printarea = function() {
            var printAreaCount = 0;
            $.fn.printArea = function () {
                var ele = $(this);
                var idPrefix = "printArea_";
                removePrintArea(idPrefix + printAreaCount);
                printAreaCount++;
                var iframeId = idPrefix + printAreaCount;
                var iframeStyle = 'position:absolute;width:0px;height:0px;left:-500px;top:-500px;';
                iframe = document.createElement('IFRAME');
                $(iframe).attr({
                    style: iframeStyle,
                    id: iframeId
                });
                document.body.appendChild(iframe);
                var doc = iframe.contentWindow.document;
                $(document).find("link").filter(function () {
                    return $(this).attr("rel").toLowerCase() == "stylesheet";
                }).each(
                        function () {
                            doc.write('<link type="text/css" rel="stylesheet" href="'
                                    + $(this).attr("href") + '" >');
                        });
                doc.write('<div class="' + $(ele).attr("class") + '">' + $(ele).html()
                        + '</div>');
                doc.close();
                var frameWindow = iframe.contentWindow;
                frameWindow.close();
                frameWindow.focus();
                frameWindow.print();
            }
            var removePrintArea = function (id) {
                $("iframe#" + id).remove();
            };
            
            $(".btnPrint").click(function(){ 
                
                var fno = $(this).attr('fno');
                var _this = this;
                var str = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: "/ajaxdata/datafill",
                    data: {'fno': fno},
                    success: function (result) {
                        var preview = eval("(" + result + ")");
                        var listhtml = "";
                        if (preview['message']) {
                            for (key in preview['message']) {
                                listhtml += '<tr>';
                                listhtml += '<td rowspan="' + preview['message'][key]['num'] + '">' + fno + '</td>';
                                listhtml += preview['message'][key]['list1'];
                                listhtml += '<td rowspan="' + preview['message'][key]['num'] + '">' + preview['message'][key]['time'] + '</td>';
                                listhtml += '<td rowspan="' + preview['message'][key]['num'] + '">' + preview['message'][key]['cz_user_name'] + '</td>';
                                listhtml += "</tr>";
                                listhtml += preview['message'][key]['list2'];
                            }
                        }
                        
                        str = '<div class="pop_cont">\
                                <div class="contRTitle2">\
                                        添加充装计划单\
                                        <span></span>\
                                </div>\
                                <div class="rqLine" id="printContent">\
                                        <div class="rqOption clearfix">\
                                                <label class="fl">充装明细：</label>\
                                                <div class="rqTabItem fl rqTabItem2" style="width:580px;">\
                                                        <table>\
                                                                <thead>\
                                                                        <tr>\
                                                                                <th>充装单号</th>\
                                                                                <th>钢瓶规格</th>\
                                                                                <th>充装数量</th>\
                                                                                <th>充装时间</th>\
                                                                                <th>操作</th>\
                                                                        </tr>\
                                                                </thead>\
                                                                <tbody id="plist">' + listhtml + '</tbody>\
                                                        </table>\
                                                </div>\
                                        </div>\
                                </div>\
                                <div  class="rqLine rqLineBtn clearfix">\
                                        <div class="rqOption fl">\
                                                <a href="javascript:;" class="blueBtn dyBtn">打印</a>\
                                        </div>\
                                        <div class="rqOption fl">\
                                                <a href="javascript:;" class="grayBtn closeBtn">取消</a>\
                                        </div>\
                                </div>\
                        </div>';
                        
                    }
                });
                var dlg = utils.showLog({
                    html: str,
                    width: '726px',
                    height: '560px'
                });
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                $(dlg.node).find('.dyBtn').off('click').on('click', function () {
                    $("#printContent").printArea();
                });
            });
        }
        
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 发布消息
            this.dialogFn();
            //局部打印功能
            this.printarea();
        }
        funcs.init();
    });
});