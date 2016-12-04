<?php /* Smarty version 3.1.27, created on 2016-12-03 11:40:04
         compiled from "F:\wamp\www\rq\app_www\modules\Wx\views\ucenter\coupon.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:268958423e942701a8_63180305%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a123ee84f30ac56bcb228a86141ae0c1e97df121' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\app_www\\modules\\Wx\\views\\ucenter\\coupon.phtml',
      1 => 1480736386,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '268958423e942701a8_63180305',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_58423e9429b127_24274669',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_58423e9429b127_24274669')) {
function content_58423e9429b127_24274669 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '268958423e942701a8_63180305';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>优惠券</title>
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

	<?php echo '<script'; ?>
 type="text/javascript">
		var jump=GetQueryString('from');

	<?php echo '</script'; ?>
>
</head>

<body id="Vbody">
	<div class="t_coupon">
		<ul>
			<li class="blue_bottom"><span class="r_tip blue_text">未使用</span></li>
			<li><span class="r_tip">已使用</span></li>
			<li><span>已过期</span></li>
		</ul>
	</div>
	<!-- 没有优惠券的情况 -->
	<div class="coupon_con">
		<div class="blank">
			<img src="/statics/images/blank.png" alt="">
			<p>订单完成后分享即可获取优惠券！</p>
		</div>
	</div>
	<!-- 未使用优惠券 -->
	<template v-for="p in promo">
	<div onclick="if(jump=='order')location.href='/wx/order/vorder?promoid=[[p.id]]&pname=[[p.price]]&limit=[[p.money]]'" v-if="p.status==0 && (p.time_end > currtime || p.time_end==0)" class="nouse_c">
		<img src="/statics/images/nouse.png" alt="" style="width: 100%;height: 100%">
		<div class="details_c">
			<div class="l_m">
				<span class="yang">￥</span>
				<span class="num_total">[[p.price]]</span>
			</div>
			<div class="validity">截止[[p.time_end==0?'  永久':p.time_end_str]]</div>
		</div>
	</div>
	</template>
	<!-- 已使用优惠券 -->
	
	<template v-for="p in promo">
	<div v-if="p.status==1" class="use_c">
		<img src="/statics/images/use.png" alt="" style="width: 100%;height: 100%">
		<div class="details_c">
			<div class="l_m">
				<span class="yang">￥</span>
				<span class="num_total">2.0</span>
			</div>
			<div class="validity">截止[[p.time_end==0?'  永久':p.time_end_str]]</div>
		</div>
	</div>
	</template>
	
	<!-- 已过期惠券 -->
	
	<template v-for="p in promo">
	<div v-if="p.time_end!=0 && p.time_end < currtime" class="delay_c">
		<img src="/statics/images/delay.png" alt="" style="width: 100%;height: 100%">
		<div class="details_c">
			<div class="l_m">
				<span class="yang">￥</span>
				<span class="num_total">2.0</span>
			</div>
			<div class="validity">截止[[p.time_end==0?'  永久':p.time_end_str]]</div>
		</div>
	</div>
	</template>
	
<?php echo '<script'; ?>
 type="text/javascript">
new Vue({
	el: '#Vbody',
	ready: function() {
		this.$set('currtime',Date.parse(new Date()));
		this.getPromotion();
	},
	methods: {
		//获取订单
		getPromotion:function(){
			
			this.$http.get('/wx/ucenter/getpromotion', function(data, status, request) {
				if(data.status==1){
					this.$set('promo', data.data);
				}else{
					
				}
				
				//this.redirect(data);
				//console.log(data);
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