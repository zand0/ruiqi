<?php /* Smarty version 3.1.27, created on 2016-12-07 12:41:58
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\modules\Wx\views\ucenter\account_bind.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:3267758479316b49253_66708119%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ee1cfb26dd133532e1f1d9a16b3135ffcdf9de18' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\modules\\Wx\\views\\ucenter\\account_bind.phtml',
      1 => 1481078845,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3267758479316b49253_66708119',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_58479316b52376_83208745',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_58479316b52376_83208745')) {
function content_58479316b52376_83208745 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '3267758479316b49253_66708119';
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
		<input id="vcode" type="number" placeholder="请输入验证码"><span v-on:click="getCode">获取验证码</span>
	</div>
	<div v-on:click="bind" class="finish">完成</div>
<?php echo '<script'; ?>
 type="text/javascript">

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
			var phone = $("#phone").val();
			this.$http.get('/wx/ucenter/getcode?phone='+phone,function(data, status, request){
				if(data.status==1){

				}else{

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