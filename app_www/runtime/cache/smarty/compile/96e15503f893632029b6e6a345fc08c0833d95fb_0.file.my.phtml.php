<?php /* Smarty version 3.1.27, created on 2016-12-03 11:34:16
         compiled from "F:\wamp\www\rq\app_www\modules\Wx\views\ucenter\my.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:2607558423d3837bc36_40920020%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '96e15503f893632029b6e6a345fc08c0833d95fb' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\app_www\\modules\\Wx\\views\\ucenter\\my.phtml',
      1 => 1480412898,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2607558423d3837bc36_40920020',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_58423d383c9e36_36185572',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_58423d383c9e36_36185572')) {
function content_58423d383c9e36_36185572 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '2607558423d3837bc36_40920020';
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