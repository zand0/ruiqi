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
		funcs.addPeopleFn=function (){
		    $('.addPeople').on('click',function(){
		        var _this = this;
		        var str = '<div class="pop_cont">\
		            <div class="contRTitle2 ">\
		                添加人员\
		                <span></span>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">登录名：</label>\
		                    <div class="inputBox fl placeholder">\
		                        <input type="text" style="width:148px;">\
		                        <span>请输入登录名</span>\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">密码：</label>\
		                    <div class="inputBox fl placeholder">\
		                        <input type="text" style="width:148px;">\
		                        <span>请输入密码</span>\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">姓名：</label>\
		                    <div class="inputBox fl placeholder">\
		                        <input type="text" style="width:148px;">\
		                        <span>请输入姓名</span>\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">手机号：</label>\
		                    <div class="inputBox fl placeholder">\
		                        <input type="text" style="width:148px;">\
		                        <span>请输入手机号码</span>\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix rqRadio">\
		                    <label class="fl">隶属：</label>\
		                    <div class="myradio fl">\
		                        <div class="radiobox clearfix">\
		                            <a href="javascript:;" class="active" data-value="气站">气站</a>\
		                            <a href="javascript:;" data-value="门店">门店</a>\
		                        </div>\
		                        <input type="hidden" value="气站">\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine clearfix">\
		                <div class="rqOption fl">\
		                    <label class="fl">所属部门或门店：</label>\
		                    <div class="mySelect fl">\
		                        <select name="" class="selectmenu" style="width:132px;">\
		                            <option selected="selected">选择部门</option>\
		                            <option>Slow</option>\
		                            <option>Medium</option>\
		                            <option>Fast</option>\
		                            <option>Faster</option>\
		                        </select>\
		                    </div>\
		                </div>\
		                <div class="rqOption fl mr0">\
		                    <label class="fl">选择角色：</label>\
		                    <div class="mySelect fl">\
		                        <select name="" class="selectmenu" style="width:132px;">\
		                            <option selected="selected">选择角色</option>\
		                            <option>Slow</option>\
		                            <option>Medium</option>\
		                            <option>Fast</option>\
		                            <option>Faster</option>\
		                        </select>\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix rqRadio">\
		                    <label class="fl">状态：</label>\
		                    <div class="myradio fl">\
		                        <div class="radiobox clearfix">\
		                            <a href="javascript:;" class="active" data-value="在职">在职</a>\
		                            <a href="javascript:;" data-value="离职">离职</a>\
		                            <a href="javascript:;" data-value="休假">休假</a>\
		                        </div>\
		                        <input type="hidden" value="在职">\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">备注：</label>\
		                    <textarea class="fl"></textarea>\
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
		            alert('保存成功');
		            dlg.close().remove();
		        });
		        // 下拉框
		        $(dlg.node).find('.selectmenu').selectmenu();
		        // 初始化
		        utils.placeHolder($('.placeholder'));
		        // 单选按钮
		        utils.radioFn();
		    });
		}
		funcs.fenpeigangweiFn=function (){
			$(document).on('click','.fenbeiBtn',function(){
		        var _this = this;
		        var str = '<div class="pop_cont">\
					<div class="contRTitle2 ">\
						分配岗位\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption clearfix">\
							<label class="fl">岗位：</label>\
							<div class="selectBox fl rqOption" style="width:170px;">\
								<div class="mySelect">\
									<select name="" class="selectmenu">\
										<option selected="selected">请选择岗位</option>\
										<option>Slow</option>\
										<option>Medium</option>\
										<option>Fast</option>\
										<option>Faster</option>\
									</select>\
								</div>\
							</div>\
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
		            html:str,
		            width:'726px',
		            height:'200px'
		        });

		        //处理自定义按钮事件
		        // 取消
			    $(dlg.node).find('.closeBtn').off('click').on('click',function(){
			        alert(1)
		            dlg.close().remove();
			    });
		        //保存
		        $(dlg.node).find('.saveBtn').off('click').on('click',function(){
		            alert('保存成功');
		            dlg.close().remove();
		        });
		        // 下拉框
		        $(dlg.node).find('.selectmenu').selectmenu();
		        // 初始化
		        utils.placeHolder($('.placeholder'));
		        // 单选按钮
		        utils.radioFn();
		    });
		}
		funcs.modifyFn=function (){
			$(document).on('click','.modBtn',function(){
		        var _this = this;
		        var str = '<div class="pop_cont">\
		            <div class="contRTitle2 ">\
		                修改人员\
		                <span></span>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">登录名：</label>\
		                    <div class="inputBox fl placeholder">\
		                        <input type="text" style="width:148px;" value="">\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">姓名：</label>\
		                    <div class="inputBox fl placeholder">\
		                        <input type="text" style="width:148px;" value="">\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">手机号：</label>\
		                    <div class="inputBox fl placeholder">\
		                        <input type="text" style="width:148px;" value="">\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix rqRadio">\
		                    <label class="fl">隶属：</label>\
		                    <div class="myradio fl">\
		                        <div class="radiobox clearfix">\
		                            <a href="javascript:;" class="active" data-value="气站">气站</a>\
		                            <a href="javascript:;" data-value="门店">门店</a>\
		                        </div>\
		                        <input type="hidden" value="气站">\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine clearfix">\
		                <div class="rqOption fl">\
		                    <label class="fl">所属部门/门店：</label>\
		                    <div class="mySelect fl">\
		                        <select name="" class="selectmenu" style="width:132px;">\
		                            <option selected="selected">选择部门</option>\
		                            <option>Slow</option>\
		                        </select>\
		                    </div>\
		                </div>\
		                <div class="rqOption fl mr0">\
		                    <label class="fl">选择角色：</label>\
		                    <div class="mySelect fl">\
		                        <select name="" class="selectmenu" style="width:132px;">\
		                            <option selected="selected">选择角色</option>\
		                            <option>Slow</option>\
		                        </select>\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix rqRadio">\
		                    <label class="fl">状态：</label>\
		                    <div class="myradio fl">\
		                        <div class="radiobox clearfix">\
		                            <a href="javascript:;" class="active" data-value="在职">在职</a>\
		                            <a href="javascript:;" data-value="离职">离职</a>\
		                            <a href="javascript:;" data-value="休假">休假</a>\
		                        </div>\
		                        <input type="hidden" value="在职">\
		                    </div>\
		                </div>\
		            </div>\
		            <div class="rqLine">\
		                <div class="rqOption clearfix">\
		                    <label class="fl">备注：</label>\
		                    <textarea class="fl"></textarea>\
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
		            alert('保存成功');
		            dlg.close().remove();
		        });
		        // 下拉框
		        $(dlg.node).find('.selectmenu').selectmenu();
		        // 初始化
		        utils.placeHolder($('.placeholder'));
		        // 单选按钮
		         utils.radioFn();
		    });
		}
		// 初始化
		funcs.init=function (){
			// 分页
			this.pageFn();
			// 添加人员
			this.addPeopleFn();
			// 分配岗位
			this.fenpeigangweiFn();
			// 修改
			this.modifyFn();
		}
		funcs.init();
	});
});
