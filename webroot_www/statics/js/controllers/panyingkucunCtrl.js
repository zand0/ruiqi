require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages', 'vendors/highcharts/highcharts'], function ($, utils, PageNav, highcharts) {
        var funcs = {};
        funcs.chartFn = function () {
            $('#container').highcharts({
                title: {
                    text: '库存/日期',
                    style: {
                        "color": "#666",
                        "fontSize": "12px",
                        "fontFamily": "宋体"
                    },
                    align: "left",
                    x: 36
                },
                credits: {
                    enabled: false // 禁用版权信息
                },
                xAxis: {
                    categories: ['7月1日', '7月2日', '7月3日', '7月4日', '7月5日', '7月6日', '7月7日', '7月8日', '7月9日', '7月10日', '7月11日', '7月12日']
                },
                yAxis: {
                    title: {
                        text: '库存(吨)'
                    },
                    plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                },
                colors: ['#7cb5ec', '#f2b359'],
                legend: {
                    title: {
                        style: {'fontWeight': 'normal', 'color': '#545454'}
                    },
                    align: 'left',
                    verticalAlign: 'top',
                    x: 100,
                    y: -10
                },
                tooltip: {
                    valueSuffix: '吨'
                },
                series: [{
                        name: '计算库存',
                        data: [-5, -10, 5, 8, 15, 4, 6, 5, 8, 10, 4, 6]
                    }, {
                        name: '盘点库存',
                        data: [-10, 5, 9, 10, 5, 4, 10, 0, 15, 4, 10, 8]
                    }]
            });
        }
        funcs.chart2Fn = function () {
            $('#container2').highcharts({
                title: {
                    text: '盈亏/日期',
                    style: {
                        "color": "#666",
                        "fontSize": "12px",
                        "fontFamily": "宋体"
                    },
                    align: "left",
                    x: 36
                },
                credits: {
                    enabled: false // 禁用版权信息
                },
                xAxis: {
                    categories: ['7月1日-7月7日', '7月8日-7月14日', '7月15日-7月22日', '7月23日-7月30日', '7月31日-8月6日', '8月7日-8月14日', '8月15日-8月22日', '8月23日-8月30日', '8月31日-9月6日', '9月7日-9月14日', '9月15日-9月22日', '9月23日-9月30日']
                },
                yAxis: {
                    title: {
                        text: '盈亏（吨）'
                    },
                    plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    valueSuffix: '吨'
                },
                series: [{
                        name: '盈亏',
                        data: [-5, -10, 5, 8, 15, 4, 6, 5, 8, 10, 4, 6]
                    }]
            });
        }
        // 月
        funcs.chart3Fn = function () {
            $('#container3').highcharts({
                title: {
                    text: '盈亏/日期',
                    style: {
                        "color": "#666",
                        "fontSize": "12px",
                        "fontFamily": "宋体"
                    },
                    align: "left",
                    x: 36
                },
                credits: {
                    enabled: false // 禁用版权信息
                },
                xAxis: {
                    categories: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
                },
                yAxis: {
                    title: {
                        text: '盈亏（吨）'
                    },
                    plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    valueSuffix: '吨'
                },
                series: [{
                        name: '盈亏',
                        data: [-5, -10, 5, 8, 15, 4, 6, 5, 8, 10, 4, 6]
                    }]
            });
        }
        // 日月周选项卡
        funcs.tabFn = function () {
            $('.rqBtn li').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
                $('.Itembox .tabItem').eq($(this).index()).show().siblings().hide();
            });
        }
        // 添加重瓶
        funcs.dialogFn = function () {
            $('#addZp').on('click', function () {
                var str = '<div class="pop_cont">\
					<div class="contRTitle2">\
						添加今日重瓶\
						<span></span>\
					</div>\
					<div class="popTable">\
						<table>\
							<thead>\
								<tr>\
									<th>重瓶规格</th>\
									<th>请输入今日重瓶</th>\
								</tr>\
							</thead>\
							<tbody>\
								<tr>\
									<td>5kg</td>\
									<td>\
										<input type="text">\
									</td>\
								</tr>\
								<tr>\
									<td>15kg</td>\
									<td>\
										<input type="text">\
									</td>\
								</tr>\
								<tr>\
									<td>50kg</td>\
									<td>\
										<input type="text">\
									</td>\
								</tr>\
							</tbody>\
						</table>\
					</div>\
					<div  class="rqLine clearfix" style="padding-left:242px;">\
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
                    height: '380px'
                });

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
            })
        }
        funcs.init = function () {
            // 日周月选项卡
            this.tabFn();
            // 日--图表
            funcs.chartFn();
            // 周--图表
            funcs.chart2Fn();
            // 月--图表
            funcs.chart3Fn();
            // 添加重瓶
            funcs.dialogFn();
        }
        // 初始化
        funcs.init();
    });
});