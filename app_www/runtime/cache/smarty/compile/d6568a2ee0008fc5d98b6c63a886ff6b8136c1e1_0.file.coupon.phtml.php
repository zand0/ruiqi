<?php /* Smarty version 3.1.27, created on 2016-12-05 09:21:52
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\modules\Wx\views\ucenter\coupon.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:31115844c130a1b402_67137289%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd6568a2ee0008fc5d98b6c63a886ff6b8136c1e1' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\modules\\Wx\\views\\ucenter\\coupon.phtml',
      1 => 1480900568,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '31115844c130a1b402_67137289',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_5844c130a24b72_40711729',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_5844c130a24b72_40711729')) {
function content_5844c130a24b72_40711729 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '31115844c130a1b402_67137289';
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