require(['/statics/js/config.js'],function (){
	require(['jquery','utils'],function ($,utils){
		var funcs={};
		funcs.hoverFn=function (){
			$('.positionSort ul li,.leaderName a').hover(function (e){
		        $(this).find('.hoverHead').show();
		    },function (e){
		        if($(this).hasClass('active'))return;
		        $(this).find('.hoverHead').hide();
		    })
		    // 点击空白头像消失
		    if($('.positionSort').length>0){
		        $(document).on('click',function (){
		            $('.positionSort ul li,.leaderName a').each(function (){
		                $(this).find('.hoverHead').hide();
		                $(this).removeClass('active');
		            });
		        });
		    }
		}
		funcs.clickFn=function (){
			$('.positionSort ul li,.leaderName a').on('click',function (e){
		         e.stopPropagation();
		        $('.positionSort ul li,.leaderName a').each(function (){
		            $(this).find('.hoverHead').hide();
		            $(this).removeClass('active');
		        });
		        $(this).addClass('active');
		        $(this).find('.hoverHead').show();
		    });
		}
		// 初始化
		funcs.init=function (){
			// 鼠标经过头像
			this.hoverFn();
			// 点击头像
			this.clickFn();
		}
		funcs.init();
	});
});
