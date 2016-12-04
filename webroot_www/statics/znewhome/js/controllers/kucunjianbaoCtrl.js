require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils', 'highcharts'], function ($, utils, highcharts) {
        var funcs = {};
        funcs.chartFn = function () {
            $('#container').highcharts({
                title: {
                    text: '2016年5月20日-2016年5月26日销售额统计',
                    x: -20 //center
                },
                credits: {
                    enabled: false // 禁用版权信息
                },
                xAxis: {
                    categories: ['5-20', '5-21', '5-22', '5-23', '5-24', '5-25', '5-26']
                },
                yAxis: {
                    title: {
                        text: '销售额'
                    },
                    plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                },
                legend: {
                    title: {
                        style: {'fontWeight': 'normal', 'color': '#545454'}
                    }
                },
                tooltip: {
                    valueSuffix: '元'
                },
                series: [{
                        name: '15KG销售额',
                        data: [1050, 1240, 1030, 1185, 1500, 1280, 1650]
                    }, {
                        name: '50KG销售额',
                        data: [1300, 1165, 1468, 1308, 1600, 1546, 1800]
                    }, {
                        name: '150KG销售额',
                        data: [1598, 1885, 1600, 1895, 2000, 1875, 1960]
                    }]
            });
            $('#container').highcharts({
                chart: {
                    type: 'area'
                },
                credits: {
                    enabled: false // 禁用版权信息
                },
                title: {
                    text: '2016年5月20日-2016年5月26日燃气库存统计'
                },
                xAxis: {
                    categories: ['5-20', '5-21', '5-22', '5-23', '5-24', '5-25', '5-26'],
                    tickmarkPlacement: 'on',
                    title: {
                        enabled: false
                    }
                },
                yAxis: {
                    title: {
                        text: '百分比'
                    }
                },
                tooltip: {
                    pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.percentage:.1f}%</b> ({point.y:,.0f} millions)<br/>',
                    shared: true
                },
                plotOptions: {
                    area: {
                        stacking: 'percent',
                        lineColor: '#ffffff',
                        lineWidth: 1,
                        marker: {
                            lineWidth: 1,
                            lineColor: '#ffffff'
                        }
                    }
                },
                series: [{
                        name: '1号罐',
                        data: [98, 80, 62, 51, 40, 20, 5]
                    }, {
                        name: '2号罐',
                        data: [100, 80, 50, 30, 10, 2, 1]
                    }, {
                        name: '残液罐',
                        data: [95, 75, 50, 30, 10, 5, 2]
                    }]
            });
        }
        funcs.init = function () {
            // 图表
            this.chartFn();
            // 统计汇总页--右侧选项卡  最近7天 14天...
            utils.tabFn();
            // 双日历
            utils.doubleRiliFn($('#start'), $('#end'));
        }
        funcs.init();
    });
});
