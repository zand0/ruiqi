require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages', 'vendors/jqueryUI/selectmenu'], function ($, utils, PageNav, selectmenu) {
        var funcs = {};
        funcs.pageFn = function () {
            var url = window.location.protocol;  //得到链接地址

            var curPage = parseInt($('.currentPage').val() || 1);
            var totalPage = parseInt($('.totalPage').val() || 1);
            var pager = new PageNav({
                currentPage: curPage,
                totalPage: totalPage,
                wrapId: '#pageNav',
                callback: function (curPage) {
                    url += '?page=' + curPage;
                    location.href = url;
                }
            });
        }
        funcs.page2Fn = function () {
            var curPage = parseInt($('.currentPage').val() || 1);
            var totalPage = parseInt($('.totalPage').val() || 1);
            var pager = new PageNav({
                currentPage: curPage,
                totalPage: totalPage,
                wrapId: '#pageNav2',
                callback: function (curPage) {
                    url += '?page=' + curPage;
                    location.href = url;
                }
            });
        }
        funcs.releaseApplayFn = function () {
            $('.releaseApply').on('click', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
		            <div class="contRTitle2 ">\
		                发布申请\
		                <span></span>\
		            </div>\
		            <div class="rqLine clearfix">\
		                <div class="rqOption fl">\
		                    <label class="fl">消息标题：</label>\
		                    <div class="inputBox fl placeholder">\
		                        <input type="text" style="width:435px;">\
		                        <span>请填写消息标题</span>\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">审批对象：</label>\
		                    <div class="selectBox fl rqOption" style="width:170px;">\
		                        <div class="mySelect">\
		                            <select name="" class="selectmenu">\
		                                <option selected="selected">选择部门</option>\
		                                <option>Slow</option>\
		                                <option>Medium</option>\
		                            </select>\
		                        </div>\
		                    </div>\
		                    <div class="selectBox fl rqOption" style="width:170px;">\
		                        <div class="mySelect">\
		                            <select name="" class="selectmenu">\
		                                <option selected="selected">选择审批人</option>\
		                                <option>Slow</option>\
		                                <option>Medium</option>\
		                            </select>\
		                        </div>\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">正文内容：</label>\
		                    <textarea class="fl" style="width:560px;height:420px;"></textarea>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix rqRadio">\
		                    <label class="fl">申请状态：</label>\
		                    <div class="myradio fl">\
								<div class="radiobox clearfix">\
									<a href="javascript:;" class="active" data-value="普通">普通</a>\
									<a href="javascript:;" data-value="紧急">紧急</a>\
									<a href="javascript:;" data-value="重要">重要</a>\
								</div>\
								<input type="hidden" value="普通">\
							</div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">申请附件：</label>\
		                    <a href="javascript:void(0);" class="blueBtn fl">上传附件</a>\
		                </div>\
		            </div>\
		            <div  class="rqLine rqLineBtn clearfix">\
		                <div class="rqOption fl">\
		                    <a href="javascript:;" class="blueBtn">发布</a>\
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
                });

                //处理自定义按钮事件
                // 取消
                $(dlg.node).find('.closeBtn').off('click').on('click', function () {
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    alert('保存成功');
                    dlg.close().remove();
                });
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
                // 初始化
                utils.placeHolder($('.placeholder'));
                // 单选按钮
                utils.radioFn();
            })
        }
        funcs.shenpiFn = function() {
            $(".shenpi").click(function(){
                var aid = $("#aid").val();
                var comment = $("#comment").val();
                if (comment != '') {
                    var that = $(this);
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/shenpi",
                        data: {'aid': aid, 'comment': comment,'type':1},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                alert('审批成功');
                            } else {
                                alert('操作失败');
                            }
                        }
                    });
                } else {
                    alert('请添加审批意见');
                    return false;
                }
            });
            $(".nshenpi").click(function(){
                var aid = $("#aid").val();
                var comment = $("#comment").val();
                if (comment != '') {
                    var that = $(this);
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/ajaxdata/shenpi",
                        data: {'aid': aid, 'comment': comment,'type':2},
                        success: function (result) {
                            var preview = eval("(" + result + ")");
                            if (preview.status >= 1) {
                                alert('审批成功');
                            } else {
                                alert('操作失败');
                            }
                        }
                    });
                } else {
                    alert('请添加审批意见');
                    return false;
                }
            });
        }
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 分页2
            this.page2Fn();
            // 发布申请
            this.releaseApplayFn();
            // 申请审批
            this.shenpiFn();
        }
        funcs.init();
    });
});