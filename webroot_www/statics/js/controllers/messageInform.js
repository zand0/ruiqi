require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages'], function ($, utils, PageNav) {
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
            $('.releaseBtn').on('click', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
                                <div class="contRTitle2 ">\
                                        发布消息\
                                        <span></span>\
                                </div>\
                                <div class="rqLine">\
                                        <div class="rqOption clearfix">\
                                                <label class="fl">接收对象：</label>\
                                                <div class="selectBox fl rqOption" style="width:170px;">\
                                                        <div class="mySelect">\
                                                                <select name="" class="selectmenu">\
                                                                        <option selected="selected">选择接收对象</option>\
                                                                        <option>Slow</option>\
                                                                        <option>Medium</option>\
                                                                        <option>Fast</option>\
                                                                        <option>Faster</option>\
                                                                </select>\
                                                        </div>\
                                                </div>\
                                                <div class="selectBox fl rqOption" style="width:170px;">\
                                                        <div class="mySelect">\
                                                                <select name="" class="selectmenu">\
                                                                        <option selected="selected">选择接收部门</option>\
                                                                        <option>Slow</option>\
                                                                        <option>Medium</option>\
                                                                        <option>Fast</option>\
                                                                        <option>Faster</option>\
                                                                </select>\
                                                        </div>\
                                                </div>\
                                                <div class="selectBox fl rqOption" style="width:170px;">\
                                                        <div class="mySelect">\
                                                                <select name="" class="selectmenu">\
                                                                        <option selected="selected">选择接收人</option>\
                                                                        <option>Slow</option>\
                                                                        <option>Medium</option>\
                                                                        <option>Fast</option>\
                                                                        <option>Faster</option>\
                                                                </select>\
                                                        </div>\
                                                </div>\
                                        </div>\
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
                                                <label class="fl">正文内容：</label>\
                                                <textarea class="fl" style="width:562px;height:450px;"></textarea>\
                                        </div>\
                                </div>\
                                <div  class="rqLine rqLineBtn clearfix">\
                                        <div class="rqOption fl">\
                                                <a href="javascript:;" class="blueBtn saveBtn">发布</a>\
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
                    alert(1)
                    dlg.close().remove();
                });
                //保存
                $(dlg.node).find('.saveBtn').off('click').on('click', function () {
                    alert('发布成功');
                    dlg.close().remove();
                });
                // 下拉框
                $(dlg.node).find('.selectmenu').selectmenu();
                // placeholder
                utils.placeHolder($('.placeholder'));
            });
        }
        funcs.countFn = function () {
            // 上一页
            $('.msgTitleRight .prevBtn').on('click', function () {
                var m = parseInt($('.msgTitleRight p font').html());
                var n = parseInt($('.msgTitleRight p span').html());
                if (n > 1) {
                    n--;
                } else {
                    n = 1;
                }
                if (n > 1) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
                if (n < m) {
                    $('.msgTitleRight .nextBtn').addClass('active');
                } else {
                    $('.msgTitleRight .nextBtn').removeClass('active');
                }
                $('.msgTitleRight p span').html(n);
            });
            // 下一页
            $('.msgTitleRight .nextBtn').on('click', function () {
                var m = parseInt($('.msgTitleRight p font').html());
                var n = parseInt($('.msgTitleRight p span').html());
                if (n < m) {
                    n++;
                } else {
                    n = m;
                }
                if (n > 1) {
                    $('.msgTitleRight .prevBtn').addClass('active');
                } else {
                    $('.msgTitleRight .prevBtn').removeClass('active');
                }
                if (n < m) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
                $('.msgTitleRight p span').html(n);
            });
        }
        // 初始化
        funcs.init = function () {
            // 分页
            this.pageFn();
            // 发布消息
            this.dialogFn();
            // 下一页 上一页
            this.countFn();
        }
        funcs.init();
    });
});
