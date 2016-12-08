<?php /* Smarty version 3.1.27, created on 2016-12-08 12:25:36
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\modules\Wx\views\ucenter\account_bind.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:119335848e0c03900d7_01434969%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ee1cfb26dd133532e1f1d9a16b3135ffcdf9de18' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\modules\\Wx\\views\\ucenter\\account_bind.phtml',
      1 => 1481171093,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '119335848e0c03900d7_01434969',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_5848e0c03a18c0_04952554',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_5848e0c03a18c0_04952554')) {
function content_5848e0c03a18c0_04952554 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '119335848e0c03900d7_01434969';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>账户绑定</title>
	<link rel="stylesheet" href="/statics/css/base.css"/>
	<!-- <link rel="stylesheet" href="/statics/css/style.css"/> -->
	<?php echo '<script'; ?>
 src="/statics/js/jquery-1.8.3.min.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="/statics/js/main.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 type="text/javascript" src="/statics/js/Vue.min.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 type="text/javascript" src="/statics/js/vue-resource.min.js"><?php echo '</script'; ?>
>
	<?php echo $_smarty_tpl->getSubTemplate ('../header.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

</head>
<body id="Vbody" style="overflow:hidden;width:100%">
	<div style="overflow:hidden;width:100%">
		<div class="tel">
			<span>手机号</span><input id="phone" type="tel" placeholder="请输入手机号" maxlength="11">
		</div>
		<div class="code">
			<input id="vcode" type="number" placeholder="请输入验证码"><span id="getCode" v-on:click="getCode">获取验证码</span>
		</div>
		<div v-on:click="bind" class="finish">完成</div>
	</div>
	
<?php echo '<script'; ?>
 type="text/javascript">
var validCode=true;

$(function  () {
	//获取短信验证码
	// $("#getCode").click (function  () {
	// 	var time=30;
	// 	var code=$(this);
	// 	if (validCode && $("#phone").val()) {
	// 		validCode=false;
	// 		//code.addClass("msgs1");
	// 	var t=setInterval(function  () {
	// 			time--;
	// 			code.html('等待'+time+"秒");
	// 			if (time==0) {
	// 				clearInterval(t);
	// 			code.html("获取验证码");
	// 				validCode=true;
	// 			//code.removeClass("msgs1");

	// 			}
	// 		},1000)
	// 	}
	// })
})
new Vue({
	el: '#Vbody',
	data:{
		
	},
	ready: function() {
		//this.$set('currtime',Date.parse(new Date()));
		//this.getPromotion();
	},
	methods: {
		//绑定方法
		bind:function(){
			var phone = $('#phone').val();
			var vcode = $("#vcode").val();
			var openid = GetQueryString('openid');
			var fromurl = GetQueryString('fromurl');
			var params = {
					phone:phone,
					vcode:vcode,
					openid:openid,
					fromurl:fromurl
			};
			$.ajax({
				  type: 'GET',
				  url: '/wx/ucenter/bind',
				  data: params,
				  cache:false,  
				  dataType:'json',
				  success: function(data){
					  if(data.status==1){
						dig("绑定成功！");
					  }else{
						dig(data.msg);
					  }
				  },
				  error:function (){
					  //dig('系统繁忙！');
				  }
			});
		},
		//获取验证码接口
		getCode:function(){
			if(!validCode){
				return;
			}
			var phone = $("#phone").val();
			this.$http.get('/wx/ucenter/getcode?phone='+phone,function(data, status, request){
				if(data.status==1){
					//获取短信验证码
					//$("#getCode").click (function  () {
						var time=30;
						var code=$("#getCode");
						if (validCode && $("#phone").val()) {
							validCode=false;
							//code.addClass("msgs1");
						var t=setInterval(function  () {
								time--;
								code.html('等待'+time+"秒");
								if (time==0) {
									clearInterval(t);
								code.html("获取验证码");
									validCode=true;
								//code.removeClass("msgs1");

								}
							},1000)
						}
					//});
					dig(data.msg);
				}else{
					dig(data.msg);
				}
			});
		}
	}
})
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
?>