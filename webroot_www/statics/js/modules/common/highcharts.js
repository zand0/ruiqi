/**
 * 图表
 * 
 */
define(['jquery','highCharts'],function (){
	var rets={};
	// rets.highChartsFn=function (id,json){
	// 	$(id).highcharts({                   //图表展示容器，与div的id保持一致
	//         chart: {
	//             type: 'column'                         //指定图表的类型，默认是折线图（line）
	//         },
	//         title: {
	//             text: 'My first Highcharts chart'      //指定图表标题
	//         },
	//         xAxis: {
	//             categories: ['my', 'first', 'chart']   //指定x轴分组
	//         },
	//         yAxis: {
	//             title: {
	//                 text: 'something'                  //指定y轴的标题
	//             }
	//         },
	//         series: [json]
	//     });
	// }
funcs.chartFn=function (){
		    $('#container').highcharts({
		    	chart: {
		            type: 'pie'
		        },
		        title: {
		            text: '本月销售金额统计',
		            x: -20 //center
		        },
		        credits:{
				    enabled:false // 禁用版权信息
				},
				plotOptions: {
		            pie: {
		                allowPointSelect: true,
		                cursor: 'pointer',
		                dataLabels: {
		                    enabled: true,
		                    color: '#666',
		                    connectorColor: '#666',
		                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
		                }
		            }
		        },
		        colors: ['#98d7a0', '#f7de6a', '#f8b566','#72dad9','#f8825e','#46bddd'],
		        legend: {
		            enabled:false
		        },
		        tooltip: {
		        	valueDecimals: 2,
		            valueSuffix: '元'
		        },
		        series: [{
		        	"name":"销售金额",
		        	"data":[
		        	['朝阳四惠店',1550],
		        	['密云万达店',2380],
		        	['海淀黄庄店',1260],
		        	['通州家园店',1740]
		        	]
		        }]
		    });
		}


});