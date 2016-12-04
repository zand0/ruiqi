require(['../statics/znewhome/js/config.js'],function (){
	require(['jquery','utils'],function ($,utils,PageNav){
		var funcs={};

		funcs.init=function (){
			// 双日历
			utils.doubleRiliFn($('#start'),$('#end'));
			// 分页--订单明细
			this.pageFn();
			// 选项卡 今日  本周
			utils.tabFn();
			// 订单状态选择
			utils.chooseFn();
		}

		funcs.init();
	});
});
