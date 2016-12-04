<?php /* Smarty version 3.1.27, created on 2016-12-04 16:49:17
         compiled from "F:\wamp\www\rq\app_www\modules\Wx\views\ordermanage\order_management.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:39565843d88de9f157_32160338%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c012f864d13084c311ff834ef38bc6b7bab50d57' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\app_www\\modules\\Wx\\views\\ordermanage\\order_management.phtml',
      1 => 1480409253,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '39565843d88de9f157_32160338',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_5843d88e0955a3_52065311',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_5843d88e0955a3_52065311')) {
function content_5843d88e0955a3_52065311 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '39565843d88de9f157_32160338';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>订单管理</title>
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
	<div class="t_manager">
		<ul>
			<li class="blue_bottom"><span class="r_tip blue_text">全部</span></li>
			<li><span class="r_tip">待收货</span></li>
			<li><span class="r_tip">已完成</span></li>
			<li><span>已评价</span></li>
		</ul>
	</div>
	<!--  全部-->
	<div class="all">
		<div v-for="order in orders" class="all_detail">
			<p class="all_head">
				<span>订单号：</span>
				<span>[[order.order_sn]]</span>
				<span>已完成</span>
			</p>
			<p>
				<span class="st">商品：</span>
				<span class="ell"><template v-for="info in order.order_info">[[info.goods_name]],</template> </span>
			</p>
			<p class="f_border">
				<span>金额：</span>
				<span>￥[[order.money]]</span>
				<span class="l_">货到付款</span>
			</p>
			<div onclick="location.href='/wx/ordermanage/comment?orderid=[[order.order_id]]'" class="go_eval">去评价</div>
		</div>
		
	</div>
	<!--  待收货-->
	<div class="wait_goods">
		
		<template v-for="order in orders">
		<div v-if="order.status==2"  class="all_detail go_ps">
			<p class="all_head">
				<span>订单号：</span>
				<span>[[order.order_sn]]</span>
				<span>配送中</span>
			</p>
			<p>
				<span class="st">商品：</span>
				<span class="ell">
				<template v-for="info in order.order_info">[[info.goods_name]],</template>
				...
				</span>
			</p>
			<p>
				<span>金额：</span>
				<span>￥[[order.money]]</span>
				<span class="l_">货到付款</span>
			</p>
		
		</div></template>
		
	</div>
	<!-- 已完成 -->
	<div class="finish_order">
		
		<template v-for="order in orders">
		<div v-if="order.status==4"  class="all_detail">
			<p class="all_head">
				<span>订单号：</span>
				<span>[[order.order_sn]]</span>
				<span>已完成</span>
			</p>
			<p>
				<span class="st">商品：</span>
				<span class="ell"><template v-for="info in order.order_info">[[info.goods_name]],</template>...</span>
			</p>
			<p class="f_border">
				<span>金额：</span>
				<span>￥[[order.money]]</span>
				<span class="l_">货到付款</span>
			</p>
			<div onclick="location.href='/wx/ordermanage/comment?orderid=[[order.order_id]]'" class="go_eval">去评价</div>
		
		</div></template>
	</div>
	<!-- 已评价 -->
	<div class="finish_eval">
		
		<template v-for="order in orders">
		<div v-if="order.status==6"  class="all_detail go_detail">
			<p class="all_head">
				<span>订单号：</span>
				<span>[[order.order_sn]]</span>
				<span>已评价</span>
			</p>
			<p>
				<span class="st">商品：</span>
				<span class="ell"><template v-for="info in order.order_info">[[info.goods_name]],</template></span>
			</p>
			<p>
				<span>金额：</span>
				<span>￥[[order.money]]</span>
				<span class="l_">货到付款</span>
			</p>
		</div></template>
	</div>
<?php echo '<script'; ?>
 type="text/javascript">
new Vue({
	el: '#Vbody',
	ready: function() {
		this.getOrder();
	},
	methods: {
		//获取订单
		getOrder:function(){
			this.$http.get('/wx/ordermanage/getlist', function(data, status, request) {
				
				if(data.status==1){
					this.$set('orders', data.data);
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