require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils', 'highcharts'], function ($, utils, highcharts) {
        var funcs = {};
        // 费用支出类别
        funcs.tabFn = function () {
            $('.zcDetail .list li').on('click', function () {
                $('.zcDetail .list li').removeClass('active');
                $(this).addClass('active');
            });
        }
        funcs.chartFn = function () {
            $('#container').highcharts({
                title: {
                    text: '2016年05月20日-2016年6月20日 支出统计',
                    x: -20 //center
                },
                chart: {
                    type: 'column',
                },
                credits: {
                    enabled: false // 禁用版权信息
                },
                xAxis: {
                    categories: ["周一", "周二", "周三", "周四", "周五"]
                },
                yAxis: {
                    title: {
                        text: '万元'
                    },
                    plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                },
                colors: ['#d24239', '#6db0b8', '#a1d6ba'],
                tooltip: {
                    valueSuffix: '万元'
                },
                legend: {
                    title: {
                        style: {'color': '#a0a0a0'}
                    }
                },
                series: [{
                        name: '燃气',
                        data: [10, 12, 10, 11, 15, 12, 16]
                    }, {
                        name: '钢瓶',
                        data: [13, 11, 14, 13, 10, 14, 18]
                    }, {
                        name: '配件',
                        data: [15, 18, 16, 19, 20, 15, 16]
                    }]
            });

        }
        // 初始化
        funcs.init = function () {
            // 图表
            this.chartFn();
            // 费用支出类别
            funcs.tabFn();
            // 日历
            utils.doubleRiliFn($('#start'), $('#end'));
        }
        funcs.init();
    });
});