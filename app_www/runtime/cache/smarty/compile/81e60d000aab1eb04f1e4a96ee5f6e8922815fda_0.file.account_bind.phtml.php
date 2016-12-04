<?php /* Smarty version 3.1.27, created on 2016-12-01 14:40:45
         compiled from "E:\xampp\htdocs\rq\app_www\modules\Wx\views\ucenter\account_bind.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:17555583fc5ed0a5f31_37053775%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '81e60d000aab1eb04f1e4a96ee5f6e8922815fda' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\app_www\\modules\\Wx\\views\\ucenter\\account_bind.phtml',
      1 => 1480574383,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17555583fc5ed0a5f31_37053775',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_583fc5ed0af609_50052751',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_583fc5ed0af609_50052751')) {
function content_583fc5ed0af609_50052751 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '17555583fc5ed0a5f31_37053775';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>账户绑定</title>
	<link rel="stylesheet" href="/statics/css/base.css"/>
	<link rel="stylesheet" href="/statics/css/style.css"/>
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
<body id="Vbody">
	<div class="tel">
		<span>手机号</span><input id="phone" type="tel" placeholder="请输入手机号" maxlength="11">
	</div>
	<div class="code">
		<input id="vcode" type="number" placeholder="请输入验证码"><span>获取验证码</span>
	</div>
	<div v-on:click="bind" class="finish">完成</div>
<?php echo '<script'; ?>
 type="text/javascript">
function GetQueryString(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return  unescape(r[2]); return null;
}
new Vue({
	el: '#Vbody',
	data:{
		
	},
	ready: function() {
		//this.$set('currtime',Date.parse(new Date()));
		//this.getPromotion();
	},
	methods: {
		//获取订单
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
						 //dig("保存成功！");alert
					  }else{
						  //dig(data.msg);
					  }
				  },
				  error:function (){
					  //dig('系统繁忙！');
				  }
			});
		},
		getCode:function(){

		}
	}
})
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
?>