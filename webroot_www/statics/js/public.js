$(function (){
	// 弹出层高度设置
	setHeight();
	$(window).on('resize',function (){
		setHeight();
	})
	function setHeight(){
		var h=$(window).height();
		$('.layer').height(h);
	}
	// 选择订气类别
	$('.classify ul li').each(function (){
		$(this).find('.isChecked').on('click',function (){
			if($(this).hasClass('active')){
				$(this).removeClass('active');
			}else{
				$(this).addClass('active');
			}
		});
	});
	// 加减数量
	$('.classify ul li').each(function (){
		$(this).find('.subtract').on('click',function (){
			var val=$(this).siblings('input').val()
			var n=Number(val);
			if(isNaN(n)||val==''){
				alert('请输入数字！');
			}else{
				if(n>0){
					n--;
					$(this).siblings('input').val(n);
				}else{
					return;
				}
			}
		});
		$(this).find('.add').on('click',function (){
			var val=$(this).siblings('input').val()
			var n=Number(val);
			if(isNaN(n)||val==''){
				alert('请输入数字！');
			}else{
				n++;
				$(this).siblings('input').val(n);
			}
		});
	});
	// 提交成功
	$('.loginBtn').on('click',function (){
		$('.layer,.successPop').show();
		ShowDiv()
	});
	//订气页 弹出层关闭按钮
	$('.successPop span').on('click',function (){
		$('.layer,.successPop').hide();
		CloseDiv();
	});

	function ShowDiv(){
	    window.ontouchmove=function(e){
	        e.preventDefault && e.preventDefault();
	        e.returnValue=false;
	        e.stopPropagation && e.stopPropagation();
	        return false;
	  }           
	};
	function CloseDiv(){
	   window.ontouchmove=function(e){
	       e.preventDefault && e.preventDefault();
	       e.returnValue=true;
	       e.stopPropagation && e.stopPropagation();
	       return true;
	   }
	};
})
