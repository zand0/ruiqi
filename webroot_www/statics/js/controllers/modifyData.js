require(['/statics/js/config.js'],function (){
	require(['jquery','utils','modules/common/showLocation','modules/common/imgOnly'],function ($,utils,location,uploader){
		var funcs={};
		funcs.addTelFn=function (){
		    $('#addTel').on('click',function (){
		        $('<div class="inputBox placeholder"><input type="text" style="width:260px;"><i></i></div>').appendTo($(this).siblings('.addDelete'));
		    });
		}
		funcs.addAreaFn=function (){
			$('#addAddress').on('click',function (){
		        $('<div class="inputBox  placeholder"><input type="text" style="width:435px;"><span>输入详细地址</span><i></i></div>').appendTo($(this).siblings('.addDelete'))
		    });
		}
		funcs.deleteFn=function (){
			$(document).on('click','.addDelete .inputBox i',function (){
		        $(this).parents('.inputBox').remove();
		    });
		}
		funcs.uploadFn=function (){
			 //上传
			uploader.upload($('.uploadTouxiang'),'#uploadVBtn','','更换头像');//上传区域父级，上传按钮，后台处理接口地址，上传按钮文字
		}
		// 初始化
		funcs.init=function (){
			// 添加电话
			this.addTelFn();
			// 添加地区
			this.addAreaFn();
			// 删除
			this.deleteFn();
			// 地区
			location.init({province:'河南省',city:'南阳市',town:'镇平县'});//初始化省市区
			// 上传图片
			this.uploadFn();
		}
		funcs.init();
	});
});
