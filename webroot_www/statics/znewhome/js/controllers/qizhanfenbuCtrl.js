require(['../statics/znewhome/js/config.js'],function (){
	require(['jquery','utils','highcharts'],function ($,utils,highcharts){
		var funcs={};
		//点击城市 显示站点
		$('.city a').on('click',function (){
			$(this).addClass('active').siblings().removeClass('active');
			// $.ajax({
			// 	url:'',
			// 	type:'GET',
			// 	data:{},
			// 	dataType:'json',
			// 	success:function (data){
					
			// 	}
			// });
		});
		// 初始化
		funcs.init=function (){
			
		}
		funcs.init();
	});
});
