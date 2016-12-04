require(['../statics/znewhome/js/config.js'],function (){
	require(['jquery','utils','highcharts'],function ($,utils,highcharts){
		var funcs={};
		funcs.tabFn=function (){
			$('#tab ul li').clone().appendTo($('#tab ul'));
			$('#tab ul').css({width:$('#tab ul li').eq(0).innerWidth()*$('#tab ul li').length});
			var W=$('#tab ul').innerWidth()/2,
			iNow=0;
			$('.menu .nextBtn').on('click',function (){
				iNow++;
				move($('#tab ul'),-iNow*$('#tab ul li').eq(0).innerWidth());
			});
			$('.menu .prevBtn').on('click',function (){
				iNow--;
				move($('#tab ul'),-iNow*$('#tab ul li').eq(0).innerWidth());
			});
			var left=0;
			function move(obj,iTarget){
				var start=left;
				var dis=iTarget-start;
				var count=Math.floor(700/30);
				var n=0;
				clearInterval(timer);
				var timer=setInterval(function (){
					n++;
					var a=1-n/count;
					left=start+dis*(1-Math.pow(a,3));
					if(left<0){
						obj.css({left:left%W});
					}else{
						obj.css({left:(left%W-W)%W});
					}
					if(n==count){
						clearInterval(timer);
					}
				},30)
			}
		}
		funcs.chartFn=function (){
			$('.classify a').on('click',function (){
				$(this).addClass('active').siblings().removeClass('active');
				var left=$(this).position().left;
				$('#icon').css({'left':left+$(this).width()-$('#icon').width()/2});
				
			});
			$('#container').highcharts({
		        title: {
		            text: '',
		            x: -20 //center
		        },
		        credits:{
				    enabled:false // 禁用版权信息
				},
		        xAxis: {
		            categories: ["09日","10日","11日","12日","13日"]
		        },
		        yAxis: {
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
		            valueSuffix: '元'
		        },
		        legend: {
		            enabled:false
		        },
		        series: [{"name":"价格","data":[115,412,115,46,112]}]
		    });

		}
		// 初始化
		funcs.init=function (){
			// 轮播图
			this.tabFn();
			// 
			this.chartFn();
		}
		funcs.init();
	});
});
