$(function (){
	$('.wrap').height($(window).height());
	// 选择按钮
	$('.chooseBtn').on('click',function (){
		$('.chooseBtn i').hide();
		$('.layer,.choosePop').show();
	});
	// 减少
	$('.sub').on('click',function (){
		var val=Number($(this).siblings('.num input').val());
		if(val&&val>=1){
			val--;
			$(this).siblings('.num input').val(val);
		}
	});
	// 增加
	$('.add').on('click',function (){
		var val=Number($(this).siblings('.num input').val());
		if(val||val==0){
			val++;
			$(this).siblings('.num input').val(val);
		}
	});
	// 输入内容不能为小数
	$('.num input').on('keyup',function (){
		var val=Number($(this).val());
		if(val){
			$(this).val(parseInt(val));
		}
	})
	// 弹层取消按钮
	$('.btnBox i').on('click',function (){
		$('.num input').each(function (){
			if($(this).val()>0){
				$('.chooseBtn i').show();
			}
		});
		$('.layer,.choosePop').hide();
	});
	// 弹层确认按钮
	$('.btnBox strong').on('click',function (){
		$('.num input').each(function (){
			if($(this).val()>0){
				$('.layer,.choosePop').hide();
				$('.chooseBtn i').show();
			}else{
				$('.tips').show();
				setTimeout(function (){$('.tips').hide()},1500);
			}
		});
	});
	// 弹层清空按钮
	$('.btnBox span').on('click',function (){
		$('.num input').val(0);
	});
})
