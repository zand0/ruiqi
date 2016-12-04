<?php /* Smarty version 3.1.27, created on 2016-11-29 17:48:20
         compiled from "E:\xampp\htdocs\rq\app_www\modules\Wx\views\ucenter\my.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:16036583d4ee49131d3_86006457%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'de3c927c26b3ee5df68f1525adb5a33a2379928e' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\app_www\\modules\\Wx\\views\\ucenter\\my.phtml',
      1 => 1480412898,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '16036583d4ee49131d3_86006457',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_583d4ee491d372_13460117',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_583d4ee491d372_13460117')) {
function content_583d4ee491d372_13460117 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '16036583d4ee49131d3_86006457';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>我的</title>
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
		<img class="icon_png" src="/statics/images/tel.png" alt="">
		<span>手机号</span>
		<!-- 未绑定 -->
		<div v-if="user==''" onclick="location.href='/wx/ucenter/vbind'" class="r_bind">未绑定 <img class="p_r" src="/statics/images/right.png" alt=""></div>
		<!-- 已绑定 -->
		<div v-else class="r_bind" style="display:;">[[user.mobile_phone]]</div>
	</div>
	<div class="else_con">
		<ul>
			<li>
				<img class="icon_png" src="/statics/images/quan.png" alt="">
				<span>优惠券</span>
				<!-- 有优惠券 -->
				<div class="right_icon go_coupon">[[promocount.count]]张<img class="p_r"  src="/statics/images/right.png" alt=""></div>
				<!-- 无优惠券 -->
				<div class="right_icon go_coupon" style="display: none;"><img class="p_r"  src="/statics/images/right.png" alt=""></div>
			</li>
			<li>
				<img class="icon_png" src="/statics/images/dingdan.png" alt="">
				<span>我的订单</span>
				<div class="right_icon go_order">查看全部订单<img class="p_r"  src="/statics/images/right.png" alt=""></div>
			</li>
		</ul>
		
	</div>
	<div class="finish">完成</div>
<?php echo '<script'; ?>
 type="text/javascript">
new Vue({
	el: '#Vbody',
	ready: function() {
		this.$set('currtime',Date.parse(new Date()));
		this.getUser();
		this.getPromoCount();
	},
	methods: {
		//获取订单
		getUser:function(){
			this.$http.get('/wx/ucenter/getuser', function(data, status, request) {
				if(data.status==1){
					this.$set('user', data.data);
				}else{
					this.$set('user', '');
				}
			});
		},
		getPromoCount:function(){
			this.$http.get('/wx/ucenter/promoCount', function(data, status, request) {
				if(data.status==1){
					this.$set('promocount', data.data);
				}else{
					
				}
			});
		},
	}
})
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
?>