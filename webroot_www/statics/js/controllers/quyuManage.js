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
            $('.addGuize').on('click', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加区域\
						<span></span>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">所在省份：</label>\
							<div class="fl areaSelect">\
								<select id="loc_province2" style="width:170px;" class="selectmenu"></select>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">所在城市：</label>\
							<div class="fl areaSelect">\
								<select id="loc_city2" style="width:170px;" class="selectmenu"></select>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">区/县：</label>\
							<div class="fl areaSelect">\
								<select id="loc_town2" style="width:170px;" class="selectmenu"></select>\
							</div>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">街道/社区：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:380px;">\
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
                    height: '420px'
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
                // 区域
                location.init({'sheng': 'loc_province2', 'shi': 'loc_city2', 'qu': 'loc_town2'});
            });
        }
        funcs.addFn = function () {
            $(document).on('click', '.addArea', function () {
                var _this = this;
                var str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						增加区域\
						<span></span>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption rqOption4 fl poptxt">\
							<label class="fl">所在省份：</label>\
							<p class="fl">河北省</p>\
						</div>\
					</div>\
					<div class="rqLine clearfix">\
						<div class="rqOption rqOption4 fl poptxt">\
							<label class="fl">所在城市：</label>\
							<p class="fl">沧州市</p>\
						</div>\
					</div>\
					<div class="rqLine clearfix poptxt">\
						<div class="rqOption rqOption4 fl">\
							<label class="fl">区/县：</label>\
							<p class="fl">和平县</p>\
						</div>\
					</div>\
					<div class="rqLine">\
						<div class="rqOption rqOption4 clearfix">\
							<label class="fl">街道/社区：</label>\
							<div class="inputBox fl placeholder">\
								<input type="text" style="width:360px;">\
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
                    height: '320px'
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
            });
        }
        
        funcs.selectRegion = function(id,shi,xid,pid,cid) {
            regionlist('.region_1',10,shi);
            regionlist('.region_2', shi,xid);
            regionlist('.region_3', xid,pid);
            if(pid>0){
                regionlist('.region_4', pid,cid);
            }
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
            // 添加区域
            this.dialogFn();
            // 增加
            this.addFn();
            
            var pid = $("#pid").val();
            var xid = $("#xid").val();
            var cid = $("#cid").val();
            this.selectRegion(10,140,xid,pid,cid);
            
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
        }
        funcs.init();
    });
});
