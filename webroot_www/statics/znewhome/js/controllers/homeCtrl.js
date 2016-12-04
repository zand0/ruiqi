require(['../statics/znewhome/js/config.js'],function (){
	require(['jquery','utils'],function ($,utils){
		var funcs={};
		// 紧急用户 押金用户
		funcs.checkFn=(function (){
			$('.choosetype .type a').on('click',function (){
				if($(this).hasClass('active')){
					$(this).removeClass('active');
					$('.checkbox input').eq($(this).index()).attr('checked',false);
				}else{
					$('.checkbox input').eq($(this).index()).attr('checked',true).siblings().attr('checked',false);
					$(this).addClass('active').siblings().removeClass('active');
				}
			});
		})();
		funcs.countFn=(function (){
			$('.guige li').each(function (){
				$(this).find('.number .sub').on('click',function (){
					var n=Number($(this).siblings('input').val());
					if(n&&n>0){
						n--;
						$(this).siblings('input').val(n);
					}
				});
				$(this).find('.number .add').on('click',function (){
					var n=Number($(this).siblings('input').val());
					if(n||n==0){
						n++;
						$(this).siblings('input').val(n);
					}else{
						alert('请输入数量');
					}
				});
			})
		})();
		funcs.chooseFn=(function (){
			$('.guige li').on('click',function (){
				$(this).addClass('active').siblings().removeClass('active');
			});
		})();
		
		// 初始化
		funcs.init=function (){
			
		}
		funcs.init();
	});
});
