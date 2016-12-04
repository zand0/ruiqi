require(['../statics/znewhome/js/config.js'],function (){
	require(['jquery','utils','highcharts'],function ($,utils,highcharts){
		var funcs={};
		funcs.chartFn=function (){
			$('#container').highcharts({
		        title: {
		            text: '',
		            x: -20 //center
		        },
		        credits:{
				    enabled:false // 禁用版权信息
				},
				colors:['#ddc8b5'],
				chart: {
		            backgroundColor: '#fff8f2',
		            type: 'line'
		        },
		        xAxis: {
		        	gridLineWidth: 0,
		        	tickWidth: 0,  
		        	lineColor : '#fff8f2',
		        	labels:{
		        		enabled:false
		        	},
		            categories: ["09日","10日","11日","12日","13日"]
		        },
		        yAxis: {
		        	gridLineWidth: 0,
		        	labels:{
		        		enabled:false
		        	},
		            title: {
		                text: ''
		            },
		            plotLines: [{
		                    value: 0,
		                    width: 1,
		                    color: '#808080'
		                }]
		        },
		        tooltip: {
		            valueSuffix: '元',
		            backgroundColor:'#f94301',
		            borderColor:'#f94301',
		            shadow:false,
		            style:{color:'#fff'}
		        },
		        legend: {
		            enabled:false
		        },
		        series: [{
		        	"name":"销售额",
         			"data":[115,120,119,110,112]
         		}]
		    });
		}
		funcs.chart2Fn=function (){
			$('#container2').highcharts({
		        title: {
		            text: '',
		            x: -20 //center
		        },
		        credits:{
				    enabled:false // 禁用版权信息
				},
				colors:['#cde2b9'],
				chart: {
		            backgroundColor: '#f1fde7',
		            type: 'line'
		        },
		        xAxis: {
		        	gridLineWidth: 0,
		        	tickWidth: 0,  
		        	lineColor : '#f1fde7',
		        	labels:{
		        		enabled:false
		        	},
		            categories: ["09日","10日","11日","12日","13日"]
		        },
		        yAxis: {
		        	gridLineWidth: 0,
		        	labels:{
		        		enabled:false
		        	},
		            title: {
		                text: ''
		            },
		            plotLines: [{
		                    value: 0,
		                    width: 1,
		                    color: '#808080'
		                }]
		        },
		        tooltip: {
		            valueSuffix: '个',
		            backgroundColor:'#f94301',
		            borderColor:'#f94301',
		            shadow:false,
		            style:{color:'#fff',fontSize:'12px',padding:'4px'}
		        },
		        legend: {
		            enabled:false
		        },
		        series: [{
		        	"name":"客户",
         			"data":[40,45,42,50,38]
         		}]
		    });
		}
		funcs.chart3Fn=function (){
			$('#container3').highcharts({
		        title: {
		            text: '',
		            x: -20 //center
		        },
		        credits:{
				    enabled:false // 禁用版权信息
				},
				colors:['#cee2fa'],
				chart: {
		            backgroundColor: '#e9f3ff',
		            type: 'line'
		        },
		        xAxis: {
		        	gridLineWidth: 0,
		        	tickWidth: 0,  
		        	lineColor : '#e9f3ff',
		        	labels:{
		        		enabled:false
		        	},
		            categories: ["09日","10日","11日","12日","13日"]
		        },
		        yAxis: {
		        	gridLineWidth: 0,
		        	labels:{
		        		enabled:false
		        	},
		            title: {
		                text: ''
		            },
		            plotLines: [{
		                    value: 0,
		                    width: 1,
		                    color: '#808080'
		                }]
		        },
		        tooltip: {
		            valueSuffix: '个',
		            backgroundColor:'#f94301',
		            borderColor:'#f94301',
		            shadow:false,
		            style:{color:'#fff',fontSize:'12px',padding:'4px'}
		        },
		        legend: {
		            enabled:false
		        },
		        series: [{
		        	"name":"客户",
         			"data":[40,45,42,50,38]
         		}]
		    });
		}
		funcs.init=function (){
			// 统计汇总-财务
			this.chartFn();
			// 统计汇总-客户
			this.chart2Fn();
			// 统计汇总-库存
			this.chart3Fn();
			// 统计汇总页--右侧选项卡  最近7天 14天
			utils.tabFn();
			// 双日历
			utils.doubleRiliFn($('#start'),$('#end'));
		}
		funcs.init();
	});
});
