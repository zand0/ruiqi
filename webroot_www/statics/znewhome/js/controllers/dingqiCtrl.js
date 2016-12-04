require(['../statics/znewhome/js/config.js'],function (){
	require(['jquery','utils'],function ($,utils){
		var funcs={};
		funcs.tabFn=function (){
			$('.cont .btn').on('click',function (){
				$(this).addClass('active').siblings().removeClass('active');
				$('.tabList .item').eq($(this).index()).show().siblings().hide();
			});
		}
		funcs.addOrderFn=function (){
			$('#addBtn').on('click',function (){
				var n=Number($('#num').val());
				var guige=0;
				var price=0;
				$('.guige a').each(function (){
					if($(this).hasClass('active')){
						guige=parseInt($(this).html());
						price=$(this).data('price');
					}
				});
				if(n){
					var str='<tr class="order">\
							<td class="textCenter">液化气-'+guige+'KG</td>\
							<td class="pl120">'+n+'瓶</td>\
							<td>￥'+price+'</td>\
							<td></td>\
							<td></td>\
							<td>￥'+price*n+'</td>\
							</tr>';
					$(str).appendTo($('.table tbody'));
				}
			});
		}
		// 选择瓶子规格
		funcs.chooseGuigeFn=function (){
			$('.guige a').on('click',function (){
				$(this).addClass('active').siblings().removeClass('active');
			});
		}
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
		// 评价 星
		funcs.evalFn=(function (){
			$('.starBox').each(function (){
				var $this=$(this);
				$(this).children('a').on('click',function (){
					var $index=$(this).index();
					$this.children('a').each(function (){
						if($(this).index()<=$index){
							$(this).addClass('active');
						}else{
							$(this).removeClass('active');
						}
					})
					$this.siblings('input').val($index+1);
				});
			})
		})();
		// 报修 
		funcs.chooseFn=(function (){
			$('.checkList').each(function (){
				var $this=$(this);
				$(this).children('a').on('click',function (){
					var $index=$(this).index();
					if($(this).hasClass('active')){
						$(this).removeClass('active');
						$this.siblings('.checkbox').children('input').eq($index).attr('checked',false);
					}else{
						$this.siblings('.checkbox').children('input').eq($index).attr('checked',true);
						$(this).addClass('active');
					}
				});
			})
		})();
		// 退瓶
		funcs.tuipingFn=(function (){
			$('.checkBtn').each(function (){
				$(this).find('a').on('click',function (){
					if($(this).hasClass('active')){
						$(this).removeClass('active');
						$(this).siblings('input').attr('checked',false);
					}else{
						$(this).siblings('input').attr('checked',true);
						$(this).addClass('active');
					}
				});
			})
		})();
		// 初始化
		funcs.init=function (){
			// 日历
			utils.datepickerFn();
			// 下拉框
			utils.selectmenuFn();
			// 选项卡
			this.tabFn();
			// 添加订单
			this.addOrderFn();
			// 选择规格
			this.chooseGuigeFn();
		}
		funcs.init();
	});
});
