require(['../statics/znewhome/js/config.js'],function (){
	require(['jquery','utils'],function ($,utils){
		var funcs={};
		funcs.focusFn=(function (){
			$('.inputArea label').each(function (){
				$(this).find('input').on('focus',function (){
					$(this).parent('label').addClass('active').siblings().removeClass('active');
				});
				$(this).find('input').on('blur',function (){
					$(this).parent('label').removeClass('active');
				});
			})
		})();
		// 初始化
		funcs.init=function (){
			
		}
		funcs.init();
	});
});
