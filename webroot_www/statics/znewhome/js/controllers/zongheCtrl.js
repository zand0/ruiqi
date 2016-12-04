require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils', 'highcharts', 'circliful'], function ($, utils, highcharts, circliful) {
        var funcs = {};
        funcs.chartFn = function () {
            $('#container').highcharts({
                chart: {
                    type: 'column'
                },
                title: {
                    text: '2016年5月1日-5月30日财务数据'
                },
                credits: {
                    enabled: false // 禁用版权信息
                },
                legend: {
                    enabled: false
                },
                colors: ['#5bb85d', '#f2b96a', '#d9544f', '#e4e4e4', '#fcaab6', '#a14e58'],
                xAxis: {
                    categories: ['1日', '2日', '3日', '4日', '5日', '6日', '7日', '8日', '9日', '10日', '11日', '12日', '13日', '14日', '15日', '16日', '17日', '18日', '19日', '20日', '21日', '22日', '23日', '24日', '25日', '26日', '27日', '28日', '29日', '30日']
                },
                yAxis: {
                    gridLineColor: '#eeeeee',
                    title: {
                        text: '万元'
                    },
                    plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        dataLabels: {
                            enabled: true,
                            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                        }
                    }
                },
                tooltip: {
                    formatter: function () {
                        return '<b>' + this.x + '</b><br/>' +
                                this.series.name + ': ' + this.y + '<br/>' +
                                'Total: ' + this.point.stackTotal;
                    }
                },
                series: [{
                        name: '销售收入',
                        data: [5, 3, 4, 7, 10, 5, 3, 4, 7, 2, 5, 3, 4, 7, 10, 5, 3, 4, 7, 2, 5, 3, 4, 7, 10, 5, 3, 4, 7, 2]
                    }, {
                        name: '回流资产',
                        data: [10, 5, 3, 4, 7, 10, 5, 3, 4, 7, 10, 5, 3, 4, 7, 10, 5, 3, 4, 7, 10, 5, 3, 4, 7, 10, 5, 3, 4, 7]
                    }, {
                        name: '应收账款',
                        data: [4, 7, 10, 5, 3, 4, 7, 10, 5, 3, 4, 7, 10, 5, 3, 4, 7, 10, 5, 3, 4, 7, 10, 5, 3, 4, 7, 10, 5, 3]
                    }, {
                        name: '库存资产',
                        data: [3, 4, 4, 6, 5, 3, 4, 4, 6, 5, 3, 4, 4, 6, 5, 3, 4, 4, 6, 5, 3, 4, 4, 6, 5, 3, 4, 4, 6, 5]
                    }, {
                        name: '采购支出',
                        data: [-3, -8, -4, -2, -5, -3, -7, -6, -2, -5, -3, -8, -4, -2, -5, -3, -7, -6, -2, -5, -3, -8, -4, -2, -5, -3, -7, -6, -2, -5]
                    }, {
                        name: '其他费用',
                        data: [-1, -6, -4, -2, -1, -9, -4, -8, -7, -5, -1, -6, -4, -2, -1, -9, -4, -8, -7, -5, -1, -6, -4, -2, -1, -9, -4, -8, -7, -5]
                    }]
            });
        }
        funcs.gzChartFn = function ($obj) {
            var h = $obj.find('.liquid').height();
            console.log(h)
            var ratio = parseFloat($obj.find('.text strong').html()) / 100;
            console.log(ratio)
            if (ratio) {
                $obj.find('.liquid').css({'top': 100 * (1 - ratio)});
                console.log(100 * (1 - ratio))
                $obj.find('.liquid .bgColor').css({'height': (h * ratio - 16)});
                console.log(h * ratio - 16)
            }
        }
        // 初始化
        funcs.init = function () {
            // 财务图表
            this.chartFn();
            // 日历
            utils.doubleRiliFn($('#start'), $('#end'));
            // 选项卡 今日  本周
            utils.tabFn();
            // 环形图表
            $('#myStat').circliful({animationStep: 4.0});
            $('#myStat4').circliful({animationStep: 4.0});
            $('#myStat6').circliful({animationStep: 4.0});
            $('#myStat7').circliful({animationStep: 4.0});
            //占比
            funcs.gzChartFn($('#gzChart'));
            // 灌装图表
            funcs.gzChartFn($('#gzChart2'));
        }
        funcs.init();
    });
});