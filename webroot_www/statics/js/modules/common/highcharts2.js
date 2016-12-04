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
	    $('#container5').highcharts({
	    	chart: {
	            type: 'column'
	        },
	        title: {
	            text: '燃气库存统计',
	            x: -20 //center
	        },
	        credits:{
			    enabled:false // 禁用版权信息
			},
	        xAxis: {
	        	title:'种类',
	            categories: ["甲烷","乙烷","丙烷","二甲醚"]
	        },
	        yAxis: {
	            title: {
	                text: '重量'
	            },
	            plotLines: [{
	                    value: 0,
	                    width: 1,
	                    color: '#808080'
	                }]
	        },
	        legend: {
	            enabled:false
	        },
	        series: [{"name":"重量","data":[4,5,8,6],"color":"#fec720"}]
	    });
	}
});